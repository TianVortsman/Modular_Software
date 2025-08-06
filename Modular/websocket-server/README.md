# Modular WebSocket Server

A scalable, real-time messaging and notification system for the Modular Software platform.

## Features

- **Real-time WebSocket connections** with authentication
- **Multi-tenant support** with customer-specific database connections
- **Event-driven architecture** for processing business events
- **Email integration** with configurable SMTP settings
- **WhatsApp Web integration** using whatsapp-web.js (no Meta API required)
- **Webhook support** for external integrations
- **Rate limiting** and security features
- **Retry logic** for failed deliveries
- **Live progress tracking** via WebSocket updates
- **Docker support** for easy deployment

## Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   PHP App       │    │  WebSocket       │    │   Mobile App    │
│   (Frontend)    │◄──►│  Server          │◄──►│   (Future)      │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   PostgreSQL    │    │   WhatsApp Web   │    │   SMTP Server   │
│  (Multi-tenant)│    │   (whatsapp-web.js)│   │   (Configurable)│
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

## Quick Start

### Prerequisites

- Node.js 18+ 
- PostgreSQL database
- Docker (optional)

### Installation

1. **Clone and install dependencies:**
   ```bash
   cd websocket-server
   npm install
   ```

2. **Configure environment:**
   ```bash
   cp env.example .env
   # Edit .env with your configuration
   ```

3. **Run the server:**
   ```bash
   npm start
   ```

### Docker Deployment

```bash
# Build the image
docker build -t modular-websocket .

# Run the container
docker run -d \
  --name websocket-server \
  -p 3001:3001 \
  --env-file .env \
  modular-websocket
```

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `PORT` | Server port | `3001` |
| `DB_HOST` | Database host | `postgres` |
| `DB_PORT` | Database port | `5432` |
| `JWT_SECRET` | JWT signing secret | Required |
| `ALLOWED_ORIGINS` | CORS allowed origins | `http://localhost:3000` |
| `SYSTEM_SMTP_HOST` | System SMTP host | `smtp.gmail.com` |
| `PUPPETEER_EXECUTABLE_PATH` | Puppeteer executable path | `/usr/bin/chromium-browser` |
| `WHATSAPP_SESSION_TIMEOUT` | WhatsApp session timeout (ms) | `300000` |

### Database Schema

The server requires the following tables in each customer database:

```sql
-- Settings table
CREATE TABLE settings_email_whatsapp (
    id SERIAL PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    use_customer_smtp BOOLEAN DEFAULT FALSE,
    smtp_host VARCHAR(255),
    smtp_port INTEGER DEFAULT 587,
    smtp_username VARCHAR(255),
    smtp_password VARCHAR(255),
    whatsapp_enabled BOOLEAN DEFAULT TRUE,
    use_customer_whatsapp BOOLEAN DEFAULT FALSE,
    whatsapp_api_key VARCHAR(255),
    webhook_enabled BOOLEAN DEFAULT FALSE,
    webhook_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Event tracking
CREATE TABLE websocket_events (
    id SERIAL PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_data JSONB NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT NOW()
);

-- Message deliveries
CREATE TABLE message_deliveries (
    id SERIAL PRIMARY KEY,
    event_id INTEGER REFERENCES websocket_events(id),
    customer_id VARCHAR(50) NOT NULL,
    channel VARCHAR(20) NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    message_content TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    sent_at TIMESTAMP,
    retry_count INTEGER DEFAULT 0
);
```

## Usage

### Triggering Events from PHP

```php
// In your PHP application
$eventData = [
    'event_type' => 'invoice:sent',
    'event_data' => [
        'invoice_id' => 123,
        'client_email' => 'client@example.com',
        'client_phone' => '+27123456789',
        'invoice_number' => 'INV-001',
        'total_amount' => 1500.00,
        'client_name' => 'John Doe'
    ]
];

$response = file_get_contents('http://localhost:3001/api/websocket-events.php', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($eventData)
    ]
]));
```

### Frontend Integration

```javascript
// Include the WebSocket sidebar
<script src="/assets/js/websocket-sidebar.js"></script>

// Trigger events from JavaScript
window.websocketSidebar.triggerEvent('invoice:sent', {
    invoice_id: 123,
    client_email: 'client@example.com',
    invoice_number: 'INV-001',
    total_amount: 1500.00
});
```

### Supported Event Types

| Event Type | Description | Required Data |
|------------|-------------|---------------|
| `invoice:sent` | Invoice sent to client | `invoice_id`, `client_email`, `invoice_number`, `total_amount` |
| `invoice:paid` | Payment received for invoice | `invoice_id`, `client_email`, `payment_amount` |
| `user:clockedIn` | User clocked in | `user_id`, `user_name`, `clock_in_time` |
| `user:clockedOut` | User clocked out | `user_id`, `user_name`, `clock_out_time` |
| `payslip:generated` | Payslip generated | `employee_id`, `employee_email`, `payslip_number`, `net_pay` |
| `document:sent` | Document sent | `document_id`, `recipient_email`, `document_type` |
| `payment:received` | Payment received | `payment_id`, `client_email`, `payment_amount` |
| `refund:created` | Refund created | `refund_id`, `client_email`, `refund_amount` |
| `timesheet:submitted` | Timesheet submitted | `timesheet_id`, `employee_name`, `manager_email` |
| `timesheet:approved` | Timesheet approved | `timesheet_id`, `employee_email`, `period` |
| `timesheet:rejected` | Timesheet rejected | `timesheet_id`, `employee_email`, `period`, `reason` |

## API Endpoints

### Authentication

**POST** `/auth/token`
- Get connection token for WebSocket authentication
- Body: `{ "customer_id": "...", "user_id": 123, "session_id": "..." }`
- Returns: `{ "token": "jwt_token" }`

### Health Check

**GET** `/health`
- Check server health
- Returns: `{ "status": "ok", "timestamp": "..." }`

## WebSocket Events

### Client to Server

- `internal:event` - Trigger a business event

### Server to Client

- `event:acknowledged` - Event received and queued
- `event:progress` - Event processing progress
- `event:completed` - Event completed successfully
- `event:failed` - Event failed
- `message:sent` - Message delivered successfully
- `message:failed` - Message delivery failed
- `whatsapp:qr_generated` - WhatsApp QR code generated
- `whatsapp:client_ready` - WhatsApp client ready
- `whatsapp:client_authenticated` - WhatsApp client authenticated
- `whatsapp:client_auth_failed` - WhatsApp authentication failed
- `whatsapp:client_disconnected` - WhatsApp client disconnected
- `whatsapp:client_logged_out` - WhatsApp client logged out

## WhatsApp Web Integration

The server includes WhatsApp Web integration using `whatsapp-web.js`, which allows sending WhatsApp messages without requiring Meta's Business API.

### Features

- **QR Code Authentication** - Scan QR code to authenticate WhatsApp Web
- **Session Management** - Automatic session saving and reuse
- **Multi-tenant Support** - Separate sessions per customer
- **Real-time Status Updates** - Live connection status via WebSocket
- **Document Support** - Send documents and media files
- **No Meta API Required** - Uses WhatsApp Web directly

### Setup

1. **Install dependencies:**
   ```bash
   npm install whatsapp-web.js puppeteer qrcode
   ```

2. **Configure Puppeteer:**
   ```bash
   # In Docker, Chromium is pre-installed
   # For local development, install Chromium or set PUPPETEER_EXECUTABLE_PATH
   ```

3. **Database setup:**
   ```sql
   -- Add WhatsApp sessions table to each customer database
   CREATE TABLE settings.whatsapp_sessions (
       id SERIAL PRIMARY KEY,
       customer_id VARCHAR(50) NOT NULL,
       status VARCHAR(50) NOT NULL,
       qr_code TEXT,
       error_message TEXT,
       last_updated TIMESTAMP DEFAULT NOW(),
       UNIQUE(customer_id)
   );
   ```

### API Endpoints

**GET** `/whatsapp/qr/:customerId`
- Get QR code for WhatsApp Web authentication
- Query params: `user_id`, `session_id`
- Returns: `{ "qr_code": "data:image/png;base64,..." }`

**GET** `/whatsapp/status/:customerId`
- Get current WhatsApp session status
- Query params: `user_id`, `session_id`
- Returns: `{ "status": "ready", "last_updated": "..." }`

**POST** `/whatsapp/logout/:customerId`
- Logout from WhatsApp session
- Body: `{ "user_id": 123, "session_id": "..." }`
- Returns: `{ "success": true, "message": "..." }`

### Frontend Integration

Include the WhatsApp sessions manager:

```html
<script src="/assets/js/whatsapp-sessions.js"></script>
<link rel="stylesheet" href="/assets/css/whatsapp-sessions.css">
```

```javascript
// Initialize WhatsApp sessions manager
const whatsappManager = new WhatsAppSessionsManager('CUSTOMER_ID');
await whatsappManager.initialize();

// Get QR code for login
const qrCode = await whatsappManager.getQRCode();

// Check status
await whatsappManager.checkStatus();

// Logout
await whatsappManager.logout();
```

### Session States

- `not_initialized` - Session not started
- `qr_ready` - QR code available for scanning
- `authenticated` - Successfully authenticated
- `ready` - Ready to send messages
- `disconnected` - Connection lost
- `auth_failed` - Authentication failed
- `logged_out` - User logged out

## Security Features

- **JWT Authentication** for WebSocket connections
- **Rate Limiting** per customer and user
- **CORS Protection** with configurable origins
- **Input Validation** for all events
- **SQL Injection Protection** with prepared statements
- **Session Verification** for PHP integration

## Monitoring and Logging

The server includes comprehensive logging:

- **Winston logging** with file and console output
- **Error tracking** with stack traces
- **Performance monitoring** for database queries
- **Connection tracking** for WebSocket clients
- **Delivery status** tracking for all messages

### Log Files

- `logs/combined.log` - All log messages
- `logs/error.log` - Error messages only

## Troubleshooting

### Common Issues

1. **Connection refused**
   - Check if the server is running on the correct port
   - Verify firewall settings

2. **Database connection failed**
   - Check database credentials in `.env`
   - Ensure PostgreSQL is running

3. **WebSocket authentication failed**
   - Verify JWT secret is set
   - Check session data in PHP

4. **Email sending failed**
   - Verify SMTP credentials
   - Check rate limits on email provider

### Debug Mode

Enable debug logging by setting:
```bash
LOG_LEVEL=debug
```

## Development

### Adding New Event Types

1. **Register handler in EventProcessor:**
   ```javascript
   this.registerHandler('new:event', this.handleNewEvent.bind(this));
   ```

2. **Implement handler method:**
   ```javascript
   async handleNewEvent(eventId, customerId, eventData, settings) {
       // Process the event
       // Send messages via email/WhatsApp/webhook
       return { deliveries: [] };
   }
   ```

3. **Add template in renderTemplate method:**
   ```javascript
   'new_event_email': `Your new event template...`
   ```

### Testing

```bash
# Run tests
npm test

# Run with coverage
npm run test:coverage
```

## License

MIT License - see LICENSE file for details.

## Support

For support and questions, please contact the development team. 