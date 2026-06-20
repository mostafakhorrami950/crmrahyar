ALTER TABLE payments ADD COLUMN short_code VARCHAR(10) NULL UNIQUE AFTER public_token;
