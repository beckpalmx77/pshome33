<?php
require_once('config/connect_db.php');

try {
    // 1. Create ims_vote_topic
    $sql1 = "CREATE TABLE IF NOT EXISTS `ims_vote_topic` (
      `topic_id` INT AUTO_INCREMENT PRIMARY KEY,
      `title` VARCHAR(255) NOT NULL COMMENT 'หัวข้อการโหวต',
      `description` TEXT NULL COMMENT 'รายละเอียดหัวข้อ',
      `status` ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'สถานะการเปิดโหวต',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql1);
    echo "Table ims_vote_topic created successfully or already exists.\n";

    // 2. Create ims_vote_option
    $sql2 = "CREATE TABLE IF NOT EXISTS `ims_vote_option` (
      `option_id` INT AUTO_INCREMENT PRIMARY KEY,
      `topic_id` INT NOT NULL,
      `option_text` VARCHAR(255) NOT NULL COMMENT 'ข้อความตัวเลือก',
      FOREIGN KEY (`topic_id`) REFERENCES `ims_vote_topic`(`topic_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql2);
    echo "Table ims_vote_option created successfully or already exists.\n";

    // 3. Create ims_vote_record
    $sql3 = "CREATE TABLE IF NOT EXISTS `ims_vote_record` (
      `vote_id` INT AUTO_INCREMENT PRIMARY KEY,
      `topic_id` INT NOT NULL,
      `house_number` VARCHAR(50) NOT NULL COMMENT 'บ้านเลขที่ผู้โหวต',
      `option_id` INT NOT NULL COMMENT 'ไอดีตัวเลือกที่โหวต',
      `voted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY `uq_topic_house` (`topic_id`, `house_number`),
      FOREIGN KEY (`topic_id`) REFERENCES `ims_vote_topic`(`topic_id`) ON DELETE CASCADE,
      FOREIGN KEY (`option_id`) REFERENCES `ims_vote_option`(`option_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql3);
    echo "Table ims_vote_record created successfully or already exists.\n";

    echo "All database tables initialized successfully.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
