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

// All endpoints now handled in server/router/index.js

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
