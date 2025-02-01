ALTER TABLE activities DROP IF EXISTS controller; 
ALTER TABLE activities ADD additional_information VARCHAR(255) NULL AFTER link; 