ALTER TABLE timesheets_projects ADD has_billing int(1) DEFAULT 0 AFTER report_headline;
UPDATE timesheets_projects SET has_billing = 1;