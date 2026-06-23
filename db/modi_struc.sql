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
