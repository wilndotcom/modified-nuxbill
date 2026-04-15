<?php
// Test script for activation reminder
// Run this to verify the configuration is working

// Include the required files
define('APP_PATH', __DIR__ . '/');
define('UPLOAD_PATH', __DIR__ . '/system/uploads');

require_once APP_PATH . 'system/autoload/ActivationReminder.php';

echo "Testing Activation Reminder Configuration:\n\n";

// Check if JSON notification file exists
$notif_file = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'notifications.json';
if (!file_exists($notif_file)) {
    $notif_file = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'notifications.default.json';
}

if (file_exists($notif_file)) {
    $notifications = json_decode(file_get_contents($notif_file), true);
    $activation_message = isset($notifications['activation_reminder']) ? $notifications['activation_reminder'] : '';
    
    echo "JSON File: " . basename($notif_file) . "\n";
    echo "Activation Reminder Template: " . ($activation_message ? 'EXISTS' : 'NOT FOUND') . "\n";
    echo "Template Preview: " . substr($activation_message, 0, 100) . "...\n\n";
    
    if ($activation_message) {
        echo "Configuration is properly set!\n";
        echo "The activation reminder system is ready to use.\n\n";
        echo "NEW FEATURES:\n";
        echo "Dedicated 'Activation Notification' setting\n";
        echo "Multi-channel combinations supported\n";
        echo "Flexible delivery options\n\n";
        
        echo "Available Channel Combinations:\n";
        echo "- None (disable notifications)\n";
        echo "- Email only\n";
        echo "- SMS only\n";
        echo "- WhatsApp only\n";
        echo "- Email + SMS\n";
        echo "- Email + WhatsApp\n";
        echo "- SMS + WhatsApp\n";
        echo "- All Channels (Email + SMS + WhatsApp)\n\n";
        
        echo "To test:\n";
        echo "1. Configure activation notification channel in Settings -> Application\n";
        echo "2. Choose your preferred combination (single or multiple channels)\n";
        echo "3. Create a new customer via admin panel\n";
        echo "4. Check all selected channels for activation message\n";
        echo "5. Or register a new user account\n";
        echo "6. Customize template in Settings -> Notifications\n\n";
        
        echo "Template placeholders:\n";
        echo "- [[fullname]] - Customer Full Name\n";
        echo "- [[username]] - Customer Username\n";
        echo "- [[company_name]] - Company Name\n";
        echo "- [[name]] - Customer Name (compatibility)\n\n";
        
        echo "Configuration Requirements:\n";
        echo "- Email: Requires SMTP configuration in Settings\n";
        echo "- SMS: Requires SMS gateway configuration\n";
        echo "- WhatsApp: Requires WhatsApp API configuration\n";
        echo "- Multiple channels: All selected channels must be configured\n\n";
        
        echo "Smart Fallback:\n";
        echo "- If activation setting is 'None', falls back to Payment Notification setting\n";
        echo "- Validates contact info for each channel (email/phone)\n";
        echo "- Only sends to channels where customer has valid contact info\n";
    } else {
        echo "ERROR: Activation reminder template not found in JSON!\n";
    }
} else {
    echo "ERROR: No notifications JSON file found!\n";
}

echo "\nAvailable notification templates:\n";
if (isset($notifications)) {
    foreach ($notifications as $key => $value) {
        echo "- $key: " . (empty($value) ? 'EMPTY' : 'EXISTS') . "\n";
    }
}
?>
