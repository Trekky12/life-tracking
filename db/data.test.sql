INSERT INTO global_settings (name, value, type) VALUES ('lastRunRecurring', 0, 'Date'), ('lastRunFinanceSummary', 0, 'Date'), ('lastRunCardReminder', 0, 'Date'); 

INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (1, 'admin', '$2y$10$gbDsuY1GyMJo78ueqWy/SOstNf2DeLpN3mKTUS9Yp.bwG7i4y4.KK', 'admin', 'admin', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (2, 'user', '$2y$10$tC4twYpdcq0TibT6MZsdI.Tmu36UkTxFNymd2icHv5KVB1oEu5mBW', 'user', 'user', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (3, 'user2', '$2y$10$pDAiS7Y30JibG.qKh03MgeO8fkmIMNrjXC.ogVAi526VRKu8sm7V.', 'user2', 'user', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (4, 'user_force_pw_change', '$2y$10$AvUMEP0RstuGIjngb6a3R.wvE0I5gk4wRI7PzBjaRe3ed1naj/0Ae', 'User (Force PW Change)', 'user', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (5, 'user_no_module', '$2y$10$naqMnK3ANOl1SkOyfZEofOaHRqEK1T2t.0.N.M.Z2OscGj2.2C832', 'User (No Modules)', 'user', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (6, 'user_module_location', '$2y$10$4SXgGX3UBYDhvHX3vnFyrelwRFEpReAIXnZ50m8Uy86CdaDuTf7ki', 'User (Module Location)', 'user', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (7, 'user_module_finance', '$2y$10$U2tVe/2n3hPQDhDHtyACj.MLBPHmZVFWzWLmW1GkbmAZI9QQvEG.S', 'User (Module Finances)', 'user', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (8, 'user_module_cars', '$2y$10$d04PQx8DRVfAUODHMZ1NPeDVTy099ekrk70fvDYygDvX5Pg2WCbRm', 'User (Module Cars)', 'user', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (9, 'user_module_boards', '$2y$10$Z35.cWlpxNFkyoyVPyUc1eej25Z5kJs6.zRZ.X54S8VqnbrRvbjAK', 'User (Module Boards)', 'user', 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (10, 'user_module_crawlers', '$2y$10$PmOrx2pR5pN.nPCGwKrwX.kxMqoL2uM5fKMn6TzgnAJMwUGC.QcU6', 'User (Module Crawlers)', 'user', 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (11, 'user_module_splitbills', '$2y$10$X6zxyJQnNarAHS6eaEKFJuIq7zW0krIIWbNOIhHauj0U0Ot/mmyOO', 'User (Module Splitbills)', 'user', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (12, 'user_module_trips', '$2y$10$MWy6UOXVwkLJAdz9VxCwoOrnhy8SaDmesYoJbriHGIiptzDXc.7re', 'User (Module Trips)', 'user', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (13, 'user_module_timesheets', '$2y$10$gujLijuoSEZ3xOUdjo10Xuw7b6t0aG.3uSYSAobgnSx5I/WwYIfXy', 'User (Module Timesheets)', 'user', 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change, secret) VALUES (14, 'user2fa', '$2y$10$zzEUi14yhm3l57c/CLmKj.P3DpvFyuU6gNdk7xcnLrhgnBd5AawSm', 'user2fa', 'user2fa', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 'ZONTUSYMICAFZZBMDZQXGSCXWSEPTKGW'); 
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (15, 'user_module_workouts', '$2y$10$LKQvexVWeM6Sm0A6oWVTqOLxcO8Fb1NUE0O3c61wlKiUvORT0f7am', 'User (Module Workouts)', 'user', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0);
INSERT INTO global_users (id, login, password, name, role, module_location, module_finance, module_cars, module_boards, module_crawlers, module_splitbills, module_trips, module_timesheets, module_workouts, module_recipes, force_pw_change) VALUES (16, 'user_module_recipes', '$2y$10$gcYdyJe502suQKdr2j85F.9Y1MMmp6MwPPPxu86XOZX92a8yMCUWm', 'User (Module Recipes)', 'user', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0);

INSERT INTO finances_categories (id, user, name, is_default) VALUES 
(1, 1, 'not categorized', 1),
(2, 2, 'not categorized', 1);
INSERT INTO finances_paymethods (id, user, name, is_default) VALUES 
(1, 1, 'Test Paymethod', 1),
(2, 2, 'Test Paymethod', 1); 

INSERT INTO splitbill_groups (id, user, name, hash, add_finances, currency, exchange_rate, exchange_fee) VALUES 
(1, 1, 'Test splitted bills group', 'ABCabc123', 0, '€', '1', '0'),
(2, 1, 'Test splitted bills group (no access to owner)', 'DEFdef456', 0, '€', '1', '0'),
(3, 1, 'Test Group Island', 'Opnel5aKBz', 0, 'ISK', '40', '1.75');
INSERT INTO splitbill_groups_user (sbgroup, user) VALUES 
(1, 1),
(1, 2),
(3, 1),
(3, 2),
(2, 2);
INSERT INTO splitbill_bill (id, sbgroup, user, name, date, time, lat, lng, acc, notice, settleup, exchange_rate, exchange_fee, spend_by, paid_by) VALUES
(1, 1, 1,'Test bill', '2020-01-01', '09:00:00', NULL, NULL, NULL, NULL, 0, '1', '0', NULl, NULL),
(2, 3, 1, 'Test', '2021-05-18', '15:00:00', NULL, NULL, NULL, NULL, 0, '40', '1.75', 'individual', 'individual'),
(3, 2, 2,'Test bill 2', '2022-01-01', '09:00:00', NULL, NULL, NULL, NULL, 0, '1', '0', NULl, NULL),
(4, 1, 2,'Test bill 3', '2022-01-01', '09:00:00', NULL, NULL, NULL, NULL, 0, '1', '0', NULl, NULL);
INSERT INTO splitbill_bill_users (id, bill, user, paid, spend, paymethod, paid_foreign, spend_foreign) VALUES
(1, 1, 1, '10.00', '10.00', NULL, NULL, NULL),
(2, 2, 1, '2.54', '1.27', 1, '100.00', '50.00'),
(3, 2, 3, '0.00', '1.27', NULL, '0.00', '50.00'),
(4, 4, 2, '10.00', '10.00', NULL, NULL, NULL);

INSERT INTO splitbill_bill_recurring (id, sbgroup, user, name, notice, settleup, exchange_rate, exchange_fee) VALUES
(1, 1, 1,'Test bill', NULL, 0, '1', '0'),
(2, 2, 2,'Test bill 2', NULL, 0, '1', '0'),
(3, 1, 2,'Test bill 3', NULL, 0, '1', '0');
INSERT INTO splitbill_bill_recurring_users (id, bill, user, paid, spend, paymethod, paid_foreign, spend_foreign) VALUES
(1, 1, 1, '10.00', '10.00', NULL, NULL, NULL);

INSERT INTO timesheets_projects (id, user, name, hash) VALUES 
(1, 1, 'Test timesheets project', 'ABCabc123'),
(2, 1, 'Test timesheets project (no access to owner)', 'DEFdef456'); 
INSERT INTO timesheets_projects_users (project, user) VALUES 
(1, 1),
(1, 2);
INSERT INTO timesheets_sheets (id, project, createdBy, changedBy, start, end, duration) VALUES
(1, 1, 1, 1, '2020-01-01 09:00:00', '2020-01-01 12:00:00', 10800),
(2, 1, 1, 1, '2021-09-13 09:00:00', '2021-09-13 12:00:00', 10800),
(3, 2, 1, 1, '2021-09-14 09:00:00', '2021-09-14 12:00:00', 10800);
INSERT INTO timesheets_categories (id, project, name) VALUES 
(1, 1, 'Test timesheets project category 1'),
(2, 1, 'Test timesheets project category 2'),
(3, 2, 'Test timesheets project category 3');
INSERT INTO timesheets_sheets_categories (sheet, category) VALUES 
(2, 1);
INSERT INTO timesheets_categorybudgets (id, project, name, categorization, main_category, value, warning1, warning2, warning3) VALUES 
(1, 1, 'Test timesheets project category budget 1', 'count', 1, 5, 2, 3, 4),
(2, 1, 'Test timesheets project category budget 2', 'duration', 2, 10800, 4200, 8400, 9600),
(3, 2, 'Test timesheets project category budget 3', 'count', 1, 10, null, null, null);
INSERT INTO timesheets_categorybudgets_categories (categorybudget, category) VALUES 
(1, 1),
(2, 2);
INSERT INTO timesheets_sheets_notices (id, sheet) VALUES
(1, 1),
(2, 3);

INSERT INTO trips (id, user, name, hash, notice) VALUES 
(1, 1, 'Test Trip', 'ABCabc123', NULL), 
(2, 1, 'Test Trip (no access to owner)', 'DEFdef456', NULL);
INSERT INTO trips_user (trip, user) VALUES 
(1, 1),
(1, 2);
INSERT INTO trips_event (id, trip, createdBy, changedBy, name, start_date, start_time, start_address, start_lat, start_lng, end_date, end_time, end_address, end_lat, end_lng, type, notice, image, position) VALUES
(1, 1, 3, 3, 'Test Event', '2020-01-01', NULL, NULL, NULL, NULL, '2020-01-02', NULL, NULL, NULL, NULL, 'EVENT', NULL, NULL, 999),
(2, 2, NULL, NULL, 'Test Event 2', '2021-01-01', NULL, NULL, NULL, NULL, '2021-01-02', NULL, NULL, NULL, NULL, 'EVENT', NULL, NULL, 999);
INSERT INTO trips_route (id, trip) VALUES
(1, 1),
(2, 2);

INSERT INTO finances (id, user, type, date, time, category, description, value, common, common_value, notice, fixed, lat, lng, acc, bill, paymethod) VALUES
(1, 1, 0, '2020-01-01', '14:44:49', 1, 'Test expense', '10.00', 0, NULL, NULL, 0, '52.51484846941138', '13.38930845260620', '0.000', NULL, 1),
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

INSERT INTO cars_service (id, createdBy, changedBy, car, date, mileage, type, fuel_volume, fuel_type, lat, lng, acc) VALUES 
(1, 1, 1, 1, '2020-01-01', 0, 0, '50.00', 1, '52.51958898995020', '13.38857889175415', '0.000');
INSERT INTO cars_service (id, createdBy, changedBy, car, date, mileage, type, notice, service_oil_before, service_oil_after, service_water_wiper_before, service_water_wiper_after, service_air_front_left_before, service_air_front_left_after, service_air_front_right_before, service_air_front_right_after, service_air_back_left_before, service_air_back_left_after, service_air_back_right_before, service_air_back_right_after, service_tire_change, service_garage) VALUES
(2, 1, 1, 1, '2020-01-01', 0, 1, 'Test', 0, 100, 0, 100, '1.0', '2.0', '1.0', '2.0', '1.0', '2.0', '1.0', '2.0', 1, 1);

INSERT INTO crawlers (id, user, name, hash, filter) VALUES 
(1, 1, 'Test Crawler', 'ABCabc123', 'createdOn'),
(2, 2, 'Test Crawler 2', 'DEFdef456', 'createdOn');
INSERT INTO crawlers_user (crawler, user) VALUES 
(1, 1),
(1, 2),
(2, 2);

INSERT INTO crawlers_links (id, crawler, createdBy, changedBy, name, link, parent, position) VALUES 
(1, 1, 1, 1, 'Test Category', 'http://localhost', NULL, 1),
(2, 1, 1, 1, 'Test Link', '#', 1, 1),
(3, 2, 2, 2, 'Test Link', '#', 1, 1);

INSERT INTO crawlers_headers (id, crawler, createdBy, changedBy, headline, field_name, field_link, field_content, sortable, diff, prefix, suffix, sort, datatype, position) VALUES 
(1, 1, 1, 1, 'title', 'title', 'link', NULL, 1, 0, NULL, NULL, NULL, NULL, 1),
(2, 1, 1, 1, 'title previous', 'title', 'link', NULL, 0, 1, NULL, NULL, NULL, NULL, 2),
(3, 1, 1, 1, 'number', 'value', NULL, NULL, 1, 0, NULL, NULL, 'desc', 'DECIMAL', 3),
(4, 2, 2, 2, 'title', 'title', 'link', NULL, 1, 0, NULL, NULL, NULL, NULL, 1);

INSERT INTO crawlers_dataset (id, crawler, createdOn, changedOn, createdBy, changedBy, identifier, saved, data, diff) VALUES
(1, 1, '2020-03-11 12:00:00', '2020-03-11 12:00:00', 1, 1, 'test', 0, '{\"title\":\"Dataset 1 Update\",\"link\":\"http:\\/\\/localhost\",\"value\":\"1\"}', '{\"title\":\"Dataset 1\",\"link\":\"http:\\/\\/localhost\",\"value\":1}'),
(2, 1, '2020-03-12 12:00:00', '2020-03-12 12:00:00', 1, 1, 'test', 1, '{\"title\":\"Dataset Test\",\"link\":\"http:\\/\\/localhost\",\"value\":\"1\"}', NULL),
(3, 2, '2020-03-12 12:00:00', '2020-03-12 12:00:00', 1, 1, 'test', 1, '{\"title\":\"Dataset 2 Test\",\"link\":\"http:\\/\\/localhost\",\"value\":\"1\"}', NULL);

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
(1, 'Test Notification Category', 'test_notification_cat', 0),
(2, 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 1),
(3, 'NOTIFICATION_CATEGORY_FINANCES_RECURRING', 'NOTIFICATION_CATEGORY_FINANCES_RECURRING', 1),
(4, 'NOTIFICATION_CATEGORY_BOARDS_CARD_ADD', 'NOTIFICATION_CATEGORY_BOARDS_CARD_ADD', 1);


INSERT INTO notifications_categories_user (category, user) VALUES 
(2, 1);

INSERT INTO notifications_clients (id, user, endpoint, authToken, publicKey, contentEncoding, ip, agent, type) VALUES 
(1, 1, 'endpoint', 'auth', 'p256dh', 'contentEncoding', '127.0.0.1', 'TEST', NULL),
(2, 2, 'http://ifttt.com/#', NULL, NULL, NULL, '127.0.0.1', 'TEST', 'ifttt');

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

INSERT INTO mail_categories (id, name, identifier) VALUES 
(1, 'MAIL_CATEGORY_FINANCE_STATISTIC', 'MAIL_CATEGORY_FINANCE_STATISTIC'),
(2, 'MAIL_CATEGORY_SPLITTED_BILLS', 'MAIL_CATEGORY_SPLITTED_BILLS'),
(3, 'MAIL_CATEGORY_BOARDS_ADD', 'MAIL_CATEGORY_BOARDS_ADD'),
(4, 'MAIL_CATEGORY_BOARDS_CARD_DUE', 'MAIL_CATEGORY_BOARDS_CARD_DUE'),
(5, 'MAIL_CATEGORY_BOARDS_CARD_ADD', 'MAIL_CATEGORY_BOARDS_CARD_ADD');

INSERT INTO global_users_mobile_favorites (id, user, position, url, icon) VALUES
(1, 1, 1, '/timesheets', 'fas fa-clock');

INSERT INTO locations (id, createdOn, user, gps_lat, gps_lng, gps_acc, steps) VALUES
(1, '2021-01-01 12:00:00', 1, '52.51697820882550', '13.38842868804932', '0.000', 10),
(2, '2021-01-01 13:00:00', 1, '52.51713489924289', '13.39113235473633', '0.000', 20),
(3, '2021-01-01 14:00:00', 1, '52.51685089744994', '13.38895976543427', '0.000', 30),
(4, '2021-01-01 15:00:00', 1, '52.52107809075955', '13.41095924377441', '0.000', 50);


INSERT INTO global_banlist (id, ip, username) VALUES 
(1, '127.0.0.2', 'user2'), 
(2, '127.0.0.2', 'user2'),
(3, '127.0.0.2', 'user2');

INSERT INTO global_users_application_passwords (id, user, name, password) VALUES
(1, 1, 'application', '$2y$10$0GLfqS6qKvfNBvqPitciAOi96Sk.Yvg9ESl4GiYRjT1AT0GgKm08S'),
(2, 2, 'application', '$2y$10$0GLfqS6qKvfNBvqPitciAOi96Sk.Yvg9ESl4GiYRjT1AT0GgKm08S'),
(3, 3, 'application', '$2y$10$0GLfqS6qKvfNBvqPitciAOi96Sk.Yvg9ESl4GiYRjT1AT0GgKm08S');

INSERT INTO workouts_muscles (id, name) VALUES
(1, 'Test muscle 1'),
(2, 'Test muscle 2'),
(3, 'Test muscle 3'),
(4, 'Test muscle 4'),
(5, 'Test muscle 5');

INSERT INTO workouts_bodyparts (id, name) VALUES
(1, 'Test bodypart');

INSERT INTO workouts_plans (id, user, name, hash, is_template) VALUES
(1, 1, 'Test workout plan', 'ABCabc123', 0),
(2, 1, 'Test workout template plan', 'ABCabc456', 1),
(3, 1, 'Test workout plan 2', 'DEFdef123', 0);

INSERT INTO workouts_exercises (id, name, category, mainBodyPart, mainMuscle) VALUES
(1, 'Exercise 1', 0, 1, 1),
(2, 'Exercise 2', 1, 1, 1),
(3, 'Exercise 3', 2, 1, 1);

INSERT INTO workouts_plans_exercises (id, plan, exercise, position, sets, type, notice, is_child) VALUES
(1, 1, 1, 0, '[{\"repeats\":10,\"weight\":null,\"time\":null,\"distance\":null},{\"repeats\":20,\"weight\":null,\"time\":null,\"distance\":null},{\"repeats\":30,\"weight\":null,\"time\":null,\"distance\":null}]', 'exercise', NULL, 0),
(2, 1, 2, 1, '[{\"repeats\":10,\"weight\":5,\"time\":null,\"distance\":null},{\"repeats\":20,\"weight\":10,\"time\":null,\"distance\":null},{\"repeats\":30,\"weight\":20,\"time\":null,\"distance\":null}]', 'exercise', NULL, 0),
(3, 1, 3, 2, '[{\"repeats\":null,\"weight\":null,\"time\":30,\"distance\":1}]', 'exercise', NULL, 0),
(4, 2, NULL, 0, '[]', 'day', 'Day 1', 0),
(5, 2, 1, 1, '[{\"repeats\":10,\"weight\":null,\"time\":null,\"distance\":null},{\"repeats\":10,\"weight\":null,\"time\":null,\"distance\":null},{\"repeats\":10,\"weight\":null,\"time\":null,\"distance\":null}]', 'exercise', NULL, 0),
(6, 2, NULL, 2, '[]', 'day', 'Day 2', 0),
(7, 2, NULL, 3, '[]', 'superset', NULL, 0),
(8, 2, 2, 4, '[{\"repeats\":10,\"weight\":5,\"time\":null,\"distance\":null},{\"repeats\":10,\"weight\":5,\"time\":null,\"distance\":null},{\"repeats\":10,\"weight\":5,\"time\":null,\"distance\":null}]', 'exercise', NULL, 1),
(9, 2, 3, 5, '[{\"repeats\":null,\"weight\":null,\"time\":600,\"distance\":10}]', 'exercise', NULL, 1),
(10, 3, NULL, 0, '[]', 'day', 'Day 1', 0),
(11, 3, 1, 1, '[{\"repeats\":10,\"weight\":null,\"time\":null,\"distance\":null},{\"repeats\":10,\"weight\":null,\"time\":null,\"distance\":null},{\"repeats\":10,\"weight\":null,\"time\":null,\"distance\":null}]', 'exercise', NULL, 0),
(12, 3, NULL, 2, '[]', 'day', 'Day 2', 0),
(13, 3, NULL, 3, '[]', 'superset', NULL, 0),
(14, 3, 2, 4, '[{\"repeats\":10,\"weight\":5,\"time\":null,\"distance\":null},{\"repeats\":10,\"weight\":5,\"time\":null,\"distance\":null},{\"repeats\":10,\"weight\":5,\"time\":null,\"distance\":null}]', 'exercise', NULL, 1),
(15, 3, 3, 5, '[{\"repeats\":null,\"weight\":null,\"time\":600,\"distance\":10}]', 'exercise', NULL, 1);


INSERT INTO workouts_sessions (id, user, plan, date) VALUES
(1, 1, 1, '2020-08-29');

INSERT INTO workouts_sessions_exercises (id, session, exercise, position, sets, type, notice, is_child) VALUES
(1, 1, 1, 0, '[{\"repeats\":10,\"weight\":null,\"time\":null,\"distance\":null},{\"repeats\":20,\"weight\":null,\"time\":null,\"distance\":null},{\"repeats\":30,\"weight\":null,\"time\":null,\"distance\":null}]', 'exercise', NULL, 0),
(2, 1, 2, 1, '[{\"repeats\":10,\"weight\":5,\"time\":null,\"distance\":null},{\"repeats\":20,\"weight\":10,\"time\":null,\"distance\":null},{\"repeats\":30,\"weight\":20,\"time\":null,\"distance\":null}]', 'exercise', NULL, 0),
(3, 1, 3, 2, '[{\"repeats\":null,\"weight\":null,\"time\":30,\"distance\":1}]', 'exercise', NULL, 0);


INSERT INTO recipes_cookbooks (id, user, name, hash) VALUES 
(1, 1, 'Test recipe cookbook 1', 'ABCabc123'),
(2, 1, 'Test recipe cookbook 2 (no access to owner)', 'DEFdef456'); 
INSERT INTO recipes_cookbooks_users (cookbook, user) VALUES 
(1, 1),
(1, 2);

INSERT INTO recipes_ingredients (id, createdBy, name) VALUES
(1, 1, 'Test Ingredient 1'),
(2, 1, 'Test Ingredient 2');

INSERT INTO recipes (id, createdBy, name, description, image, preparation_time, waiting_time, servings, link, hash) VALUES
(1, 1, 'Test Recipe 1', 'Test Description', NULL, 1, 2, 3, 'https://www.google.com', 'ABCabc123'),
(2, 1, 'Test Recipe 2', NULL, NULL, NULL, NULL, NULL, NULL, 'DEFdef456'),
(3, 1, 'Test Recipe 3', NULL, NULL, NULL, NULL, NULL, NULL, 'GHIghi789');

INSERT INTO recipes_steps (id, createdBy, recipe, position, name, description, preparation_time, waiting_time) VALUES
(1, 1, 1, 0, 'Schritt 1', 'Description Step 1', 1, 2),
(2, 1, 1, 1, 'Schritt 2', 'Cooking', NULL, NULL);

INSERT INTO recipes_recipe_ingredients (id, createdBy, recipe, step, ingredient, position, amount, notice) VALUES
(1, 1, 1, 1, 1, 0, '1', NULL),
(2, 1, 1, 1, 2, 1, '2', NULL);

INSERT INTO recipes_cookbook_recipes (id, createdBy, cookbook, recipe) VALUES 
(1, 1, 1, 1),
(2, 1, 2, 1); 

INSERT INTO recipes_mealplans (id, user, name, hash) VALUES 
(1, 1, 'Test recipe mealplan 1', 'ABCabc123'),
(2, 1, 'Test recipe mealplan 2 (no access to owner)', 'DEFdef456'); 
INSERT INTO recipes_mealplans_users (mealplan, user) VALUES 
(1, 1),
(1, 2);
INSERT INTO recipes_mealplans_recipes (id, createdBy, mealplan, recipe, date, position, notice) VALUES
(1, 1, 1, 1, '2021-08-23', 0, NULL),
(2, 1, 1, 2, '2021-08-24', 0, NULL),
(3, 1, 1, NULL, '2021-08-25', 999, 'Notice without specific recipe');


INSERT INTO global_widgets (id, user, name, options, position) VALUES
(1, 2, 'last_finance_entries', '[]', 999),
(2, 2, 'finances_month_expenses', '[]', 999),
(3, 2, 'last_refuel', '{\"car\":\"1\"}', 999);


INSERT INTO activities (id, createdOn, user, type, module, controller, object, object_id, object_description, parent_object, parent_object_id, parent_object_description, link) VALUES
(1, '2021-05-23 09:50:46', 1, 'create', 'finances', NULL, 'App\\Domain\\Finances\\FinancesEntry', 3, 'Test Entry (4.00 €)', NULL, NULL, NULL, '/finances/edit/3'),
(2, '2021-05-23 10:06:27', 1, 'create', 'finances', NULL, 'App\\Domain\\Finances\\FinancesEntry', 4, 'Test Entry (4.00 €)', NULL, NULL, NULL, '/finances/edit/4'),
(3, '2021-05-23 10:10:25', 1, 'create', 'finances', NULL, 'App\\Domain\\Finances\\FinancesEntry', 5, 'Test Entry (4.00 €)', NULL, NULL, NULL, '/finances/edit/5'),
(4, '2021-05-23 10:20:37', 1, 'create', 'finances', NULL, 'App\\Domain\\Finances\\FinancesEntry', 6, 'Test Entry (4.00 €)', NULL, NULL, NULL, '/finances/edit/6'),
(5, '2021-05-23 10:28:36', 1, 'create', 'notifications', NULL, 'App\\Domain\\Notifications\\Categories\\Category', 5, 'Test Notification Category 2', NULL, NULL, NULL, '/notifications/categories/edit/5'),
(6, '2021-05-23 10:28:37', 1, 'update', 'notifications', NULL, 'App\\Domain\\Notifications\\Categories\\Category', 5, 'Test Notification Category 2 Updated', NULL, NULL, NULL, '/notifications/categories/edit/5'),
(7, '2021-05-23 10:28:38', 1, 'delete', 'notifications', NULL, 'App\\Domain\\Notifications\\Categories\\Category', 5, 'Test Notification Category 2 Updated', NULL, NULL, NULL, '/notifications/categories/edit/5'),
(8, '2021-05-23 10:28:46', 1, 'delete', 'notifications', NULL, 'App\\Domain\\Notifications\\Clients\\NotificationClient', 7, ' (2021-05-23 12:28:40)', NULL, NULL, NULL, '/notifications/clients/'),
(9, '2021-05-23 10:28:48', 1, 'create', 'general', NULL, 'App\\Domain\\User\\User', 17, 'a_test', NULL, NULL, NULL, '/users/edit/17'),
(10, '2021-05-23 10:28:50', 1, 'update', 'general', NULL, 'App\\Domain\\User\\User', 17, 'a_test', NULL, NULL, NULL, '/users/edit/17'),
(11, '2021-05-23 10:28:51', 1, 'delete', 'general', NULL, 'App\\Domain\\User\\User', 17, 'a_test', NULL, NULL, NULL, '/users/edit/17'),
(12, '2021-05-23 10:28:59', 1, 'create', 'boards', NULL, 'App\\Domain\\Board\\Board', 3, 'Test Board 2', NULL, NULL, NULL, '/boards/edit/3'),
(13, '2021-05-23 10:29:00', 1, 'update', 'boards', NULL, 'App\\Domain\\Board\\Board', 3, 'Test Board 2 Updated', NULL, NULL, NULL, '/boards/edit/3'),
(14, '2021-05-23 10:29:01', 1, 'delete', 'boards', NULL, 'App\\Domain\\Board\\Board', 3, 'Test Board 2 Updated', NULL, NULL, NULL, '/boards/edit/3'),
(15, '2021-05-23 10:29:02', 2, 'create', 'boards', NULL, 'App\\Domain\\Board\\Card\\Card', 2, 'Test Card 2', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(16, '2021-05-23 10:29:03', 2, 'update', 'boards', NULL, 'App\\Domain\\Board\\Card\\Card', 2, 'Test Card 2 Updated', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(17, '2021-05-23 10:29:04', 2, 'delete', 'boards', NULL, 'App\\Domain\\Board\\Card\\Card', 2, 'Test Card 2 Updated', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(18, '2021-05-23 10:29:06', 1, 'create', 'boards', NULL, 'App\\Domain\\Board\\Card\\Card', 3, 'Test Card 2', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(19, '2021-05-23 10:29:07', 1, 'update', 'boards', NULL, 'App\\Domain\\Board\\Card\\Card', 3, 'Test Card 2 Updated', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(20, '2021-05-23 10:29:08', 1, 'delete', 'boards', NULL, 'App\\Domain\\Board\\Card\\Card', 3, 'Test Card 2 Updated', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(21, '2021-05-23 10:29:11', 2, 'create', 'boards', NULL, 'App\\Domain\\Board\\Label\\Label', 2, 'Test Label 2', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(22, '2021-05-23 10:29:12', 2, 'update', 'boards', NULL, 'App\\Domain\\Board\\Label\\Label', 2, 'Test Label 2 Updated', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(23, '2021-05-23 10:29:13', 2, 'delete', 'boards', NULL, 'App\\Domain\\Board\\Label\\Label', 2, 'Test Label 2 Updated', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(24, '2021-05-23 10:29:15', 1, 'create', 'boards', NULL, 'App\\Domain\\Board\\Label\\Label', 3, 'Test Label 2', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123'),
(25, '2021-05-23 10:29:16', 1, 'update', 'boards', NULL, 'App\\Domain\\Board\\Label\\Label', 3, 'Test Label 2 Updated', 'App\\Domain\\Board\\Board', 1, 'Test Board', '/boards/view/ABCabc123');

INSERT INTO activities_users (createdOn, activity, user) VALUES
('2021-05-23 09:50:46', 1, 1),
('2021-05-23 10:06:27', 2, 1),
('2021-05-23 10:10:25', 3, 1),
('2021-05-23 10:20:37', 4, 1),
('2021-05-23 10:28:46', 8, 1),
('2021-05-23 10:28:59', 12, 1),
('2021-05-23 10:29:00', 13, 1),
('2021-05-23 10:29:00', 13, 3),
('2021-05-23 10:29:01', 14, 1),
('2021-05-23 10:29:01', 14, 3),
('2021-05-23 10:29:02', 15, 1),
('2021-05-23 10:29:02', 15, 2),
('2021-05-23 10:29:03', 16, 1),
('2021-05-23 10:29:03', 16, 2),
('2021-05-23 10:29:04', 17, 1),
('2021-05-23 10:29:04', 17, 2),
('2021-05-23 10:29:06', 18, 1),
('2021-05-23 10:29:06', 18, 2),
('2021-05-23 10:29:07', 19, 1),
('2021-05-23 10:29:07', 19, 2),
('2021-05-23 10:29:08', 20, 1),
('2021-05-23 10:29:08', 20, 2),
('2021-05-23 10:29:11', 21, 1),
('2021-05-23 10:29:11', 21, 2),
('2021-05-23 10:29:12', 22, 1),
('2021-05-23 10:29:12', 22, 2),
('2021-05-23 10:29:13', 23, 1),
('2021-05-23 10:29:13', 23, 2),
('2021-05-23 10:29:15', 24, 1),
('2021-05-23 10:29:15', 24, 2),
('2021-05-23 10:29:16', 25, 1),
('2021-05-23 10:29:16', 25, 2);