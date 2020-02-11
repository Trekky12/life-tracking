INSERT INTO global_settings (name, value, type) VALUES ('lastRunRecurring', 0, 'Date'), ('lastRunFinanceSummary', 0, 'Date'), ('lastRunCardReminder', 0, 'Date'); 

INSERT INTO global_users (login, password, role) VALUES ('admin', '$2y$10$gbDsuY1GyMJo78ueqWy/SOstNf2DeLpN3mKTUS9Yp.bwG7i4y4.KK', 'admin');

INSERT INTO finances_categories (id, user, name, is_default) VALUES (1, 1, 'not categorized', 1);

INSERT INTO finances_paymethods (id, user, name, is_default) VALUES (1, 1, 'default paymethod', 1); 

INSERT INTO notifications_categories (id, name, identifier, internal) VALUES (1, 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 1);