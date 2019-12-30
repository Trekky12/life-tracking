DROP TABLE IF EXISTS global_users;
CREATE TABLE IF NOT EXISTS global_users (
    id INTEGER unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    login VARCHAR(50) NOT NULL,
    password VARCHAR(255) NULL,
    name varchar(255) DEFAULT NULL,
    lastname varchar(255) DEFAULT NULL,
    mail varchar(255) DEFAULT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    image VARCHAR(255) NULL,
    module_location int(1) DEFAULT 0,
    module_finance int(1) DEFAULT 0,
    module_cars int(1) DEFAULT 0,
    module_boards int(1) DEFAULT 0,
    module_crawlers int(1) DEFAULT 0,
    module_splitbills int(1) DEFAULT 0,
    module_trips int(1) DEFAULT 0,
    module_timesheets int(1) DEFAULT 0,
    force_pw_change int(1) DEFAULT 1,
    mails_user int(1) DEFAULT 1,
    mails_finances int(1) DEFAULT 1,
    mails_board int(1) DEFAULT 1,
    mails_board_reminder int(1) DEFAULT 1,
    mails_splitted_bills  int(1) DEFAULT 1,
    start_url varchar(255) DEFAULT NULL,
    PRIMARY KEY(id),
    UNIQUE(login)
);
INSERT INTO global_users (login, password, role) VALUES ('admin', '$2y$10$gbDsuY1GyMJo78ueqWy/SOstNf2DeLpN3mKTUS9Yp.bwG7i4y4.KK', 'admin');

/**
ALTER TABLE global_users ADD module_crawlers INT(1) DEFAULT 0 AFTER module_boards; 
ALTER TABLE global_users ADD module_timesheets INT(1) DEFAULT 0 AFTER module_trips; 
*/

DROP TABLE IF EXISTS global_banlist;
CREATE TABLE global_banlist (
    id INTEGER unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(255) NOT NULL,
    username varchar(255) DEFAULT NULL,
    changedOn TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS global_tokens;
CREATE TABLE global_tokens (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned NOT NULL,
    token VARCHAR(140) NOT NULL,
    ip VARCHAR(255) NULL,
    agent VARCHAR(255) NULL,
    PRIMARY KEY (id),
    UNIQUE(token),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS locations;
CREATE TABLE locations (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    identifier varchar(255) DEFAULT NULL,
    device varchar(255) DEFAULT NULL,
    date varchar(255) DEFAULT NULL,
    time varchar(255) DEFAULT NULL,
    times int(20) DEFAULT NULL,
    ups int(20) DEFAULT NULL,
    batt int(3) DEFAULT NULL,
    wifi int(1) DEFAULT NULL,
    gps int(1) DEFAULT NULL,
    screen int(1) DEFAULT NULL,
    mfield DECIMAL(10,6) DEFAULT NULL,
    gps_lat DECIMAL(17,14) DEFAULT NULL,
    gps_lng DECIMAL(17,14) DEFAULT NULL,
    gps_acc DECIMAL(10,3) DEFAULT NULL,
    gps_alt DECIMAL(20,14) DEFAULT NULL,
    gps_alt_acc DECIMAL(10,3) DEFAULT NULL,
    gps_spd DECIMAL(12,9) DEFAULT NULL,
    gps_spd_acc DECIMAL(10,3) DEFAULT NULL,
    gps_tms int(20) DEFAULT NULL,
    gps_bearing DECIMAL(6,1) DEFAULT NULL,
    gps_bearing_acc DECIMAL(6,3) DEFAULT NULL,
    net_lat DECIMAL(17,14) DEFAULT NULL,
    net_lng DECIMAL(17,14) DEFAULT NULL,
    net_acc DECIMAL(10,3) DEFAULT NULL,
    net_tms int(20) DEFAULT NULL,
    cell_id varchar(255) DEFAULT NULL,
    cell_sig varchar(255) DEFAULT NULL,
    cell_srv varchar(255) DEFAULT NULL,
    steps int(20)  DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS finances_categories;
CREATE TABLE finances_categories (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    is_default int(1) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO finances_categories (id, user, name) VALUES (1, 1, 'not categorized');

DROP TABLE IF EXISTS finances_paymethods;
CREATE TABLE finances_paymethods (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    is_default int(1) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS finances;
CREATE TABLE finances (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    type int(1) DEFAULT 1,
    date DATE NOT NULL,
    time TIME NOT NULL,
    category int(11) UNSIGNED DEFAULT NULL ,
    description varchar(255) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    common int(1) DEFAULT 0,
    common_value DECIMAL(10,2),
    notice TEXT DEFAULT NULL,
    fixed int(1) DEFAULT 0,
    lat DECIMAL(17,14) DEFAULT NULL,
    lng DECIMAL(17,14) DEFAULT NULL,
    acc DECIMAL(10,3) DEFAULT NULL,
    bill INTEGER unsigned DEFAULT NULL,
    paymethod int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES finances_categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(bill) REFERENCES splitbill_bill(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE
    UNIQUE(bill, user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
ALTER TABLE finances add paymethod int(11) UNSIGNED DEFAULT NULL;
ALTER TABLE finances ADD CONSTRAINT finances_ibfk_4 FOREIGN KEY (paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE;
*/

DROP TABLE IF EXISTS finances_recurring;
CREATE TABLE finances_recurring (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    start DATE DEFAULT NULL,
    end DATE DEFAULT NULL,
    type int(1) DEFAULT 1,
    category int(11) UNSIGNED DEFAULT NULL,
    description varchar(255) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    common int(1) DEFAULT 0,
    common_value DECIMAL(10,2),
    notice TEXT DEFAULT NULL,
    last_run TIMESTAMP NULL DEFAULT NULL,
    unit varchar(255) DEFAULT 'month',
    multiplier int(5) DEFAULT 1,
    paymethod int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES finances_categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
ALTER TABLE finances_recurring add paymethod int(11) UNSIGNED DEFAULT NULL;
ALTER TABLE finances_recurring ADD CONSTRAINT finances_recurring_ibfk_4 FOREIGN KEY (paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE;
*/

DROP TABLE IF EXISTS finances_categories_assignment;
CREATE TABLE finances_categories_assignment (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    description varchar(255) NOT NULL,
    category int(11) unsigned DEFAULT NULL,
    min_value DECIMAL(10,2) DEFAULT NULL,
    max_value DECIMAL(10,2) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES finances_categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS finances_budgets;
CREATE TABLE finances_budgets (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    description varchar(255) NOT NULL,
    value DECIMAL(10,2) DEFAULT NULL,
    is_hidden INT(1) DEFAULT 0,
--    saved DECIMAL(10,2) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS finances_budgets_categories;
CREATE TABLE finances_budgets_categories (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    budget INTEGER unsigned DEFAULT NULL,
    category INTEGER unsigned DEFAULT NULL,
    UNIQUE(budget, category),
    FOREIGN KEY(budget) REFERENCES finances_budgets(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(category) REFERENCES finances_categories(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS cars;
CREATE TABLE cars (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    mileage_per_year INT(20) DEFAULT NULL,
    mileage_term INT(3) DEFAULT NULL,
    mileage_start_date DATE DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS cars_user;
CREATE TABLE cars_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    car INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(car, user),
    FOREIGN KEY(car) REFERENCES cars(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS cars_service;
CREATE TABLE cars_service (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    car INTEGER unsigned DEFAULT NULL,
    date DATE NOT NULL,
    mileage int(20) UNSIGNED DEFAULT NULL,
    type int(1) DEFAULT NULL,
    fuel_price DECIMAL(6,2) DEFAULT NULL,
    fuel_volume DECIMAL(6,2) DEFAULT NULL,
    fuel_total_price DECIMAL(6,2) DEFAULT NULL,
    fuel_type int(1) DEFAULT NULL,
    fuel_distance INT(20) DEFAULT NULL,
    fuel_calc_consumption int(1) DEFAULT 1,
    fuel_consumption DECIMAL(6,2) DEFAULT NULL,
    fuel_location varchar(255) DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    service_oil_before INT(3) DEFAULT NULL,
    service_oil_after INT(3) DEFAULT NULL,
    service_water_wiper_before INT(3) DEFAULT NULL,
    service_water_wiper_after INT(3) DEFAULT NULL,
    service_air_front_left_before DECIMAL(2,1) DEFAULT NULL,
    service_air_front_left_after DECIMAL(2,1) DEFAULT NULL,
    service_air_front_right_before DECIMAL(2,1) DEFAULT NULL,
    service_air_front_right_after DECIMAL(2,1) DEFAULT NULL,
    service_air_back_left_before DECIMAL(2,1) DEFAULT NULL,
    service_air_back_left_after DECIMAL(2,1) DEFAULT NULL,
    service_air_back_right_before DECIMAL(2,1) DEFAULT NULL,
    service_air_back_right_after DECIMAL(2,1) DEFAULT NULL,
    service_tire_change int(1) DEFAULT NULL,
    service_garage int(1) DEFAULT NULL,
    lat DECIMAL(17,14) DEFAULT NULL,
    lng DECIMAL(17,14) DEFAULT NULL,
    acc DECIMAL(10,3) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(car) REFERENCES cars(id) ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS boards;
CREATE TABLE boards (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) DEFAULT NULL,
    hash VARCHAR(255) DEFAULT NULL,
    archive INT(1) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS boards_user;
CREATE TABLE boards_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    board INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(board, user),
    FOREIGN KEY(board) REFERENCES boards(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS boards_stacks;
CREATE TABLE boards_stacks (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    board INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedOn TIMESTAMP NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    archive INT(1) DEFAULT 0,
    position INT(10) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(board) REFERENCES boards(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS boards_cards;
CREATE TABLE boards_cards (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    stack INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedOn TIMESTAMP NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    title varchar(255) DEFAULT NULL,
    date DATE DEFAULT NULL,
    time TIME DEFAULT NULL,
    description TEXT DEFAULT NULL,
    archive INT(1) DEFAULT 0,
    position INT(10) NULL,
    hash VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(stack) REFERENCES boards_stacks(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS boards_cards_user;
CREATE TABLE boards_cards_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    card INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(card, user),
    FOREIGN KEY(card) REFERENCES boards_cards(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS boards_labels;
CREATE TABLE boards_labels (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    board INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    background_color VARCHAR(255) DEFAULT NULL,
    text_color VARCHAR(255) DEFAULT '#000000',
    PRIMARY KEY (id),
    FOREIGN KEY(board) REFERENCES boards(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS boards_cards_label;
CREATE TABLE boards_cards_label (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    card INTEGER unsigned DEFAULT NULL,
    label INTEGER unsigned DEFAULT NULL,
    UNIQUE(card, label),
    FOREIGN KEY(card) REFERENCES  boards_cards(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(label) REFERENCES boards_labels(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS boards_comments;
CREATE TABLE boards_comments (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    card INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    comment TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(card) REFERENCES boards_cards(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS global_settings;
CREATE TABLE global_settings (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  changedOn TIMESTAMP NULL,
  name varchar(255) NOT NULL,
  value text,
  type varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO global_settings (name, value, type) VALUES ('lastRunRecurring', 0, 'Date'), ('lastRunFinanceSummary', 0, 'Date'), ('lastRunCardReminder', 0, 'Date'); 


DROP TABLE IF EXISTS notifications_categories;
CREATE TABLE notifications_categories (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) NOT NULL,
    identifier varchar(255) NOT NULL,
    internal int(1) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE(identifier),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
ALTER TABLE notifications_categories ADD internal INT(1) DEFAULT 0 AFTER identifier;
INSERT INTO notifications_categories (name, identifier, internal) VALUES ('NOTIFICATION_CATEGORY_SPLITTED_BILLS', 'NOTIFICATION_CATEGORY_SPLITTED_BILLS', 1);
*/

DROP TABLE IF EXISTS notifications_clients;
CREATE TABLE notifications_clients (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned NOT NULL,
    endpoint VARCHAR(512) NOT NULL,
    auth VARCHAR(255) NULL,
    p256dh VARCHAR(255) NULL,
    contentEncoding VARCHAR(255) NULL,
    ip VARCHAR(255) NULL,
    agent VARCHAR(255) NULL,
    PRIMARY KEY (id),
    UNIQUE(endpoint),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS notifications_categories_clients;
CREATE TABLE notifications_categories_clients (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category INTEGER unsigned DEFAULT NULL,
    client INTEGER unsigned DEFAULT NULL,
    UNIQUE(category, client),
    FOREIGN KEY(category) REFERENCES notifications_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(client) REFERENCES notifications_clients(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS notifications_categories_users;
CREATE TABLE notifications_categories_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(category, user),
    FOREIGN KEY(category) REFERENCES notifications_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    category INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    title varchar(255) NOT NULL,
    message varchar(255) NOT NULL,
    seen TIMESTAMP NULL,
    link varchar(255) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES notifications_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS crawlers;
CREATE TABLE crawlers (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) DEFAULT NULL,
    hash VARCHAR(255) DEFAULT NULL,
    filter VARCHAR(50) NULL DEFAULT 'createdOn',
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS crawlers_user;
CREATE TABLE crawlers_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    crawler INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(crawler, user),
    FOREIGN KEY(crawler) REFERENCES crawlers(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS crawlers_headers;
CREATE TABLE crawlers_headers (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    crawler INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    headline varchar(255) DEFAULT NULL,
    field_name varchar(255) DEFAULT NULL,
    field_link varchar(255) DEFAULT NULL,
    field_content varchar(255) DEFAULT NULL,
    sortable INT(1) DEFAULT 0,
    diff INT(1) DEFAULT 0,
    prefix varchar(255) DEFAULT NULL,
    suffix varchar(255) DEFAULT NULL,
    sort varchar(10) DEFAULT NULL,
    datatype varchar(20) DEFAULT NULL,
    position INT(10) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(crawler) REFERENCES crawlers(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
ALTER TABLE crawlers_headers ADD sortable INT(1) DEFAULT 0 AFTER field_content;
*/

DROP TABLE IF EXISTS crawlers_dataset;
CREATE TABLE crawlers_dataset (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    crawler INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    identifier varchar(255) DEFAULT NULL,
    data JSON,
    diff JSON NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(crawler) REFERENCES crawlers(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX(identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS crawlers_links;
CREATE TABLE crawlers_links (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    crawler INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    link TEXT DEFAULT NULL,
    parent int(11) unsigned DEFAULT NULL,
    position INT(10) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(crawler) REFERENCES crawlers(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(parent) REFERENCES crawlers_links(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS splitbill_groups;
CREATE TABLE splitbill_groups (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) DEFAULT NULL,
    hash VARCHAR(255) DEFAULT NULL,
    add_finances int(1) DEFAULT 0,
    currency varchar(100) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS splitbill_groups_user;
CREATE TABLE splitbill_groups_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sbgroup INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(sbgroup, user),
    FOREIGN KEY(sbgroup) REFERENCES splitbill_groups(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS splitbill_bill;
CREATE TABLE splitbill_bill (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    sbgroup INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) DEFAULT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    lat DECIMAL(17,14) DEFAULT NULL,
    lng DECIMAL(17,14) DEFAULT NULL,
    acc DECIMAL(10,3) DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    settleup INT(1) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY(sbgroup) REFERENCES splitbill_groups(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
ALTER TABLE splitbill_bill ADD settleup INT(1) DEFAULT 0; 
*/

DROP TABLE IF EXISTS splitbill_bill_users;
CREATE TABLE splitbill_bill_users (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    bill INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    paid DECIMAL(10,2) DEFAULT NULL,
    spend DECIMAL(10,2) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(bill) REFERENCES splitbill_bill(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE(bill, user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
ALTER TABLE splitbill_bill_users add paymethod int(11) UNSIGNED DEFAULT NULL;
ALTER TABLE splitbill_bill_users ADD CONSTRAINT splitbill_bill_users_ibfk_3 FOREIGN KEY (paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE;
*/

DROP TABLE IF EXISTS trips;
CREATE TABLE trips (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) DEFAULT NULL,
    hash VARCHAR(255) DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS trips_user;
CREATE TABLE trips_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trip INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(trip, user),
    FOREIGN KEY(trip) REFERENCES trips(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS trips_event;
CREATE TABLE trips_event (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    trip INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    start_time TIME DEFAULT NULL,
    start_address VARCHAR(255) DEFAULT NULL,
    start_lat DECIMAL(17,14) DEFAULT NULL,
    start_lng DECIMAL(17,14) DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    end_address VARCHAR(255) DEFAULT NULL,
    end_lat DECIMAL(17,14) DEFAULT NULL,
    end_lng DECIMAL(17,14) DEFAULT NULL,
    type VARCHAR(100) DEFAULT 'EVENT',
    notice TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(trip) REFERENCES trips(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS global_users_mobile_favorites;
CREATE TABLE global_users_mobile_favorites (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned NOT NULL,
    position INT(10) NULL,
    url VARCHAR(255) NULL,
    icon VARCHAR(100) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS timesheets_projects;
CREATE TABLE timesheets_projects (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) DEFAULT NULL,
    hash VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS timesheets_projects_users;
CREATE TABLE timesheets_projects_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    project INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(project, user),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS timesheets_sheets;
CREATE TABLE timesheets_sheets (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    project INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    start DATETIME DEFAULT NULL,
    end DATETIME DEFAULT NULL,
    diff INTEGER DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    start_lat DECIMAL(17,14) DEFAULT NULL,
    start_lng DECIMAL(17,14) DEFAULT NULL,
    start_acc DECIMAL(10,3) DEFAULT NULL,
    end_lat DECIMAL(17,14) DEFAULT NULL,
    end_lng DECIMAL(17,14) DEFAULT NULL,
    end_acc DECIMAL(10,3) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS activities;
CREATE TABLE activities (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    type varchar(255) NULL,
    module varchar(255) NULL,
    controller varchar(255) NULL,
    object varchar(255) NULL,
    object_id int(11) NULL,
    object_description TEXT NULL,
    parent_object varchar(255) NULL,
    parent_object_id int(11) NULL,
    parent_object_description TEXT NULL,
    link varchar(255) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS activities_users;
CREATE TABLE activities_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activity INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(activity, user),
    FOREIGN KEY(activity) REFERENCES activities(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;