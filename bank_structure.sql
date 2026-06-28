-- ----------------------------
-- Table structure for ims_bank_account
-- ----------------------------
DROP TABLE IF EXISTS `ims_bank_account`;
CREATE TABLE `ims_bank_account` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `account_code` VARCHAR(20) NOT NULL COMMENT 'รหัสอ้างอิงบัญชีภายในระบบ (เช่น ACC-001)',
  `account_name` VARCHAR(255) NOT NULL COMMENT 'ชื่อบัญชี เช่น บัญชีออมทรัพย์โครงการ',
  `bank_name` VARCHAR(100) NOT NULL COMMENT 'ธนาคาร เช่น กสิกรไทย, ไทยพาณิชย์',
  `account_no` VARCHAR(50) NOT NULL COMMENT 'เลขที่บัญชีธนาคาร',
  `branch_name` VARCHAR(255) DEFAULT NULL COMMENT 'สาขาของธนาคาร',
  `opening_balance` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 COMMENT 'ยอดยกมาเริ่มต้น ณ วันที่ระบุ',
  `opening_date` DATE NOT NULL COMMENT 'วันที่บันทึกยอดยกมาเริ่มต้น',
  `current_balance` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 COMMENT 'ยอดคงเหลือสะสมปัจจุบัน',
  `status` VARCHAR(1) DEFAULT 'Y' COMMENT 'สถานะการใช้งาน (Y = ใช้งาน, N = ยกเลิก)',
  `created_by` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_by` VARCHAR(100) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_account_code` (`account_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for ims_bank_transaction
-- ----------------------------
DROP TABLE IF EXISTS `ims_bank_transaction`;
CREATE TABLE `ims_bank_transaction` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `bank_account_id` INT NOT NULL COMMENT 'รหัสเชื่อมโยงไปยัง ims_bank_account',
  `doc_no` VARCHAR(50) NOT NULL COMMENT 'เลขที่เอกสารบันทึกรายการ (เช่น TXN-202606-0001)',
  `transaction_date` DATETIME NOT NULL COMMENT 'วันเวลาที่ทำรายการจริงในสลิป',
  `transaction_type` VARCHAR(20) NOT NULL COMMENT 'ประเภทรายการ: DEPOSIT, WITHDRAW, ADJUST_ADD, ADJUST_SUB, INTEREST, FEE',
  `amount` DECIMAL(15, 2) NOT NULL COMMENT 'จำนวนเงิน',
  `fee` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 COMMENT 'ค่าธรรมเนียมธนาคารในการทำรายการ (ถ้ามี)',
  `ref_no` VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่อ้างอิงสลิปโอนเงิน / เลขที่เช็ค',
  `description` TEXT DEFAULT NULL COMMENT 'รายละเอียดการทำรายการ / หมายเหตุ',
  `picture_slip` VARCHAR(255) DEFAULT NULL COMMENT 'พาธจัดเก็บไฟล์สลิป/หลักฐานการโอน',
  `status` VARCHAR(1) DEFAULT 'Y' COMMENT 'สถานะรายการ (Y = สำเร็จ, N = รออนุมัติ, C = ยกเลิก)',
  `created_by` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_by` VARCHAR(100) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_doc_no` (`doc_no`),
  KEY `idx_account_txn` (`bank_account_id`),
  KEY `idx_txn_date` (`transaction_date`),
  KEY `idx_txn_type` (`transaction_type`),
  CONSTRAINT `fk_bank_transaction_account` FOREIGN KEY (`bank_account_id`) REFERENCES `ims_bank_account` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
