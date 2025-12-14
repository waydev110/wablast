"use strict";
const cache = require("./../lib/cache");
const express = require("express");
const router = express.Router();

/**
 * THIS IS MAIN ROUTER
 */
const controllers = require("../controllers");
//const store = require("../controllers/store");
const { initialize } = require("../whatsapp");
const { sendBlastMessage } = require("../controllers/blast");
const { clearChatSession } = require("../controllers/incomingMessage");
//const CryptoJS = require("crypto-js");
const {
  checkDestination,
  checkConnectionBeforeBlast,
} = require("../lib/middleware");
//const validation = process.env.AUTH;

// sendFile will from here. Delete or comment if no use anymore
router.get("/", (req, res) => {
  const path = require("path");
  res.sendFile(path.join(__dirname, "../../public/index.html"));
});

router.post("/backend-logout", controllers.deleteCredentials);

router.post("/backend-generate-qr", controllers.createInstance);

router.post("/backend-initialize", initialize);

router.post(
  "/backend-send-list",
  checkDestination,
  controllers.sendListMessage
);

router.post(
  "/backend-send-template",
  checkDestination,
  controllers.sendTemplateMessage
);

router.post(
  "/backend-send-button",
  checkDestination,
  controllers.sendButtonMessage
);
router.post("/backend-send-media", checkDestination, controllers.sendMedia);

router.post("/backend-send-sticker", checkDestination, controllers.sendSticker);

router.post("/backend-send-text", checkDestination, controllers.sendText);

router.post("/backend-send-location", checkDestination, controllers.sendLocation);

router.post("/backend-send-vcard", checkDestination, controllers.sendVcard);

router.post("/backend-send-poll", checkDestination, controllers.sendPoll);

router.post("/backend-getgroups", controllers.fetchGroups);

router.post("/backend-blast", checkConnectionBeforeBlast, sendBlastMessage);
router.post("/backend-logout-device", controllers.logoutDevice);

router.post("/backend-check-number", controllers.checkNumber);

router.post("/backend-clearCache", async (req, res) => {
  clearChatSession();
  await controllers.sendAvailable(req, res);
  await cache.myCache.flushAll();
  console.log("Cache cleared");
  return res.json({ status: "success" });
});

// HTTP Polling endpoints for shared hosting compatibility
const wa = require("../whatsapp");
const { dbQuery } = require("../database");

router.post("/start-connection", async (req, res) => {
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

router.post("/connect-via-code", async (req, res) => {
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

router.get("/poll-connection/:device", async (req, res) => {
  try {
    const { device } = req.params;
    const query = `SELECT qr_code, connection_data, status, qr_generated_at, pairing_code FROM devices WHERE body = '${device}'`;
    const result = await dbQuery(query);
    
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

router.post("/logout-device-http", async (req, res) => {
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

// STORE
//router.post('/backend-store-chats', store.chats)

module.exports = router;
