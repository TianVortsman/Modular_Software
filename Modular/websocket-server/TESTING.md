# WhatsApp Web Integration Testing Guide

## 🚀 Quick Start Testing

### 1. Environment Setup

1. **Create `.env` file** in `Modular/websocket-server/`:
```bash
cp env.example .env
```

2. **Update `.env` with your credentials**:
```ini
# Your Gmail credentials
SYSTEM_SMTP_USER=your_email@gmail.com
SYSTEM_SMTP_PASS=your_app_password_here

# Database (use your existing PostgreSQL credentials)
DB_HOST=postgres
DB_PORT=5432
DB_NAME=modular_system
DB_USER=postgres
DB_PASSWORD=your_password_here
```

### 2. Database Migration

Run the migration in **each customer database**:
```sql
-- Connect to each customer's database and run:
\i Modular/db-init/websocket_messaging_schema.sql
```

### 3. Start the WebSocket Server

```bash
# Build and start the Docker containers
docker-compose up -d websocket-server

# Check logs
docker-compose logs -f websocket-server
```

### 4. Test WhatsApp Web Integration

#### Option A: Using the Test Script
```bash
# Run the test script
cd Modular/websocket-server
node test-whatsapp.js
```

#### Option B: Using the Frontend (Recommended)
1. Navigate to any page in your system (sidebar is on every page)
2. Look for the WhatsApp button in the sidebar (top right, next to tutorial button)
3. Click the WhatsApp button to open the QR code modal
4. Click "Initialize WhatsApp" to generate a QR code
5. Scan the QR code with your WhatsApp mobile app

#### Option C: Using the Test Page
1. Navigate to `/whatsapp-test.html` in your browser
2. Follow the instructions on the page
3. Use the manual test button to verify API connectivity

### 5. Test Message Sending

Once authenticated, test sending a message:
```javascript
// In browser console on admin page
window.whatsappSessionsManager.sendTestMessage('+27606970361', '🧪 Test message!');
```

## 🔧 Integration with Existing System

### Using ClientDatabase Controller

The system uses your existing `ClientDatabase` controller for per-client database connections:

```php
// Example usage in any module
$customerDb = \App\Core\Database\ClientDatabase::getInstance($customerId);
$pdo = $customerDb->connect();

// Now you can access the new tables in this customer's database
$stmt = $pdo->query("SELECT * FROM settings.whatsapp_sessions WHERE customer_id = '$customerId'");
```

### Frontend Integration

The WhatsApp session management automatically loads on admin/settings pages through `sidebar.js`. It provides:

- QR code display for WhatsApp login
- Session status monitoring
- Real-time status updates via WebSocket
- Logout functionality

## 📱 WhatsApp Web Features

### Session States
- `qr_ready`: QR code generated, waiting for scan
- `authenticated`: QR scanned, authenticating
- `ready`: Session ready, can send messages
- `disconnected`: Session lost
- `auth_failed`: Authentication failed
- `logged_out`: User logged out

### Message Sending
```javascript
// Send text message
await whatsappManager.sendMessage(customerId, '+27606970361', 'Hello!');

// Send document
await whatsappManager.sendDocument(customerId, '+27606970361', filePath, 'document.pdf');
```

## 🐛 Troubleshooting

### Common Issues

1. **QR Code Not Appearing**
   - Check WebSocket server logs: `docker-compose logs websocket-server`
   - Verify database connection
   - Check if Puppeteer is working

2. **Session Not Saving**
   - Verify `/app/sessions` volume is mounted
   - Check file permissions in Docker container

3. **Messages Not Sending**
   - Verify WhatsApp session is in 'ready' state
   - Check phone number format (+27606970361)
   - Review WebSocket server logs

4. **Database Connection Issues**
   - Verify PostgreSQL is running
   - Check database credentials in `.env`
   - Ensure migration ran successfully

### Debug Commands

```bash
# Check WebSocket server status
curl http://localhost:3001/health

# Check WhatsApp session status
curl http://localhost:3001/whatsapp/status/test_customer_001

# Get QR code
curl http://localhost:3001/whatsapp/qr/test_customer_001
```

## 🔒 Security Notes

- Each customer has isolated WhatsApp sessions
- Sessions are stored per customer in their database
- WebSocket channels are isolated by customer_id
- All operations require proper authentication

## 📊 Monitoring

Monitor the system through:
- WebSocket server logs
- Database tables: `settings.whatsapp_sessions`, `events`, `message_deliveries`
- Frontend console logs
- Network tab for WebSocket connections

## 🎯 Next Steps

1. Test with your WhatsApp number (+27606970361)
2. Verify email sending with your Gmail credentials
3. Test the full messaging queue system
4. Integrate with existing modules (invoice, timesheet, etc.)
5. Set up monitoring and alerting 