INSERT INTO global_users (login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES ('admin', '$2y$10$gbDsuY1GyMJo78ueqWy/SOstNf2DeLpN3mKTUS9Yp.bwG7i4y4.KK', 'Administrator', 'admin', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

INSERT INTO global_settings (name, value, type) VALUES ('lastRunRecurring', 0, 'Date');
INSERT INTO global_settings (name, value, type) VALUES ('lastRunFinanceSummary', 0, 'Date');
INSERT INTO global_settings (name, value, type) VALUES ('lastRunCardReminder', 0, 'Date');
INSERT INTO global_settings (name, value, type) VALUES ('lastRunRecurringSplitbills', 0, 'Date');
INSERT INTO global_settings (name, value, type) VALUES ('lastRunRecurringTransactions', 0, 'Date'); 

INSERT INTO finances_categories (id, user, name, is_default) VALUES (1, 1, 'not categorized', 1);

INSERT INTO finances_paymethods (id, user, name, is_default) VALUES (1, 1, 'default paymethod', 1); 

INSERT INTO notifications_categories (id, name, identifier, internal) VALUES (1, 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 1);
INSERT INTO notifications_categories (id, name, identifier, internal) VALUES (2, 'NOTIFICATION_CATEGORY_FINANCES_RECURRING', 'NOTIFICATION_CATEGORY_FINANCES_RECURRING', 1);
INSERT INTO notifications_categories (id, name, identifier, internal) VALUES (3, 'NOTIFICATION_CATEGORY_BOARDS_CARD_ADD', 'NOTIFICATION_CATEGORY_BOARDS_CARD_ADD', 1);

INSERT INTO mail_categories (id, name, identifier) VALUES (1, 'MAIL_CATEGORY_FINANCE_STATISTIC', 'MAIL_CATEGORY_FINANCE_STATISTIC');
INSERT INTO mail_categories (id, name, identifier) VALUES (2, 'MAIL_CATEGORY_SPLITTED_BILLS', 'MAIL_CATEGORY_SPLITTED_BILLS');
INSERT INTO mail_categories (id, name, identifier) VALUES (3, 'MAIL_CATEGORY_BOARDS_ADD', 'MAIL_CATEGORY_BOARDS_ADD');
INSERT INTO mail_categories (id, name, identifier) VALUES (4, 'MAIL_CATEGORY_BOARDS_CARD_DUE', 'MAIL_CATEGORY_BOARDS_CARD_DUE');
INSERT INTO mail_categories (id, name, identifier) VALUES (5, 'MAIL_CATEGORY_BOARDS_CARD_ADD', 'MAIL_CATEGORY_BOARDS_CARD_ADD');
