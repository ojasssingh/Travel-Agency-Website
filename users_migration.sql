ALTER TABLE users
ADD COLUMN username VARCHAR(50) NULL AFTER id;

UPDATE users
SET username = CONCAT('user', id)
WHERE username IS NULL OR username = '';

ALTER TABLE users
MODIFY username VARCHAR(50) NOT NULL;

ALTER TABLE users
ADD UNIQUE KEY unique_username (username);
