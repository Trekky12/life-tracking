CREATE TABLE IF NOT EXISTS timesheets_sheets_files (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    createdOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changedOn TIMESTAMP NULL,
    user INTEGER unsigned DEFAULT NULL,
    sheet INTEGER unsigned DEFAULT NULL,
    name varchar(255) NOT NULL,
    type varchar(255) NOT NULL,
    filename varchar(255) NOT NULL,
    encryptedCEK varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY(user) REFERENCES global_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY(sheet) REFERENCES timesheets_sheets(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;