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

 Date: 14/05/2026 13:35:10
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ims_installment_detail
-- ----------------------------
DROP TABLE IF EXISTS `ims_installment_detail`;
CREATE TABLE `ims_installment_detail`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `installment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `line_no` int NOT NULL,
  `installment_number` int NOT NULL,
  `doc_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `amount_due` double(10, 2) NOT NULL,
  `principal_per_installment` double(10, 2) NOT NULL,
  `interest_per_installment` double(10, 2) NULL DEFAULT 0.00,
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `amount_paid` double(10, 2) NULL DEFAULT 0.00,
  `payment_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` enum('due','paid','overdue') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'due',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `create_date` timestamp NULL DEFAULT current_timestamp(),
  `update_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `print_status` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `print_first_date` timestamp NULL DEFAULT NULL,
  `print_last_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 86 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
