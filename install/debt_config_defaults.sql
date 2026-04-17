-- Debt Notification System - Default Configuration
-- Run this to add default settings for debt notification

-- Enable debt notifications
INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_notifications_enabled', '1') 
ON DUPLICATE KEY UPDATE `value` = '1';

-- Debt notification channels (SMS,WhatsApp,Email,Inbox)
INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_notification_channels', 'SMS,WhatsApp,Email,Inbox') 
ON DUPLICATE KEY UPDATE `value` = 'SMS,WhatsApp,Email,Inbox';

-- Grace period in days (default: 7)
INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_grace_period_days', '7') 
ON DUPLICATE KEY UPDATE `value` = '7';

-- Enable automatic disconnection
INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_auto_disconnect', '1') 
ON DUPLICATE KEY UPDATE `value` = '1';

-- Warning days before deadline (default: 3)
INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_warning_days', '3') 
ON DUPLICATE KEY UPDATE `value` = '3';

-- Final notice days before deadline (default: 1)
INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_final_notice_days', '1') 
ON DUPLICATE KEY UPDATE `value` = '1';

-- Debt notification templates (will be used in notification system)
INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_message_initial', 'Dear [[name]], your account has a debt of [[amount]]. Please settle within [[days]] days to avoid disconnection.') 
ON DUPLICATE KEY UPDATE `value` = 'Dear [[name]], your account has a debt of [[amount]]. Please settle within [[days]] days to avoid disconnection.';

INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_message_warning', 'URGENT: Dear [[name]], your debt of [[amount]] must be paid within [[days]] days. Your service will be disconnected after deadline.') 
ON DUPLICATE KEY UPDATE `value` = 'URGENT: Dear [[name]], your debt of [[amount]] must be paid within [[days]] days. Your service will be disconnected after deadline.';

INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_message_final', 'FINAL NOTICE: Dear [[name]], your debt of [[amount]] must be paid by tomorrow. Immediate disconnection will occur after deadline.') 
ON DUPLICATE KEY UPDATE `value` = 'FINAL NOTICE: Dear [[name]], your debt of [[amount]] must be paid by tomorrow. Immediate disconnection will occur after deadline.';

INSERT INTO `tbl_appconfig` (`setting`, `value`) 
VALUES ('debt_message_disconnection', 'Dear [[name]], your service has been disconnected due to unpaid debt of [[amount]]. Please settle to restore service.') 
ON DUPLICATE KEY UPDATE `value` = 'Dear [[name]], your service has been disconnected due to unpaid debt of [[amount]]. Please settle to restore service.';
