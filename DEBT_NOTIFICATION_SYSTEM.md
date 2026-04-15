# Debt Notification and Disconnection System

This system automatically tracks customer debt, sends reminder notifications, and disconnects customers who fail to settle their debt within the grace period.

## Features

- **Automatic Debt Tracking**: Tracks when customers go into debt (negative balance)
- **Multi-Channel Notifications**: Sends reminders via SMS, WhatsApp, Email, and Inbox
- **Progressive Warnings**: Sends initial, warning, and final notifications based on deadline proximity
- **Automatic Disconnection**: Disconnects customers when debt deadline expires
- **Customer Portal Warnings**: Shows prominent debt warnings in customer dashboard
- **Configurable Grace Period**: Admin can set the number of days customers have to settle debt

## Installation

### 1. Database Setup

Run the SQL files in order:

```bash
# Create debt tracking table
mysql -u username -p database_name < install/debt_notification.sql

# Add default configuration
mysql -u username -p database_name < install/debt_config_defaults.sql
```

### 2. Cron Job Setup

Add the following cron jobs to run automatically:

#### Debt Notification Cron (Run daily at 9:00 AM)
```bash
0 9 * * * /usr/bin/php /path/to/system/cron_debt_notification.php
```

Or run every 6 hours:
```bash
0 */6 * * * /usr/bin/php /path/to/system/cron_debt_notification.php
```

#### Debt Disconnection Cron (Run every hour)
```bash
0 * * * * /usr/bin/php /path/to/system/cron_debt_disconnect.php
```

Or run every 30 minutes:
```bash
*/30 * * * * /usr/bin/php /path/to/system/cron_debt_disconnect.php
```

### 3. Configuration

Configure the debt notification system in the admin panel:

- **Settings → General Settings (App)** → open **User Notification** panel → **Debt Notification** section:
  - **Enable Debt Notifications**: Turn debt reminders on/off
  - **Debt Notification Channels**: SMS, WhatsApp, Email, Inbox (multiple allowed)
  - **Debt Grace Period (Days)**: 1–90 days to settle before disconnection (default: 7)
  - **Disconnect on Overdue Debt**: Allow automatic disconnection when deadline has passed

- **Settings → User Notification (Notifications)** → customize message templates:
  - **Debt Notification (Initial)**, **Debt Warning (3 days before)**, **Debt Final Notice (1 day before)**, **Debt Disconnection Notice**

Optional: run `install/debt_config_defaults.sql` to pre-fill config if the UI section is not yet present; otherwise use the UI.

## How It Works

### 1. Debt Detection

When a customer's balance goes negative:
- A debt tracking record is automatically created
- Deadline is set based on `debt_grace_period_days` configuration
- Initial notification is sent immediately

### 2. Notification Schedule

The system sends notifications at different intervals:

- **Initial Notification**: Sent immediately when debt is detected
- **Warning Notification**: Sent 3 days before deadline
- **Final Notification**: Sent 1 day before deadline (shows hours remaining)

### 3. Disconnection Process

When the deadline passes:
- Customer's active internet plans are disconnected
- Disconnection notification is sent
- Debt tracking record is marked as disconnected

### 4. Debt Resolution

When customer balance returns to zero or positive:
- Debt tracking record is marked as resolved
- Customer can reconnect manually or admin can restore service

## Customer Portal

Customers with debt will see:
- **Prominent Warning Banner**: Red alert banner at top of dashboard showing debt amount and deadline
- **Wallet Display**: Shows debt amount in red with "Debt" badge
- **Settle Debt Button**: Direct link to top-up page

## Notification Messages

### Initial Notification
```
Dear [Customer Name],

You currently have an outstanding debt of [Amount].

Please settle your debt by [Deadline Date] to avoid disconnection of your internet service.

You can top up your balance through the customer portal or contact our support team.

Thank you for your prompt attention to this matter.

Best regards,
[Company Name]
```

### Warning Notification (3 days before)
```
Dear [Customer Name],

URGENT: Your debt of [Amount] must be settled within [X] days (by [Deadline Date]).

Failure to settle your debt will result in immediate disconnection of your internet service.

Please settle your account immediately to avoid service interruption.

Best regards,
[Company Name]
```

### Final Notification (1 day before)
```
Dear [Customer Name],

FINAL NOTICE: Your debt of [Amount] must be settled by [Deadline Date] (within [X] hours).

Your internet service will be disconnected automatically if payment is not received by the deadline.

Please make immediate payment to avoid service interruption.

Best regards,
[Company Name]
```

### Disconnection Notification
```
Dear [Customer Name],

Your internet service has been disconnected due to outstanding debt of [Amount].

The deadline for payment was [Deadline Date], which has now passed.

To restore your service, please settle your debt immediately by topping up your balance.

Once payment is received, your service will be restored automatically.

If you have any questions, please contact our support team.

Best regards,
[Company Name]
```

## Database Schema

### tbl_customer_debt_tracking

| Column | Type | Description |
|--------|------|-------------|
| id | int | Primary key |
| customer_id | int | Customer ID |
| debt_start_date | datetime | When debt started |
| debt_amount | decimal(15,2) | Current debt amount |
| deadline_date | datetime | Payment deadline |
| notification_sent_initial | datetime | Initial notification sent |
| notification_sent_warning | datetime | Warning notification sent |
| notification_sent_final | datetime | Final notification sent |
| disconnected | tinyint(1) | Whether disconnected |
| disconnected_date | datetime | When disconnected |
| resolved | tinyint(1) | Whether debt resolved |
| resolved_date | datetime | When resolved |

## Troubleshooting

### Notifications Not Sending

1. Check cron jobs are running: `crontab -l`
2. Check cron logs for errors
3. Verify notification channels are configured correctly
4. Ensure customer has valid phone/email for selected channels

### Customers Not Being Disconnected

1. Verify `debt_disconnection_enabled` is set to 'yes'
2. Check cron job is running
3. Verify customer has active plans
4. Check router/device connectivity
5. Review cron logs for disconnection errors

### Debt Tracking Not Created

- Debt tracking is created automatically by the notification cron
- Ensure cron job is running regularly
- Check database table exists and has correct permissions

## Manual Operations

### Mark Debt as Resolved

```sql
UPDATE tbl_customer_debt_tracking 
SET resolved = 1, resolved_date = NOW() 
WHERE customer_id = [CUSTOMER_ID];
```

### Reset Debt Tracking

```sql
UPDATE tbl_customer_debt_tracking 
SET resolved = 0, disconnected = 0, resolved_date = NULL, disconnected_date = NULL 
WHERE customer_id = [CUSTOMER_ID];
```

### Extend Deadline

```sql
UPDATE tbl_customer_debt_tracking 
SET deadline_date = DATE_ADD(deadline_date, INTERVAL 7 DAY) 
WHERE customer_id = [CUSTOMER_ID];
```

## Security Notes

- Cron jobs use file locking to prevent concurrent execution
- Customer data is masked in logs when not in CLI mode
- All disconnection operations are logged
- Admin can disable disconnection via configuration

## Support

For issues or questions:
1. Check cron logs: `/path/to/cache/debt_notification.lock` and `/path/to/cache/debt_disconnect.lock`
2. Review application logs
3. Verify database connectivity
4. Check router/device API connectivity
