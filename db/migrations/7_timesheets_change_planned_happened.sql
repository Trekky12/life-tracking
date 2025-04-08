ALTER TABLE timesheets_sheets ADD is_happened int(1) DEFAULT 0 AFTER is_payed;
UPDATE timesheets_sheets SET is_happened = 1 WHERE is_planned = 0;
ALTER TABLE timesheets_sheets DROP COLUMN is_planned;