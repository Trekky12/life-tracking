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

INSERT INTO finances (id, user, type, date, time, category, description, value, common, common_value, notice, fixed, lat, lng, acc, bill, paymethod) VALUES
(1, 1, 0, '2020-01-01', '14:44:49', 1, 'Test expense', '10.00', 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 1),
(2, 1, 1, '2020-01-01', '14:45:01', 1, 'Test income', '100.00', 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL);

INSERT INTO finances_recurring (id, user, start, end, type, category, description, value, common, common_value, notice, unit, multiplier, paymethod) VALUES
(1, 1, '2020-01-01', NULL, 0, 1, 'Test monthly entry expense', '10.00', 0, NULL, NULL, 'month', 1, NULL),
(2, 1, '2020-01-01', NULL, 1, 1, 'Test monthly entry income', '10.00', 0, NULL, NULL, 'month', 1, NULL);

INSERT INTO finances_budgets (id, user, description, value, is_hidden, is_remaining) VALUES
(1, 1, 'Test Budget Entry', '5.00', 0, 0),
(2, 1, 'Rest', '5.00', 0, 1);

INSERT INTO finances_budgets_categories (budget, category) VALUES (1,1);

INSERT INTO finances_categories_assignment (id, user, description, category, min_value, max_value) VALUES
(1, 1, 'Test assignment', 1, NULL, NULL);

INSERT INTO cars (id, user, name, mileage_per_year, mileage_term, mileage_start_date) VALUES (1, 1, 'Test Car', '10000', '4', '2020-01-28');
INSERT INTO cars_user (car, user) VALUES (1, 1); 
INSERT INTO cars_user (car, user) VALUES (1, 2); 

INSERT INTO cars_service (id, createdBy, changedBy, car, date, mileage, type, fuel_volume, fuel_type) VALUES (1, 1, 1, 1, '2020-01-01', 0, 0, '50.00', 1);
INSERT INTO cars_service (id, createdBy, changedBy, car, date, mileage, type, notice, service_oil_before, service_oil_after, service_water_wiper_before, service_water_wiper_after, service_air_front_left_before, service_air_front_left_after, service_air_front_right_before, service_air_front_right_after, service_air_back_left_before, service_air_back_left_after, service_air_back_right_before, service_air_back_right_after, service_tire_change, service_garage) VALUES
(2, 1, 1, 1, '2020-01-01', 0, 1, 'Test', 0, 100, 0, 100, '1.0', '2.0', '1.0', '2.0', '1.0', '2.0', '1.0', '2.0', 1, 1);

INSERT INTO crawlers (id, user, name, hash, filter) VALUES (1, 1, 'Test Crawler', 'ABCabc123', 'createdOn');
INSERT INTO crawlers_user (crawler, user) VALUES (1, 1);
INSERT INTO crawlers_user (crawler, user) VALUES (1, 2);
INSERT INTO crawlers_links (id, crawler, createdBy, changedBy, name, link, parent, position) VALUES (20, 1, 2, 2, 'Test Category', 'http://localhost', NULL, 1);
INSERT INTO crawlers_headers (id, crawler, createdBy, changedBy, headline, field_name, field_link, field_content, sortable, diff, prefix, suffix, sort, datatype, position) VALUES 
(1, 1, 1, 1, 'title', 'title', 'link', NULL, 1, 0, NULL, NULL, NULL, NULL, 1),
(2, 1, 1, 1, 'number', 'value', NULL, NULL, 1, 0, NULL, NULL, 'desc', 'DECIMAL', 2);