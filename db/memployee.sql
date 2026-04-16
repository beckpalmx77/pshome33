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

 Date: 16/04/2026 14:31:54
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for memployee
-- ----------------------------
DROP TABLE IF EXISTS `memployee`;
CREATE TABLE `memployee`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sex` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '-',
  `prefix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '-',
  `f_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `l_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `nick_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '-',
  `birthday` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `position_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `dept_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `department_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `start_work_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `work_time_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Y',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `week_holiday` int NULL DEFAULT NULL,
  `create_date` timestamp NULL DEFAULT current_timestamp(),
  `update_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `dept_id_approve` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '-',
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `year` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `line_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `picture` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `salary_type` enum('D','M') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `salary` decimal(10, 2) NULL DEFAULT NULL,
  `salary_history` decimal(10, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of memployee
-- ----------------------------
INSERT INTO `memployee` VALUES (1, 'PS33-2025-00001', 'F', NULL, 'ธนัชญาน์', 'เทพหัสดินฯ', 'ริน', NULL, '0650904645', NULL, 'P003', NULL, NULL, '01-05-2025', 'S001', 'Y', NULL, 0, '2025-05-11 14:12:57', '2026-01-29 17:27:45', '-', 'emp_68591527137784.74757582.png', '2025', 'U2c11430e6f4768a9a09c8c494185592e', '', 'D', 500.00, 500.00);
INSERT INTO `memployee` VALUES (2, 'PS33-2025-00002', 'M', 'นาย', 'อรุณ', 'ทองนพรัตน์', 'เจี๊ยบ', NULL, '', NULL, 'P003', NULL, NULL, '05-05-2025', 'S002', 'Y', NULL, 0, '2025-05-11 20:46:57', '2026-01-29 17:27:45', '-', 'emp_6859151ea638f7.84188625.png', '2025', 'Ua1802b3cbad3f95a003382214baadd64', '', 'D', 500.00, 500.00);
INSERT INTO `memployee` VALUES (3, 'PS33-2024-00001', 'M', 'นาย', 'ทดสอบ', 'ระบบ', 'เทส', NULL, '0225225252', NULL, 'P001', NULL, NULL, '01-05-2025', 'S003', 'N', NULL, 7, '2025-05-14 14:11:55', '2026-01-29 17:27:47', 'R', '', '2024', '-', '', 'M', 0.00, 0.00);
INSERT INTO `memployee` VALUES (4, 'PS33-2025-00003', 'F', 'นางสาว', 'ดลฤดี', 'เจริญจิตต์', 'ดาว', NULL, '0862999954', NULL, 'P001', NULL, NULL, '01-06-2025', 'S003', 'N', NULL, 0, '2025-06-02 08:34:58', '2026-01-29 17:27:51', '-', '', '2025', 'Ua0108dbfb1cdf794aabd054c334da723', '', 'M', 12000.00, 12000.00);
INSERT INTO `memployee` VALUES (5, 'PS33-2025-00004', 'F', 'นางสาว', 'วราพร', 'แสงสุรีย์ฉาย', 'บีบี', NULL, '0628281823', NULL, 'P002', NULL, NULL, '01-06-2025', 'S003', 'N', NULL, 3, '2025-06-02 08:46:09', '2026-02-14 15:26:55', '-', '', '2025', 'U9a053209c4dc04ed4f1ad166ac98fc22', '', 'M', 12000.00, 12000.00);
INSERT INTO `memployee` VALUES (6, 'PS33-2025-00005', 'F', NULL, 'สายใจ', 'ขำสา', 'ใจ', NULL, '', NULL, 'P004', NULL, NULL, '01-05-2025', 'S004', 'N', NULL, 7, '2025-06-19 15:30:38', '2026-01-29 17:28:08', '-', '', '2025', NULL, '', 'M', 13500.00, 13500.00);
INSERT INTO `memployee` VALUES (7, 'PS33-2025-00006', 'F', NULL, 'เรือน', 'อุ้ตมา', 'เรือน', NULL, '', NULL, 'P004', NULL, NULL, '01-06-2025', 'S005', 'Y', NULL, 7, '2025-06-19 15:31:37', '2026-02-26 09:23:35', '-', '', '2025', NULL, '', 'M', 13500.00, 12000.00);
INSERT INTO `memployee` VALUES (8, 'PS33-2024-00002', 'M', NULL, 'Test', 'System', 'Test', NULL, '-', NULL, 'P001', NULL, NULL, '01-05-2025', 'S003', 'N', NULL, 7, '2025-07-11 23:27:43', '2026-03-24 13:19:36', '-', '', '2004', 'U2794e5a7e68ccdf3a57add52e570ba85', '', 'M', 0.00, 0.00);
INSERT INTO `memployee` VALUES (9, 'PS33-2025-00007', 'F', 'นาง', 'เกสรา', 'ไชยมงคล', 'จอย', NULL, '', NULL, 'P001', NULL, NULL, '10-11-2025', 'S003', 'Y', NULL, 6, '2025-11-12 10:37:50', '2026-03-30 09:54:53', '-', '', '2025', 'U80a32a9fff05635ec1c608e24f3f45e2', '', 'M', 13500.00, 12000.00);
INSERT INTO `memployee` VALUES (10, 'PS33-2026-00001', 'F', 'นางสาว', 'อนุสรา', 'พรมมาศร', 'อ้อม', NULL, '', NULL, 'P002', NULL, NULL, '12-01-2026', 'S003', 'Y', NULL, 7, '2026-01-14 09:23:11', '2026-01-29 17:28:02', '-', '', '2026', 'Ud40b6c20c76f030bea5d4a0f781a23ab', '', 'M', 11000.00, 11000.00);
INSERT INTO `memployee` VALUES (11, 'PS33-2026-00002', 'F', NULL, 'สำเดียง', 'พรมบุตร', 'สำเคียง', NULL, '', NULL, 'P004', NULL, NULL, '05-01-2026', 'S004', 'Y', NULL, 7, '2026-01-29 09:40:56', '2026-03-10 13:08:34', '-', '', '2026', NULL, '', 'M', 12000.00, 400.00);
INSERT INTO `memployee` VALUES (12, 'PS33-2026-00003', 'F', 'นาง', 'สาวิตรี', 'วรศิริ', 'นิว', NULL, '0953670893', NULL, 'P004', NULL, NULL, '12-03-2026', 'S004', 'Y', NULL, 7, '2026-03-10 13:12:37', '2026-03-10 13:16:32', '-', 'emp_69afb740b42790.01237315.pdf', '2026', NULL, '', 'M', 12000.00, 0.00);

SET FOREIGN_KEY_CHECKS = 1;
