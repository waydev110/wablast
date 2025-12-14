"use strict";

const wa = require("./server/whatsapp");
const fs = require("fs");
const dbs = require('./server/database/index');
require("dotenv").config();
const lib = require("./server/lib");
global.log = lib.log;

/**
 * EXPRESS FOR ROUTING
 */
const express = require("express");
const app = express();
const http = require("http");
const server = http.createServer(app);

/**
 * HTTP POLLING MODE - NO SOCKET.IO FOR SHARED HOSTING COMPATIBILITY
 * QR codes and connection status are stored in database and polled via HTTP
 */
const port = process.env.PORT_NODE;

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

app.use(bodyParser.json());
app.use(express.static("src/public"));
app.use(require("./server/router"));

// HTTP Polling endpoints for QR code and connection status
app.post("/start-connection", async (req, res) => {
  try {
    const { device } = req.body;
    if (!device) {
      return res.status(400).json({ status: false, msg: "Device number required" });
    }
    // Start connection process (non-blocking)
    wa.connectToWhatsApp(device, null, false);
    res.json({ status: true, msg: "Connection initiated" });
  } catch (error) {
    console.error("Start connection error:", error);
    res.status(500).json({ status: false, msg: error.message });
  }
});

app.post("/connect-via-code", async (req, res) => {
  try {
    const { device } = req.body;
    if (!device) {
      return res.status(400).json({ status: false, msg: "Device number required" });
    }
    // Start connection with pairing code
    wa.connectToWhatsApp(device, null, true);
    res.json({ status: true, msg: "Pairing code generation initiated" });
  } catch (error) {
    console.error("Connect via code error:", error);
    res.status(500).json({ status: false, msg: error.message });
  }
});

app.get("/poll-connection/:device", async (req, res) => {
  try {
    const { device } = req.params;
    const query = `SELECT qr_code, connection_data, status, qr_generated_at, pairing_code FROM devices WHERE body = '${device}'`;
    const result = await dbs.dbQuery(query);
    
    if (result && result.length > 0) {
      const deviceData = result[0];
      res.json({
        status: true,
        data: {
          qr_code: deviceData.qr_code,
          connection_data: deviceData.connection_data ? JSON.parse(deviceData.connection_data) : null,
          device_status: deviceData.status,
          qr_generated_at: deviceData.qr_generated_at,
          pairing_code: deviceData.pairing_code
        }
      });
    } else {
      res.json({ status: false, msg: "Device not found" });
    }
  } catch (error) {
    console.error("Poll connection error:", error);
    res.status(500).json({ status: false, msg: error.message });
  }
});

app.post("/logout-device-http", async (req, res) => {
  try {
    const { device } = req.body;
    if (!device) {
      return res.status(400).json({ status: false, msg: "Device number required" });
    }
    wa.deleteCredentials(device, null);
    res.json({ status: true, msg: "Logout initiated" });
  } catch (error) {
    console.error("Logout device error:", error);
    res.status(500).json({ status: false, msg: error.message });
  }
});

server.listen(port, () => {
    console.log(`Server running in HTTP POLLING mode on port: ${port}`);
});

dbs.db.query("SELECT * FROM devices WHERE status = 'Connected'", (err, results) => {
  if (err) {
    console.error('Error executing query:', err);
  }
  results.forEach(row => {
    const number = row.body;
    if (/^\d+$/.test(number)) {
      wa.connectToWhatsApp(number);
    }
  });
});
