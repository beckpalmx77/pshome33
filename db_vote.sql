-- --------------------------------------------------------
-- SQL Script: db_vote.sql
-- Description: ตารางสำหรับระบบลงประชามติและโหวตของลูกบ้าน (PS33 Home System)
-- Tables: ims_vote_topic, ims_vote_option, ims_vote_record
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. โครงสร้างตาราง `ims_vote_topic` (หัวข้อการโหวต)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ims_vote_topic` (
  `topic_id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL COMMENT 'หัวข้อการโหวต / ประเด็นประชามติ',
  `description` TEXT NULL COMMENT 'รายละเอียดข้อมูลประกอบคำอธิบาย',
  `status` ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'สถานะหัวข้อโหวต (active = เปิดโหวต, inactive = ปิดโหวต)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่เวลาสร้างหัวข้อ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางหัวข้อประชามติหลัก';

-- --------------------------------------------------------
-- 2. โครงสร้างตาราง `ims_vote_option` (รายการตัวเลือก)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ims_vote_option` (
  `option_id` INT AUTO_INCREMENT PRIMARY KEY,
  `topic_id` INT NOT NULL COMMENT 'ไอดีอ้างอิงตารางหัวข้อโหวต',
  `option_text` VARCHAR(255) NOT NULL COMMENT 'ข้อความเนื้อหาตัวเลือกคำตอบ',
  FOREIGN KEY (`topic_id`) REFERENCES `ims_vote_topic`(`topic_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางรายการตัวเลือกคำตอบประชามติ';

-- --------------------------------------------------------
-- 3. โครงสร้างตาราง `ims_vote_record` (ประวัติการลงคะแนน)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ims_vote_record` (
  `vote_id` INT AUTO_INCREMENT PRIMARY KEY,
  `topic_id` INT NOT NULL COMMENT 'ไอดีอ้างอิงตารางหัวข้อโหวต',
  `house_number` VARCHAR(50) NOT NULL COMMENT 'บ้านเลขที่ของลูกบ้านที่ใช้สิทธิ์',
  `option_id` INT NOT NULL COMMENT 'ไอดีตัวเลือกที่เลือกโหวต',
  `voted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันเวลาที่บันทึกโหวต',
  -- UNIQUE KEY เพื่อป้องกันสิทธิ์ 1 บ้านเลขที่ต่อ 1 โหวตในแต่ละหัวข้อ
  UNIQUE KEY `uq_topic_house` (`topic_id`, `house_number`),
  FOREIGN KEY (`topic_id`) REFERENCES `ims_vote_topic`(`topic_id`) ON DELETE CASCADE,
  FOREIGN KEY (`option_id`) REFERENCES `ims_vote_option`(`option_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางบันทึกคะแนนประชามติแยกตามบ้านเลขที่';

SET FOREIGN_KEY_CHECKS = 1;

