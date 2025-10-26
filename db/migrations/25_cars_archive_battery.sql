ALTER TABLE cars ADD archive INT(1) DEFAULT 0 AFTER hash;

ALTER TABLE cars ADD refill_type ENUM('fuel','battery') DEFAULT 'fuel' AFTER mileage_start;

ALTER TABLE cars_service CHANGE fuel_price refill_price DECIMAL(6,2) DEFAULT NULL;
ALTER TABLE cars_service CHANGE fuel_volume refill_amount DECIMAL(6,2) DEFAULT NULL;
ALTER TABLE cars_service CHANGE fuel_total_price refill_total_price DECIMAL(6,2) DEFAULT NULL;
ALTER TABLE cars_service CHANGE fuel_type refill_full INT(1) DEFAULT 0;
ALTER TABLE cars_service CHANGE fuel_distance refill_distance INT(20) DEFAULT NULL;
ALTER TABLE cars_service CHANGE fuel_calc_consumption calc_refill_consumption int(1) DEFAULT 1;
ALTER TABLE cars_service CHANGE fuel_consumption refill_consumption DECIMAL(6,2) DEFAULT NULL;
ALTER TABLE cars_service CHANGE fuel_location refill_location varchar(255) DEFAULT NULL;