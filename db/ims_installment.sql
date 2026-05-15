/*
 Navicat Premium Dump SQL

 Source Server         : Mysql-141.98.17.150-PS33
 Source Server Type    : MySQL
 Source Server Version : 100622 (10.6.22-MariaDB)
 Source Host           : 141.98.17.150:3306
 Source Schema         : pshomeco_house_dbs

 Target Server Type    : MySQL
 Target Server Version : 100622 (10.6.22-MariaDB)
 File Encoding         : 65001

 Date: 14/05/2026 13:35:20
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ims_installment
-- ----------------------------
DROP TABLE IF EXISTS `ims_installment`;
CREATE TABLE `ims_installment`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `installment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `house_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doc_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `debtor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `principal_amount` double(10, 2) NOT NULL,
  `down_payment` double(10, 2) NULL DEFAULT 0.00,
  `principal_amount_balance` double(10, 2) NULL DEFAULT NULL,
  `num_installments` int NOT NULL,
  `installment_per_period` double(10, 2) NOT NULL,
  `interest_rate` double(10, 2) NULL DEFAULT 0.00,
  `installment_img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `start_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` enum('pending','active','completed','overdue','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'pending',
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `payment_due_day_period` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
