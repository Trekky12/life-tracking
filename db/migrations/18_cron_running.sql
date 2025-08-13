INSERT INTO global_settings (name, value, type) VALUES ('isCronRunning', 0, 'Boolean');

ALTER TABLE global_settings ADD reference INTEGER unsigned DEFAULT NULL AFTER type;

INSERT INTO notifications_categories (name, identifier, internal) VALUES ('NOTIFICATION_CATEGORY_TIMESHEET_CHECK_REMINDER', 'NOTIFICATION_CATEGORY_TIMESHEET_CHECK_REMINDER', 1);