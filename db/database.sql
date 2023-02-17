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
    module_workouts int(1) DEFAULT 0,
    module_recipes int(1) DEFAULT 0,
    force_pw_change int(1) DEFAULT 1,
    start_url varchar(255) DEFAULT NULL,
    secret VARCHAR(255) NULL,
    PRIMARY KEY(id),
    UNIQUE(login)
);

DROP TABLE IF EXISTS global_banlist;
CREATE TABLE global_banlist (
    id INTEGER unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(255) NOT NULL,
    username varchar(255) DEFAULT NULL,
    changedOn TIMESTAMP NULL,
    PRIMARY KEY(id)
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

DROP TABLE IF EXISTS finances_accounts;
CREATE TABLE finances_accounts (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    hash VARCHAR(255) DEFAULT NULL,    
    name varchar(255) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS finances_paymethods;
CREATE TABLE finances_paymethods (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    is_default int(1) DEFAULT 0,
    account int(11) UNSIGNED DEFAULT NULL,
    round_up_savings int(1) DEFAULT 0,
    round_up_savings_account int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(account) REFERENCES finances_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(round_up_savings_account) REFERENCES finances_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
    is_active int(1) DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES finances_categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
    is_remaining INT(1) DEFAULT 0,
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

DROP TABLE IF EXISTS finances_transactions;
CREATE TABLE finances_transactions (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    description varchar(255) DEFAULT NULL,
    value DECIMAL(10,2) NOT NULL,
    account_from int(11) UNSIGNED DEFAULT NULL,
    account_to int(11) UNSIGNED DEFAULT NULL,   
    is_confirmed INT(1) DEFAULT 0, 
    finance_entry int(11) UNSIGNED DEFAULT NULL,
    bill_entry int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(account_from) REFERENCES finances_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(account_to) REFERENCES finances_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS finances_transactions_recurring;
CREATE TABLE finances_transactions_recurring (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    description varchar(255) DEFAULT NULL,
    value DECIMAL(10,2) NOT NULL,
    account_from int(11) UNSIGNED DEFAULT NULL,
    account_to int(11) UNSIGNED DEFAULT NULL,   
    start DATE DEFAULT NULL,
    end DATE DEFAULT NULL,
    last_run TIMESTAMP NULL DEFAULT NULL,
    unit varchar(255) DEFAULT 'month',
    multiplier int(5) DEFAULT 1,
    is_active int(1) DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(account_from) REFERENCES finances_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(account_to) REFERENCES finances_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE
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
    mileage_start INT(20) DEFAULT NULL,
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
    FOREIGN KEY(car) REFERENCES cars(id) ON DELETE CASCADE ON UPDATE CASCADE
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
    hash VARCHAR(255) DEFAULT NULL,
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
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
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

DROP TABLE IF EXISTS global_widgets;
CREATE TABLE global_widgets (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  user INTEGER unsigned DEFAULT NULL,
  createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  changedOn TIMESTAMP NULL,
  name varchar(255) NOT NULL,
  options text,
  position INT(10) NULL,
  PRIMARY KEY (id),
  FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS notifications_categories;
CREATE TABLE notifications_categories (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) NOT NULL,
    identifier varchar(255) NOT NULL,
    internal int(1) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE(identifier),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS notifications_categories_user;
CREATE TABLE notifications_categories_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(category, user),
    FOREIGN KEY(category) REFERENCES notifications_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS notifications_clients;
CREATE TABLE notifications_clients (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned NOT NULL,
    endpoint VARCHAR(512) NOT NULL,
    authToken VARCHAR(255) NULL,
    publicKey VARCHAR(255) NULL,
    contentEncoding VARCHAR(255) NULL,
    ip VARCHAR(255) NULL,
    agent VARCHAR(255) NULL,
    type VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE(endpoint),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS notifications_subscription_clients;
CREATE TABLE notifications_subscription_clients (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category INTEGER unsigned DEFAULT NULL,
    client INTEGER unsigned DEFAULT NULL,
    object_id int(11) unsigned NULL,
    UNIQUE(category, client, object_id),
    FOREIGN KEY(category) REFERENCES notifications_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(client) REFERENCES notifications_clients(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS notifications_subscription_users;
CREATE TABLE notifications_subscription_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    object_id int(11) unsigned NULL,
    UNIQUE(category, user, object_id),
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
    link varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES notifications_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS mail_categories;
CREATE TABLE mail_categories (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    identifier varchar(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE(identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS mail_subscription_users;
CREATE TABLE mail_subscription_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    object_id int(11) unsigned NULL,
    UNIQUE(category, user, object_id),
    FOREIGN KEY(category) REFERENCES mail_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
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
    lastAccess DATE DEFAULT NULL,
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

DROP TABLE IF EXISTS crawlers_dataset;
CREATE TABLE crawlers_dataset (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    crawler INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    identifier varchar(255) DEFAULT NULL,
    saved INT(1) DEFAULT 0,
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
    exchange_rate varchar(100) DEFAULT NULL,
    exchange_fee varchar(100) DEFAULT NULL,
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
    exchange_rate varchar(100) DEFAULT NULL,
    exchange_fee varchar(100) DEFAULT NULL,
    paid_by varchar(20) DEFAULT NULL,
    spend_by varchar(20) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(sbgroup) REFERENCES splitbill_groups(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS splitbill_bill_users;
CREATE TABLE splitbill_bill_users (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    bill INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    paid DECIMAL(10,2) DEFAULT NULL,
    spend DECIMAL(10,2) DEFAULT NULL,
    paymethod int(11) UNSIGNED DEFAULT NULL,
    paid_foreign DECIMAL(10,2) DEFAULT NULL,
    spend_foreign DECIMAL(10,2) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(bill) REFERENCES splitbill_bill(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE(bill, user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS splitbill_bill_recurring;
CREATE TABLE splitbill_bill_recurring (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    sbgroup INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    settleup INT(1) DEFAULT 0,
    exchange_rate varchar(100) DEFAULT NULL,
    exchange_fee varchar(100) DEFAULT NULL,
    start DATE DEFAULT NULL,
    end DATE DEFAULT NULL,
    last_run TIMESTAMP NULL DEFAULT NULL,
    unit varchar(255) DEFAULT 'month',
    multiplier int(5) DEFAULT 1,
    is_active int(1) DEFAULT 1,
    paid_by varchar(20) DEFAULT NULL,
    spend_by varchar(20) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(sbgroup) REFERENCES splitbill_groups(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS splitbill_bill_recurring_users;
CREATE TABLE splitbill_bill_recurring_users (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    bill INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    paid DECIMAL(10,2) DEFAULT NULL,
    spend DECIMAL(10,2) DEFAULT NULL,
    paymethod int(11) UNSIGNED DEFAULT NULL,
    paid_foreign DECIMAL(10,2) DEFAULT NULL,
    spend_foreign DECIMAL(10,2) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(bill) REFERENCES splitbill_bill_recurring(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE(bill, user)
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
    category int(11) UNSIGNED DEFAULT NULL,
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
    bill_paid DECIMAL(10,2) DEFAULT NULL,
    paymethod int(11) UNSIGNED DEFAULT NULL,
    transaction int(11) UNSIGNED DEFAULT NULL,
    transaction_round_up_savings int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES finances_categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(bill) REFERENCES splitbill_bill(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(paymethod) REFERENCES finances_paymethods(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(transaction) REFERENCES finances_transactions(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(transaction_round_up_savings) REFERENCES finances_transactions(id) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE(bill, user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE finances_transactions ADD FOREIGN KEY(finance_entry) REFERENCES finances(id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE finances_transactions ADD FOREIGN KEY(bill_entry) REFERENCES splitbill_bill(id) ON DELETE CASCADE ON UPDATE CASCADE;


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
    image VARCHAR(255) NULL,
    position INT(10) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(trip) REFERENCES trips(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS trips_route;
CREATE TABLE trips_route (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    trip INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    waypoints JSON NULL DEFAULT NULL,
    profile VARCHAR(255) NULL,
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

DROP TABLE IF EXISTS global_users_application_passwords;
CREATE TABLE global_users_application_passwords (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned NOT NULL,
    name VARCHAR(255) NULL,
    password VARCHAR(255) NULL,
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
    is_day_based INT(1) DEFAULT 0,
    default_view varchar(255) DEFAULT 'month',
    has_duration_modifications INT(1) DEFAULT 0,
    time_conversion_rate varchar(100) DEFAULT NULL,
    default_duration INT(11) NULL,
    password VARCHAR(255) NULL,
    salt VARCHAR(255) NULL,
    show_month_button INT(1) DEFAULT 1,
    show_quarters_buttons INT(1) DEFAULT 0,
    customers_name_singular VARCHAR(255) DEFAULT NULL,
    customers_name_plural VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
ALTER TABLE `timesheets_projects` ADD `customers_name_singular` VARCHAR(255) NULL DEFAULT NULL AFTER `show_quarters_buttons`, ADD `customers_name_plural` VARCHAR(255) NULL DEFAULT NULL AFTER `customers_name_singular`; 
*/

DROP TABLE IF EXISTS timesheets_projects_users;
CREATE TABLE timesheets_projects_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    project INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(project, user),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS timesheets_customers;
CREATE TABLE timesheets_customers (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    project INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
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
    duration INTEGER DEFAULT NULL,
    duration_modified INTEGER DEFAULT NULL,
    notice BLOB DEFAULT NULL,
    start_lat DECIMAL(17,14) DEFAULT NULL,
    start_lng DECIMAL(17,14) DEFAULT NULL,
    start_acc DECIMAL(10,3) DEFAULT NULL,
    end_lat DECIMAL(17,14) DEFAULT NULL,
    end_lng DECIMAL(17,14) DEFAULT NULL,
    end_acc DECIMAL(10,3) DEFAULT NULL,
    is_billed int(1) DEFAULT 0,
    is_payed int(1) DEFAULT 0,
    customer INTEGER unsigned DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(customer) REFERENCES timesheets_customers(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 
ALTER TABLE `timesheets_sheets` ADD `customer` INT UNSIGNED NULL DEFAULT NULL AFTER `is_payed`; 
ALTER TABLE `timesheets_sheets` ADD CONSTRAINT `timesheets_sheets_ibfk_4` FOREIGN KEY (`customer`) REFERENCES `timesheets_customers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE; 
*/

DROP TABLE IF EXISTS timesheets_categories;
CREATE TABLE timesheets_categories (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    project INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS timesheets_sheets_categories;
CREATE TABLE timesheets_sheets_categories (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sheet INTEGER unsigned DEFAULT NULL,
    category INTEGER unsigned DEFAULT NULL,
    UNIQUE(sheet, category),
    FOREIGN KEY(sheet) REFERENCES timesheets_sheets(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(category) REFERENCES timesheets_categories(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS timesheets_categorybudgets;
CREATE TABLE timesheets_categorybudgets (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    project INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    categorization ENUM('duration','duration_modified', 'count') default NULL,
    notice TEXT DEFAULT NULL,
    customer INTEGER unsigned DEFAULT NULL,
    main_category INTEGER unsigned DEFAULT NULL,
    value INT(11) NOT NULL,
    warning1 INT(11) NULL,
    warning2 INT(11) NULL,
    warning3 INT(11) NULL,
    start DATE DEFAULT NULL,
    end DATE DEFAULT NULL,
    is_hidden INT(1) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(main_category) REFERENCES timesheets_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(customer) REFERENCES timesheets_customers(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
ALTER TABLE `timesheets_categorybudgets` ADD `customer` INT UNSIGNED NULL DEFAULT NULL AFTER `notice`; 
ALTER TABLE `timesheets_categorybudgets` ADD CONSTRAINT `timesheets_categorybudgets_ibfk_4` FOREIGN KEY (`customer`) REFERENCES `timesheets_customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; 
*/

DROP TABLE IF EXISTS timesheets_categorybudgets_categories;
CREATE TABLE timesheets_categorybudgets_categories (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    categorybudget INTEGER unsigned DEFAULT NULL,
    category INTEGER unsigned DEFAULT NULL,
    FOREIGN KEY(categorybudget) REFERENCES timesheets_categorybudgets(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(category) REFERENCES timesheets_categories(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS timesheets_sheets_notices;
CREATE TABLE timesheets_sheets_notices (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    sheet INTEGER unsigned DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(sheet) REFERENCES timesheets_sheets(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
INSERT INTO timesheets_sheets_notices (sheet, createdOn, changedOn, createdBy, changedBy, notice)
SELECT id, createdOn, changedOn, createdBy, changedBy, notice
FROM timesheets_sheets
*/

DROP TABLE IF EXISTS timesheets_noticefields;
CREATE TABLE timesheets_noticefields (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    project INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) NOT NULL,
    description varchar(255) DEFAULT NULL,
    datatype varchar(20) DEFAULT NULL,
    initialization TEXT DEFAULT NULL,
    position INT(10) NULL,
    is_default int(1) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
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


DROP TABLE IF EXISTS workouts_bodyparts;
CREATE TABLE workouts_bodyparts (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS workouts_muscles;
CREATE TABLE workouts_muscles (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    image_primary VARCHAR(255) NULL,
    image_secondary VARCHAR(255) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS workouts_exercises;
CREATE TABLE workouts_exercises (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) NOT NULL,
    instructions TEXT,
    category INT(10) NULL,
    level INT(10) NULL,
    rating INT(10) NULL,
    mainBodyPart INTEGER unsigned DEFAULT NULL,
    mainMuscle INTEGER unsigned DEFAULT NULL,
    image VARCHAR(255) NULL,
    thumbnail VARCHAR(255) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(mainBodyPart) REFERENCES workouts_bodyparts(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(mainMuscle) REFERENCES workouts_muscles(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS workouts_plans;
CREATE TABLE workouts_plans (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    hash VARCHAR(255) DEFAULT NULL,
    is_template INT(1) DEFAULT 0,
    notice TEXT DEFAULT NULL,
    category INT(10) NULL,
    level INT(10) NULL,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS workouts_exercises_muscles;
CREATE TABLE workouts_exercises_muscles (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    exercise INTEGER unsigned DEFAULT NULL,
    muscle INTEGER unsigned DEFAULT NULL,
    is_primary INT(1) DEFAULT 0,
    UNIQUE(exercise, muscle),
    FOREIGN KEY(exercise) REFERENCES workouts_exercises(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(muscle) REFERENCES workouts_muscles(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS workouts_plans_exercises;
CREATE TABLE workouts_plans_exercises (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    plan INTEGER unsigned DEFAULT NULL,
    exercise INTEGER unsigned DEFAULT NULL,
    position INT(10) NULL,
    sets JSON NULL DEFAULT NULL,
    type VARCHAR(255) NULL DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    is_child INT(1) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY(plan) REFERENCES workouts_plans(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(exercise) REFERENCES workouts_exercises(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS workouts_sessions;
CREATE TABLE workouts_sessions (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    plan INTEGER unsigned DEFAULT NULL,
    date DATE DEFAULT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(plan) REFERENCES workouts_plans(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS workouts_sessions_exercises;
CREATE TABLE workouts_sessions_exercises (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session INTEGER unsigned DEFAULT NULL,
    exercise INTEGER unsigned DEFAULT NULL,
    position INT(10) NULL,
    sets JSON NULL DEFAULT NULL,
    type VARCHAR(255) NULL DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    is_child INT(1) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY(session) REFERENCES workouts_sessions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(exercise) REFERENCES workouts_exercises(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS recipes_cookbooks;
CREATE TABLE recipes_cookbooks (
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

DROP TABLE IF EXISTS recipes_cookbooks_users;
CREATE TABLE recipes_cookbooks_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cookbook INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(cookbook, user),
    FOREIGN KEY(cookbook) REFERENCES recipes_cookbooks(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS recipes_groceries;
CREATE TABLE recipes_groceries (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    unit varchar(50) DEFAULT NULL,
    is_food int(1) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE(name),
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
ALTER TABLE recipes_groceries ADD UNIQUE(name); 
*/

DROP TABLE IF EXISTS recipes;
CREATE TABLE recipes (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    name varchar(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) NULL,
    preparation_time INT(10) NULL,
    waiting_time INT(10) NULL,
    servings INT(10) NULL,
    link varchar(255) NULL,
    hash VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(createdBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(changedBy) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS recipes_steps;
CREATE TABLE recipes_steps (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INTEGER unsigned DEFAULT NULL,
    recipe INTEGER unsigned DEFAULT NULL,
    position INT(10) NULL,
    name varchar(255) NULL,
    description TEXT,
    preparation_time INT(10) NULL,
    waiting_time INT(10) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(recipe) REFERENCES recipes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS recipes_recipe_ingredients;
CREATE TABLE recipes_recipe_ingredients (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INTEGER unsigned DEFAULT NULL,
    recipe INTEGER unsigned DEFAULT NULL,
    step int(11) unsigned NULL,
    ingredient int(11) unsigned NULL,
    position INT(10) NULL,
    amount VARCHAR(10) NULL,
    unit varchar(50) DEFAULT NULL,
    notice TEXT,
    PRIMARY KEY (id),
    FOREIGN KEY(recipe) REFERENCES recipes(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(step) REFERENCES recipes_steps(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(ingredient) REFERENCES recipes_groceries(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
ALTER TABLE `recipes_recipe_ingredients` ADD `unit` VARCHAR(50) NULL DEFAULT NULL AFTER `amount`; 
*/

DROP TABLE IF EXISTS recipes_cookbook_recipes;
CREATE TABLE recipes_cookbook_recipes (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INTEGER unsigned DEFAULT NULL,
    cookbook int(11) unsigned NULL,
    recipe INTEGER unsigned DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(cookbook) REFERENCES recipes_cookbooks(id) ON DELETE CASCADE ON UPDATE CASCADE,    
    FOREIGN KEY(recipe) REFERENCES recipes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS recipes_mealplans;
CREATE TABLE recipes_mealplans (
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

DROP TABLE IF EXISTS recipes_mealplans_users;
CREATE TABLE recipes_mealplans_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mealplan INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(mealplan, user),
    FOREIGN KEY(mealplan) REFERENCES recipes_mealplans(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS recipes_mealplans_recipes;
CREATE TABLE recipes_mealplans_recipes (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INTEGER unsigned DEFAULT NULL,
    mealplan int(11) unsigned NULL,
    recipe INTEGER unsigned DEFAULT NULL,
    date DATE NOT NULL,
    position INT(10) NULL,
    notice TEXT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(mealplan) REFERENCES recipes_mealplans(id) ON DELETE CASCADE ON UPDATE CASCADE,    
    FOREIGN KEY(recipe) REFERENCES recipes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS recipes_shoppinglists;
CREATE TABLE recipes_shoppinglists (
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

DROP TABLE IF EXISTS recipes_shoppinglists_users;
CREATE TABLE recipes_shoppinglists_users (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shoppinglist INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(shoppinglist, user),
    FOREIGN KEY(shoppinglist) REFERENCES recipes_shoppinglists(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS recipes_shoppinglists_entries;
CREATE TABLE recipes_shoppinglists_entries (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INTEGER unsigned DEFAULT NULL,
    shoppinglist int(11) unsigned NULL,
    grocery INTEGER unsigned DEFAULT NULL,
    amount VARCHAR(10) NULL,
    unit varchar(50) DEFAULT NULL,
    position INT(10) NULL,
    done TIMESTAMP NULL,
    notice TEXT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(shoppinglist) REFERENCES recipes_shoppinglists(id) ON DELETE CASCADE ON UPDATE CASCADE,    
    FOREIGN KEY(grocery) REFERENCES recipes_groceries(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;