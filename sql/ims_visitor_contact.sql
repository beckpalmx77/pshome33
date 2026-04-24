-- ===========================================
-- Table: ims_visitor_contact
-- ติดต่อลูกบ้าน - ผู้มาติดต่อ, การแลกบัตร, check-in, check-out
-- อ้างอิง house_number จาก ims_house_master
-- ===========================================
CREATE TABLE IF NOT EXISTS ims_visitor_contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    house_number VARCHAR(50) DEFAULT NULL COMMENT 'บ้านเลขที่ (อ้างอิงจาก ims_house_master.house_number)',
    visitor_name VARCHAR(200) DEFAULT NULL COMMENT 'ชื่อผู้มาติดต่อ',
    phone_number VARCHAR(50) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
    visitor_type VARCHAR(50) DEFAULT NULL COMMENT 'ประเภท (ลูกบ้าน/ช่าง/แขก/ญาติ/อื่นๆ)',
    purpose VARCHAR(200) DEFAULT NULL COMMENT 'วัตถุประสงค์',
    
    -- การแลกบัตร
    card_exchange VARCHAR(1) DEFAULT 'N' COMMENT 'Y=แลกแล้ว, N=ยังไม่ได้แลก',
    card_no VARCHAR(50) DEFAULT NULL COMMENT 'หมายเลขบัตร',
    card_exchange_date DATETIME DEFAULT NULL COMMENT 'วันที่แลกบัตร',
    
    -- Check In / Check Out
    check_in_datetime DATETIME DEFAULT NULL COMMENT 'เวลาเข้า',
    check_out_datetime DATETIME DEFAULT NULL COMMENT 'เวลาออก',
    check_in_by VARCHAR(200) DEFAULT NULL COMMENT 'ผู้ให้เข้า',
    check_out_by VARCHAR(200) DEFAULT NULL COMMENT 'ผู้ให้ออก',
    check_in_status VARCHAR(1) DEFAULT 'N' COMMENT 'Y=เช็คอินแล้ว, N=ยังไม่ได้เช็คอิน',
    
    -- รูปภาพ (เก็บ filename)
    picture_1 VARCHAR(200) DEFAULT NULL COMMENT 'รูปภาพ 1',
    picture_2 VARCHAR(200) DEFAULT NULL COMMENT 'รูปภาพ 2',
    picture_3 VARCHAR(200) DEFAULT NULL COMMENT 'รูปภาพ 3',
    picture_4 VARCHAR(200) DEFAULT NULL COMMENT 'รูปภาพ 4',
    picture_5 VARCHAR(200) DEFAULT NULL COMMENT 'รูปภาพ 5',
    
    -- ข้อมูลเพิ่มเติม
    note TEXT DEFAULT NULL COMMENT 'หมายเหตุ',
    create_by VARCHAR(200) DEFAULT NULL COMMENT 'ผู้สร้าง',
    create_datetime DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้าง',
    update_by VARCHAR(200) DEFAULT NULL COMMENT 'ผู้แก้ไข',
    update_datetime DATETIME DEFAULT NULL COMMENT 'วันที่แก้ไข',
    
    INDEX idx_house_number (house_number),
    INDEX idx_card_no (card_no),
    INDEX idx_check_in_status (check_in_status),
    INDEX idx_check_in_datetime (check_in_datetime),
    INDEX idx_check_out_datetime (check_out_datetime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ติดต่อลูกบ้าน - ผู้มาติดต่อ';

-- ===========================================
-- Rename field: contact_name -> visitor_name
-- ===========================================
-- ALTER TABLE ims_visitor_contact CHANGE contact_name visitor_name VARCHAR(200) DEFAULT NULL COMMENT 'ชื่อผู้มาติดต่อ';