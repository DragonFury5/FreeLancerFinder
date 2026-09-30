USE flf;

-- 1. When does a PAID transaction become withdrawable?
ALTER TABLE transactions
  ADD COLUMN available_at TIMESTAMP NULL DEFAULT NULL AFTER status;

-- 2. Payout details on the Lancer
ALTER TABLE lancers
  ADD COLUMN payout_bank    VARCHAR(60)  DEFAULT '' AFTER category,
  ADD COLUMN payout_account VARCHAR(60)  DEFAULT '' AFTER payout_bank,
  ADD COLUMN payout_name    VARCHAR(120) DEFAULT '' AFTER payout_account;

-- 3. Withdrawals table
CREATE TABLE IF NOT EXISTS withdrawals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lancer_id INT NOT NULL,
  amount INT NOT NULL,
  fee INT NOT NULL DEFAULT 5000,
  flf_covers_fee TINYINT(1) NOT NULL DEFAULT 1,
  bank VARCHAR(60) NOT NULL,
  account VARCHAR(60) NOT NULL,
  account_name VARCHAR(120) NOT NULL,
  status ENUM('PENDING','SENT','REJECTED') NOT NULL DEFAULT 'PENDING',
  tripay_ref VARCHAR(64) DEFAULT '',
  note VARCHAR(255) DEFAULT '',
  receipt_id VARCHAR(32) NOT NULL UNIQUE,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (lancer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;