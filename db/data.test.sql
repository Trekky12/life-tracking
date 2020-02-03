INSERT INTO global_settings (name, value, type) VALUES ('lastRunRecurring', 0, 'Date'), ('lastRunFinanceSummary', 0, 'Date'), ('lastRunCardReminder', 0, 'Date'); 

INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (1, 'admin', '$2y$10$gbDsuY1GyMJo78ueqWy/SOstNf2DeLpN3mKTUS9Yp.bwG7i4y4.KK', 'admin', 'admin', 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (2, 'user', '$2y$10$tC4twYpdcq0TibT6MZsdI.Tmu36UkTxFNymd2icHv5KVB1oEu5mBW', 'user', 'user', 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (3, 'user2', '$2y$10$pDAiS7Y30JibG.qKh03MgeO8fkmIMNrjXC.ogVAi526VRKu8sm7V.', 'user2', 'user', 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0); 

INSERT INTO finances_categories (id, user, name, is_default) VALUES 
(1, 1, 'not categorized', 1),
(2, 2, 'not categorized', 1);
INSERT INTO finances_paymethods (id, user, name, is_default) VALUES 
(1, 1, 'Test Paymethod', 1),
(2, 2, 'Test Paymethod', 1); 

INSERT INTO splitbill_groups (id, user, name, hash, add_finances, currency, exchange_rate, exchange_fee) VALUES 
(1, 1, 'Test splitted bills group', 'ABCabc123', 0, '€', '1', '0'),
(2, 1, 'Test splitted bills group (no access to owner)', 'DEFdef456', 0, '€', '1', '0');
INSERT INTO splitbill_groups_user (sbgroup, user) VALUES 
(1, 1),
(1, 2),
(2, 3);
INSERT INTO splitbill_bill (id, sbgroup, user, name, date, time, lat, lng, acc, notice, settleup, exchange_rate, exchange_fee) VALUES
(1, 2, 3,'Test bill', '2020-01-01', '09:00:00', NULL, NULL, NULL, NULL, 0, '1', '0');
INSERT INTO splitbill_bill_users (id, bill, user, paid, spend, paymethod, paid_foreign, spend_foreign) VALUES
(1, 1, 3, '10.00', '10.00', NULL, NULL, NULL);

INSERT INTO timesheets_projects (id, user, name, hash) VALUES 
(1, 1, 'Test timesheets project', 'ABCabc123'),
(2, 1, 'Test timesheets project (no access to owner)', 'DEFdef456'); 
INSERT INTO timesheets_projects_users (project, user) VALUES 
(1, 1),
(1, 2),
(2, 3);
INSERT INTO timesheets_sheets (id, project, createdBy, changedBy, start, end, diff) VALUES
(1, 1, 1, 1, '2020-01-01 09:00:00', '2020-01-01 12:00:00', 10800),
(2, 2, 1, 1, '2020-01-01 09:00:00', '2020-01-01 12:00:00', 10800);

INSERT INTO trips (id, user, name, hash, notice) VALUES 
(1, 1, 'Test Trip', 'ABCabc123', NULL), 
(2, 1, 'Test Trip (no access to owner)', 'DEFdef456', NULL);
INSERT INTO trips_user (trip, user) VALUES 
(1, 1),
(1, 2),
(2, 3);
INSERT INTO trips_event (id, trip, createdBy, changedBy, name, start_date, start_time, start_address, start_lat, start_lng, end_date, end_time, end_address, end_lat, end_lng, type, notice, image, position) VALUES
(1, 2, 3, 3, 'Test Event', '2020-01-01', NULL, NULL, NULL, NULL, '2020-01-02', NULL, NULL, NULL, NULL, 'EVENT', NULL, NULL, 999);