const express = require('express');
const clockControl = require('./api/clockControl'); // this is the router we built

const app = express();
app.use(express.json());

// Mount the control API on "/clock"
app.use('/clock', clockControl);

// Start the control API server on port 3000
app.listen(3000, () => {
  console.log('🧠 Clock control API running at http://localhost:3000');
});
