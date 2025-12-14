const dbs = require('./database/index');

/**
 * Helper functions for HTTP polling mode
 * Stores QR codes and connection data in database instead of emitting via Socket.IO
 */

async function saveQrCode(device, qrCode) {
    try {
        const query = `UPDATE devices SET qr_code = ?, qr_generated_at = NOW() WHERE body = ?`;
        await dbs.dbQuery(query, [qrCode, device]);
        console.log(`QR code saved for device: ${device}`);
        return true;
    } catch (error) {
        console.error(`Error saving QR code for ${device}:`, error);
        return false;
    }
}

async function savePairingCode(device, code) {
    try {
        const query = `UPDATE devices SET pairing_code = ?, qr_generated_at = NOW() WHERE body = ?`;
        await dbs.dbQuery(query, [code, device]);
        console.log(`Pairing code saved for device: ${device}`);
        return true;
    } catch (error) {
        console.error(`Error saving pairing code for ${device}:`, error);
        return false;
    }
}

async function saveConnectionData(device, connectionData) {
    try {
        const dataJson = JSON.stringify(connectionData);
        const query = `UPDATE devices SET connection_data = ?, qr_code = NULL, pairing_code = NULL WHERE body = ?`;
        await dbs.dbQuery(query, [dataJson, device]);
        console.log(`Connection data saved for device: ${device}`);
        return true;
    } catch (error) {
        console.error(`Error saving connection data for ${device}:`, error);
        return false;
    }
}

async function clearConnectionData(device) {
    try {
        const query = `UPDATE devices SET qr_code = NULL, connection_data = NULL, pairing_code = NULL, qr_generated_at = NULL WHERE body = ?`;
        await dbs.dbQuery(query, [device]);
        console.log(`Connection data cleared for device: ${device}`);
        return true;
    } catch (error) {
        console.error(`Error clearing connection data for ${device}:`, error);
        return false;
    }
}

module.exports = {
    saveQrCode,
    savePairingCode,
    saveConnectionData,
    clearConnectionData
};
