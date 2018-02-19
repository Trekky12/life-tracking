DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
    id INTEGER unsigned NOT NULL AUTO_INCREMENT,
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    login VARCHAR(50) NOT NULL,
    password VARCHAR(255) NULL,
    name varchar(255) DEFAULT NULL,
    lastname varchar(255) DEFAULT NULL,
    mail varchar(255) DEFAULT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    module_location int(1) DEFAULT 0,
    module_finance int(1) DEFAULT 0,
    module_fuel int(1) DEFAULT 0,
    PRIMARY KEY(id),
    UNIQUE(login)
);
INSERT INTO users (login, password, role) VALUES ('admin', '$2y$10$gbDsuY1GyMJo78ueqWy/SOstNf2DeLpN3mKTUS9Yp.bwG7i4y4.KK', 'admin');

/*ALTER TABLE users ADD module_location int(1) DEFAULT 0 AFTER role;
ALTER TABLE users ADD module_finance int(1) DEFAULT 0 AFTER module_location;
ALTER TABLE users ADD module_fuel int(1) DEFAULT 0 AFTER module_finance;*/

DROP TABLE IF EXISTS locations;
CREATE TABLE locations (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user INTEGER unsigned DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
     FOREIGN KEY(user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `finances_categories` (`id`, `user`, `name`) VALUES (1, 1, 'not categorized');

DROP TABLE IF EXISTS finances;
CREATE TABLE finances (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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

/*
ALTER TABLE finances ADD common int(1) DEFAULT 0 AFTER value;
ALTER TABLE finances ADD common_value DECIMAL(10,2) AFTER common;

ALTER TABLE finances_monthly ADD common int(1) DEFAULT 0 AFTER value;
ALTER TABLE finances_monthly ADD common_value DECIMAL(10,2) AFTER common;
*/

DROP TABLE IF EXISTS finances_monthly;
CREATE TABLE finances_monthly (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    name varchar(255) DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS user_cars;
CREATE TABLE user_cars (
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user INTEGER unsigned DEFAULT NULL,
    car INTEGER unsigned DEFAULT NULL,
    UNIQUE(user, car),
    FOREIGN KEY(user) REFERENCES users(id),
    FOREIGN KEY(car) REFERENCES cars(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS fuel;
CREATE TABLE fuel (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user INTEGER unsigned DEFAULT NULL,
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
    FOREIGN KEY(user) REFERENCES users(id),
    FOREIGN KEY(car) REFERENCES cars(id),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
ALTER TABLE fuel ADD car INTEGER unsigned DEFAULT NULL REFERENCES cars(id) AFTER user;
ALTER TABLE fuel ADD CONSTRAINT fuel_ibfk_2 FOREIGN KEY (car) REFERENCES cars(id);
*/

