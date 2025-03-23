const axios = require('axios');
const xmlbuilder = require('xmlbuilder');

async function openDoor(ip, user = 'admin', pass = '12345', doorNo = 1) {
  const xml = xmlbuilder
    .create('RemoteControlDoor')
    .ele('doorNo', doorNo).up()
    .ele('cmd', 'open')
    .end({ pretty: true });

  try {
    const url = `http://${ip}/ISAPI/AccessControl/RemoteControl/door/${doorNo}`;

    const res = await axios.put(url, xml, {
      auth: { username: user, password: pass },
      headers: {
        'Content-Type': 'application/xml',
        'Content-Length': Buffer.byteLength(xml)
      },
      timeout: 5000
    });

    console.log(`✅ Door opened: ${res.status}`);
    return true;
  } catch (err) {
    console.error('❌ Failed to open door:', err.message);
    return false;
  }
}

module.exports = { openDoor };
