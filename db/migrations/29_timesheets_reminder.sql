CREATE TABLE IF NOT EXISTS timesheets_reminders (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    project INTEGER unsigned DEFAULT NULL,
    name varchar(255) NOT NULL,
    trigger_type ENUM('after_last_sheet_plus_1h','after_last_sheet', 'after_each_sheet') DEFAULT 'after_last_sheet_plus_1h',
    title TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS timesheets_reminders_messages (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    reminder INTEGER unsigned NOT NULL,
    message TEXT NOT NULL,
    send_count INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY(reminder) REFERENCES timesheets_reminders(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS timesheets_reminders_sent (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    project INTEGER unsigned DEFAULT NULL, 
    reminder INTEGER unsigned DEFAULT NULL,
    timesheet INTEGER unsigned DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(reminder) REFERENCES timesheets_reminders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(timesheet) REFERENCES timesheets_sheets(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE notifications_categories
  ADD COLUMN reminder int(11) unsigned DEFAULT NULL AFTER internal,
  ADD CONSTRAINT notifications_categories_ibfk_2 FOREIGN KEY (reminder)
    REFERENCES timesheets_reminders(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

ALTER TABLE notifications_categories CHANGE identifier identifier VARCHAR(255) NULL;

ALTER TABLE notifications_categories CHANGE name name VARCHAR(255) NULL;

DELETE FROM notifications_categories WHERE identifier = "NOTIFICATION_CATEGORY_TIMESHEET_CHECK_REMINDER";

DELETE FROM global_settings WHERE name = "lastRunTimesheetNotifyProject";
