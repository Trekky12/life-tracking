ALTER TABLE crawlers ADD archive INT(1) DEFAULT 0 AFTER filter;
ALTER TABLE splitbill_groups ADD archive INT(1) DEFAULT 0 AFTER exchange_fee; 
ALTER TABLE timesheets_projects ADD archive INT(1) DEFAULT 0 AFTER has_end; 
ALTER TABLE workouts_plans ADD archive INT(1) DEFAULT 0 AFTER level; 