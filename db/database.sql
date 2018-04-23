DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
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
    module_fuel int(1) DEFAULT 0,
    module_boards int(1) DEFAULT 0,
    force_pw_change int(1) DEFAULT 1,
    mails_user int(1) DEFAULT 1,
    mails_finances int(1) DEFAULT 1,
    mails_board int(1) DEFAULT 1,
    mails_board_reminder int(1) DEFAULT 1,
    PRIMARY KEY(id),
    UNIQUE(login)
);
INSERT INTO users (login, password, role) VALUES ('admin', '$2y$10$gbDsuY1GyMJo78ueqWy/SOstNf2DeLpN3mKTUS9Yp.bwG7i4y4.KK', 'admin');

/**
ALTER TABLE users ADD mails_user int(1) DEFAULT 1 AFTER force_pw_change; 
ALTER TABLE users ADD mails_finances int(1) DEFAULT 1 AFTER mails_user; 
ALTER TABLE users CHANGE board_notification_mails mails_board INT(1) DEFAULT 1; 
ALTER TABLE users ADD mails_board_reminder int(1) DEFAULT 1 AFTER mails_board; 
*/

DROP TABLE IF EXISTS banlist;
CREATE TABLE banlist (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(255) NOT NULL,
    username varchar(255) DEFAULT NULL,
    changedOn TIMESTAMP NULL
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
    gps_lat DECIMAL(16,14) DEFAULT NULL,
    gps_lng DECIMAL(16,14) DEFAULT NULL,
    gps_acc DECIMAL(6,3) DEFAULT NULL,
    gps_alt DECIMAL(20,14) DEFAULT NULL,
    gps_spd DECIMAL(12,9) DEFAULT NULL,
    gps_tms int(20) DEFAULT NULL,
    net_lat DECIMAL(16,14) DEFAULT NULL,
    net_lng DECIMAL(16,14) DEFAULT NULL,
    net_acc DECIMAL(6,3) DEFAULT NULL,
    net_tms int(20) DEFAULT NULL,
    cell_id varchar(255) DEFAULT NULL,
    cell_sig varchar(255) DEFAULT NULL,
    cell_srv varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS finances_categories;
CREATE TABLE finances_categories (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `finances_categories` (`id`, `user`, `name`) VALUES (1, 1, 'not categorized');

DROP TABLE IF EXISTS finances;
CREATE TABLE finances (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    type int(1) DEFAULT 1,
    date DATE NOT NULL,
    time TIME NOT NULL,
    category int(11) UNSIGNED  NOT NULL DEFAULT 1,
    description varchar(255) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    common int(1) DEFAULT 0,
    common_value DECIMAL(10,2),
    notice TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES finances_categories(id) ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS finances_monthly;
CREATE TABLE finances_monthly (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    start DATE DEFAULT NULL,
    end DATE DEFAULT NULL,
    type int(1) DEFAULT 1,
    category int(11) UNSIGNED NOT NULL DEFAULT 1,
    description varchar(255) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    common int(1) DEFAULT 0,
    common_value DECIMAL(10,2),
    notice TEXT DEFAULT NULL,
    last_run TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(category) REFERENCES finances_categories(id) ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS cars;
CREATE TABLE cars (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS cars_user;
CREATE TABLE cars_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    car INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(car, user),
    FOREIGN KEY(car) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY(user) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS fuel;
CREATE TABLE fuel (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    createdBy INTEGER unsigned DEFAULT NULL,
    changedBy INTEGER unsigned DEFAULT NULL,
    car INTEGER unsigned DEFAULT NULL,
    date DATE NOT NULL,
    mileage int(20) UNSIGNED DEFAULT NULL,
    price DECIMAL(6,2) DEFAULT NULL,
    volume DECIMAL(6,2) DEFAULT NULL,
    total_price DECIMAL(6,2) DEFAULT NULL,
    type int(1) DEFAULT NULL,
    distance INT(20) DEFAULT NULL,
    calc_consumption int(1) DEFAULT 1,
    consumption DECIMAL(6,2) DEFAULT NULL,
    location varchar(255) DEFAULT NULL,
    notice TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(createdBy) REFERENCES users(id),
    FOREIGN KEY(changedBy) REFERENCES users(id),
    FOREIGN KEY(car) REFERENCES cars(id),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/**
ALTER TABLE fuel ADD changedBy INTEGER unsigned DEFAULT NULL AFTER user;
ALTER TABLE fuel ADD CONSTRAINT fuel_ibfk_3 FOREIGN KEY (changedBy) REFERENCES users(id);
ALTER TABLE fuel CHANGE user createdBy INTEGER UNSIGNED DEFAULT NULL; 
*/


DROP TABLE IF EXISTS boards;
CREATE TABLE boards (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    name varchar(255) DEFAULT NULL,
    hash VARCHAR(255) NOT NULL,
    archive INT(1) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE(hash),
    FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS boards_user;
CREATE TABLE boards_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    board INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(board, user),
    FOREIGN KEY(board) REFERENCES boards(id)  ON DELETE CASCADE,
    FOREIGN KEY(user) REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS stacks;
CREATE TABLE stacks (
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
   FOREIGN KEY(board) REFERENCES boards(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS cards;
CREATE TABLE cards (
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
    FOREIGN KEY(stack) REFERENCES stacks(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS cards_user;
CREATE TABLE cards_user (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    card INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    UNIQUE(card, user),
    FOREIGN KEY(card) REFERENCES cards(id)  ON DELETE CASCADE,
    FOREIGN KEY(user) REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS labels;
CREATE TABLE labels (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    board INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    background_color VARCHAR(255) DEFAULT NULL,
    text_color VARCHAR(255) DEFAULT '#000000',
    PRIMARY KEY (id),
    FOREIGN KEY(board) REFERENCES boards(id),
    FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS cards_label;
CREATE TABLE cards_label (
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    card INTEGER unsigned DEFAULT NULL,
    label INTEGER unsigned DEFAULT NULL,
    UNIQUE(card, label),
    FOREIGN KEY(card) REFERENCES cards(id)  ON DELETE CASCADE,
    FOREIGN KEY(label) REFERENCES labels(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS comments;
CREATE TABLE comments (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    card INTEGER unsigned DEFAULT NULL,
    user INTEGER unsigned DEFAULT NULL,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    comment TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(card) REFERENCES cards(id),
    FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
