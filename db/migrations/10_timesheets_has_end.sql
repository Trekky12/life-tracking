ALTER TABLE timesheets_projects ADD has_end int(1) DEFAULT 0 AFTER has_billing;
UPDATE timesheets_projects SET has_end = 1;