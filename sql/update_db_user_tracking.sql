-- ============================================================
-- SQL Commands for Modifying Database Structure
-- Target Tables: ims_reciepts, ims_payment_voucher
-- Target Views: v_ims_reciepts
-- ============================================================

-- 1. Add created_by and updated_by columns to ims_reciepts
ALTER TABLE `ims_reciepts` 
  ADD COLUMN `created_by` VARCHAR(255) NULL DEFAULT NULL AFTER `approve_status`,
  ADD COLUMN `updated_by` VARCHAR(255) NULL DEFAULT NULL AFTER `created_by`;

-- 2. Add created_by and updated_by columns to ims_payment_voucher
ALTER TABLE `ims_payment_voucher` 
  ADD COLUMN `created_by` VARCHAR(255) NULL DEFAULT NULL AFTER `create_name`,
  ADD COLUMN `updated_by` VARCHAR(255) NULL DEFAULT NULL AFTER `created_by`;

-- 3. Recreate View v_ims_reciepts to include created_by and updated_by
CREATE OR REPLACE VIEW `v_ims_reciepts` AS 
SELECT 
    `ims_reciepts`.`id` AS `id`,
    `ims_reciepts`.`runno` AS `runno`,
    `ims_reciepts`.`doc_id` AS `doc_id`,
    `ims_reciepts`.`reciept_date` AS `reciept_date`,
    `ims_reciepts`.`rec_month` AS `rec_month`,
    `ims_reciepts`.`rec_year` AS `rec_year`,
    `ims_reciepts`.`inv` AS `inv`,
    `ims_reciepts`.`category_id` AS `category_id`,
    `ims_category`.`category_name` AS `category_name`,
    `ims_reciepts`.`description` AS `description`,
    `ims_reciepts`.`qty` AS `qty`,
    `ims_reciepts`.`amount` AS `amount`,
    `ims_reciepts`.`remark` AS `remark`,
    `ims_reciepts`.`created_at` AS `created_at`,
    `ims_reciepts`.`updated_at` AS `updated_at`,
    `ims_reciepts`.`created_by` AS `created_by`,
    `ims_reciepts`.`updated_by` AS `updated_by`,
    `ims_reciepts`.`unit_id` AS `unit_id`,
    `ims_unit`.`unit_name` AS `unit_name`,
    `ims_reciepts`.`approve_status` AS `approve_status`,
    `ims_month`.`month_name` AS `month_name`,
    `ims_reciepts`.`file_attach` AS `file_attach`,
    `ims_reciepts`.`supplier_name` AS `supplier_name`,
    `ims_reciepts`.`supplier_type` AS `supplier_type`,
    `ims_reciepts`.`supplier_car_no` AS `supplier_car_no`,
    `ims_reciepts`.`payment_method` AS `payment_method`
FROM `ims_reciepts`
LEFT JOIN `ims_category` ON `ims_reciepts`.`category_id` = `ims_category`.`category_id`
LEFT JOIN `ims_unit` ON `ims_reciepts`.`unit_id` = `ims_unit`.`unit_id`
LEFT JOIN `ims_month` ON `ims_reciepts`.`rec_month` = `ims_month`.`month_id`;
