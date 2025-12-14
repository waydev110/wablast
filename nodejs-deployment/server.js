"use strict";

const fs = require("fs");
const path = require("path");

// Setup logging to file
const logFile = path.join(__dirname, 'startup.log');
function logToFile(message) {
  const timestamp = new Date().toISOString();
  const logMessage = `[${timestamp}] ${message}\n`;
  fs.appendFileSync(logFile, logMessage);
  console.log(message);
}

// Clear previous log on startup
try {
  fs.writeFileSync(logFile, `=== Server Starting at ${new Date().toISOString()} ===\n`);
} catch (e) {
  console.error("Cannot create log file:", e);
}

logToFile("Loading modules...");

let wa, dbs, lib;
try {
  wa = require("./server/whatsapp");
  logToFile("✓ whatsapp.js loaded");
} catch (e) {
  logToFile("✗ Failed to load whatsapp.js: " + e.message);
  throw e;
}

try {
  dbs = require('./server/database/index');
  logToFile("✓ database loaded");
} catch (e) {
  logToFile("✗ Failed to load database: " + e.message);
  throw e;
}

try {
  require("dotenv").config();
  logToFile("✓ dotenv loaded");
  logToFile(`Environment: DB_HOST=${process.env.DB_HOST}, DB_DATABASE=${process.env.DB_DATABASE}, DB_USERNAME=${process.env.DB_USERNAME}`);
} catch (e) {
  logToFile("✗ Failed to load dotenv: " + e.message);
}

try {
  lib = require("./server/lib");
  global.log = lib.log;
  logToFile("✓ lib loaded");
} catch (e) {
  logToFile("✗ Failed to load lib: " + e.message);
  throw e;
}

/**
 * EXPRESS FOR ROUTING
 */
logToFile("Setting up Express...");
const express = require("express");
const app = express();
const http = require("http");
const server = http.createServer(app);
logToFile("✓ Express initialized");

/**
 * HTTP POLLING MODE - NO SOCKET.IO FOR SHARED HOSTING COMPATIBILITY
 * QR codes and connection status are stored in database and polled via HTTP
 */
// Use Passenger port (for cPanel) or PORT_NODE (for localhost)
const port = process.env.PORT || process.env.PORT_NODE || 3100;

app.use((req, res, next) => {
  res.set("Cache-Control", "no-store");
  next();
});

const bodyParser = require("body-parser");

app.use(
  bodyParser.urlencoded({
    extended: false,
    limit: "50mb",
    parameterLimit: 100000,
  })
);
logToFile("✓ Body parser configured");

try {
  app.use(express.static("src/public"));
  logToFile("✓ Static files configured");
} catch (e) {
  logToFile("✗ Static files error: " + e.message);
}

try {
  app.use(require("./server/router"));
  logToFile("✓ Router loaded");
} catch (e) {
  logToFile("✗ Router loading failed: " + e.message);
  throw e;
}

// All endpoints now handled in server/router/index.js
logToFile(`Starting server on port: ${port}...`);

server.listen(port, () => {
    logToFile(`✓✓✓ Server running in HTTP POLLING mode on port: ${port}`);
    logToFile("Server is READY to accept connections");
    
    // Auto-reconnect connected devices after successful server start
    try {
      dbs.db.query("SELECT * FROM devices WHERE status = 'Connected'", (err, results) => {
        if (err) {
          logToFile('Database query error: ' + err.message);
          return; // Continue running even if query fails
        }
        if (results && Array.isArray(results)) {
          logToFile(`Found ${results.length} connected devices to reconnect`);
          results.forEach(row => {
            const number = row.body;
            if (/^\d+$/.test(number)) {
              logToFile(`Auto-connecting device: ${number}`);
              wa.connectToWhatsApp(number).catch(e => logToFile(`Failed to connect ${number}: ${e.message}`));
            }
          });
        } else {
          logToFile("No connected devices found");
        }
      });
    } catch (e) {
      logToFile("Error in auto-reconnect: " + e.message);
    }
});

server.on('error', (err) => {
  logToFile(`✗✗✗ Server error: ${err.message}`);
  logToFile(`Error code: ${err.code}`);
  if (err.code === 'EADDRINUSE') {
    logToFile(`Port ${port} is already in use!`);
  }
});
