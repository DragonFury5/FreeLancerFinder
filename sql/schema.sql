USE flf;

ALTER TABLE transactions
  ADD COLUMN customer_name VARCHAR(120) DEFAULT '' AFTER lancer_name;

-- Backfill existing rows from the users table where possible
UPDATE transactions t
LEFT JOIN users u ON u.id = t.user_id
SET t.customer_name = COALESCE(NULLIF(u.name, ''), 'Guest')
WHERE t.customer_name = '' OR t.customer_name IS NULL;