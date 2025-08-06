# Document Sending Service Architecture

This document describes the new reusable document sending service architecture that can be used across the entire application.

## Overview

The document sending functionality has been refactored into a service-based architecture to promote reusability and maintainability. Instead of duplicating code across different modules, you can now use the centralized `DocumentSendingService`.

## Architecture Components

### 1. PHP Service Layer

#### `src/Services/DocumentSendingService.php`
- **Purpose**: Core service that handles all document sending operations
- **Features**:
  - Email sending with PDF attachments
  - WhatsApp sending (placeholder for future implementation)
  - PDF generation if not exists
  - Document status updates
  - Comprehensive logging
  - Error handling

#### `src/Controllers/DocumentSendingController.php`
- **Purpose**: Clean interface for other modules to use the service
- **Usage**: Simple wrapper around the service for easy integration

### 2. JavaScript Service Layer

#### `public/assets/js/document-sending-service.js`
- **Purpose**: Reusable JavaScript service for frontend document sending
- **Features**:
  - Email and WhatsApp sending
  - Confirmation dialogs using ResponseModal
  - Contact validation
  - Error handling with handleApiResponse
  - Table refresh after successful sending

## Usage Examples

### PHP Backend Usage

#### Basic Usage
```php
require_once __DIR__ . '/src/Services/DocumentSendingService.php';

$service = new \App\Services\DocumentSendingService();

// Send by email
$result = $service->sendDocumentByEmail($document_id);

// Send by WhatsApp
$result = $service->sendDocumentByWhatsApp($document_id);
```

#### With Custom Options
```php
$options = [
    'subject' => 'Custom Subject',
    'body' => 'Custom email body content'
];

$result = $service->sendDocumentByEmail($document_id, $options);
```

#### Using the Controller
```php
require_once __DIR__ . '/src/Controllers/DocumentSendingController.php';

$controller = new \App\Controllers\DocumentSendingController();

// Send with method specification
$result = $controller->sendDocument($document_id, 'email', $options);
```

### JavaScript Frontend Usage

#### Basic Usage
```javascript
// Send by email
const result = await documentSendingService.sendByEmail(documentId);

// Send by WhatsApp
const result = await documentSendingService.sendByWhatsApp(documentId);
```

#### With Confirmation Dialog
```javascript
const result = await documentSendingService.sendWithConfirmation(
    documentId, 
    'email', 
    documentNumber, 
    clientName
);
```

#### Check Contact Availability
```javascript
const emailCheck = documentSendingService.checkClientContact(clientData, 'email');
if (emailCheck.hasContact) {
    // Client has email
    console.log('Email:', emailCheck.contactInfo);
} else {
    // Client doesn't have email
    console.log('Error:', emailCheck.errorMessage);
}
```

#### Generate Action Buttons
```javascript
const buttons = documentSendingService.generateActionButtons(
    documentId, 
    documentNumber, 
    clientName, 
    clientData
);
// Returns HTML string with appropriate buttons
```

## Integration in New Modules

### 1. Include the JavaScript Service
```html
<script src="../../../public/assets/js/document-sending-service.js"></script>
```

### 2. Use in Your JavaScript
```javascript
// Example: Add send buttons to a table
function addSendButtonsToTable(tableData) {
    tableData.forEach(row => {
        const buttons = documentSendingService.generateActionButtons(
            row.document_id,
            row.document_number,
            row.client_name,
            row
        );
        // Add buttons to your table row
    });
}
```

### 3. PHP API Integration
```php
// In your API endpoint
require_once __DIR__ . '/../../../src/Services/DocumentSendingService.php';

$service = new \App\Services\DocumentSendingService();
$result = $service->sendDocumentByEmail($document_id, $options);

echo json_encode($result);
```

## Benefits of This Architecture

1. **DRY Principle**: No code duplication across modules
2. **Consistency**: Same behavior and error handling everywhere
3. **Maintainability**: Changes in one place affect all modules
4. **Extensibility**: Easy to add new sending methods (SMS, etc.)
5. **Testing**: Centralized service is easier to test
6. **Performance**: Optimized code that's reused instead of duplicated

## Error Handling

The service provides comprehensive error handling:

- **Frontend**: Uses `handleApiResponse` for consistent error display
- **Backend**: Returns structured error responses with error codes
- **Logging**: All actions are logged to the audit system
- **Fallbacks**: Graceful degradation when optional features fail

## Future Extensions

The service is designed to be easily extended:

1. **New Sending Methods**: Add SMS, Slack, etc.
2. **Custom Templates**: Support for different email/WhatsApp templates
3. **Batch Sending**: Send multiple documents at once
4. **Scheduling**: Schedule documents to be sent later
5. **Tracking**: Track delivery and read receipts

## Migration Guide

If you have existing document sending code:

1. **Replace direct API calls** with `documentSendingService.sendByEmail()`
2. **Replace confirmation dialogs** with `documentSendingService.sendWithConfirmation()`
3. **Replace contact validation** with `documentSendingService.checkClientContact()`
4. **Remove duplicate code** and use the centralized service

## Configuration

The service uses the existing email configuration from `EmailService.php`. To customize:

1. **Email Settings**: Modify `src/Services/EmailService.php`
2. **PDF Templates**: Modify the `generateDocumentHTML()` method
3. **Logging**: Customize the `logDocumentSent()` method
4. **File Paths**: Adjust the `getPdfFilePath()` method 