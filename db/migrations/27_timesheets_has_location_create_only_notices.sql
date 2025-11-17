ALTER TABLE timesheets_projects ADD has_location INT(1) DEFAULT 0 AFTER has_end;
ALTER TABLE timesheets_projects ADD create_only_notices INT(1) DEFAULT 0 AFTER has_location; 