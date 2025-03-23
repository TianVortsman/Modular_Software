const fs = require('fs');
const path = require('path');

function extractJsonFromMultipart(body) {
  const match = body.match(/name="event_log"\s*\r?\n\r?\n([\s\S]+?)\r?\n--/);
  if (!match) return null;

  try {
    return JSON.parse(match[1]);
  } catch (err) {
    console.error('❌ Failed to parse JSON:', err.message);
    return null;
  }
}

// 🧠 Map of major + sub event types to human-readable meanings
const eventTypeMap = {
  '5-21': 'Door Open',
  '5-22': 'Door Close',
  '5-75': 'Clock-In',
  '3': 'Menu Access',
  '1': 'Alarm Triggered'
};

function resolveEventType(major, sub) {
  if (major && sub) {
    const combined = `${major}-${sub}`;
    return eventTypeMap[combined] || 'Access Event';
  }
  if (major) {
    return eventTypeMap[`${major}`] || 'Unknown Event';
  }
  return 'Unknown Event';
}

const { detectCapabilities } = require('../lib/detectCapabilities');

async function registerClockDevice(rawBody, accountNumber) {
  const event = extractJsonFromMultipart(rawBody);
  if (!event) return;

  const device = event.AccessControllerEvent || {};
  const ip = event.ipAddress;
  const serial = event.deviceID || device.deviceID || 'unknown';

  const caps = await detectCapabilities(ip);
  console.log(`🧠 Capabilities for ${serial}:`, caps);

  // TODO: Save caps to DB under this clock/device
}


async function handleClockData(rawBody, accountNumber) {
  const event = extractJsonFromMultipart(rawBody);
  if (!event) return;

  const device = event.AccessControllerEvent || {};

  const major = device.majorEventType;
  const sub = device.subEventType;
  const eventLabel = resolveEventType(major, sub);

  const clocking = {
    employeeId: device.employeeNoString,
    name: device.name || null,
    eventTime: event.dateTime,
    eventType: event.eventType,
    attendanceStatus: device.attendanceStatus,
    verifyMode: device.currentVerifyMode,
    serialNo: device.serialNo,
    majorEventType: major,
    subEventType: sub,
    doorNo: device.doorNo || null,
    eventLabel
  };

  console.log(`🕒 Clocking from ${accountNumber}:`, clocking);

  // 🔧 TODO: Connect to customer DB and insert into time_logs
}

module.exports = {
  registerClockDevice,
  handleClockData
};
