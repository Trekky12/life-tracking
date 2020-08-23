INSERT INTO global_settings (name, value, type) VALUES ('lastRunRecurring', 0, 'Date'), ('lastRunFinanceSummary', 0, 'Date'), ('lastRunCardReminder', 0, 'Date'); 

INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (1, 'admin', '$2y$10$gbDsuY1GyMJo78ueqWy/SOstNf2DeLpN3mKTUS9Yp.bwG7i4y4.KK', 'admin', 'admin', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (2, 'user', '$2y$10$tC4twYpdcq0TibT6MZsdI.Tmu36UkTxFNymd2icHv5KVB1oEu5mBW', 'user', 'user', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (3, 'user2', '$2y$10$pDAiS7Y30JibG.qKh03MgeO8fkmIMNrjXC.ogVAi526VRKu8sm7V.', 'user2', 'user', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (4, 'user_force_pw_change', '$2y$10$AvUMEP0RstuGIjngb6a3R.wvE0I5gk4wRI7PzBjaRe3ed1naj/0Ae', 'User (Force PW Change)', 'user', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (5, 'user_no_module', '$2y$10$naqMnK3ANOl1SkOyfZEofOaHRqEK1T2t.0.N.M.Z2OscGj2.2C832', 'User (No Modules)', 'user', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (6, 'user_module_location', '$2y$10$4SXgGX3UBYDhvHX3vnFyrelwRFEpReAIXnZ50m8Uy86CdaDuTf7ki', 'User (Module Location)', 'user', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (7, 'user_module_finance', '$2y$10$U2tVe/2n3hPQDhDHtyACj.MLBPHmZVFWzWLmW1GkbmAZI9QQvEG.S', 'User (Module Finances)', 'user', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (8, 'user_module_cars', '$2y$10$d04PQx8DRVfAUODHMZ1NPeDVTy099ekrk70fvDYygDvX5Pg2WCbRm', 'User (Module Cars)', 'user', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (9, 'user_module_boards', '$2y$10$Z35.cWlpxNFkyoyVPyUc1eej25Z5kJs6.zRZ.X54S8VqnbrRvbjAK', 'User (Module Boards)', 'user', 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (10, 'user_module_crawlers', '$2y$10$PmOrx2pR5pN.nPCGwKrwX.kxMqoL2uM5fKMn6TzgnAJMwUGC.QcU6', 'User (Module Crawlers)', 'user', 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (11, 'user_module_splitbills', '$2y$10$X6zxyJQnNarAHS6eaEKFJuIq7zW0krIIWbNOIhHauj0U0Ot/mmyOO', 'User (Module Splitbills)', 'user', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (12, 'user_module_trips', '$2y$10$MWy6UOXVwkLJAdz9VxCwoOrnhy8SaDmesYoJbriHGIiptzDXc.7re', 'User (Module Trips)', 'user', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills) VALUES (13, 'user_module_timesheets', '$2y$10$gujLijuoSEZ3xOUdjo10Xuw7b6t0aG.3uSYSAobgnSx5I/WwYIfXy', 'User (Module Timesheets)', 'user', 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, force_pw_change, mails_user, mails_finances, mails_board, mails_board_reminder, mails_splitted_bills, secret) VALUES (14, 'user2fa', '$2y$10$zzEUi14yhm3l57c/CLmKj.P3DpvFyuU6gNdk7xcnLrhgnBd5AawSm', 'user2fa', 'user2fa', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 'ZONTUSYMICAFZZBMDZQXGSCXWSEPTKGW'); 

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
(1, 2);
INSERT INTO splitbill_bill (id, sbgroup, user, name, date, time, lat, lng, acc, notice, settleup, exchange_rate, exchange_fee) VALUES
(1, 1, 1,'Test bill', '2020-01-01', '09:00:00', NULL, NULL, NULL, NULL, 0, '1', '0');
INSERT INTO splitbill_bill_users (id, bill, user, paid, spend, paymethod, paid_foreign, spend_foreign) VALUES
(1, 1, 1, '10.00', '10.00', NULL, NULL, NULL);
INSERT INTO splitbill_bill_recurring (id, sbgroup, user, name, notice, settleup, exchange_rate, exchange_fee) VALUES
(1, 1, 1,'Test bill', NULL, 0, '1', '0');
INSERT INTO splitbill_bill_recurring_users (id, bill, user, paid, spend, paymethod, paid_foreign, spend_foreign) VALUES
(1, 1, 1, '10.00', '10.00', NULL, NULL, NULL);

INSERT INTO timesheets_projects (id, user, name, hash) VALUES 
(1, 1, 'Test timesheets project', 'ABCabc123'),
(2, 1, 'Test timesheets project (no access to owner)', 'DEFdef456'); 
INSERT INTO timesheets_projects_users (project, user) VALUES 
(1, 1),
(1, 2);
INSERT INTO timesheets_sheets (id, project, createdBy, changedBy, start, end, diff) VALUES
(1, 1, 1, 1, '2020-01-01 09:00:00', '2020-01-01 12:00:00', 10800);

INSERT INTO trips (id, user, name, hash, notice) VALUES 
(1, 1, 'Test Trip', 'ABCabc123', NULL), 
(2, 1, 'Test Trip (no access to owner)', 'DEFdef456', NULL);
INSERT INTO trips_user (trip, user) VALUES 
(1, 1),
(1, 2);
INSERT INTO trips_event (id, trip, createdBy, changedBy, name, start_date, start_time, start_address, start_lat, start_lng, end_date, end_time, end_address, end_lat, end_lng, type, notice, image, position) VALUES
(1, 1, 3, 3, 'Test Event', '2020-01-01', NULL, NULL, NULL, NULL, '2020-01-02', NULL, NULL, NULL, NULL, 'EVENT', NULL, NULL, 999);

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
INSERT INTO cars_user (car, user) VALUES 
(1, 1),
(1, 2); 

INSERT INTO cars_service (id, createdBy, changedBy, car, date, mileage, type, fuel_volume, fuel_type) VALUES (1, 1, 1, 1, '2020-01-01', 0, 0, '50.00', 1);
INSERT INTO cars_service (id, createdBy, changedBy, car, date, mileage, type, notice, service_oil_before, service_oil_after, service_water_wiper_before, service_water_wiper_after, service_air_front_left_before, service_air_front_left_after, service_air_front_right_before, service_air_front_right_after, service_air_back_left_before, service_air_back_left_after, service_air_back_right_before, service_air_back_right_after, service_tire_change, service_garage) VALUES
(2, 1, 1, 1, '2020-01-01', 0, 1, 'Test', 0, 100, 0, 100, '1.0', '2.0', '1.0', '2.0', '1.0', '2.0', '1.0', '2.0', 1, 1);

INSERT INTO crawlers (id, user, name, hash, filter) VALUES (1, 1, 'Test Crawler', 'ABCabc123', 'createdOn');
INSERT INTO crawlers_user (crawler, user) VALUES 
(1, 1),
(1, 2);
INSERT INTO crawlers_links (id, crawler, createdBy, changedBy, name, link, parent, position) VALUES (1, 1, 2, 2, 'Test Category', 'http://localhost', NULL, 1);
INSERT INTO crawlers_headers (id, crawler, createdBy, changedBy, headline, field_name, field_link, field_content, sortable, diff, prefix, suffix, sort, datatype, position) VALUES 
(1, 1, 1, 1, 'title', 'title', 'link', NULL, 1, 0, NULL, NULL, NULL, NULL, 1),
(2, 1, 1, 1, 'number', 'value', NULL, NULL, 1, 0, NULL, NULL, 'desc', 'DECIMAL', 2);
INSERT INTO crawlers_dataset (id, crawler, createdOn, changedOn, createdBy, changedBy, identifier, saved, data) VALUES
(1, 1, '2020-03-11 12:00:00', '2020-03-11 12:00:00', 1, 1, 'test', 0, '{\"title\":\"Dataset Test\",\"link\":\"http:\\/\\/localhost\",\"value\":\"1\"}');

INSERT INTO boards (id, user, name, hash, archive) VALUES
(1, 1, 'Test Board', 'ABCabc123', 0),
(2, 1, 'Test Board (no access to owner)', 'DEFdef456', 0);
INSERT INTO boards_user (board, user) VALUES
(1, 1),
(1, 2);
INSERT INTO boards_labels (id, board, user, name, background_color, text_color) VALUES
(1, 1, 1, 'Test Label', '#ffff00', '#000000');
INSERT INTO boards_stacks (id, board, createdBy, changedBy, name, archive, position) VALUES
(1, 1, 1, 1, 'Test Stack', 0, 999);
INSERT INTO boards_cards (id, stack, createdBy, changedBy, title, date, time, description, archive, position, hash) VALUES
(1, 1, 1, 1, 'Test Card', '2020-01-01', '12:00:00', 'Test Description', 0, 999, 'ABCabc123');
INSERT INTO boards_cards_label (card, label) VALUES
(1, 1);
INSERT INTO boards_cards_user (card, user) VALUES
(1, 1),
(1, 2);

INSERT INTO notifications_categories (id, name, identifier, internal) VALUES 
(1, 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 1),
(2, 'Test Notification Category', 'test_notification_cat', 0);

INSERT INTO notifications_clients (id, user, endpoint, authToken, publicKey, contentEncoding, ip, agent) VALUES (1, 1, 'endpoint', 'auth', 'p256dh', 'contentEncoding', '127.0.0.1', 'TEST');

INSERT INTO notifications (id, category, user, title, message, seen, link) VALUES 
(1, 1, 1, 'Test Notification 1', 'Test', NULL, ''),
(2, 1, 1, 'Test Notification 2', 'Test', NULL, ''),
(3, 1, 1, 'Test Notification 3', 'Test', NULL, ''),
(4, 1, 1, 'Test Notification 4', 'Test', NULL, ''),
(5, 1, 1, 'Test Notification 5', 'Test', NULL, ''),
(6, 1, 1, 'Test Notification 6', 'Test', NULL, ''),
(7, 1, 1, 'Test Notification 7', 'Test', NULL, ''),
(8, 1, 1, 'Test Notification 8', 'Test', NULL, ''),
(9, 1, 1, 'Test Notification 9', 'Test', NULL, ''),
(10, 1, 1, 'Test Notification 10', 'Test', NULL, '');

INSERT INTO global_banlist (id, ip, username) VALUES 
(1, '127.0.0.2', 'user2'), 
(2, '127.0.0.2', 'user2'),
(3, '127.0.0.2', 'user2');

INSERT INTO global_users_application_passwords (id, user, name, password) VALUES
(1, 1, 'application', '$2y$10$0GLfqS6qKvfNBvqPitciAOi96Sk.Yvg9ESl4GiYRjT1AT0GgKm08S'),
(2, 2, 'application', '$2y$10$0GLfqS6qKvfNBvqPitciAOi96Sk.Yvg9ESl4GiYRjT1AT0GgKm08S'),
(3, 3, 'application', '$2y$10$0GLfqS6qKvfNBvqPitciAOi96Sk.Yvg9ESl4GiYRjT1AT0GgKm08S');

INSERT INTO workouts_muscles (id, name, category) VALUES
(1, 'Test muscle 1',0),
(2, 'Test muscle 2',1),
(3, 'Test muscle 3',2),
(4, 'Test muscle 4',3),
(5, 'Test muscle 5',2);

INSERT INTO workouts_bodyparts (id, name) VALUES
(1, 'Test bodypart');

INSERT INTO workouts_plans (id, user, name) VALUES
(1, 1, 'Test workout plan');

INSERT INTO workouts_exercises (id, name) VALUES
(1, 'Exercise 1'),
(2, 'Exercise 2'),
(3, 'Exercise 3');