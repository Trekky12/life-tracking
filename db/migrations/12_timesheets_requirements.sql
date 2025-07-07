CREATE TABLE IF NOT EXISTS timesheets_requirement_types(
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    project INTEGER unsigned DEFAULT NULL,
    name varchar(255) NOT NULL,
    description varchar(255) DEFAULT NULL,
    datatype varchar(20) DEFAULT NULL,
    initialization TEXT DEFAULT NULL,
    validity_period ENUM('month','quarter', 'year') DEFAULT 'month',
    position INT(10) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(project) REFERENCES timesheets_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS timesheets_customers_requirements (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    requirement_type INTEGER unsigned DEFAULT NULL,
    customer INTEGER unsigned DEFAULT NULL,
    value TEXT DEFAULT NULL,
    start DATE DEFAULT NULL,
    end DATE DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(requirement_type) REFERENCES timesheets_requirement_types(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(customer) REFERENCES timesheets_customers(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;