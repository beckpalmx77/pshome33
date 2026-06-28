-- --------------------------------------------------------
-- 1. Modify tables and add indexes
-- --------------------------------------------------------

-- 1.1 Modify line_user_id column in ims_house_payment to allow indexing
ALTER TABLE ims_house_payment MODIFY COLUMN line_user_id VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

-- 1.2 Create Index on ims_house_payment
CREATE INDEX idx_line_user_id ON ims_house_payment (line_user_id);

-- 1.3 Create Index on ims_house_master
CREATE INDEX idx_house_number ON ims_house_master (house_number);

-- 1.4 Create Indexes on ims_house_line_user
CREATE INDEX idx_line_phone ON ims_house_line_user (line_phone);
CREATE INDEX idx_line_user_id ON ims_house_line_user (line_user_id);

-- 1.5 Create Index on ims_user
CREATE INDEX idx_user_id ON ims_user (user_id);


-- --------------------------------------------------------
-- 2. Re-create View with Optimized Structure & Extra Fields
-- --------------------------------------------------------
CREATE OR REPLACE VIEW v_ims_house_payment AS 
SELECT 
    h.id, 
    h.runno, 
    h.doc_id, 
    h.payment_date, 
    h.house_number, 
    h.detail, 
    h.period_month_start, 
    h.period_month_to, 
    h.period_year, 
    h.amount, 
    h.picture_payment, 
    h.remark, 
    h.payment_type, 
    h.payment_status,
    CASE 
        WHEN h.payment_status = 'Y' THEN 'ชำระเรียบร้อยแล้ว' 
        WHEN h.payment_status = 'N' THEN 'ยังไม่ยืนยันการชำระ' 
        ELSE 'ไม่ทราบสถานะ' 
    END AS payment_status_desc,
    h.created_at, 
    h.updated_at, 
    h.print_first_date, 
    h.print_last_date, 
    h.print_status,
    m_start.month_name AS month_name_start,
    m_to.month_name AS month_name_to,
    house.alley,
    house.contact_name,
    house.phone_number,
    h.line_user_id,
    u.line_picture_profile,
    h.line_picture_profile_show,
    u.line_user_name,
    hm.area_size,
    hm.garbage_collection_fee,
    hm.common_fee,
    h.payment_method,
    h.create_by,
    h.approve_by,
    h.update_count
FROM ims_house_payment h
LEFT JOIN ims_month m_start ON h.period_month_start = m_start.month
LEFT JOIN ims_month m_to ON h.period_month_to = m_to.month
LEFT JOIN ims_house house ON h.house_number = house.house_number
LEFT JOIN ims_house_master hm ON hm.house_number = h.house_number
LEFT JOIN v_ims_user u ON u.line_user_id = h.line_user_id;


-- --------------------------------------------------------
-- 3. Re-create View v_ims_house with Soft Delete Filter
-- --------------------------------------------------------
CREATE OR REPLACE VIEW v_ims_house AS 
SELECT 
    ims_house.id AS id,
    ims_house.house_number AS house_number,
    ims_house.contact_name AS contact_name,
    ims_house.phone_number AS phone_number,
    ims_house.alley AS alley,
    ims_house.remark AS remark,
    ims_house.status AS status,
    ims_house.picture AS picture,
    ims_house.car_no1 AS car_no1,
    ims_house.car_no2 AS car_no2,
    ims_house.car_no3 AS car_no3,
    ims_house.car_no4 AS car_no4,
    ims_house.car_no5 AS car_no5,
    ims_house.house_status AS house_status,
    ims_house_line_user.line_user_id AS line_user_id,
    ims_house_line_user.line_user_name AS line_user_name,
    ims_house_line_user.line_picture_profile AS line_picture_profile,
    m_house_master.area_size AS area_size,
    m_house_master.garbage_collection_fee AS garbage_collection_fee,
    m_house_master.common_fee AS common_fee 
FROM ims_house 
LEFT JOIN ims_house_line_user ON ims_house_line_user.house_number = ims_house.house_number AND ims_house_line_user.line_phone = ims_house.phone_number
LEFT JOIN ims_house_master ON m_house_master.house_number = ims_house.house_number
WHERE COALESCE(ims_house.status, '') != 'N';
