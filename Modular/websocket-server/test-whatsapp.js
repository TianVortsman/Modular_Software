const WhatsAppWebManager = require('./lib/WhatsAppWebManager');
const DatabaseManager = require('./lib/DatabaseManager');

async function testWhatsAppWeb() {
    console.log('🧪 Testing WhatsApp Web Integration...');
    
    // Initialize database manager
    const dbManager = new DatabaseManager();
    await dbManager.connect();
    
    // Initialize WhatsApp Web Manager
    const whatsappManager = new WhatsAppWebManager(dbManager);
    
    // Test customer ID (you can change this)
    const testCustomerId = 'test_customer_001';
    
    try {
        console.log('📱 Initializing WhatsApp session for customer:', testCustomerId);
        
        // Initialize session
        await whatsappManager.initializeSession(testCustomerId);
        
        // Check status
        const status = await whatsappManager.getSessionStatus(testCustomerId);
        console.log('📊 Session status:', status);
        
        if (status.status === 'qr_ready' && status.qr_code) {
            console.log('📱 QR Code generated! Scan with WhatsApp mobile app');
            console.log('🔗 QR Code data URL length:', status.qr_code.length);
            
            // Wait for authentication
            console.log('⏳ Waiting for QR code scan...');
            
            // Set up event listeners
            whatsappManager.on('qr:generated', (data) => {
                console.log('🔄 New QR code generated for customer:', data.customer_id);
            });
            
            whatsappManager.on('client:ready', (data) => {
                console.log('✅ WhatsApp client ready for customer:', data.customer_id);
                
                // Test sending a message
                testSendMessage(whatsappManager, testCustomerId);
            });
            
            whatsappManager.on('client:authenticated', (data) => {
                console.log('🔐 WhatsApp client authenticated for customer:', data.customer_id);
            });
            
            whatsappManager.on('client:disconnected', (data) => {
                console.log('❌ WhatsApp client disconnected for customer:', data.customer_id);
            });
            
            // Keep the process running
            console.log('🔄 Monitoring WhatsApp session... (Press Ctrl+C to stop)');
            
        } else if (status.status === 'ready') {
            console.log('✅ WhatsApp session already ready!');
            await testSendMessage(whatsappManager, testCustomerId);
        }
        
    } catch (error) {
        console.error('❌ Error testing WhatsApp Web:', error.message);
    }
}

async function testSendMessage(whatsappManager, customerId) {
    try {
        console.log('📤 Testing message sending...');
        
        // Test with your number
        const testNumber = '+27606970361';
        const testMessage = '🧪 Test message from Modular System WhatsApp Web integration!';
        
        const result = await whatsappManager.sendMessage(customerId, testNumber, testMessage);
        console.log('✅ Message sent successfully!');
        console.log('📊 Result:', result);
        
    } catch (error) {
        console.error('❌ Error sending test message:', error.message);
    }
}

// Run the test
testWhatsAppWeb().catch(console.error); 