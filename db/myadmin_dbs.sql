/*
 Navicat Premium Data Transfer

 Source Server         : Mylocalhost
 Source Server Type    : MySQL
 Source Server Version : 100414
 Source Host           : localhost:3306
 Source Schema         : myadmin_dbs

 Target Server Type    : MySQL
 Target Server Version : 100414
 File Encoding         : 65001

 Date: 29/10/2021 14:55:21
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ims_brand
-- ----------------------------
DROP TABLE IF EXISTS `ims_brand`;
CREATE TABLE `ims_brand`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brand_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `create_date` timestamp(0) NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_brand
-- ----------------------------
INSERT INTO `ims_brand` VALUES (2, 'B-0001', 'Apple', 'Active', '2021-10-04 16:54:51');
INSERT INTO `ims_brand` VALUES (4, 'B-0002', 'Samsung', 'Active', '2021-10-04 16:23:21');
INSERT INTO `ims_brand` VALUES (5, 'B-0005', 'PANASONIC', 'Active', '2021-10-04 16:23:21');
INSERT INTO `ims_brand` VALUES (6, 'B-0006', 'SHARP', 'Active', '2021-10-04 16:23:21');
INSERT INTO `ims_brand` VALUES (7, 'B-0007', 'TOSHIBA', 'Active', '2021-10-04 16:23:21');
INSERT INTO `ims_brand` VALUES (8, 'B-0008', 'Huawei', 'Active', '2021-10-04 16:23:21');
INSERT INTO `ims_brand` VALUES (9, 'B-0009', 'FORD', 'Active', '2021-10-04 16:54:59');
INSERT INTO `ims_brand` VALUES (10, 'B-0010', 'IZUZU', 'Active', '2021-10-04 16:55:12');
INSERT INTO `ims_brand` VALUES (11, 'B-0011', 'CISCO', 'Active', '2021-10-14 10:22:33');
INSERT INTO `ims_brand` VALUES (12, 'B-0012', 'ASUS', 'Active', '2021-10-14 10:25:06');

-- ----------------------------
-- Table structure for ims_customer
-- ----------------------------
DROP TABLE IF EXISTS `ims_customer`;
CREATE TABLE `ims_customer`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `f_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `balance` double(10, 2) NULL DEFAULT 0,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_customer
-- ----------------------------
INSERT INTO `ims_customer` VALUES (1, 'C-0001', 'โรนัลโด', 'London,UK', '123456789', 0.00, 'Active');
INSERT INTO `ims_customer` VALUES (2, 'C-0002', 'เบคแฮม', 'Newyork, USA', '987654321', 0.00, 'Active');
INSERT INTO `ims_customer` VALUES (3, 'C-0003', 'แกรี่ มัวร์', 'Paris, France', '2147483647', 0.00, 'Active');
INSERT INTO `ims_customer` VALUES (9, 'C-0004', 'ไมเคิล', 'หนองบุญมาก ', '0900105656', 0.00, 'Active');
INSERT INTO `ims_customer` VALUES (10, 'C-0010', 'คาวานี่', 'เอควาดอ', '062222', 0.00, 'Active');
INSERT INTO `ims_customer` VALUES (11, 'C-0011', 'อเล็ก เฟอร์กูสัน', 'Manchester', '01565565', 0.00, 'Active');

-- ----------------------------
-- Table structure for ims_event
-- ----------------------------
DROP TABLE IF EXISTS `ims_event`;
CREATE TABLE `ims_event`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `start_event` date NULL DEFAULT NULL,
  `end_event` date NULL DEFAULT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `text_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 30 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_event
-- ----------------------------
INSERT INTO `ims_event` VALUES (15, 'ชำระเงิน Supplier ', '2021-09-01', '2021-09-01', NULL, NULL);
INSERT INTO `ims_event` VALUES (25, 'วางบิลลูกค้า', '2021-09-10', '2021-09-10', NULL, NULL);
INSERT INTO `ims_event` VALUES (26, 'เก็บเช๊คลูกค้า', '2021-09-24', '2021-09-24', NULL, NULL);
INSERT INTO `ims_event` VALUES (27, 'ทดลอง', '2021-09-02', '2021-09-02', NULL, NULL);
INSERT INTO `ims_event` VALUES (28, 'ทำใบเสนอราคา', '2021-09-04', '2021-09-04', NULL, NULL);
INSERT INTO `ims_event` VALUES (29, 'ทดสอบแก้ไข', '2021-10-01', '2021-10-01', NULL, NULL);

-- ----------------------------
-- Table structure for ims_order_detail
-- ----------------------------
DROP TABLE IF EXISTS `ims_order_detail`;
CREATE TABLE `ims_order_detail`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doc_date` date NULL DEFAULT NULL,
  `line_no` int(11) NOT NULL,
  `product_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `unit_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` double(11, 2) NOT NULL DEFAULT 0,
  `price` double(11, 2) NOT NULL DEFAULT 0,
  `create_date` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 67 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_order_detail
-- ----------------------------
INSERT INTO `ims_order_detail` VALUES (65, '2021-ORD-000001', '2021-09-30', 1, 'EL-BF0009', 'U-0001', 15.00, 5000.00, '2021-09-30 14:27:52');
INSERT INTO `ims_order_detail` VALUES (66, '2021-ORD-000002', '2021-09-30', 1, 'EL-MB003', 'U-0001', 10.00, 6500.00, '2021-09-30 15:21:17');

-- ----------------------------
-- Table structure for ims_order_detail_temp
-- ----------------------------
DROP TABLE IF EXISTS `ims_order_detail_temp`;
CREATE TABLE `ims_order_detail_temp`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doc_date` date NULL DEFAULT NULL,
  `line_no` int(11) NOT NULL,
  `product_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `unit_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` double(11, 2) NOT NULL DEFAULT 0,
  `price` double(11, 2) NULL DEFAULT 0,
  `create_date` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 95 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_order_detail_temp
-- ----------------------------
INSERT INTO `ims_order_detail_temp` VALUES (93, 'mi9rT1BrP8zJs6d:1632986849122', '2021-09-30', 1, 'EL-BF0009', 'U-0001', 15.00, 5000.00, '2021-09-30 14:27:50');
INSERT INTO `ims_order_detail_temp` VALUES (94, '6*tws3bDkWjbvOg:1632990060602', '2021-09-30', 1, 'EL-MB003', 'U-0001', 10.00, 6500.00, '2021-09-30 15:21:15');

-- ----------------------------
-- Table structure for ims_order_master
-- ----------------------------
DROP TABLE IF EXISTS `ims_order_master`;
CREATE TABLE `ims_order_master`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doc_year` int(11) NULL DEFAULT NULL,
  `doc_runno` int(11) NULL DEFAULT NULL,
  `customer_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `doc_date` date NULL DEFAULT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Active',
  `KeyAddData` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `create_date` timestamp(0) NULL DEFAULT current_timestamp(0),
  `update_date` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 55 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_order_master
-- ----------------------------
INSERT INTO `ims_order_master` VALUES (53, '2021-ORD-000001', 2021, 1, 'C-0001', '2021-09-30', 'Active', 'mi9rT1BrP8zJs6d:1632986849122', '2021-09-30 14:27:52', '2021-09-30 14:36:06');
INSERT INTO `ims_order_master` VALUES (54, '2021-ORD-000002', 2021, 2, 'C-0002', '2021-09-30', 'Active', '6*tws3bDkWjbvOg:1632990060602', '2021-09-30 15:21:17', '2021-10-04 13:12:36');

-- ----------------------------
-- Table structure for ims_pgroup
-- ----------------------------
DROP TABLE IF EXISTS `ims_pgroup`;
CREATE TABLE `ims_pgroup`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pgroup_id` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pgroup_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 30 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_pgroup
-- ----------------------------
INSERT INTO `ims_pgroup` VALUES (3, 'EL', 'เครื่องใช้ไฟฟ้า-ZX', 'Active');
INSERT INTO `ims_pgroup` VALUES (4, 'MB', 'โทรศัพท์', 'Active');
INSERT INTO `ims_pgroup` VALUES (5, 'GN', 'ของใช้ทั่วไป', 'Active');
INSERT INTO `ims_pgroup` VALUES (6, 'CA', 'เครื่องยนต์ วัสดุยนต์', 'Active');
INSERT INTO `ims_pgroup` VALUES (7, 'ZZ', 'ค่าบริการ/ค่าแรง', 'Active');
INSERT INTO `ims_pgroup` VALUES (8, 'CH', 'สารเคมี', 'Active');
INSERT INTO `ims_pgroup` VALUES (9, 'ST', 'อุปกรณ์เหล็กเหล็กรูปพรรณ', 'Active');
INSERT INTO `ims_pgroup` VALUES (11, 'SE', 'ซีล / โอริง', 'Active');
INSERT INTO `ims_pgroup` VALUES (12, 'OF', 'อุปกรณ์สำนักงาน', 'Active');
INSERT INTO `ims_pgroup` VALUES (13, 'FL', 'หน้าแปลน / เฟล็กซ์', 'Active');
INSERT INTO `ims_pgroup` VALUES (14, 'MA', 'เครื่องจักร / วัสดุ', 'Active');
INSERT INTO `ims_pgroup` VALUES (15, 'TO', 'เครื่องมือช่าง/อุปกรณ์ซ่อมบำรุง', 'Active');
INSERT INTO `ims_pgroup` VALUES (16, 'CO', 'วัสดุงานก่อสร้าง', 'Active');
INSERT INTO `ims_pgroup` VALUES (17, 'BE', 'สายพาน', 'Active');
INSERT INTO `ims_pgroup` VALUES (18, 'PL', 'กระสอบจัมโบ้ และถุง ต่างฯ', 'Active');
INSERT INTO `ims_pgroup` VALUES (19, 'SU', 'วัสดุสิ้นเปลืองเครื่องใช้สุขภัณฑ์', 'Active');
INSERT INTO `ims_pgroup` VALUES (20, 'DR', 'เวชภัณฑ์', 'Active');
INSERT INTO `ims_pgroup` VALUES (21, 'MI', 'เบ็ดเตล็ด', 'Active');
INSERT INTO `ims_pgroup` VALUES (22, 'WE', 'ลวดเชื่อม', 'Active');
INSERT INTO `ims_pgroup` VALUES (23, 'MU', 'พูลเล่ย์', 'Active');
INSERT INTO `ims_pgroup` VALUES (25, 'OI', 'เชื้อเพลิง/สารหล่อลื่น', 'Active');
INSERT INTO `ims_pgroup` VALUES (26, 'LA', 'LA', 'Active');
INSERT INTO `ims_pgroup` VALUES (27, 'NU', 'น็อต สกรู', 'Active');
INSERT INTO `ims_pgroup` VALUES (28, 'BA', 'ลูกปืน / ส่วนประกอบ', 'Active');
INSERT INTO `ims_pgroup` VALUES (29, 'PV', 'อุปกรณ์พีวีซี  พีอี', 'Active');

-- ----------------------------
-- Table structure for ims_product
-- ----------------------------
DROP TABLE IF EXISTS `ims_product`;
CREATE TABLE `ims_product`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pgroup_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `brand_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `name_t` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '-',
  `name_e` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '-',
  `quantity` double(11, 2) NOT NULL DEFAULT 0,
  `unit_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '-',
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` timestamp(0) NOT NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0),
  `picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'img/icon/product-001.png',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1007 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_product
-- ----------------------------
INSERT INTO `ims_product` VALUES (1, 'EL-MB001', 'EL', 'B-0008', 'หัวเหว่ย 7 SE', 'iphone 6s mobile', 15.00, 'U-0001', 'Active', '2021-10-02 14:41:00', 'product-001.png');
INSERT INTO `ims_product` VALUES (2, 'EL-MB002', 'EL', 'B-0002', 'samsung galaxy', 'ddsgds', 15.00, 'U-0001', 'Active', '2021-10-02 14:41:00', 'product-001.png');
INSERT INTO `ims_product` VALUES (3, 'EL-MB003', 'EL', 'B-0002', 'iphone 7', 'iphone 7 mobile', 13.00, 'U-0001', 'Active', '2021-10-02 14:41:00', 'product-001.png');
INSERT INTO `ims_product` VALUES (4, 'EL-MB004', 'EL', 'B-0002', 'Apple Iphone 12 ไอโฟน ', '-', 20.00, 'U-0001', 'Active', '2021-10-02 14:41:00', 'product-001.png');
INSERT INTO `ims_product` VALUES (5, 'OF-PN001', 'OF', 'B-0002', 'ปากกาสีน้ำเงิน', '-', 50.00, 'U-0003', 'Active', '2021-10-02 14:41:00', 'product-001.png');
INSERT INTO `ims_product` VALUES (6, 'EL-BF0009', 'EL', 'B-0001', 'เครื่องดูดฝุ่น', '-', 50.00, 'U-0001', 'Active', '2021-09-21 16:40:48', 'product-001.png');
INSERT INTO `ims_product` VALUES (7, 'ST-1-LSP202035-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 2 นิ้ว x 2 นิ้ว หนา 3.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (8, 'CA-1-KANPUN008-CG', 'CA', NULL, 'แกนหลัง ลูกปืน ถ้วย', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (9, 'TO-1-TMMJIB301-CG', 'TO', NULL, 'เครื่องวัดอุณหภูมิติดแผง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (10, 'OF-1-PLEXTOR12-CG', 'OF', NULL, 'SSD PLEXTOR 128 GB. (PX-12883C)', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (11, 'MA-1-SPRAY1600-CG', 'MA', NULL, 'SPRAY พ่นล้างถ่าน', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (12, 'EL-1-MAM095220-CG', 'EL', NULL, 'แมกเนติกคอนแทค SN-95 220V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (13, 'SE-1-T06289012-CG', 'SE', NULL, 'ซีล TC 62-89-12', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (14, 'BE-1-B03600000-CG', 'BE', NULL, 'สายพานร่อง B ขนาด 36 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (15, 'BA-1-002221200-CG', 'BA', NULL, 'ลูกปืน 22212 E', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (16, 'BA-1-002222800-CG', 'BA', NULL, 'ลูกปืน 22228 CCK', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (17, 'CA-1-GRONG4DR5-CG', 'CA', NULL, 'กรอง 4DR5', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (18, 'CA-1-GRONGGJH3-CG', 'CA', NULL, 'กรองเกียร์ JH30', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (19, 'CA-1-HNGTYSD25-CG', 'CA', NULL, 'เครื่องยนต์ NISSAN SD25 (มือสอง)', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (20, 'CO-1-SENB00414-CG', 'CO', NULL, 'สีน้ำมันสีน้ำเงิน กัปตัน เบอร์ 414 ขนาด 18  ลิตร', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (21, 'EL-1-BATE50512-CG', 'EL', NULL, 'แบตเตอรี่ UFS 5.5Ah12V', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (22, 'EL-1-BG3003P1M-CG', 'EL', NULL, 'เบรกเกอร์ 300A 3P NF400-CW', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (23, 'EL-1-INAACS550-CG', 'EL', NULL, 'INVERTER ABB รุ่น ACS550-01-03A3-4 ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (24, 'CO-1-TAKLO0000-CG', 'CO', NULL, 'ตะขอ ป.ปลา 6 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (25, 'MA-1-TAGS00418-CG', 'MA', NULL, 'ตะแกรง STL เบอร์ 4 ลวด 19 x 1.20 เมตร x ตัด รู 5.3', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (26, 'MI-1-BAIHSTL80-CG', 'MI', NULL, 'ใบหินเจียรตัดสแตนเลส 8 นิ้ว x 1 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:06', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (27, 'MI-1-HUNCHET00-CG', 'MI', NULL, 'หัวฉีดพ่นน้ำ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (28, 'PL-1-MAI000120-CG', 'PL', NULL, 'พาเลทไม้  ขนาด 1.20 x 1.20 x 13.5 ซม.  มือสอง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (29, 'PV-1-GLOBLF12K-CG', 'PV', NULL, 'ก๊อกวาล์วเหล็กหล่อหน้าแปลน 1 1/2 นิ้ว x 10K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (30, 'SE-1-MACLOWARA-CG', 'SE', NULL, 'ซีลปั๊มน้ำ LOWARA', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (31, 'ST-1-KNGSSS049-CG', 'ST', NULL, 'ข้องอสแตนเลสเชื่อม 304 90 องศา 2 นิ้ว SCH10S', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (32, 'ST-1-LPK012215-CG', 'ST', NULL, 'เหล็กแผ่นกลม 12 1/4 นิ้ว หนา 15 มิล', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (33, 'EL-1-CBG040380-CG', 'EL', NULL, 'เซอร์กิตเบรกเกอร์ SK-53B 380V 40A', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (34, 'SU-1-COFFEE002-CG', 'SU', NULL, 'กาแฟมอคโคน่า ROYAL GOLD', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (35, 'EL-1-MAMSN0050-CG', 'EL', NULL, 'แมกเนติกคอนแทค SN-50 220V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (36, 'ST-1-LSP303025-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3 นิ้ว x 3 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (37, 'FL-1-IR0060000-CG', 'FL', NULL, 'หน้าแปลนเชื่อม ขนาด 3/4 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (38, 'ST-1-LSP303026-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3 นิ้ว x 3 นิ้ว หนา 2.6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (39, 'BA-1-002862200-CG', 'BA', NULL, 'ลูกปืน 28622', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (40, 'BA-1-003030600-CG', 'BA', NULL, 'ลูกปืน NSK 30306', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (41, 'BA-1-003130000-CG', 'BA', NULL, 'สลิป HE313  ใช้กับ 22213EK', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (42, 'BA-1-00522S000-CG', 'BA', NULL, 'ซีล TSN 522 ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (43, 'BA-1-007309BP1-CG', 'BA', NULL, 'ลูกปืน 7309 BECBP', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (44, 'BA-1-042381000-CG', 'BA', NULL, 'ลูกปืน 42381/42587', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (45, 'CA-1-AMCHR5000-CG', 'CA', NULL, 'แอมป์ไฟชาร์ท +-50', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (46, 'CA-1-SAIFIA015-CG', 'CA', NULL, 'สายไฟอ่อน ขนาด 1.5 M', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (47, 'CO-1-COLERBALL-CG', 'CO', NULL, 'สีพ่นอุตสาหกรรม สีบรอนซ์', '-', 0.00, '1851', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (48, 'EL-1-FAILRFL02-CG', 'EL', NULL, 'ชุดโคมไฟถนน (พร้อมหลอด36W + บัลลาสต์ + สตาร์ทเตอร์)', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (49, 'EL-1-FAILRFL03-CG', 'EL', NULL, 'ชุดโคมหลอด 36 W (พร้อมหลอด 36 W + บัลลาสต์ + สตาร์ทเตอร์)', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (50, 'EL-1-HANDYBOX4-CG', 'EL', NULL, 'HANDY BOX 4 x 4', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (51, 'EL-1-MON007500-CG', 'EL', NULL, 'มอเตอร์หน้าแปลน 7.5HP 4P 380V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (52, 'EL-1-SMULTI151-CG', 'EL', NULL, 'สายไฟคอนโทรล 15 x 1 SQmm 250 V (100 เมตร)', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (53, 'LA-1-RX2906200-CG', 'LA', NULL, '62(R-10112)Straight Connector(APC-050)/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (54, 'EL-1-LODH01500-CG', 'EL', NULL, 'หลอดฮาโลเจน 1500 วัตต์ ', '-', 0.00, '1290', 'Active', '2021-10-02 14:53:07', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (55, 'MA-1-TAGS80023-CG', 'MA', NULL, 'ตะแกรง STL เบอร์ 8 ลวด 23 x 1.20 เมตร รู 2.56', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (56, 'MI-1-TANBAN002-CG', 'MI', NULL, 'ถ่านนาฬิกากลมแบนใหญ่', '-', 0.00, '1254', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (57, 'PV-1-NAPEN3091-CG', 'PV', NULL, 'หน้าแปลนพีวีซี 3 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (58, 'PV-1-YSN10K040-CG', 'PV', NULL, 'วายสเตรนเนอร์หน้าแปลน 4 นิ้ว 10K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (59, 'SE-1-ORING3565-CG', 'SE', NULL, 'โอริง 65 x 3.5 มิล', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (60, 'ST-1-KOCOP1802-CG', 'ST', NULL, 'ข้อโค้งประปา 180 องศา 2 นิ้ว ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (61, 'MI-1-NAPKIN200-CG', 'MI', NULL, 'เศษผ้าเย็บสำเร็จ', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (62, 'ST-1-SS4005X80-CG', 'ST', NULL, 'เหล็กแผ่นดำ 4 x 8 ฟุต หนา 5 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (63, 'SU-1-COFFEE100-CG', 'SU', NULL, 'กาแฟทรีอินวัน', '-', 0.00, '1295', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (64, 'MI-1-NAMYA5123-CG', 'MI', NULL, 'น้ำยาเดทตอล 750 มล', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (65, 'BA-1-222130000-CG', 'BA', NULL, 'ลูกปืน 22213 ( 2 1/4นิ้ว ) เม็ดหมอน', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (66, 'NU-1-LMD240160-CG', 'NU', NULL, 'น็อตมิลดำ M24 x 160 มิล พร้อมหัว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (67, 'EL-1-MAMSN0220-CG', 'EL', NULL, 'แมกเนติกคอนแทค SN-220 220V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (68, 'ST-1-PAPD06045-CG', 'ST', NULL, 'แป๊บกลมดำ 6 นิ้ว หนา 4.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (69, 'ET-1-COMPS2800-CG', 'ET', NULL, 'คอมเพรสเซอร์แอร์ 18000 BTU 220V มิตซูบิชิ NH47VNDT', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (70, 'BA-1-627LLB1KN-CG', 'BA', NULL, 'ลูกปืน 627LLB/1K NTN', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (71, 'BA-1-B06312000-CG', 'BA', NULL, 'ลูกปืน 6212 (แบบเม็ดกลมโลหะ ไม่มีฝาปิด)', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (72, 'BA-1-BEARINGSZ-CG', 'BA', NULL, 'ลูกปืน LGD-12-เครื่องชั่ง TCB-A5', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (73, 'BA-1-N308EMNSK-CG', 'BA', NULL, 'ลูกปืน N308EM NSK', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (74, 'CA-1-BOOTTHONG-CG', 'CA', NULL, 'บูชทองเหลืองประตูรถ ISUZU ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (75, 'CA-1-LOKMAK124-CG', 'CA', NULL, 'ลูกหมาก TY', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (76, 'CA-1-PLAO30HPP-CG', 'CA', NULL, 'เพลาข้อเหวี่ยง 30 HP PP-360', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (77, 'CA-1-SLAKBOUT0-CG', 'CA', NULL, 'สลักบูชประตู ISUZU TFR', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (78, 'CA-1-SLAKPRATU-CG', 'CA', NULL, 'สลักประตูรถ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (79, 'EL-1-CAPA30025-CG', 'EL', NULL, 'คาปาซิเตอร์ 300MF 250 V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (80, 'EL-1-CMOTER250-CG', 'EL', NULL, 'C MOTOR START 145-193 UF 250(แคปรัน)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (81, 'LA-1-RX2906600-CG', 'LA', NULL, '66(107764)Flexible Conduit/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (82, 'LA-1-RX2906900-CG', 'LA', NULL, '69(R-20081)Wrench/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (83, 'MA-1-STER00000-CA', 'MA', NULL, 'สเตอร์หน้ารถจักรยาน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (84, 'MA-1-TAGANG120-CG', 'MA', NULL, 'ตะแกรง STL 12 Mesh ลวด# 27 กว้าง 1.20 ม. รู 1.7 มิล', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:08', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (85, 'PV-1-HPL112000-CG', 'PV', NULL, 'หางปลาไหลพีวีซี 1 1/2 นิ้ว       ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (86, 'SE-1-AE01834F0-CG', 'SE', NULL, 'ซีล AE1834F', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (87, 'SE-1-MICRO2020-CG', 'SE', NULL, 'ไมโครไฟเบอร์ ขนาด 2 นิ้ว หนา 2 นิ้ว ยาว 1 เมตร', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (88, 'SE-1-OR0140710-CG', 'SE', NULL, 'โอริง 014 x 71 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (89, 'SE-1-OR0302500-CG', 'SE', NULL, 'โอริง 3 x 25 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (90, 'SE-1-PAGNA1005-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 10 นิ้ว 150API', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (91, 'ST-1-LBL020080-CG', 'ST', NULL, 'เหล็กแบน 2 นิ้ว หนา 8 มิล ยาว 6 เมตร ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (92, 'CO-1-PUNHGF012-CG', 'CO', NULL, 'ปูนเทโครงสร้าง (รถ/คิว)', '-', 0.00, '1257', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (93, 'MA-1-PIPE00000-CG', 'MA', NULL, 'ท่อสามทางปั๊มลม 10 HP', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (94, 'BA-1-NSK206000-CG', 'BA', NULL, 'ลูกปืน NSK 62/28', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (95, 'BA-1-NU1036I00-CG', 'BA', NULL, 'ลูกปืน NU 1036 MYC3 INNER RING', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (96, 'BA-1-RING13200-CG', 'BA', NULL, 'แหวนกำหนดตำแหน่ง FRB 13.5/200', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (97, 'ST-1-LSP303030-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3 นิ้ว x 3 นิ้ว หนา 3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (98, 'BA-1-RING15250-CG', 'BA', NULL, 'แหวนกำหนดตำแหน่ง 15/250', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (99, 'EL-1-MAOVER11A-CG', 'EL', NULL, 'แมกเนติกพร้อมโอเวอร์โหลดรีเลย์ Mitsubishi MSO-N12 11A (9-13) 220V.50Hz', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (100, 'EL-1-MOSAMSUN1-CG', 'EL', NULL, 'มอเตอร์คอยล์ร้อน 1/6HP 6P 220V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (101, 'BA-1-SARIKBB01-CG', 'BA', NULL, 'สายพานร่อง B เบอร์ 79 รหัสนี้ไม่ได้ใช้', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (102, 'BA-1-UCF213025-CG', 'BA', NULL, 'ลูกปืน UCF 213  รูเพลา 2 1/2 นิ้ว  ', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (103, 'BE-1-095095000-CG', 'BE', NULL, 'สายพาน 9.5 x 950', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (104, 'CA-1-CLU111300-CG', 'CA', NULL, 'จานครัช 11 นิ้ว 13 ฟัน', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (105, 'CA-1-HANDBRAKE-CG', 'CA', NULL, 'ชุดเบรคมือ ISUZU บฉ-9687', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (106, 'CA-1-POK100LOR-CG', 'CA', NULL, 'ปลอกสูบ รถสิบล้อ', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (107, 'CO-1-BOOT15500-CG', 'CO', NULL, 'บูชประตูเหล็ก  1 1/4 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (108, 'EL-1-DUCT00001-CG', 'EL', NULL, 'รางหลังเต่าพลาสติก ขนาด 1/2 นิ้ว x 2 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (109, 'EL-1-LBBOX0010-CG', 'EL', NULL, 'LB บล็อก ขนาด 1 นิ้ว ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (110, 'EL-1-LUGSQ0210-CG', 'EL', NULL, 'ลูกเซอร์กิตเบรกเกอร์ 2A 1P 220V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (111, 'EL-1-NEAFR2000-CG', 'EL', NULL, 'ชุดกรองลม AFR-2000 0.5-8 bar PT 1/4 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (112, 'EL-1-TEBTON025-CG', 'EL', NULL, 'เทปทนความร้อน 25 มิล', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (113, 'OF-0000092555-CG', 'OF', NULL, 'แฟ้มแหวนกระดาษ', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:09', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (114, 'SE-1-OR0120200-CG', 'SE', NULL, 'โอริง RMT 12 x 2 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (115, 'SE-1-OR0408400-CG', 'SE', NULL, 'โอริง 4 x 84 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (116, 'SE-1-OR0475355-CG', 'SE', NULL, 'โอริง EPDM  ID 47.5 x 3.55 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (117, 'ST-1-LBL010041-CG', 'ST', NULL, 'เหล็กแบน 1 นิ้ว หนา 4.5 มิล ยาว 6 เมตร ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (118, 'ST-1-LEK786MH1-CG', 'ST', NULL, 'เหล็กเพลาขาว 7/8 นิ้ว x 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (119, 'ST-1-LEX125160-CG', 'ST', NULL, 'แป๊ปดำ ขนาดโตนอก 1/2 นิ้ว โตใน 5/16 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (120, 'ST-1-LP5010250-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 25 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (121, 'MI-1-NAMYA4874-CG', 'MI', NULL, 'น้ำยาเดทตอล 1200 มล.', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (122, 'BE-1-095107500-CG', 'BE', NULL, 'สายพาน 9.5 x 1075', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (123, 'BE-1-133200LUM-CG', 'BE', NULL, 'สายพานลำเลียงหน้าเรียบ ยาว 13 เมตร กว้าง 32 นิ้ว ต่อกลม', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (124, 'ST-1-LSP303032-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3 นิ้ว x 3 นิ้ว หนา 3.2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (125, 'EL-1-MO005000K-CG', 'EL', NULL, 'มอเตอร์  5HP 4P 380V แบบขาตั้ง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (126, 'CA-1-FD2600000-CG', 'CA', NULL, 'ลูกปืนคลัท FD26', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (127, 'CA-1-KHATE30HP-CG', 'CA', NULL, 'ขาเตะน้ำมัน 30 HP PP-630', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (128, 'CA-1-L32TAG120-CG', 'CA', NULL, 'ลูกปืนรับน้ำหนัก 32TAG12', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (129, 'CA-1-PASDBRAKE-CG', 'CA', NULL, 'ผ้าเบรค C240', '-', 0.00, '1258', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (130, 'CA-1-SIPUMJH6U-CG', 'CA', NULL, 'ใส้ปั๊มไฮโดรลิกซ์ ตัวบน JH60', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (131, 'CO-1-TAPUMGIBM-CG', 'CO', NULL, 'ตะปูตอกกิ๊บ (ตอกไม้)', '-', 0.00, '1291', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (132, 'DR-1-SAYYANGRU-CG', 'DR', NULL, 'สายยางรัดห้ามเลือด', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (133, 'EL-1-CAPA15450-CG', 'EL', NULL, 'คาปาซิเตอร์ 15UF 450V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (134, 'BE-1-B03900000-CG', 'BE', NULL, 'สายพานร่อง B ขนาด 39 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (135, 'EL-1-MOSAMSUNG-CG', 'EL', NULL, 'มอเตอร์คอยล์ร้อน 60 W 220 V ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (136, 'LA-1-RX2900100-CG', 'LA', NULL, '1(107770)Base รุ่น RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (137, 'MA-1-PUMNM0000-CG', 'MA', NULL, 'ปั๊มน้ำสำหรับหม้อไอน้ำ NOCCHI  รุ่น VLR4-160AT 4HP 380 V. ท่อส่ง1 1/4 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (138, 'NU-1-GPTM15540-CG', 'NU', NULL, 'สกรูเกลียวปล่อย ยาว 1/2 นอ้ว ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (139, 'NU-1-LMD240090-CG', 'NU', NULL, 'น็อตมิลดำ M24 x 90 มิล พร้อมหัว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:10', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (140, 'SE-1-PAGNA0305-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 3 นิ้ว 150API', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (141, 'SE-1-PAGNA1601-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 16 นิ้ว PN16', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (142, 'SE-1-SN2203465-CG', 'SE', NULL, 'ซีลกันน้ำมัน 22-34-6.5', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (143, 'ST-1-LI1050010-CG', 'ST', NULL, 'เหล็กไอบีม ขนาด 10นิ้วx5นิ้วx10มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (144, 'ST-1-LP4080025-CG', 'ST', NULL, 'เหล็กแผ่นดำ 4 x 8 ฟุต หนา 2.5 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (145, 'ST-1-LP4080200-CG', 'ST', NULL, 'เหล็กแผ่นดำ 4 x 8 ฟุต หนา 20 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (146, 'ST-1-LP5010100-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 10 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (147, 'ST-1-LP5010160-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 16 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (148, 'ST-1-PAPD04050-CG', 'ST', NULL, 'แป๊บกลมดำ 4 นิ้ว หนา 5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (149, 'ST-1-POSB30013-CG', 'ST', NULL, 'เหล็กเพลาฟ้า 3 นิ้ว ยาว 134 ซม.', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (150, 'SU-1-COFFEE004-CG', 'SU', NULL, 'กาแฟ ชนิดถุง 450 กรัม เนสกาแฟ Red Cup  ', '-', 0.00, '1272', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (151, 'ST-1-LSP404030-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 4 นิ้ว x 4 นิ้ว หนา 3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (152, 'EL-1-MAOVER210-CG', 'EL', NULL, 'แมกเนติกพร้อมโอเวอร์โหลดรีเลย์ Mitsubishi MSO-N220 210A (170-250) 220V.50Hz', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (153, 'EL-1-MAOVER25A-CG', 'EL', NULL, 'แมกเนติกพร้อมโอเวอร์โหลดรีเลย์ Mitsubishi MSO-N10 2.5A (2-3) 220V.50Hz', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (154, 'EL-1-TEMITI200-CG', 'EL', NULL, 'Temperature Indicator TI20/K/0-1200C/ 24VDC', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (155, 'CO-1-FARGYPSUM-CG', 'CO', NULL, 'ฝ้ายิบซั่ม', '-', 0.00, '1279', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (156, 'CO-1-KOPOPA999-CG', 'CO', NULL, 'ขอ ป.ปลา 9  นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (157, 'EL-1-CAPA25450-CG', 'EL', NULL, 'คาปาซิเตอร์ 2.5 UF 450 VAC', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (158, 'EL-1-GIBSAY500-CG', 'EL', NULL, 'กิ๊บรัดสายไฟ เบอร์ 5', '-', 0.00, '1291', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (159, 'EL-1-GIBSAY600-CG', 'EL', NULL, 'กิ๊บรัดสายไฟ เบอร์ 6', '-', 0.00, '1291', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (160, 'EL-1-HANG07012-CG', 'EL', NULL, 'หางปลากลมเปลือย ขนาด 70-12 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (161, 'LA-1-IK2377100-CG', 'LA', NULL, 'IKA-1A 2377100 Bearing bushing (pos.9 AOD1.1)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (162, 'LA-1-RX2900300-CG', 'LA', NULL, '3(R-20023)Hammer Block รุ่น RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (163, 'MA-1-SO0012NEW-CG', 'MA', NULL, 'โซ่ 12 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (164, 'MA-1-TAGS00612-CG', 'MA', NULL, 'ตะแกรง STL เบอร์ 6 ลวด 20 x 1.20 เมตร x ตัด', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (165, 'MI-1-KOTOL12MM-CG', 'MI', NULL, 'ข้อต่อลมต่อตรง ขนาด 12mm x 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (166, 'MI-1-TOLS00100-CG', 'MI', NULL, 'ท่อลมแรงดันสูง (AIR HOSE) ID 1 นิ้ว 300 PSI ', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:11', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (167, 'NU-1-HJM0615MM-CG', 'NU', NULL, 'น็อตหัวจม M6 x 15 มิล พร้อมแหวนอีแปะ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (168, 'NU-1-LNC002004-CG', 'NU', NULL, 'น็อตดำ NC 1/4 นิ้ว ยาว1/2 นิ้ว พร้อมแหวนสปริง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (169, 'OF-1-OKIML0390-CG', 'OF', NULL, 'หนามเตย เครื่องปริ้นเตอร์ OKI ML390', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (170, 'PV-1-SLPVN1006-CG', 'PV', NULL, 'สามทางพีวีซี 1 นิ้ว ลด 3/4 นิ้ว x 13.5', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (171, 'PV-1-YSNT15004-CG', 'PV', NULL, 'วายสเตรนเนอร์ทองเหลือง 1/2 นิ้ว 150P', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (172, 'ST-1-LBL050180-CG', 'ST', NULL, 'เหล็กแบน 5 นิ้ว หนา 18 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (173, 'ST-1-LCHAG1040-CG', 'ST', NULL, 'เหล็กฉาก 1 นิ้ว x 1 นิ้ว หนา 4 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (174, 'ST-1-LP4080380-CG', 'ST', NULL, 'เหล็กแผ่นดำ 4 x 8 ฟุต หนา 38 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (175, 'ST-1-SSCL02045-CG', 'ST', NULL, 'สแตนเลสฉาก 2 นิ้ว x 2 นิ้ว หนา 4.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (176, 'ST-1-SSPL48060-CG', 'ST', NULL, 'สแตนเลสแผ่น 4 x 8 ฟุต หนา 6 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (177, 'TO-1-EAVEBOAR0-CG', 'TO', NULL, 'ครอบเชิงชาย', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (178, 'TO-1-KEY000701-CG', 'TO', NULL, 'ประแจแหวนข้างปากตาย  7 MM', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (179, 'ST-1-LSP404032-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 4 นิ้ว x 4 นิ้ว หนา 3.2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (180, 'EL-1-MAOVER42A-CG', 'EL', NULL, 'แมกเนติกพร้อมโอเวอร์โหลดรีเลย์ Mitsubishi MSO-N50 42A (34-50) 220V.50Hz', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (181, 'LA-1-IKAC48022-CG', 'LA', NULL, 'O-ring (pos.22 C48)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (182, 'CA-1-IVALE4JG2-CG', 'CA', NULL, 'ตะเกียบวาล์ว ISUZU 4JG2', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (183, 'CA-1-JH30KR000-CG', 'CA', NULL, 'ชาร์พกันรุน JH30 DA220', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (184, 'CA-1-JH30SK000-CG', 'CA', NULL, 'ชาร์พก้าน JH30 DA220', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (185, 'CA-1-PJ838437A-CG', 'CA', NULL, 'ลูกปืนแผงงารถยก 838437ABM', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (186, 'CA-1-SEPBP2094-CG', 'CA', NULL, 'ซีลพวงมาลัย โคมัทสุ BP2094E ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (187, 'LA-1-RX2903100-CG', 'LA', NULL, '31(ZA11167)Grommet/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (188, 'CA-1-YANGNR195-CG', 'CA', NULL, 'ยางนอก 195 R14C Radial ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (189, 'CO-1-LUKONG040-CG', 'CO', NULL, 'ลูกกรงแก้ว ขนาด 40 ซม.', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (190, 'CO-1-WOOD01510-CG', 'CO', NULL, 'ไม้รวก ขนาด 1.5 นิ้ว ยาว 10 เมตร', '-', 0.00, '1859', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (191, 'CO-1-WOOD18350-CG', 'CO', NULL, 'ไม้เนื้อแข็ง หนา 1 นิ้ว กว้าง 8 นิ้ว ยาว 3.50 เมตร', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (192, 'EL-1-BOXROY000-CG', 'EL', NULL, 'กล่องกันน้ำ Nano-406  3 x 5.5 x 2.5', '-', 0.00, '1253', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (193, 'EL-1-HANG05010-CG', 'EL', NULL, 'หางปลากลมเปลือย ขนาด 50-10 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (194, 'LA-1-IKAC48023-CG', 'LA', NULL, 'O-ring V80G (pos.23 C48)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (195, 'LA-1-RX2923A00-CG', 'LA', NULL, '23A(106582)1/2นิ้ว I.D.Shim/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (196, 'MA-1-PUMAUTO00-CG', 'MA', NULL, 'เครื่องปั้มน้ำอัตโนมัติ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:12', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (197, 'MA-1-PUMSTNXF2-CG', 'MA', NULL, 'ปั๊มหอยโข่ง SUS STAC NXF2 50/1000', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (198, 'NU-1-LCHM16055-CG', 'NU', NULL, 'น็อตหัวเหลี่ยมธรรมดา M16 x 55 มิล พร้อมหัว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (199, 'PL-1-P12012015-CG', 'PL', NULL, 'พาเลทไม้ ขนาด 120 x 120 x 15 ซ.ม.', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (200, 'PV-1-PLDIAF000-CG', 'PV', NULL, 'พลาสติกไดอะแฟรมวาล์ว รุ่น K521 ขนาด 1 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (201, 'PV-1-YSN10K030-CG', 'PV', NULL, 'วายสเตรนเนอร์หน้าแปลน 3 นิ้ว 10K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (202, 'SE-1-EBARA0014-CG', 'SE', NULL, 'ซีลก้นหอยแบบสปริงปั๊มน้ำ 14 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (203, 'SE-1-PAGNA2501-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 2 1/2 นิ้ว PN16', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (204, 'ST-1-KOAOI1000-CG', 'ST', NULL, 'เหล็กข้ออ้อย 10 มิล ยาว 10 เมตร SD40', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (205, 'ST-1-LBL035090-CG', 'ST', NULL, 'เหล็กแบน 3 1/2 นิ้ว หนา 9 มิล ยาว 6 เมตร ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (206, 'ST-1-LH1500710-CG', 'ST', NULL, 'เหล็กเอชบีม 150 x 150 x 7 x 10 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (207, 'ST-1-POSW05600-CG', 'ST', NULL, 'เหล็กเพลาขาว 5/8 นิ้ว x 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (208, 'ST-1-POSW12600-CG', 'ST', NULL, 'เหล็กเพลาขาว 1 1/4 นิ้ว x 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (209, 'ST-1-SSPL48090-CG', 'ST', NULL, 'สแตนเลสแผ่น 4 x 8 ฟุต หนา 9 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (210, 'ST-1-STELPAO36-CG', 'ST', NULL, 'เหล็กเพลาขาว 3 นิ้ว x 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (211, 'ST-1-LSP404040-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 4 นิ้ว x 4 นิ้ว หนา 4 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (212, 'EL-1-MAOVER67A-CG', 'EL', NULL, 'แมกเนติกพร้อมโอเวอร์โหลดรีเลย์ Mitsubishi MSO-N80 67A (54-80) 220V.50Hz', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (213, 'EL-1-SAILAN220-CG', 'EL', NULL, 'ไฟไซเรนชนิดหมุนแบบลูกฟูก 220V', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (214, 'BA-1-SLIP2310X-CG', 'BA', NULL, 'สลิป HE 2310X NTN(ใช้กับUKF210)', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (215, 'BA-1-UKF210ASA-CG', 'BA', NULL, 'ลูกปืน UKF 210 ASAHI', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (216, 'CA-1-JH30LS000-CG', 'CA', NULL, 'ลูกสูบ JH30 DA220', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:13', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (217, 'CA-1-LO30HP630-CG', 'CA', NULL, 'ลูกสูบ 30 HP PP-630', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (218, 'CA-1-SLLSCA910-CG', 'CA', NULL, 'สลักลูกสูบ CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (219, 'CO-1-SESPAORNG-CG', 'CO', NULL, 'สีสเปรย์สีส้ม เบอร์ 226', '-', 0.00, '1252', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (220, 'EL-1-GRABF0027-CG', 'EL', NULL, 'กระบอกฟิวส์ไฟฟ้าแรงสูง 27KV', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (221, 'MA-1-SKKILOOK0-CG', 'MA', NULL, 'เสื้อขาไก่ลูกบด', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (222, 'MI-1-KONOL1212-CG', 'MI', NULL, 'ข้องอลม งอ90องศา ขนาด12mm x 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (223, 'MI-1-KOTOL1238-CG', 'MI', NULL, 'ข้อต่อลมต่อตรง ขนาด12mm x 3/8 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (224, 'MI-1-KRAJOK001-CG', 'MI', NULL, 'กระจกหนา 5มิล ขนาด 60.5x120 ซม.', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (225, 'MI-1-UBOLNC150-CG', 'MI', NULL, 'ยูโบลท์ฉาก ขนาด 1 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (226, 'MU-1-B08020200-CG', 'MU', NULL, 'พลูเล่ย์ ขนาด 8 นิ้ว 2 ร่องB รูเพลา 2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (227, 'MU-1-C0862500N-CG', 'MU', NULL, 'พลูเล่ย์ ขนาด 8 นิ้ว 6 ร่องC 2 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (228, 'MU-1-REAR4JA1T-CG', 'MU', NULL, 'พลูเล่ย์ REAR 4JA1 (ISUZU)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (229, 'PL-1-MAI011130-CG', 'PL', NULL, 'พาเลทไม้  ขนาด 1.10 x 1.30 x 13.5 ซม.  มือสอง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (230, 'PV-1-YSNP16020-CG', 'PV', NULL, 'วายสเตรนเนอร์หน้าแปลน 4 นิ้ว PN16 ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (231, 'SE-1-PAGNA1605-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 16 นิ้ว 150API', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (232, 'SE-1-TC163020C-CG', 'SE', NULL, 'ซีลยาง TC 16-30-8', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (233, 'ST-1-HB0200200-CG', 'ST', NULL, 'เหล็กเอชบีม 200 x 200 x 12 มิล x 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (234, 'ST-1-LBL025045-CG', 'ST', NULL, 'เหล็กแบน 2 1/2 นิ้ว หนา 4.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (235, 'ST-1-LBL080090-CG', 'ST', NULL, 'เหล็กแบน 8 นิ้ว หนา 9 มิล ยาว 6 เมตร ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (236, 'ST-1-LBL080400-CG', 'ST', NULL, 'เหล็กแบน 8 นิ้ว หนา 40 มิล ยาว 6 เมตร ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (237, 'ST-1-SSBL01515-CG', 'ST', NULL, 'สแตนเลสแบน 1 1/2 นิ้ว หนา 1.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (238, 'TO-1-SAYPLAS00-CG', 'TO', NULL, 'สาย-07 สายตัดพลาสม่า 7 เมตร เครื่องตัด CVT-72', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (239, 'ST-1-LST012300-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมตัน 1 1/4 นิ้ว ยาว 3 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:14', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (240, 'EL-1-MARKER015-CG', 'EL', NULL, 'วายมาร์คเกอร์ มาร์คสายไฟ(A-Z) เข้าสาย 1.5-2.5 mm. 500ชิ้น', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (241, 'LA-1-RX2903300-CG', 'LA', NULL, '33(R-40011)Pedestal Cover/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (242, 'CA-1-DOMLOLTTA-CG', 'CA', NULL, 'ดุมล้อหลังรถโฟล์คลิฟ โตโยต้า', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (243, 'CA-1-KAKAITTAL-CG', 'CA', NULL, 'ขาไก่บังคับเลี้ยวซ้าย โตโยต้า', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (244, 'CA-1-P556005NM-CG', 'CA', NULL, 'กรองน้ำมันไฮดรอลิกส์ P556005', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (245, 'CA-1-SLUKLJH30-CG', 'CA', NULL, 'สลักลูกสูบ JH30', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (246, 'CA-1-TANUMJAN0-CG', 'CA', NULL, 'ตาน้ำจาน JH60', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (247, 'CO-1-SENWH0110-CG', 'CO', NULL, 'สีน้ำอะคริลิกสีขาว ', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (248, 'LA-1-RX2906700-CG', 'LA', NULL, '67(R-10076)Wire Clip/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (249, 'LA-1-SODIUM007-CG', 'LA', NULL, 'Sodium Silicate Solution Type CD50', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (250, 'MA-1-BOOTSK009-CG', 'MA', NULL, 'บูช SK-09', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (251, 'MA-1-STAWB30XT-CG', 'MA', NULL, 'ชุดดึงสตาร์ท เครื่องสูบน้ำ HONDA WB30XT', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (252, 'MA-1-STL300033-CG', 'MA', NULL, 'ตะแกรง STL 30Mesh#33 รู 0.594 มิล ลวดโต0.254มิลกว้าง1.50เมตร', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (253, 'MI-1-TARGON000-CG', 'MI', NULL, 'ถังก๊าซอาร์กอน 2คิว', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (254, 'MU-1-PULAE62B2-CG', 'MU', NULL, 'พลูเล่ย์ ขนาด 6 นิ้ว 2 ร่องB รูเพลา 24 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (255, 'NU-1-STUD20545-CG', 'NU', NULL, 'สตัดเกลียว 2 ข้าง 1/2 นิ้ว ยาว 4 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (256, 'PV-1-HEADABS10-CG', 'PV', NULL, 'หัวกรองน้ำพีวีซี 1 นิ้ว ABS ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (257, 'SE-1-NAPN80314-CG', 'SE', NULL, 'ซีลกระบอกลม 80-68-8.4', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (258, 'SE-1-OR0504203-CG', 'SE', NULL, 'โอริง 50 x 42 x 3 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (259, 'SE-1-OR0508800-CG', 'SE', NULL, 'โอริง 5 x 88 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (260, 'SE-1-OR1714SE2-CG', 'SE', NULL, 'โอริงขนาด 1.7*14มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (261, 'SE-1-PAGNA1516-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน DIN15  PN16', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:15', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (262, 'ST-1-LBL010050-CG', 'ST', NULL, 'เหล็กแบน 1 นิ้ว หนา 5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (263, 'ST-1-LBL012090-CG', 'ST', NULL, 'เหล็กแบน 1 1/4 นิ้ว หนา 9 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (264, 'ST-1-LBL015020-CG', 'ST', NULL, 'เหล็กแบน 1 1/2 นิ้ว หนา 2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (265, 'ST-1-LP5020008-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 20 ฟุต หนา 8 มิล ', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (266, 'ST-1-POSW11150-CG', 'ST', NULL, 'เหล็กเพลาขาว 1 1/8 นิ้ว x 1.5 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (267, 'TO-1-SALAMIX14-CG', 'TO', NULL, 'ชุดปืนพ่นทรายหัวเซรามิค ขนาด 1/4 นิ้ว', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (268, 'CA-1-TCM100011-CG', 'CA', NULL, 'ลูกปืนครัช TCM', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (269, 'ST-1-LST025600-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมตัน 2 1/2 นิ้ว ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (270, 'EL-1-MASK04444-CG', 'EL', NULL, 'หน้ากากบล๊อคลอย 4ช่อง 4นิ้วx4นิ้ว (WNG)', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (271, 'CA-1-COVPFCAT9-CG', 'CA', NULL, 'เสื้อลูกปืนเพลาขับหน้า CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (272, 'CA-1-JH30PS000-CG', 'CA', NULL, 'ปลอกสูบ JH30 DA220', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (273, 'CA-1-PUMPBRMTX-CG', 'CA', NULL, 'แม่ปั๊มเบรคล่าง โตโยต้าไมตี้เอ็กซ์ (1นิ้ว)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (274, 'CA-1-WANLOS100-CG', 'CA', NULL, 'แหวนลูกสูบ รถสิบล้อ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (275, 'EL-1-TERM30A4P-CG', 'EL', NULL, 'เทอร์มินอลต่อสาย 30A 4P', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (276, 'MI-1-NOM200000-CG', 'MI', NULL, 'หัวตัดแก๊ส เบอร์ 2NX', '-', 0.00, '1292', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (277, 'MI-1-REGUSMC40-CG', 'MI', NULL, 'เร็กกูเรเตอร์REGULATOR SMC AF40 04D A1/2นิ้ว', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (278, 'MI-1-SILUR8121-CG', 'MI', NULL, 'สายลมโพลียูรีเทน 8 x12 mm 100m', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (279, 'NU-1-LNC050325-CG', 'NU', NULL, 'น็อตดำ NC 5/8 นิ้ว ยาว 3 1/2 นิ้ว พร้อมหัว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (280, 'PV-1-FA0300000-CG', 'PV', NULL, 'ฝาครอบพีวีซี 3 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (281, 'SE-1-OR0404800-CG', 'SE', NULL, 'โอริง 4 x 48 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (282, 'SE-1-OR0406900-CG', 'SE', NULL, 'โอริง 4 x 69 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (283, 'SE-1-OR0632150-CG', 'SE', NULL, 'โอริง 6.3 x 215', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (284, 'SE-1-PAGNA041K-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 4 นิ้ว 10K', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (285, 'SE-1-PAGNA0505-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 5 นิ้ว 150API', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (286, 'SE-1-PAGNA0606-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 6 นิ้ว 600API', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (287, 'SE-1-PAGNA0805-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 8 นิ้ว 150API', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:16', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (288, 'SE-1-PAGNA085B-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 8 นิ้ว 150NON B16', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (289, 'SE-1-T04207212-CG', 'SE', NULL, 'ซีล TC 42-72-12', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (290, 'ST-1-KOL60408N-CG', 'ST', NULL, 'ข้อลดสตรีมเชื่อมไม่มีตะเข็บ 6 นิ้ว ลด 4 นิ้ว SCH80', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (291, 'ST-1-LP4080500-CG', 'ST', NULL, 'เหล็กแผ่นดำ 4 x 8 ฟุต หนา 50 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (292, 'ST-1-LP5010190-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 19 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (293, 'ST-1-LP5010200-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 20 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (294, 'ST-1-LP5010400-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 40 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (295, 'ST-1-LP5020100-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 20 ฟุต หนา 10 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (296, 'ST-1-PAPD05040-CG', 'ST', NULL, 'แป๊บกลมดำ 5 นิ้ว หนา 4 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (297, 'ST-1-PAPD06047-CG', 'ST', NULL, 'แป๊บกลมดำ 6 นิ้ว  หนา 4.7 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (298, 'ST-1-PAPS10063-CG', 'ST', NULL, 'แป๊บสเตย์วงนอก 100 มิล รูใน 63 มิล ยาว 50 ซม.', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (299, 'BA-1-006328000-CG', 'BA', NULL, 'ลูกปืน 6328-ZZ', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (300, 'EL-1-MAT020380-CG', 'EL', NULL, 'แมกเนติกคอนแทค PAK-20J 380V TOGAMI', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (301, 'MI-1-SAKLAD041-CG', 'MI', NULL, 'สักหลาดสีขาวหนา 3/8*1ม.*1.80ม.', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (302, 'ST-1-LST030500-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมตัน 3 นิ้ว ยาว 5 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (303, 'EL-1-MAT093380-CG', 'EL', NULL, 'แมกเนติกคอนแทค ABB A9-30-1 380V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (304, 'PL-1-TKSF20342-CG', 'PL', NULL, 'กระสอบพลาสติกสวมถุงใน 20 นิ้ว x 34 นิ้ว ตราดอกไม้(25กก.)', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (305, 'BE-1-183200LUM-CG', 'BE', NULL, 'สายพานลำเลียงหน้าเรียบ ยาว 18 เมตร กว้าง 32 นิ้ว ต่อกลม', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (306, 'CA-1-BVALCA910-CG', 'CA', NULL, 'บ่าวาล์ว CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (307, 'CA-1-S02535006-CG', 'CA', NULL, 'ซีลพวงมาลัย 25-35-6 (CAT 910)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (308, 'CA-1-SAYDUM000-CG', 'CA', NULL, 'สายดั้มรถสิบล้อ ขนาด 12 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (309, 'CA-1-SLUG30HPP-CG', 'CA', NULL, 'สลักลูกสูบ 30 HP PP-630', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (310, 'EL-1-EH3ELECTR-CG', 'EL', NULL, 'EH-3 ELECTRODE HOLDER', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (311, 'EL-1-TOAL00040-CG', 'EL', NULL, 'ท่ออ่อนเหล็กร้อยสาย 1/2 นิ้ว', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (312, 'EL-1-YGME00250-CG', 'EL', NULL, 'โคมเมทัลฮาไลน์ 250 วัตต์      ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (313, 'LA-1-RX2928A00-CG', 'LA', NULL, '28A(Std.No.7)Drive Screws/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (314, 'MA-1-GEAR12015-CG', 'MA', NULL, 'เกียร์ทดรอบ เบอร์ 120 1:50', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:17', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (315, 'MA-1-KAVIDP310-CG', 'MA', NULL, 'ขาวิดน้ำมัน ปั๊มลมพูม่า PP310 10HP', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (316, 'MU-1-B16300200-CG', 'MU', NULL, 'พลูเล่ย์ ขนาด 16 นิ้ว 3 ร่องB รูเพลา 2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (317, 'PV-1-PLDIAF002-CG', 'PV', NULL, 'พลาสติกไดอะแฟรมวาล์ว รุ่น K524 ขนาด 2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (318, 'SE-1-OR0243000-CG', 'SE', NULL, 'โอริง EPDM  ID 24 x 3 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (319, 'SE-1-OR0352870-CG', 'SE', NULL, 'โอริง 3.5 x 28.7 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (320, 'SE-1-PAGNA0306-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 3 นิ้ว 600API', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (321, 'SE-1-PAGNA0406-CG', 'SE', NULL, 'ปะเก็นเหล็กนอกสแตนเลสใน 4 นิ้ว 600P', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (322, 'SE-1-T04006207-CG', 'SE', NULL, 'ซีล TC 40-62-7 ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (323, 'ST-1-LP5010150-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 15 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (324, 'ST-1-POK2500T0-CG', 'ST', NULL, 'เหล็กเพลาขาว 2 1/2 นิ้ว x ตัด', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (325, 'ST-1-STEL51016-CG', 'ST', NULL, 'เหล็กแผ่นหัวแดง SS55C ขนาด 5 ฟุต x10 ฟุต หนา16 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (326, 'SU-1-PILLOW000-CG', 'SU', NULL, 'หมอน', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (327, 'TO-1-LFUTVDSB8-CG', 'TO', NULL, 'ไขควงตอกแบน ขนาด 8 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (328, 'EL-1-METTLER01-CG', 'EL', NULL, 'STIRRING ROD / DLT SERIES ยี่ห้อ METTLER TOLE DO', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (329, 'EL-1-SWITG2TANG-CG', 'EL', NULL, 'สวิตซ์ 2 ทาง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (330, 'CA-1-FTSJH3000-CG', 'CA', NULL, 'กรองน้ำมันโซล่า JH30 DA220', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (331, 'CA-1-GASJH30KR-CG', 'CA', NULL, 'ปะเก็นชุดใหญ่ JH30 DA220', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (332, 'CA-1-GRAT70016-CG', 'CA', NULL, 'กะทะล้อ 7.00-12 6 รู', '-', 0.00, '1285', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (333, 'CA-1-PSKLEC240-CG', 'CA', NULL, 'ชุดปีกผีเสื้อคันเร่ง อีซูซุ C240', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (334, 'CA-1-VALID30HP-CG', 'CA', NULL, 'วาล์วไอดี 30 HP PP-630', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (335, 'CH-1-SOHYCA254-CG', 'CH', NULL, 'SODIUM HYDROGEN CABONATE ยี่ห้อUNILAB บรรจุ1กิโลกรัม', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:18', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (336, 'EL-1-LRBOX0004-CG', 'EL', NULL, 'LR บล็อก ขนาด 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (337, 'LA-1-AMMONIUMC-CG', 'LA', NULL, 'Ammonium chloride (NH4cl) ยี่ห้อ Carlo ERBA ขนาด 1 kg.', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (338, 'LA-1-DEWARFL03-CG', 'LA', NULL, 'Dewar Flask 3 Lt. for Reservoir (Glass part only)', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (339, 'LA-1-FLASK0200-CG', 'LA', NULL, 'Erlenmeyer Flask ขนาด 200ml.', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (340, 'LA-1-RX2933A00-CG', 'LA', NULL, '33A(8/18x.75)Self Tapping Screw/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (341, 'MA-1-AIRFIL063-CG', 'MA', NULL, 'กรองอากาศ SA-063 (ขนาด 63x52x230 ) เครื่องเทอร์บาย', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (342, 'MA-1-CABLE0100-CG', 'MA', NULL, 'เชือกสตาร์ทฮอนด้า 5.5 มิล', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (343, 'PV-1-GATEL4150-CG', 'PV', NULL, 'เกทวาล์วเหล็กหล่อหน้าแปลน 4 นิ้ว 150P', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (344, 'SE-1-OR0190530-CG', 'SE', NULL, 'โอริง EPDM  ID 190 x 5.3 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (345, 'SE-1-OR0350108-CG', 'SE', NULL, 'โอริง 3.5 x 108  มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (346, 'SE-1-OR0582650-CG', 'SE', NULL, 'โอริง EPDM  ID 58 x 26.5 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (347, 'ST-1-LCHAG1045-CG', 'ST', NULL, 'เหล็กฉาก 1 นิ้ว x 1 นิ้ว หนา 4.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (348, 'ST-1-LCHAG1550-CG', 'ST', NULL, 'เหล็กฉาก 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (349, 'ST-1-LCHAG4060-CG', 'ST', NULL, 'เหล็กฉาก 4 นิ้ว x 4 นิ้ว หนา 6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (350, 'ST-1-LP4080008-CG', 'ST', NULL, 'เหล็กแผ่นดำ 4 x 8 ฟุต หนา 0.8 มิล ', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (351, 'ST-1-LPL400804-CG', 'ST', NULL, 'เหล็กแผ่นลาย 4 x 8 ฟุต หนา 4 มิล รวมลาย', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (352, 'ST-1-LWF201071-CG', 'ST', NULL, 'เหล็กไวแฟรงค์ 200 x 100 x 7 x 10 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (353, 'ST-1-SSCL01515-CG', 'ST', NULL, 'สแตนเลสฉาก 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 1.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (354, 'ST-1-SSCL02560-CG', 'ST', NULL, 'สแตนเลสฉาก 2 1/2 นิ้ว x 2 1/2 นิ้ว หนา 6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (355, 'TO-1-HAMMERGIB-CG', 'TO', NULL, 'ค้อนตีกิ๊บ', '-', 0.00, '1265', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (356, 'TO-1-KENKEMSAY-CG', 'TO', NULL, 'คีมตัดสายไฟ KENNEDY 255mm KEN-558-8220K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (357, 'LA-1-SASNUN000-CG', 'LA', NULL, 'ชุดตรวจ COVID-19', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (358, 'EL-1-MO0005220-CG', 'EL', NULL, 'มอเตอร์ 1/2 HP 2P 220V. 3000 RPM', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (359, 'ST-1-LWF272012-CG', 'ST', NULL, 'เหล็กไวแฟรงค์ 275 x 200 x 10 x 12 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (360, 'ST-1-LWF401512-CG', 'ST', NULL, 'เหล็กไวแฟรงค์ 400 x 150 x 10 x 12 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (361, 'EL-1-MO0300000-CG', 'EL', NULL, 'มอเตอร์ 30HP 2P 2940RPM  22KW IP55 CLASS F  ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (362, 'BA-1-SLIPH2310-CG', 'BA', NULL, 'สลิปH 2310(ใช้กับUK210)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (363, 'BA-1-UCF213M00-CG', 'BA', NULL, 'ลูกปืน UCF 213-DI  ', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (364, 'CA-1-FAITOYO12-CG', 'CA', NULL, 'ไฟท้ายรถยก TOYOTA 12V', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (365, 'CA-1-FATO2J001-CG', 'CA', NULL, 'ฝาสูบ TOYOTA 2J', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (366, 'CA-1-LUGRKS210-CG', 'CA', NULL, 'ลูกปืนรับน้ำหนักคานหน้า หกล้อขนฟืน', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:19', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (367, 'CA-1-NUTLTCM00-CG', 'CA', NULL, 'น็อตยึดล้อหลัง TCM', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (368, 'CA-1-SG0254008-CG', 'CA', NULL, 'ซีลเกียร์ 25 40 8 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (369, 'CA-1-START12HD-CG', 'CA', NULL, 'ไดร์สตาร์ท 12V HONDA CIVIC', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (370, 'CA-1-YANGFK640-CG', 'CA', NULL, 'ยางฝาครอบวาล์ว อีซูซุ DA640', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (371, 'CA-1-YANI16924-CG', 'CA', NULL, 'ยางใน ขนาด 16.9-24', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (372, 'EL-1-HANPLA153-CG', 'EL', NULL, 'หางปลาแฉกหุ้มฉนวน ขนาด 1.5-3', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (373, 'LA-1-GRAN100ML-CG', 'LA', NULL, 'GRANDUATED CYLINDER  ปริมาตร100ml.', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (374, 'LA-1-POTASSIUM-CG', 'LA', NULL, 'Potassium hudroxide (KOH) ยี่ห้อ MERCK ขนาด 1 kg.', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (375, 'LA-1-SORPTOMAT-CG', 'LA', NULL, 'P/N 40311200 PT100 For N2 Temp', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (376, 'MI-1-MASKNP306-CG', 'MI', NULL, 'หน้ากากป้องกันสารพิษกรองคู่รุ่น NP306 +ใส้กรอง RC-203', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (377, 'PV-1-KOC004090-CG', 'PV', NULL, 'ข้อโค้งพีวีซี 90 องศา 4 นิ้ว ES2 ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (378, 'SE-1-AE3932SEL-CG', 'SE', NULL, 'ซีลยาง AE3932 NOK', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (379, 'ST-1-KOAOI1500-CG', 'ST', NULL, 'เหล็กข้ออ้อย 15 มิล ยาว 10 เมตร SD40', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (380, 'ST-1-LBOX02116-CG', 'ST', NULL, 'เหล็กกล่องไม้ขีด 2 นิ้ว x 1 นิ้ว หนา 1.6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (381, 'ST-1-LSP101028-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 นิ้ว x 1 นิ้ว หนา 2.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (382, 'ST-1-POSB07050-CG', 'ST', NULL, 'เหล็กเพลาฟ้า 70 มิล ยาว 50 ซม.', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (383, 'ST-1-SSPL51030-CG', 'ST', NULL, 'สแตนเลสแผ่น 5 x 10 ฟุต หนา 3 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (384, 'ST-1-ZIPL48010-CG', 'ST', NULL, 'สังกะสีแผ่นเรียบ 4 x 8 ฟุต หนา 1 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (385, 'TO-1-DST03UNF0-CG', 'TO', NULL, 'ดอกต๊าปเกลียวขนาด 3/8นิ้ว UNF เกลียวละเอียด', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:20', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (386, 'TO-1-DSTM80125-CG', 'TO', NULL, 'ดอกต๊าปเกลียวขนาด M8 เกลียว 1.25', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (387, 'TO-1-KEN572532-CG', 'TO', NULL, 'ไขควงแฉกหัวทู่ KEN-572-5320K 6 NO.3 PT', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (388, 'EL-1-MON005000-CG', 'EL', NULL, 'มอเตอร์หน้าแปลน 5HP 4P 3สาย380V SF-JRV', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (389, 'EL-1-MOTERMVSI-CG', 'EL', NULL, 'มอเตอร์เขย่า MVSI 3/100-S90', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (390, 'ST-1-LSP202032-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 2 นิ้ว x 2 นิ้ว หนา 3.2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (391, 'CA-1-BKCAT9100-CG', 'CA', NULL, 'ผ้าเบรคหน้า CAT910', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (392, 'CA-1-GRONGS240-CG', 'CA', NULL, 'กรองน้ำมันโซล่า ISUZU C-240', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (393, 'CA-1-TAKAI0400-CG', 'CA', NULL, 'ตาไก่แป็ปน้ำมัน 4 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (394, 'CA-1-VALIS30HP-CG', 'CA', NULL, 'วาล์วไอเสีย 30 HP PP-630TPP-360', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (395, 'CA-1-YANGTOYOT-CG', 'CA', NULL, 'ยางฝาครอบวาล์ว TOYOTA 3L', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (396, 'CO-1-SESPAYGR1-CG', 'CO', NULL, 'สีสเปรย์สีเขียวสะท้อนแสง', '-', 0.00, '1252', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (397, 'CO-1-SPAYBLJO1-CG', 'CO', NULL, 'สเปรย์ตรวจสอบรอยร้าวที่ผิวแนวเชื่อม Penetration (กระป๋องสีแดง)', '-', 0.00, '1252', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (398, 'EL-1-FSBOXD600-CG', 'EL', NULL, 'F.S. BOX 4 x 4 x 3/4 นิ้ว รุ่น 2 ทางตรงข้ามFSC', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (399, 'EL-1-HANPLA380-CG', 'EL', NULL, 'หางปลา 3/8 นิ้ว', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (400, 'LA-1-IODICARLO-CG', 'LA', NULL, 'Iodine resubirned carlo 100g', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (401, 'LA-1-PRICE1010-CG', 'LA', NULL, 'Price for filter use with fluegas probe Spare particle 10 off 10.0% Discount', '-', 0.00, '1253', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (402, 'MA-1-EBCMA100T-CG', 'MA', NULL, 'ปั้มน้ำ EBARA 1HP CMA 1.00T', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (403, 'MA-1-MAGNET17I-CG', 'MA', NULL, 'แม่เหล็กใช้กับสายพานกว้าง17นิ้ว W450mm x L450mm x 200mm', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (404, 'MA-1-MAGNET30I-CG', 'MA', NULL, 'แม่เหล็กใช้กับสายพานกว้าง30นิ้ว W750mm x L550mm x 215mm', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:21', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (405, 'MA-1-PANCB25B2-CG', 'MA', NULL, 'แปรงถ่าน CB85', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (406, 'MI-1-SAYYIL010-CG', 'MI', NULL, 'ท่อใยลวด ขนาด 1 นิ้ว', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (407, 'MI-1-TUNGR0406-CG', 'MI', NULL, 'ถุง ขนาด 4 x 6 นิ้ว', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (408, 'SE-1-OR0090106-CG', 'SE', NULL, 'โอริง 9 x 106 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (409, 'ST-1-LBL010040-CG', 'ST', NULL, 'เหล็กแบน 1 นิ้ว หนา 4 มิล ยาว 6 เมตร ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (410, 'ST-1-LSP121230-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/4 นิ้ว x 1 1/4 นิ้ว หนา 3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (411, 'ST-1-SSBL01040-CG', 'ST', NULL, 'สแตนเลสแบน 1 นิ้ว หนา 4 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (412, 'ST-1-SSPL48007-CG', 'ST', NULL, 'สแตนเลสแผ่น 4 x 8 ฟุต หนา 0.7 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (413, 'ST-1-SSPL51090-CG', 'ST', NULL, 'สแตนเลสแผ่น 5 x 10 ฟุต หนา 9 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (414, 'MI-1-OUPARKI01-CG', 'MI', NULL, 'อุปกรณ์ชักโครก วี้ก้า AP01', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (415, 'SU-1-GRADTM001-CG', 'SU', NULL, 'เซลล็อก เช็ดหน้า', '-', 0.00, '1253', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (416, 'BA-1-UC213N000-CG', 'BA', NULL, 'ลูกปืน UC 213-208 D1', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (417, 'EL-1-NEGG00601-CG', 'EL', NULL, 'ข้องอเกลียว MPL06-01 ขนาด 6 มิล เกลียว 1/8 นิ้ว   ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (418, 'BE-1-B00700001-CG', 'BE', NULL, 'สายพานร่อง B ขนาด 70 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (419, 'ST-1-NIPPSS060-CG', 'ST', NULL, 'นิเปิ้ลสแตนเลส 3/4 นิ้ว 150P', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (420, 'BE-1-B06400000-CG', 'BE', NULL, 'สายพานร่อง B ขนาด 64 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (421, 'ST-1-NIPST0004-CG', 'ST', NULL, 'นิเปิ้ลสตรีม 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (422, 'BE-1-B09300000-CG', 'BE', NULL, 'สายพานร่อง B ขนาด 93 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (423, 'CA-1-FUNGC4201-CG', 'CA', NULL, 'เฟืองขับลูกเบี้ยว C 240', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (424, 'CA-1-GEARDUMP0-CG', 'CA', NULL, 'ชุดเกียร์รถดัมเปอร์ (ตามตัวอย่าง)', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:22', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (425, 'CA-1-GRONGA000-CG', 'CA', NULL, 'กรองอากาศ CAT สิบล้อ', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (426, 'CA-1-JH30WLS00-CG', 'CA', NULL, 'แหวนลูกสูบ JH30 DA220', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (427, 'CA-1-KAMPU1201-CG', 'CA', NULL, 'น็อตตั้งก้ามปูคลัช โคมัทสุ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (428, 'CA-1-LUGYB0546-CG', 'CA', NULL, 'ลูกยางเบรค SEIKEN B 546  1 5/8', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (429, 'CA-1-NOTTEEN20-CG', 'CA', NULL, 'น็อตตีนผี S45', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (430, 'CA-1-RUBLNS000-CG', 'CA', NULL, 'ท่อยางหม้อน้ำล่างนิสสัน', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (431, 'CA-1-SEALGJHB0-CG', 'CA', NULL, 'ซีลเกียร์ JH30 ตัวบน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (432, 'CA-1-VALIDCA91-CG', 'CA', NULL, 'วาล์วไอดี CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (433, 'CA-1-YANGF7012-CG', 'CA', NULL, 'ยางตันหล่อดอก 7.00-12', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (434, 'CA-1-YANGFVTX0-CG', 'CA', NULL, 'ยางฝาวาล์ว TX', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (435, 'EL-1-BGBM3SB02-CG', 'EL', NULL, 'เบรกเกอร์มอเตอร์สตาร์ท FUJI BM3RSB -020', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (436, 'EL-1-GIBSAY100-CG', 'EL', NULL, 'กิ๊บรัดสายไฟ เบอร์ 1', '-', 0.00, '1291', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (437, 'EL-1-SNYY04250-CG', 'EL', NULL, 'สายไฟ NYY 4 x 25 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (438, 'EL-1-TUATAN100-CG', 'EL', NULL, 'ตัวต้านทานแบบปรับค่าได้ รุ่น B 10 K ชนิด 3 ขา', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (439, 'MI-1-COP12PH00-CG', 'MI', NULL, 'ข้อต่อคอปเปอร์เกลียวนอก ขนาด 1/2 นิ้ว 40-PH', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (440, 'PL-1-C035IFAB0-CG', 'PL', NULL, 'ถุงกระดาษพิมพ์ IFAB 35-25x23.5x3xPE ขาว+2K+PE', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (441, 'PL-1-P11011013-CG', 'PL', NULL, 'พาเลทไม้ 110 x110*13.5 อบน้ำยา IPPC', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (442, 'SE-1-ORM020320-CG', 'SE', NULL, 'โอริง M2-032', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (443, 'SE-1-T035608NO-CG', 'SE', NULL, 'ซีล TC 35-60-8', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (444, 'ST-1-PA4080040-CG', 'ST', NULL, 'อลูมิเนียมแผ่น 4 x 8 ฟุต หนา 4 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (445, 'TO-1-KEMKOMA12-CG', 'TO', NULL, 'คีมคอม้า 12นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (446, 'TO-1-KRAINGSE2-CG', 'TO', NULL, 'มีดโป้วสี 2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (447, 'ST-1-PAOD10060-CG', 'ST', NULL, 'เหล็กเพลาขาวหัวแดง 1 นิ้ว ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (448, 'EL-1-NOTEBOOK6-CG', 'EL', NULL, 'จอโน๊ตบุ๊ค 15.6 นิ้ว SLIM', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (449, 'CO-1-PUNNOK012-CG', 'CO', NULL, 'ปูนฉาบนกเขียว', '-', 0.00, '1272', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (450, 'EL-1-SELECSWA1-CG', 'EL', NULL, 'ซีเลคเตอร์ A', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (451, 'BE-1-B02700000-CG', 'BE', NULL, 'สายพานร่อง B ขนาด 27 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (452, 'CA-1-JAN10LOR1-CG', 'CA', NULL, 'จานคลัชรถสิบล้อ 531250 1262', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (453, 'CA-1-JH30FA000-CG', 'CA', NULL, 'ฝากรองโซล่า JH30 DA220', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:23', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (454, 'CA-1-KOWIAG100-CG', 'CA', NULL, 'ข้อเหวี่ยง รถสิบล้อ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (455, 'CA-1-LEAWKOMAT-CG', 'CA', NULL, 'ซีลกระบอกเลี้ยว โคมัทสุ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (456, 'CA-1-LODVA4J2G-CG', 'CA', NULL, 'หลอดวาล์ว ISUZU 4JG2', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (457, 'CA-1-MKRBCBAPK-CG', 'CA', NULL, 'ลูกปืนปลายก้านสูบเครื่องตัดหญ้า MAKITA RBC411', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (458, 'CA-1-SPINGK000-CG', 'CA', NULL, 'สปริงขาเบรค-ครัช', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (459, 'CA-1-TAAG2775H-CG', 'CA', NULL, 'ซีลกันน้ำมันเพลาข้าง TOYOTA AG2775H', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (460, 'CA-1-TORYMTX12-CG', 'CA', NULL, 'ท่อยางหม้อน้ำบน โตโยต้า MTX', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (461, 'CA-1-VATCAT910-CG', 'CA', NULL, 'ตะเกียบวาล์ว  CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (462, 'CA-1-YA1GTAN24-CG', 'CA', NULL, 'ยางแท่นตัวถัง โคมัทสุ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (463, 'CH-1-NITROGPUR-CG', 'CH', NULL, 'ULTRA HIGHT PURITY NITROGEN', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (464, 'EL-1-BATGP1272-CG', 'EL', NULL, 'แบตเตอรี่ GP1272 F2 12V', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (465, 'EL-1-FUSE02A50-CG', 'EL', NULL, 'ลูกฟิวส์ E16 2A 500V (ฟิวส์ขวด)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (466, 'EL-1-HANG02505-CG', 'EL', NULL, 'หางปลากลมหุ้มฉนวน ขนาด 2.5-5 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (467, 'LA-1-HOTPLATE1-CG', 'LA', NULL, 'HOT PLATE', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (468, 'LA-1-RX2901700-CG', 'LA', NULL, '17(R-30006)Sieve Support Plate/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (469, 'MA-1-LUYYON120-CG', 'MA', NULL, 'เลื่อยยนต์ 12นิ้ว', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (470, 'MA-1-PUMPJ50B2-CG', 'MA', NULL, 'ปั๊มจุ่มสูบน้ำเสีย รุ่น 50B 2.75 ท่อเข้า 2 นิ้ว 380 Vol ใบพัดเปิด แบบตะกอนผ่าน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (471, 'MA-1-WAYNGSK09-CG', 'MA', NULL, 'แหวนยาง SK-09', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (472, 'MI-1-RONY4T001-CG', 'MI', NULL, 'ล้อไนล่อน 4นิ้ว ล้อตาย NY4T', '-', 0.00, '1285', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (473, 'MI-1-WHEEL0017-CG', 'MI', NULL, 'ล้อรถเข็นน้ำ ขอบ 17', '-', 0.00, '1285', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (474, 'OF-1-SHIRT0300-CG', 'OF', NULL, 'เสื้อฟอร์มโปโลสีเทา', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (475, 'PV-1-FAGN00500-CG', 'PV', NULL, 'ฝาครอบเกลียวใน PVC 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (476, 'PV-1-GLOBL0048-CG', 'PV', NULL, 'โกลบวาล์วเหล็กหล่อ 1/2 นิ้ว CLASS 800P A105N PROTECK', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (477, 'SE-1-OR0140790-CG', 'SE', NULL, 'โอริง 014 x 79 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (478, 'EL-1-PLUGP3TO2-CG', 'EL', NULL, 'ตัวแปลงปลั๊ก3ขาเป็น2ขาแบน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (479, 'EL-1-SLIYCY407-CG', 'EL', NULL, 'สายคอนโทรล LIYCY 4 x 0.75 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (480, 'TO-1-BLOCK0027-CG', 'TO', NULL, 'ลูกบล็อก KOKEN ขนาด 1/2 นิ้ว เบอร์ 27', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:24', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (481, 'NU-1-NUTCHUP38-CG', 'NU', NULL, 'น็อตชุบซิงค์ 3/8 นิ้ว ยาว 4 นิ้ว พร้อมน็อตตัวเมีย+แหวนอีแปะ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (482, 'CA-1-BAVLKT925-CG', 'CA', NULL, 'บ่าวาล์วรถสิบล้อฮีโน่ รุ่น KT925 EH700', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (483, 'CA-1-HAN100LOR-CG', 'CA', NULL, 'คอห่านน้ำติดฝาสูบ รถสิบล้อ', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (484, 'CA-1-LPHJH6000-CG', 'CA', NULL, 'ลูกปืนหัวกระโหลกล้อ (อัดจี๋) JH60', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (485, 'CA-1-PUMCUSNPR-CG', 'CA', NULL, 'แม่ปั๊มครัชล่าง NPR', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (486, 'CA-1-PUMPBLIS0-CG', 'CA', NULL, 'แม่ปั๊มเบรคล่าง รถหกล้อ 1 1/16นิ้ว 51336L', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (487, 'CA-1-SBCAT9100-CG', 'CA', NULL, 'เสื้อสูบ CAT910', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (488, 'CA-1-SHARP30HP-CG', 'CA', NULL, 'ชาร์ป 30 HP PP-630', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (489, 'CA-1-SWITCHNUM-CG', 'CA', NULL, 'สวิตช์วัดน้ำมันเครื่อง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (490, 'CA-1-TORA10100-CG', 'CA', NULL, 'ท่อระบาย 1 นิ้ว ยาว 1 เมตร', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (491, 'CA-1-VAISEA10L-CG', 'CA', NULL, 'วาล์วไอเสีย รถสิบล้อ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (492, 'CA-1-YANGSR911-CG', 'CA', NULL, 'ยางนอก ขนาด 6.00-14 SR 911', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (493, 'CO-1-SAKEN58NE-CG', 'CO', NULL, 'สเก็น ขนาด 5/8 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (494, 'EL-1-LENOVO330-CG', 'EL', NULL, 'โน๊ตบุ๊ค lenovo ideapad 330 14ast', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (495, 'EL-1-LOKTAN150-CG', 'EL', NULL, 'แคล้มลูกตาลต่อสายเบอร์ 150SQ.MM', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (496, 'EL-1-SOLIMAC45-CG', 'EL', NULL, 'โซลินอยด์วาวล์ MAC 45-A-AC1-DABJ-1KA', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (497, 'EL-1-THW1120SQ-CG', 'EL', NULL, 'สายไฟ THW 1 x 120 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (498, 'EL-1-TUNMA9607-CG', 'EL', NULL, 'ทุ่นเครื่องเจียร์ Makita รุ่น 9607 NB', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (499, 'FL-1-SSB40010K-CG', 'FL', NULL, 'หน้าแปลนสแตนเลสเชื่อมแบบบอด ขนาด 4 นิ้ว 10K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (500, 'MA-1-TUBE50845-CG', 'MA', NULL, 'TUBE BOILER STB O.D.50.8มิล หนา4.5มิล ยาว6เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (501, 'MA-1-YOYR03800-CG', 'MA', NULL, 'ยางยอย โรเท็กซ์ เบอร์ 38  ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (502, 'MI-1-NOM030000-CG', 'MI', NULL, 'หัวตัดแก๊ส  3/0NX', '-', 0.00, '1292', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (503, 'SE-1-140904SEL-CG', 'SE', NULL, 'ซีล 140904*2604', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (504, 'SE-1-DUMB402VD-CG', 'SE', NULL, 'ซีล DUMD BTR3EF8-ED', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (505, 'SE-1-E89951519-CG', 'SE', NULL, 'ซีล 8995*1519', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:25', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (506, 'SE-1-SEALPIS01-CG', 'SE', NULL, 'ซีลลูกสูบไฮโดรลิกซ์', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (507, 'TO-1-FGTUY8000-CG', 'TO', NULL, 'ประแจแหวนข้างปากตาย เบอร์ 8', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (508, 'TO-1-PSGO04040-CG', 'TO', NULL, 'เพรชเชอร์เกจ แบบน้ำมัน 4 นิ้ว เกลียว 1/2 นิ้ว 1000PSI', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (509, 'ST-1-PAOD30050-CG', 'ST', NULL, 'เหล็กเพลาหัวแดง ขนาด 3 นิ้ว x 50 ซม.', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (510, 'ST-2-PJ4820127-CG', 'ST', NULL, 'เหล็กแผ่นเจาะรู 4 x 8 ฟุต หนา 2 มิล รู 12.7 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (511, 'TO-1-CHAAK30NI-CG', 'TO', NULL, 'ฉากเหล็ก ขนาด 30 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (512, 'EL-1-MAOVER22A-CG', 'EL', NULL, 'แมกเนติกพร้อมโอเวอร์โหลดรีเลย์ Mitsubishi MSO-N25 22A (18-26) 220V.50Hz', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (513, 'EL-1-SOLINY030-CG', 'EL', NULL, 'โซลินอยด์วาล์ว YUKEN รุ่น DSG-03-3C2-A-220-50-50HZ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (514, 'TO-1-CREAM1200-CG', 'TO', NULL, 'คีมถ่างแหวนปากงอ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (515, 'BA-1-NU1036O00-CG', 'BA', NULL, 'ลูกปืน NU 1036 MYC3 OUTER RING', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (516, 'BE-1-SAIPANT52-CG', 'BE', NULL, 'สายพานไทม์มิ่งร่อง B ขนาด 52 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (517, 'CH-1-BIO728700-CG', 'CH', NULL, 'BIOCIDE ANTIMICROBIAL 7287', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (518, 'EL-1-BGS3P36KA-CG', 'EL', NULL, 'เซอร์กิตเบรกเกอร์ รุ่น CVS630F 3P 3d 380/415V.36KA', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (519, 'EL-1-SOLIUNI01-CG', 'EL', NULL, 'โซลินอยด์วาล์วน้ำเกลียวใน1นิ้ว UNI-D รุ่น UW-25 220VAC', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (520, 'EL-1-SVCT03100-CG', 'EL', NULL, 'สายไฟ VCT 3 x 1 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (521, 'EL-1-SVSF15000-CG', 'EL', NULL, 'สายไฟ VSF 1.5 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (522, 'EL-1-SWITPOW00-CG', 'EL', NULL, 'สวิทซ์ชิ่งเพาเวอร์ซัพพลาย Model 1-5A2K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (523, 'LA-1-RX2901800-CG', 'LA', NULL, '18(R-30023)Sieve Support Clamp Bar/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (524, 'LA-1-SILVERN01-CG', 'LA', NULL, 'Silver Nitrate 0.1N 500ml. Ajax #640', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (525, 'MA-1-SLEEVTK01-CG', 'MA', NULL, 'เสื้อลูกปืนลูกเบี้ยวตะแกรงร่อน', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (526, 'MI-1-FHW000000-CG', 'MI', NULL, 'สายฉีดน้ำล้างรถ แรงดันสูง Bosch 5 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (527, 'CA-1-KAN100LOR-CG', 'CA', NULL, 'ก้านสูบ รถสิบล้อ', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (528, 'MI-1-PORTABST0-CG', 'MI', NULL, 'เปลสนามชนิดมีล้อ celica', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (529, 'MI-1-SPEEDLM00-CG', 'MI', NULL, 'ป้ายจราจร จำกัดความเร็ว 30 กม./ชม. วัสดุอลูมิเนียม เส้นผ่าศูนย์กลาง 45 ซม.', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (530, 'MI-1-SPUNBWH01-CG', 'MI', NULL, 'ผ้าสปันบอนด์สีขาว ขนาด 1 เมตร x 1 เมตร', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (531, 'MU-1-C06060000-CG', 'MU', NULL, 'พลูเล่ย์ ขนาด 6 นิ้ว 6 ร่องC แบบตัน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (532, 'NU-1-HNN050000-CG', 'NU', NULL, 'หัวน็อต NC 5/8 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (533, 'NU-1-LNC100080-CG', 'NU', NULL, 'น็อตดำ NC 1 นิ้ว ยาว 8 นิ้ว พร้อมหัว พร้อมแหวน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (534, 'PL-1-P10510513-CG', 'PL', NULL, 'พาเลทไม้ ขนาด 105 x 105 x 13.5 ซม. ลูกเต๋า', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (535, 'SE-1-TC2234700-CG', 'SE', NULL, 'ซีล TC 22-34-7 พวงมาลัย', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (536, 'MA-1-PRESSSER0-CG', 'MA', NULL, 'Pressure Switch ปั้มลม 30 แรง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (537, 'ST-1-PAOSTM300-CG', 'ST', NULL, 'เพลาสตัดเกลียวตลอด M 30 x 1 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (538, 'EL-1-MAGSN400C-CG', 'EL', NULL, 'แมกเนติกคอนแทค SN-400 COIL 200-24 VOL', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:26', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (539, 'EL-1-FIT5ING01-CG', 'EL', NULL, 'ฟิตติ้ง1/8 * 6 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (540, 'ST-1-PAOSTUS06-CG', 'ST', NULL, 'เพลาสตัดเกลียวตลอด 3/4 นิ้ว x 1 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (541, 'ST-1-TUBE51326-CG', 'ST', NULL, 'TUBE BOILER STB O.D. 51มิล หนา3.2มิล ยาว6.10เมตร ST35.8', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (542, 'CA-1-MUCHMUNPO-CG', 'CA', NULL, 'มีอจับช่วยหมุนพวงมาลัย', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (543, 'CA-1-SAYPCLTNP-CG', 'CA', NULL, 'สายน้ำมันปั๊มครัชล่างรถหกล้อ NPR', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (544, 'CA-1-SLUBGTH60-CG', 'CA', NULL, 'สลักบุ้งกี๋ JH60', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (545, 'CO-1-SHOWERYAN-CG', 'CO', NULL, 'บัวยางติดผนัง สีดำ ', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (546, 'EL-1-BG10A3P0M-CG', 'EL', NULL, 'เบรกเกอร์ 10A 3P NF30-CS', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (547, 'EL-1-SLICV0120-CG', 'EL', NULL, 'สาย CV 1 x  120 SQmm', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (548, 'EL-1-SOCKET08S-CG', 'EL', NULL, 'ซ็อกเก็ตรีเรย์ PYS 08', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (549, 'EL-1-SOLINYOK0-CG', 'EL', NULL, 'โซลินอยด์วาล์วน้ำมัน YUKEN รุ่น DSG-03-3C2-A-240-50', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (550, 'EL-1-TAMCOCB12-CG', 'EL', NULL, 'ตู้คอนโทรล ขนาด 570 x 690 x 250 มิล (TAMCO CB12)', '-', 0.00, '1270', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (551, 'EL-1-TANLOFAHB-CG', 'EL', NULL, 'ฐานล่อฟ้าแบบเรียบ', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (552, 'MA-1-BIPAD16NE-CG', 'MA', NULL, 'ใบพัดลม ขนาด 16 นิ้ว', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (553, 'MA-1-SEALP30HP-CG', 'MA', NULL, 'ซีลเพลาข้อเหวี่ยงปั๊มลม PUMA 30HP', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (554, 'MI-1-LAMYAB000-CG', 'MI', NULL, 'รำหยาบ', '-', 0.00, '1272', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (555, 'MI-1-SAY20000N-CG', 'MI', NULL, 'สายยางใสขนาด 2 นิ้ว หนา', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (556, 'MI-1-TOONGS58N-CG', 'MI', NULL, 'ถุงซิปขนาด 5 x 8 นิ้ว', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (557, 'OF-1-DUMPFAX00-CG', 'OF', NULL, 'ชุดดั๊มเครื่อง FAX  PANASONIC  KX-FL422', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (558, 'ST-1-KOLOD1210-CG', 'ST', NULL, 'ข้อลดสังกะสี ขนาด 12 นิ้ว ลด 10 นิ้ว หนา 0.55 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (559, 'ST-1-LBL090060-CG', 'ST', NULL, 'เหล็กแบน 9 นิ้ว หนา 6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (560, 'ST-1-LP4080023-CG', 'ST', NULL, 'เหล็กแผ่นดำ 4 x 8 ฟุต หนา 2.3 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (561, 'TO-1-BLOCKT041-CG', 'TO', NULL, 'ลูกบล็อก KINGTONY ขนาด 1 นิ้ว เบอร์ 41', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (562, 'TO-1-DSS000750-CG', 'TO', NULL, 'ดอกสว่านเจาะสแตนเลส 7 มิล', '-', 0.00, '1264', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (563, 'TO-1-JAMPA38GD-CG', 'TO', NULL, 'หัวจับสว่าน ขนาด 3/8 นิ้ว GEF/DF', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:27', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (564, 'TO-1-KENKON205-CG', 'TO', NULL, 'ค้อน KENNEDY 1150g (2 1/2นิ้ว) 415มิล KEN-525-1250K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (565, 'TO-1-PATAT8MM0-CG', 'TO', NULL, 'ประแจบล็อก T เบอร์8', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (566, 'ST-1-PAOSTUS12-CG', 'ST', NULL, 'เพลาสตัดเกลียวตลอด 1/2 นิ้ว x 1 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (567, 'BE-1-C10300000-CG', 'BE', NULL, 'สายพานร่อง C ขนาด 103 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (568, 'ST-1-PAOSTUT11-CG', 'ST', NULL, 'เพลาสตัดเกลียวตลอด 1 นิ้ว x 1 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (569, 'EL-1-TERM01000-CG', 'EL', NULL, 'เทอร์โมคัพเปิล TYPE:K JB 30N เส้นผ่าศูนย์กลาง 9.5 x 100 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (570, 'CA-1-HINMAKITA-CG', 'CA', NULL, 'หินขัดฝาสูบ MAKITA', '-', 0.00, '1254', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (571, 'CA-1-IVALD4J2G-CG', 'CA', NULL, 'วาล์วไอดี ISUZU 4JG2', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (572, 'CA-1-KC272ID00-CG', 'CA', NULL, 'ลูกปืนกากบาทยอยเพลาขับล้อ KC272ID', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (573, 'CA-1-LUGYB3658-CG', 'CA', NULL, 'ลูกยางเบรค SEIKEN 7/8H HC3658R ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (574, 'CA-1-PAKENC240-CG', 'CA', NULL, 'ปะเก็นฝาสูบ C240', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (575, 'CA-1-RUB2INC50-CG', 'CA', NULL, 'ท่อยางหม้อน้ำตรง เส้นผ่าศูนย์กลาง 50 mm 2 นิ้ว x 50 cm', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (576, 'CA-1-RUBTT2J00-CG', 'CA', NULL, 'ยางแท่นเครื่อง TOYOTA 2J', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (577, 'CA-1-VALVNTT3L-CG', 'CA', NULL, 'วาล์วน้ำ 52มิล 88องศา โตโยต้า3L', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (578, 'CA-1-YANG16924-CG', 'CA', NULL, 'ยางนอก ขนาด16.9-24', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (579, 'CH-1-POTAFERRI-CG', 'CH', NULL, 'Potassium Ferricyanide K3Fe(CN)6 500g UNIVAR', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (580, 'CH-1-SODIUMCO3-CG', 'CH', NULL, 'SODIUM CARBONATE ANHYDROUS ยี่ห้อ UNIVAR บรรจุ500กรัม', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (581, 'EL-1-BG100A1P0-CG', 'EL', NULL, 'เบรกเกอร์ 100A 1P NF32-CW', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (582, 'EL-1-HPT501200-CG', 'EL', NULL, 'หางปลาทองแดง 50-12', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (583, 'MA-1-PUMPYA304-CG', 'MA', NULL, 'ปั้มพ่นยา3สูบ ขนาดเข้า3/4นิ้ว', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (584, 'MA-1-RESONATOR-CG', 'MA', NULL, 'Resonator Special Steel for Fixing on Ultrasonic Sieve', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (585, 'MA-1-TAKAIS002-CG', 'MA', NULL, 'ตาไก่ SUS 316 1/4 นิ้ว แบบชุด', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (586, 'MI-1-TOSUBN500-CG', 'MI', NULL, 'ท่อสูบน้ำผ้าใบสีน้ำเงินขนาด 5นิ้ว', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (587, 'NU-1-HG0340006-CG', 'NU', NULL, 'น็อตชุบเกลียวตลอด ขนาด 3/4 ยาว 6 นิ้ว พร้อมหัว พร้อมแหวนสปริง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (588, 'NU-1-LNC060020-CG', 'NU', NULL, 'น็อตดำ NC 3/4 นิ้ว ยาว 2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (589, 'PV-1-FAGN01000-CG', 'PV', NULL, 'ฝาครอบเกลียวใน PVC 1 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (590, 'SE-1-EBARA2200-CG', 'SE', NULL, 'ซีลก้นหอยแบบสปริงปั๊มน้ำ 22 มิล CM-155 CAPIDA', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (591, 'SE-1-OR0353970-CG', 'SE', NULL, 'โอริง ขนาด3.5 x 39.7', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (592, 'SE-1-SEALCAPID-CG', 'SE', NULL, 'ชุดซีลยางโอริง CAPIDA รุ่น N80-250A', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (593, 'ST-1-TOSANG010-CG', 'ST', NULL, 'ท่อสังกะสี ขนาด 10 นิ้ว ยาว 4 เมตร', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:28', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (594, 'TO-1-SQUARE024-CG', 'TO', NULL, 'ฉากเหล็ก ขนาด 24 นิ้ว', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (595, 'BE-1-C10700000-CG', 'BE', NULL, 'สายพานร่อง C ขนาด 107 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (596, 'ST-1-PAOPAP263-CG', 'ST', NULL, 'เพลาแป๊บ 2.6 นิ้ว x 32 มิล x 1เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (597, 'CA-1-FUNGC2402-CG', 'CA', NULL, 'เฟืองขับน้ำมันไฮโดรลิกซ์ C 240', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (598, 'EL-1-THWA010SQ-CG', 'EL', NULL, 'สายไฟ THW-A  1 x 10 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (599, 'ST-1-PAPD00320-CG', 'ST', NULL, 'แป๊บกลมดำ 3/8 นิ้ว หนา 2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (600, 'CA-1-JACKTPOOO-CG', 'CA', NULL, 'แจ็คเสียบตัวผู้', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (601, 'CA-1-KHOTORCRK0-CG', 'CA', NULL, 'ข้อต่อฉากแหวน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (602, 'CA-1-MAPUMP034-CG', 'CA', NULL, 'แม่ปั้มเบรกตัวบน  ขนาด3/4  MITSUBISHI', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (603, 'CA-1-MKRBCWAN0-CG', 'CA', NULL, 'แหวนลูกสูบเครื่องตัดหญ้า MAKITA RBC411', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (604, 'CA-1-YOYYANG00-CG', 'CA', NULL, 'ยอยกากบาทรถตัก', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (605, 'CO-1-CTFCAST16-CG', 'CO', NULL, 'คอนกรีตทนไฟ Cast 16', '-', 0.00, '1272', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (606, 'LA-1-CMC180025-CG', 'LA', NULL, 'C.M.C.1800 25 KG', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (607, 'MA-1-FAFAN0001-CG', 'MA', NULL, 'ฝาครอบใบพัดลมระบายอากาศ', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (608, 'MA-1-PUMPANTI0-CG', 'MA', NULL, 'ปั๊มน้ำยาแอนตี้สเกล NFPZ 11-ICETZ-30-VICE', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (609, 'MA-1-PUMPHCL00-CG', 'MA', NULL, 'ปั๊มกรดเกลือ HCl NFSX 1106-2HCSXDA1-VTC', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (610, 'OF-1-FAMHONG20-CG', 'OF', NULL, 'แฟ้มเก็บเอกสารแบบห่วงคันโยก ขนาด 2 นิ้ว', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (611, 'OF-1-TELPANA00-CG', 'OF', NULL, 'โทรศัพท์ตั้งโต๊ะไร้สาย PANASONIC', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (612, 'PV-1-GS1500060-CG', 'PV', NULL, 'ประตูน้ำสแตนเลส 3/4 นิ้ว 150', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (613, 'PV-1-SLPVN0604-CG', 'PV', NULL, 'สามทางพีวีซี 3/4 นิ้ว ลด 1/2 นิ้ว x 13.5', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (614, 'SE-1-SN2525335-CG', 'SE', NULL, 'ซีลกันน้ำมัน 25-25-33-5', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:29', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (615, 'SE-1-SN4505575-CG', 'SE', NULL, 'ซีลกันน้ำมัน 45-55-7.5', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (616, 'SE-1-T01903507-CG', 'SE', NULL, 'ซีล TC 19-35-7 ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (617, 'SE-1-T03004511-CG', 'SE', NULL, 'ซีล TC 30-45-11', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (618, 'ST-1-LBOX31530-CG', 'ST', NULL, 'เหล็กกล่องไม้ขีด 3 นิ้ว x 1 1/2 นิ้ว หนา 3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (619, 'ST-1-LC0402025-CG', 'ST', NULL, 'เหล็กตัวซี 4 นิ้ว x 2 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (620, 'ST-1-TOSTEAM34-CG', 'ST', NULL, 'ท่อสตรีมไม่มีตะเข็บ ขนาด 3/4 นิ้ว ยาว 6 เมตร SCH60', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (621, 'TO-1-KUNK04018-CG', 'TO', NULL, 'ด้ามขันแข็ง 1/2 x 18 นิ้ว Kingtony', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (622, 'TO-1-MAKIT6412-CG', 'TO', NULL, 'สว่านไฟฟ้า MAKITA 3/8นิ้ว รุ่น 6412', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (623, 'TO-1-WANKH1213-CG', 'TO', NULL, 'ประแจแหวน เบอร์ 12-13', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (624, 'ST-1-PAPD00426-CG', 'ST', NULL, 'แป๊บกลมดำ 1/2 นิ้ว หนา 2.6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (625, 'EL-1-MON000370-CG', 'EL', NULL, 'มอเตอร์หน้าแปลน 0.37KW 4P 3สาย220/380V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (626, 'ST-1-PAPD00615-CG', 'ST', NULL, 'แป๊บกลมดำ 3/4 นิ้ว หนา 1.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (627, 'ST-1-PAPD00618-CG', 'ST', NULL, 'แป๊บกลมดำ 3/4 นิ้ว หนา 1.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (628, 'EL-1-O02001826-CG', 'EL', NULL, 'โอเวอร์โหลด TH-N20 18-26A', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (629, 'BE-1-C12700000-CG', 'BE', NULL, 'สายพานร่อง C ขนาด 127 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (630, 'BE-1-C15200000-CG', 'BE', NULL, 'สายพานร่อง C ขนาด 152 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (631, 'BE-1-SPC6200LW-CG', 'BE', NULL, 'สายพานร่อง SPC ขนาด 6200 LW', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (632, 'CA-1-10LOX1243-CG', 'CA', NULL, 'ชุดซ่อมปั้มเบรค สิบล้อ 1-87830044-1', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (633, 'CA-1-BKINGTONY-CG', 'CA', NULL, 'ลูกบล็อก KINGTONY ขนาด 3/4 นิ้ว เบอร์ 36', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (634, 'CA-1-FANCAT910-CG', 'CA', NULL, 'ใบพัดลมหม้อน้ำ CAT 910 (ใบเหล็ก)', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (635, 'CA-1-FISPORT12-CG', 'CA', NULL, 'ไฟสปอร์ตไลท์ 12V รถโฟล์คลิฟท์', '-', 0.00, '1290', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (636, 'CA-1-SEBZ2501F-CG', 'CA', NULL, 'ซีลกันน้ำมัน BZ 2501F', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (637, 'CA-1-SEN476075-CG', 'CA', NULL, 'ซีลกันน้ำมันเพลาข้าง 47-60-7.5', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (638, 'DR-1-KARACHIMY-CG', 'DR', NULL, 'ยาครีมการามัยซิน', '-', 0.00, '1290', 'Active', '2021-10-02 14:53:30', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (639, 'EL-1-STHW15000-CG', 'EL', NULL, 'สายไฟ THW 1 x 1.5 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (640, 'EL-1-STHW25000-CG', 'EL', NULL, 'สายไฟ THW 2.5 SQmm ยาว 100 เมตร', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (641, 'EL-1-STHWA10SQ-CG', 'EL', NULL, 'สายไฟ THW 1 x 10 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (642, 'EL-1-TERMCERA4-CG', 'EL', NULL, 'เทอร์มินอลลูกเต๋าเซรามิค 8SQ.mm 60A', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (643, 'EL-1-TOOMOS300-CG', 'EL', NULL, 'ตู้พลาสติกABS ฝาทึบ มีหลังคา 300 x 425 x 155 มิล', '-', 0.00, '1270', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (644, 'EL-1-TR1P380V0-CG', 'EL', NULL, 'หม้อแปลงไฟฟ้า เข้า380V ออก220V 1Phase 10A 2.2KVA ClassF', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (645, 'EL-1-TUPABSEU0-CG', 'EL', NULL, 'ตู้พลาสติกABS 350 x 480 x 180 มิล แบบฝาทึบ', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (646, 'EL-1-VCT440SQM-CG', 'EL', NULL, 'สายไฟ VCT 4 x 4 SQ MM', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (647, 'LA-1-ULTRAS80M-CG', 'LA', NULL, 'ตะแกรงอัลตราโซนิค SS-304 # 80MESH', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (648, 'CA-1-LVALCA910-CG', 'CA', NULL, 'หลอดวาล์ว CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (649, 'MA-1-PUNGMALAI-CG', 'MA', NULL, 'พวงมาลัยวาล์ว (ตามตัวอย่าง)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (650, 'MI-1-TUNG150GL-CG', 'MI', NULL, 'ถังน้ำพลาสติกขนาด 15 GL', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (651, 'MU-1-FSOK08019-CG', 'MU', NULL, 'เฟืองโซ่คู่ เบอร์ 80 19 ฟัน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (652, 'NU-1-HJM010065-CG', 'NU', NULL, 'น็อตหัวจมมิลดำ M10 x 65 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (653, 'SE-1-OR0041900-CG', 'SE', NULL, 'โอริง 4 x 190 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (654, 'TO-1-SQUARE036-CG', 'TO', NULL, 'ฉากเหล็ก ขนาด 36 นิ้ว', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (655, 'ST-1-LST003600-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมตัน 3/8 นิ้ว ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (656, 'ST-1-POSB06550-CG', 'ST', NULL, 'เหล็กเพลาฟ้า 65 มิล ยาว 50 ซม.', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (657, 'EL-1-SNYY04100-CG', 'EL', NULL, 'สายไฟ NYY 4 x 10 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (658, 'CA-1-BOOTKSCAT-CG', 'CA', NULL, 'บูชก้านสูบ CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (659, 'CA-1-NISANTD27-CG', 'CA', NULL, 'ยางครอบฝาวาล์ว นิสสัน TD27', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (660, 'CA-1-PAPCUNNPR-CG', 'CA', NULL, 'แป๊บคันชัก NPR', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (661, 'CA-1-PRAFDA120-CG', 'CA', NULL, 'ปะเก็นฝาข้าง อีซูซุ DA120', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (662, 'CA-1-SAYSTOP00-CG', 'CA', NULL, 'สายดึงดับ 1 - 1.5 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (663, 'CA-1-SHAFTPKMS-CG', 'CA', NULL, 'เพลาลูกเบี้ยว โคมัทสุ FD25', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (664, 'CA-1-SWITBS704-CG', 'CA', NULL, 'เทอร์โมสวิตช์ BS7047', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (665, 'CA-1-YANG13024-CG', 'CA', NULL, 'ยางหล่อดอก 13.00-24', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (666, 'CA-1-YANGKT02J-CG', 'CA', NULL, 'ยางครอบฝาวาล์ว TOYOTA 2J', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (667, 'CO-1-CHALACRE0-CG', 'CO', NULL, 'แชลคแดง เบอร์ 7', '-', 0.00, '1851', 'Active', '2021-10-02 14:53:31', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (668, 'EL-1-LODJ02514-CG', 'EL', NULL, 'หลอดจำปา 25 วัตต์ เกลียว E14', '-', 0.00, '1290', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (669, 'EL-1-SNYY04350-CG', 'EL', NULL, 'สายไฟ NYY 4 x 35 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (670, 'EL-1-TERMIIN00-CG', 'EL', NULL, 'เทอร์มินอลต่อสายโทรศัพท์ภายใน ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (671, 'EL-1-VOLTAR380-CG', 'EL', NULL, 'VOLTAGE RERAY 380V AC', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (672, 'MA-1-PUMEPEN75-CG', 'MA', NULL, 'ปั๊มน้ำอิตาลี PENTAX CM65/125B 7.5HP', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (673, 'MA-1-RFLA20000-CG', 'MA', NULL, 'ยางแผ่นดำ หนา 20 มิล x 2.40 เมตร ', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (674, 'MI-1-BASKET002-CG', 'MI', NULL, 'ตะกร้าพลาสติก ขนาด 9 x 13 x 6 นิ้ว', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (675, 'MI-1-SPBELT000-CG', 'MI', NULL, 'สเปรย์ฉีดสายพาน', '-', 0.00, '1252', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (676, 'MI-1-TUNG18000-CG', 'MI', NULL, 'ถังน้ำ ขนาด18ลิตร', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (677, 'NU-1-HJM010090-CG', 'NU', NULL, 'น็อตหัวจมมิลดำ M10 x 90 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (678, 'OF-1-ROUNTER00-CG', 'OF', NULL, 'ROUNTER N300 (DIR-605) D-LINK', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (679, 'OF-1-TOORCA440-CG', 'OF', NULL, 'ตู้เอกสาร ORCA 4 ชั้น', '-', 0.00, '1270', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (680, 'OI-1-JAPMAXGA0-CG', 'OI', NULL, 'จารบีผสมสารเมกาไลท์ VENTEX-GS910 ขนาด 35 ปอนด์', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (681, 'PL-1-PALAT1201-CG', 'PL', NULL, 'พาเลทไม้ 120 x 120 x 13.5  ซม (อมน้ำยา)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (682, 'ST-1-POSB23300-CG', 'ST', NULL, 'เหล็กเพลาฟ้า 2 3/8 นิ้ว ยาว 3 เมตร', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (683, 'ST-1-POSB35013-CG', 'ST', NULL, 'เหล็กเพลาฟ้า 3 1/2 นิ้ว ยาว 134 ซม.', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (684, 'SU-1-CHA000000-CG', 'SU', NULL, 'ชาลิปตัน กล่อง / 100 ซอง', '-', 0.00, '1253', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (685, 'TO-1-KEM0515SQ-CG', 'TO', NULL, 'คีมย้ำหางปลา 0.5-16 SQ.MM', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:32', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (686, 'TO-1-SAYSB0350-CG', 'TO', NULL, 'สายเชื่อมสีดำ 35 มิล ', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (687, 'CA-1-SEALFL0014-CG', 'CA', NULL, 'ซีลกันจารบีสลักข้าง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (688, 'NU-1-NOTHOJI01-CG', 'NU', NULL, 'น็อตหัวจมดำ 8 x 20', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (689, 'EL-1-POLPU064B-CG', 'EL', NULL, 'สายลมPolyurethane 06 มิลสีขาว PU 06 x 4 มิล', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (690, 'ST-1-LST006600-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมตัน 3/4 นิ้ว ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (691, 'EL-1-SAYLY008A-CG', 'EL', NULL, 'สายลมไนล่อน 08 มิลสีฟ้า PA12 08 x 6 มิล', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (692, 'BA-1-FBJ322170-CG', 'BA', NULL, 'ลูกปืน FBJ32217', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (693, 'CA-1-BAT2000GS-CG', 'CA', NULL, 'แบตเตอรี่ GS N200', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (694, 'CA-1-HJH600000-CG', 'CA', NULL, 'กรองน้ำมันไฮโดรลิครถตัก JH60', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (695, 'CA-1-LOKKATUBU-CG', 'CA', NULL, 'ลูกกระทุ้ง  TOYOTA BU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (696, 'CA-1-LUKMISNPR-CG', 'CA', NULL, 'ลูกหมากคันส่ง ISUZU NPR ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (697, 'CA-1-SIMUPTR04-CG', 'CA', NULL, 'แผ่นชิม ตัวบนลูกปืนอัดจี๋ รถตัก TR004', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (698, 'CA-1-TUNGSUMRO-CG', 'CA', NULL, 'ถังสำรองน้ำมัน ขนาด40ลิตร', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (699, 'EL-1-AMM961505-CG', 'EL', NULL, 'แอมป์มิเตอร์ 96 x 96 มิล 150/5A', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (700, 'EL-1-ERICSON04-CG', 'EL', NULL, 'อีริคสันคุปปิ้ง ขนาด 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (701, 'EL-1-SAYLY010W-CG', 'EL', NULL, 'สายลมไนล่อน 10 มิลสีขาว PA12 10 x 8 มิล', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (702, 'EL-1-WIRECV095-CG', 'EL', NULL, 'สาย CV ขนาด 95 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (703, 'MA-1-SEAL5575C-CG', 'MA', NULL, 'MECHANICAL SEAL SIZE 55*75', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (704, 'MU-1-FSOD06036-CG', 'MU', NULL, 'เฟืองโซ่เดี่ยว เบอร์ 60 36 ฟัน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (705, 'MU-1-YOYSO6018-CG', 'MU', NULL, 'ยอยโซ่ KC-6018', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (706, 'MU-1-YOYSO6022-CG', 'MU', NULL, 'ยอยโซ่คู่ เบอร์ 6022', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (707, 'NU-1-HUANOT122-CG', 'NU', NULL, 'หัวน็อตชุบ 1/2', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (708, 'PV-1-SECTNV004-CG', 'PV', NULL, 'SECTION VALVE 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (709, 'SE-1-DKIY63582-CG', 'SE', NULL, 'ซีล DKIY 63.5 - 82.68 - 9.52', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (710, 'SE-1-T03806510-CG', 'SE', NULL, 'ซีล TC 38-65-10', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (711, 'ST-1-LSP151515-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 1.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:33', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (712, 'ST-1-LSP151518-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 1.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (713, 'ST-1-LSP151520-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (714, 'ST-1-LSP151523-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 2.3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (715, 'ST-1-LSP151525-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (716, 'ST-1-LSP151528-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 2.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (717, 'ST-1-POSB15014-CG', 'ST', NULL, 'เหล็กเพลาฟ้า 1 1/2 นิ้ว ยาว 140 ซม.', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (718, 'ST-1-LSP303028-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3 นิ้ว x 3 นิ้ว หนา 2.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (719, 'LA-1-CONDU0000-CG', 'LA', NULL, 'Conductivity Standard 84uS/CM 250g Mettler', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (720, 'EL-1-MMCON0010-CG', 'EL', NULL, 'เอ็นเอ็ม คอนเนคเตอร์ ขนาด 1 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (721, 'EL-1-YGHIHGB01-CG', 'EL', NULL, 'โคมไฮเบย์หลอดแสงจันทร์', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (722, 'CA-1-GRONGSET8-CG', 'CA', NULL, 'กรองโซล่าคูโบต้า ET-80 RT-110', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (723, 'CA-1-GRONGSTFR-CG', 'CA', NULL, 'กรองโซล่า TFR87 8-97916993-T', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (724, 'CA-1-IVALS4J2G-CG', 'CA', NULL, 'วาล์วไอเสีย ISUZU 4JG2', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (725, 'CA-1-PUMTOJH30-CG', 'CA', NULL, 'ปั๊มท๊อก JH30', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (726, 'CA-1-WANLO0001-CG', 'CA', NULL, 'แหวนรอง(ตามตัวอย่าง)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (727, 'CO-1-SPAYBJO01-CG', 'CO', NULL, 'สเปรย์ตรวจสอบรอยร้าวที่ผิวแนวเชื่อม cleaning (กระป๋องสีฟ้า)', '-', 0.00, '1252', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (728, 'FL-1-GA010010K-CG', 'FL', NULL, 'หน้าแปลนเชื่อมชุบกัลวาไนซ์ ขนาด 1 นิ้ว 10K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (729, 'FL-1-PEJ03020K-CG', 'FL', NULL, 'Powerflex Expansion Joint ขนาด 3 นิ้ว 20K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (730, 'FL-1-PEJ06020K-CG', 'FL', NULL, 'Powerflex Expansion Joint ขนาด 6 นิ้ว 20K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (731, 'MA-1-DISC00000-CG', 'MA', NULL, 'DISC KSB PUMPS', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (732, 'MA-1-PUMAH30HP-CG', 'MA', NULL, 'หัวปั๊มลมพูม่า 30HP  PP630 4สูบ', '-', 0.00, '1292', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (733, 'MI-1-SOJUGA124-CG', 'MI', NULL, 'โซ่คล้องจักรยาน', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (734, 'SE-1-OR0356700-CG', 'SE', NULL, 'โอริง 3.5 x 67', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (735, 'SE-1-OR0544803-CG', 'SE', NULL, 'โอริง 54 x 48 x 3 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (736, 'SE-1-ORING30HP-CG', 'SE', NULL, 'โอริงถ้วยกดวาวล์ไอดี 30 HP PP-630', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:34', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (737, 'ST-1-LSP121215-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/4 นิ้ว x 1 1/4 นิ้ว หนา 1.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (738, 'ST-1-LSP121223-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/4 นิ้ว x 1 1/4 นิ้ว หนา 2.3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (739, 'ST-1-LSP121225-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/4 นิ้ว x 1 1/4 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (740, 'ST-1-LSP121226-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/4 นิ้ว x 1 1/4 นิ้ว หนา 2.6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (741, 'ST-1-LSP151530-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (742, 'ST-1-LSP151532-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/2 นิ้ว x 1 1/2 นิ้ว หนา 3.2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (743, 'ST-1-PAPD00623-CG', 'ST', NULL, 'แป๊บกลมดำ 3/4 นิ้ว หนา 2.3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (744, 'TO-1-FAICHAYPO-CG', 'TO', NULL, 'ไฟฉายตำรวจ ใช้ถ่าน Size D', '-', 0.00, '1251', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (745, 'TO-1-KENKEM6IN-CG', 'TO', NULL, 'คีมปากตัด KENNEDY 160mm(6 3/8นิ้ว) KEN-558-5410K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (746, 'TO-1-KENKEM7IN-CG', 'TO', NULL, 'คีมปากรวม KENNEDY 180mm(7นิ้ว) KEN-558-5530K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (747, 'ST-1-PAPD00625-CG', 'ST', NULL, 'แป๊บกลมดำ 3/4 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (748, 'EL1-LODTONG00-CG', 'EL', NULL, 'ลวดทนความร้อน 2000W  220V', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (749, 'FL-1-FLEPN1640-CG', 'FL', NULL, 'Flexible Ruber joint 4 นิ้ว PN16 ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (750, 'ST-1-PAPD00630-CG', 'ST', NULL, 'แป๊บกลมดำ 3/4 นิ้ว หนา 3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (751, 'CA-1-GEARJH30F-CG', 'CA', NULL, 'ชุดเกียร์ท๊อกเดินหน้าถอยหลังรถตัก JH30', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (752, 'CA-1-LOK4JG2IS-CG', 'CA', NULL, 'ลูกลอกสายพานไทม์มิ่ง ISUZU 4JG2 บฉ9687', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (753, 'CA-1-PUMPHIJH3-CG', 'CA', NULL, 'ปั้มไฮโดรลิกซ์ JH30', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (754, 'CA-1-PUMPTTBU0-CG', 'CA', NULL, 'ปั๊มน้ำโตโยต้า BU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (755, 'CA-1-SEALORKOM-CG', 'CA', NULL, 'ซีลกันฝุ่น 360-22-13130 โคมัทสุ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (756, 'CA-1-TOYANGG06-CG', 'CA', NULL, 'ท่อยางไฮดรอลิก 3/4 นิ้ว', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (757, 'EL-1-COMPRES32-CG', 'EL', NULL, 'คอมเพรสเซอร์แอร์ 32000 BTU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (758, 'EL-1-FLH006A00-CG', 'EL', NULL, 'ฟิวส์แรงสูง(Fuse Link) 6A 22KV', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (759, 'EL-1-FLH032ACA-CG', 'EL', NULL, 'ฟิวส์แรงสูง(Fuse Link) 32A  660V ใช้กับ CAP BANK', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (760, 'EL-1-GRABFL030-CG', 'EL', NULL, 'กระบอกฟิวส์หลอดแก้ว ขนาด 30 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (761, 'EL-1-LLBOX0010-CG', 'EL', NULL, 'LL บล็อก ขนาด 1 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:35', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (762, 'LA-1-RX2900500-CG', 'LA', NULL, '5(R-30010)Rotating Guard รุ่น RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (763, 'MA-1-GASHOSE14-CG', 'MA', NULL, 'สายแก๊ส hose pipe 1/4 นิ้ว * L600 mm', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (764, 'OI-1-PUMASA300-CG', 'OI', NULL, 'น้ำมันเครื่องอัดอากาศระบบโรตารี่สกรู PUMA SAS-300บรรจุ20ลิตร ', '-', 0.00, '1851', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (765, 'SE-1-SN9010510-CG', 'SE', NULL, 'ซีลกันน้ำมัน 90-105-10', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (766, 'ST-1-LBL020088-CG', 'ST', NULL, 'เหล็กแบน 2 นิ้ว หนา 8.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (767, 'ST-1-LSP101015-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 นิ้ว x 1 นิ้ว หนา 1.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (768, 'ST-1-LSP101016-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 นิ้ว x 1 นิ้ว หนา 1.6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (769, 'ST-1-LSP101018-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 นิ้ว x 1 นิ้ว หนา 1.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (770, 'ST-1-LSP101020-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 นิ้ว x 1 นิ้ว หนา 2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (771, 'ST-1-LSP101023-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 นิ้ว x 1 นิ้ว หนา 2.3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (772, 'ST-1-LSP101025-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 นิ้ว x 1 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (773, 'ST-1-LSP121216-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/4 นิ้ว x 1 1/4 นิ้ว หนา 1.6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (774, 'ST-1-LSP121217-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 1 1/4 นิ้ว x 1 1/4 นิ้ว หนา 1.7 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (775, 'ST-1-PAPD01020-CG', 'ST', NULL, 'แป๊บกลมดำ 1 นิ้ว หนา 2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (776, 'EL-1-NEGG00602-CG', 'EL', NULL, 'ข้องอเกลียว MPL06-02 ขนาด 6 มิล เกลียว 1/4 นิ้ว ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (777, 'ST-1-LSP303018-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3 นิ้ว x 3 นิ้ว หนา 1.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (778, 'LA-1-SULPHURIC-CG', 'LA', NULL, 'Sulphuric acid AR E MERCK 2.5Lite', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (779, 'TO-1-LUEYTANU0-CG', 'TO', NULL, 'เลื่อยคันธนู', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (780, 'CA-1-SHAFTISC2-CG', 'CA', NULL, 'เพลาลูกเบี้ยว ISUZU C-240', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (781, 'CA-1-SHARTOYBU-CG', 'CA', NULL, 'ชาร์พก้าน  TOYOTA BU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (782, 'CA-1-SIMDNTR04-CG', 'CA', NULL, 'แผ่นชิม ตัวล่างลูกปืนอัดจี๋ รถตัก TR004', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (783, 'CA-1-TIMKEN320-CG', 'CA', NULL, 'ลูกปืนล้อ TIMKEN Y32008(บม7893) ', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (784, 'CA-1-VAVKM4D95-CG', 'CA', NULL, 'ยางครอบฝาวาล์ว โคมัทสุ 4D95S', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (785, 'CH-1-ASCORBIC5-CG', 'CH', NULL, 'Ascorbic Acid 500g POCH (C6H8O6)', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (786, 'EL-1-AMIM5005A-CG', 'EL', NULL, 'แอมป์มิเตอร์ 96 x 96 500/5A', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (787, 'EL-1-SOLI48852-CG', 'EL', NULL, 'โซลินอยด์วาล์ว 488.52.0.1.M58 P2.5-10BAR', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (788, 'LA-1-HYDRO0010-CG', 'LA', NULL, 'Hydroxylamine hydrochloride (500g)', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (789, 'MA-1-YOANGMH80-CG', 'MA', NULL, 'ยางยอย MH80', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (790, 'OF-1-HDEXTER05-CG', 'OF', NULL, 'ฮาร์ดดิส แบบ External ความจุ 500 GB', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:36', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (791, 'OF-1-HDEXTER20-CG', 'OF', NULL, 'ฮาร์ดดิส แบบ External ความจุ 2 TB', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (792, 'PV-1-KOTAKI031-CG', 'PV', NULL, 'ข้อต่อตาไก่ทองเหลือง 3/8 นิ้ว 1ทาง เกลียวนอก ตัวL', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (793, 'PV-1-TOHUGD310-CG', 'PV', NULL, 'ท่ออ่อนสตีม 3 นิ้ว ยาว 10 เมตร', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (794, 'ST-1-LH2500914-CG', 'ST', NULL, 'เหล็กเอชบีม 250 x 250 x 9 x 14 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (795, 'ST-1-LLT101200-CG', 'ST', NULL, 'เหล็กหล่อตัน 10 นิ้ว x 12 นิ้ว', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (796, 'ST-1-LSP060612-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3/4 นิ้ว x 3/4 นิ้ว หนา 1.2 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (797, 'ST-1-LSP202023-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 2 นิ้ว x 2 นิ้ว หนา 2.3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (798, 'ST-1-LSP202025-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 2 นิ้ว x 2 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (799, 'ST-1-LSP202028-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 2 นิ้ว x 2 นิ้ว หนา 2.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (800, 'EL-1-ROMMMMMMO-CG', 'EL', NULL, 'สายลม 6x4 มิล สีฟ้า', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (801, 'CA-1-TANAM0510-CG', 'CA', NULL, 'ตาน้ำ 51 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (802, 'ST-1-POKN10011-CG', 'ST', NULL, 'เหล็กเพลาตัน 2 1/2 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (803, 'EL-1-TMCP21450-CG', 'EL', NULL, 'แท่งเซรามิควัดแรงดันเตา 20 x 14 x 500 มิล', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (804, 'BA-1-6311EAE40-CG', 'BA', NULL, 'ลูกปืน 6311', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (805, 'CO-1-SAYYANG11-CG', 'CO', NULL, 'สายยางใส 1/4 นิ้ว', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (806, 'ST-1-LSP404025-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 4 นิ้ว x 4 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (807, 'CA-1-JH30SA000-CG', 'CA', NULL, 'ชาร์พอก JH30 DA220', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (808, 'CA-1-LUGM0200R-CG', 'CA', NULL, 'ลูกหมากเกลียว 20 มิล ขวา 43750-20541-71 (งอ)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (809, 'CA-1-NIPPH0390-CG', 'CA', NULL, 'นิเปิ้ลไฮดรอลิก 90 องศา 3/8 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (810, 'CA-1-PRAFDA641-CG', 'CA', NULL, 'ปะเก็นฝาสูบ DA640', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (811, 'CA-1-REPASJH30-CG', 'CA', NULL, 'ชุดซ่อมกระบอกยก รถตัก JH30', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (812, 'CA-1-SCREWKT10-CG', 'CA', NULL, 'สกรูล้อหลัง KT รถสิบล้อ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (813, 'CA-1-SKOOPAO48-CG', 'CA', NULL, 'สกรูเพลากลาง FN', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (814, 'CA-1-STUD78450-CG', 'CA', NULL, 'STUD VALE EH ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:37', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (815, 'CA-1-TANAM4450-CG', 'CA', NULL, 'ตาน้ำจาน ขนาดโต44มิลหนา5มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (816, 'EL-1-BEAKER2PA-CG', 'EL', NULL, 'BEAKER 2P PANASONIC(พร้อมหน้ากากขนาด10AT)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (817, 'LA-1-HYDROMETE-CG', 'LA', NULL, 'Hydrometer 1.240-1.300 (Precision)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (818, 'MA-1-REGLSCG00-CG', 'MA', NULL, 'ตัวเร่งแก๊ส SCG', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (819, 'MI-1-TUNG2000L-CG', 'MI', NULL, 'ถังพลาสติก 200ลิตร สีน้ำเงิน', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (820, 'NU-1-GPTM10015-CG', 'NU', NULL, 'สกรูเกลียวปล่อยเตเปอร์ 10 นิ้ว ยาว 1 1/2 นิ้ว', '-', 0.00, '1253', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (821, 'ST-1-LSP060615-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3/4 นิ้ว x 3/4 นิ้ว หนา 1.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (822, 'ST-1-LSP060616-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 3/4 นิ้ว x 3/4 นิ้ว หนา 1.6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (823, 'ST-1-LSP404026-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 4 นิ้ว x 4 นิ้ว หนา 2.6 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (824, 'ST-1-PAPD04045-CG', 'ST', NULL, 'แป๊บกลมดำ 4 นิ้ว หนา 4.5 มิล ยาว 6 เมตร ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (825, 'TO-1-BTID23L34-CG', 'TO', NULL, 'บูชยูลิเทนสีเหลือง ขนาด ID23*OD40*L34 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (826, 'TO-1-KHAITOKB4-CG', 'TO', NULL, 'ไขควงตอกแบน ขนาด 4 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (827, 'TO-1-PAJALUERN-CG', 'TO', NULL, 'ประแจเลื่อน ขนาด 18 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (828, 'WE-1-NAMYASS00-CG', 'WE', NULL, 'น้ำยากัดแนวเชื่อมแสตนเลส GEL-122', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (829, 'NU-1-HNN600000-CG', 'NU', NULL, 'น็อต NC M20x25', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (830, 'CA-1-SAPING4D9-CG', 'CA', NULL, 'สปริงวาล์ว 4D95', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (831, 'OI-1-GASOLI910-CG', 'OI', NULL, 'น้ำมันแก๊สโซฮอล 91', '-', 0.00, '1286', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (832, 'EL-1-RANGA0001-CG', 'EL', NULL, 'รางอลูมิเนียมปลีกใน', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (833, 'ST-1-PAPD01025-CG', 'ST', NULL, 'แป๊บกลมดำ 1 นิ้ว หนา 2.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (834, 'BA-1-00528S000-CG', 'BA', NULL, 'ซีล TSN 528 ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:38', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (835, 'CA-1-KSCAT9100-CG', 'CA', NULL, 'ก้านสูบ CAT910', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (836, 'CA-1-LOOKSCA91-CG', 'CA', NULL, 'ลูกสูบ CAT910', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (837, 'CA-1-MVALMATSU-CG', 'CA', NULL, 'ซีลหลอดวาล์ว รถยก โคมัทสุ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (838, 'CA-1-PAKEM10LO-CG', 'CA', NULL, 'ปะเก็นชุดใหญ่ รถสิบล้อ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (839, 'CA-1-TOA002530-CG', 'CA', NULL, 'ท่ออากาศ 2 1/2 นิ้ว ยาว 30 นิ้ว', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (840, 'CA-1-TOHINID27-CG', 'CA', NULL, 'ท่อไฮดรอลิก Nissan SD27 ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (841, 'CA-1-YANGDA640-CG', 'CA', NULL, 'ยางหมวกวาล์ว DA640', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (842, 'CA-1-YANGF5515-CG', 'CA', NULL, 'ยางตันหล่อดอก 5.50-15', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (843, 'CA-1-YANGG0000-CG', 'CA', NULL, 'ยางแท่นเกียร์ โตโยต้า 12361-23001-71', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (844, 'CO-1-BOOT15012-CG', 'CO', NULL, 'บูชประตูเหล็ก  1/2  นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (845, 'EL-1-SELECSWV1-CG', 'EL', NULL, 'ซีเลคเตอร์ V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (846, 'EL-1-TRAMS160T-CG', 'EL', NULL, 'TRANSMETER PRESSURE SPAN PN160 2.5 TO 250 MBAR', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (847, 'LA-1-IKAC48021-CG', 'LA', NULL, 'O-ring 2 x (pos.21 C48)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (848, 'MA-1-PUMLOW003-CG', 'MA', NULL, 'ปั๊มน้ำ LOWARA 3 แรง 380V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (849, 'NU-1-HT5810000-CG', 'NU', NULL, 'น็อตหัวเหลี่ยม 5/8 นิ้ว ยาว 1  นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (850, 'OF-1-MAINBOCOM-CG', 'OF', NULL, 'Motherboard คอมพิวเตอร์', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (851, 'PV-1-BALLNKK30-CG', 'PV', NULL, 'บอลวาล์วเหล็กหล่อหน้าแปลน 3 นิ้ว 10K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (852, 'SE-1-SF0250376-CG', 'SE', NULL, 'ซีลกันฝุ่น 25-37-6', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (853, 'ST-1-LH1256509-CG', 'ST', NULL, 'เหล็กเอชบีม 125 x 125 x 6.5 x 9 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:39', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (854, 'ST-1-LH3001015-CG', 'ST', NULL, 'เหล็กเอชบีม 300 x 300 x 10 x 15 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (855, 'ST-1-LH3501219-CG', 'ST', NULL, 'เหล็กเอชบีม 350 x 350 x 12 x 19 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (856, 'ST-1-LLT085075-CG', 'ST', NULL, 'เหล็กหล่อตัน 8 1/2 นิ้ว หนา 75 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (857, 'ST-1-LLT105045-CG', 'ST', NULL, 'เหล็กหล่อตัน 10 1/2 นิ้ว หนา 45 มิล', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (858, 'ST-1-LLT203012-CG', 'ST', NULL, 'เหล็กหล่อตัน 2 นิ้ว x 3 นิ้ว x 12 นิ้ว', '-', 0.00, '1275', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (859, 'ST-1-LP5010045-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 4.5 มิล', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (860, 'ST-1-LPJ486016-CG', 'ST', NULL, 'เหล็กแผ่นเจาะรู 4 x 8 ฟุต หนา 6 มิล รู 16 มิล ', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (861, 'TO-1-CDFBHHFB6-CG', 'TO', NULL, 'ไขควงตอกแบน ขนาด 6 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (862, 'TO-1-CHUTKHA1I-CG', 'TO', NULL, 'ชุดไขควงแบน 12-14 นิ้ว', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (863, 'TO-1-SCREW0200-CG', 'TO', NULL, 'ไขควงตุ้ม หัวแฉก', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (864, 'EL-1-SWES220RL-CG', 'EL', NULL, 'สวิทซ์หัวเห็ดกดล็อค 22 มิล Red TEND', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (865, 'ST-1-PAPD01027-CG', 'ST', NULL, 'แป๊บกลมดำ 1 นิ้ว หนา 2.7 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (866, 'EL-1-SWITCHMHP-CG', 'EL', NULL, 'สวิทซ์สว่าน MAKITA รุ่นHP2050 TGB/3A WB-2', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (867, 'CA-1-BOOTTOYBU-CG', 'CA', NULL, 'บูชก้านสูบ  TOYOTA BU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (868, 'CA-1-CHAPSAO00-CG', 'CA', NULL, 'ชาร์พเสา กระบอกยกสูง', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (869, 'CA-1-FASUB4D95-CG', 'CA', NULL, 'ฝาสูบรถยก โคมัทสุ 4D95S', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (870, 'CA-1-HPPT01500-CG', 'CA', NULL, 'หัวเผา PT-15 11V', '-', 0.00, '1292', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (871, 'CA-1-KENIS4JG2-CG', 'CA', NULL, 'ปะเก็นชุดใหญ่ ISUZU 4JG2', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (872, 'CA-1-MKRBCSLLS-CG', 'CA', NULL, 'สลักลูกสูบเครื่องตัดหญ้า MAKITA RBC411', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (873, 'CA-1-NUTPFCAT9-CG', 'CA', NULL, 'น็อตยึดเพลาขับหน้า CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:40', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (874, 'CA-1-PLOKTOYBU-CG', 'CA', NULL, 'ปลอกสูบ  TOYOTA BU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (875, 'CA-1-POKSUBCAT-CG', 'CA', NULL, 'ปลอกสูบ CAT910', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (876, 'CA-1-SAPOKTOBU-CG', 'CA', NULL, 'ชาร์พอก  TOYOTA BU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (877, 'CA-1-SEALKOMAT-CG', 'CA', NULL, 'ซีลกันน้ำมัน 325-22-21350 โคมัทสุ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (878, 'CA-1-SFKRCAT91-CG', 'CA', NULL, 'ชาร์พกันรุน CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (879, 'CA-1-SLUGTOYBU-CG', 'CA', NULL, 'สลักลูกสูบ  TOYOTA BU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (880, 'CA-1-VALISCA91-CG', 'CA', NULL, 'วาล์วไอเสีย CAT910', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (881, 'CA-1-YANGNI140-CG', 'CA', NULL, 'ยางใน 1400 - 24', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (882, 'CH-1-NITRICA65-CG', 'CH', NULL, 'NITRIC ACID 65% MERCKขนาด2.5ลิตร', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (883, 'EL-1-BG250SW15-CG', 'EL', NULL, 'เบรกเกอร์ 150A 3P NF250-SW', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (884, 'EL-1-BOX701024-CG', 'EL', NULL, 'ตู้ไฟเหล็ก ขนาด 70 x100 x 24 ซม. (กว้าง x สูง x ลึก)', '-', 0.00, '1270', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (885, 'EL-1-FANMO0618-CG', 'EL', NULL, 'ใบพัดมอเตอร์ 6 นิ้ว x 18 มิล', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (886, 'LA-1-PLATIC7CM-CG', 'LA', NULL, 'PLASTIC LUNNEL เส้นผ่านศูนย์กลาง7cm', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (887, 'LA-1-PTFEOSC01-CG', 'LA', NULL, 'PTFE OUTLET SCREW CAP GL14', '-', 0.00, '1261', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (888, 'LA-1-RX2902000-CG', 'LA', NULL, '20(R-20020)Bearing Plate/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (889, 'MI-1-BUNDAI015-CG', 'MI', NULL, 'บันไดอลูมิเนียม 1.5 เมตร', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (890, 'MU-1-B00700700-CG', 'MU', NULL, 'พลูเล่ย์ ขนาด 7 นิ้ว แบบตัน  3 ร่องB', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (891, 'SE-1-SEEL42557-CG', 'SE', NULL, 'ซีลยาง ขนาด 42-55-7', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (892, 'SE-1-ST0182554-CG', 'SE', NULL, 'ซีลทนกรด 18 x 25.5 x 4 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (893, 'ST-1-LPL408043-CG', 'ST', NULL, 'เหล็กแผ่นลาย 4 x 8 ฟุต หนา 4.3 มิล ไม่รวมลาย', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (894, 'TO-1-CLAMP6IN0-CG', 'TO', NULL, 'ปากกาจับงาน 6นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (895, 'TO-1-SLIDETL01-CG', 'TO', NULL, 'ด้ามเลื่อนขนาด 1/2 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (896, 'MU-1-B50020150-CG', 'MU', NULL, 'พลูเล่ย์ ขนาด 5 นิ้ว 2 ร่อง B รูเพลา 1 1/2 นิ้ว ร่องลิ่ม 8 มิล ลึก 2 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (897, 'LA-1-MACHINE25-CG', 'LA', NULL, 'เครื่องตีป่น(สลัก4ชุด)มอเตอร์ 2 HP 2 POLE 3 PH พร้อมตะแกรงวัสดุ รู เส้นผ่าศูนย์กลาง 1.5 mm', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (898, 'LA-1-MAGNETICO-CG', 'LA', NULL, 'Permament Magnetic bar for test Approx > 12000 Gamss', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:41', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (899, 'ST-1-LSP404045-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 4 นิ้ว x 4 นิ้ว หนา 4.5 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (900, 'CA-1-CRUT05124-CG', 'CA', NULL, 'ชูดซ่อมครัชบน โฟล์คลิฟท์', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (901, 'CA-1-FTEJH3000-CG', 'CA', NULL, 'กรองน้ำมันเครื่อง JH30 DA220', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (902, 'CA-1-HUAPTOYBU-CG', 'CA', NULL, 'หัวเผา  TOYOTA BU', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (903, 'CA-1-SEALGJHL0-CG', 'CA', NULL, 'ซีลเพลาขับหลังเกียร์ JH30', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (904, 'CA-1-WR1LDCA00-CG', 'CA', NULL, 'ออร์โต้เดรน Armstrong 1-LDC', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (905, 'CA-1-YANGKD640-CG', 'CA', NULL, 'ยางครอบฝาวาล์ว DA 640', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (906, 'CA-1-YANKS2100-CG', 'CA', NULL, 'ยางแท่นเครื่อง KS 21', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (907, 'CO-1-SKYLUE620-CG', 'CO', NULL, 'สีฟ้า SKYBLUE EN620 TOA', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (908, 'EL-1-BG0321P0S-CG', 'EL', NULL, 'เบรกเกอร์ 32A 1P QOH 132X  ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (909, 'EL-1-CAVSF0015-CG', 'EL', NULL, 'สายไฟ VSF 1.5 SQmm', '-', 0.00, '1282', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (910, 'EL-1-ELECONT04-CG', 'EL', NULL, 'ตู้คอนโทรลภายนอก กว้าง 200 มิล สูง 300 มิล ลึก150 มิล', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (911, 'EL-1-LOOKY0020-CG', 'EL', NULL, 'ลูกย่อย 20A 1P', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (912, 'LA-1-RX2906100-CG', 'LA', NULL, '91(R-10019)Cable Tie Ml ฟุตg/RX-29', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (913, 'MA-1-BIPAD36NE-CG', 'MA', NULL, 'ใบพัดลม ขนาด 36นิ้ว', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (914, 'MA-1-FILTCLO01-CG', 'MA', NULL, 'ผ้ากรอง PE/PE 550g + AS ขนาดกว้าง 215 ซม. ยาว 1 เมตร', '-', 0.00, '1279', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (915, 'MA-1-GEAR02520-CG', 'MA', NULL, 'เกียร์ทดรอบ PA25 1:20', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (916, 'MI-1-DILUENT00-CG', 'MI', NULL, 'สารละลายกาวสารพัดประโยชน์ X-66', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (917, 'NU-1-HJM010125-CG', 'NU', NULL, 'น็อตหัวจมมิลดำ M10 x 125 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (918, 'NU-1-LMD200200-CG', 'NU', NULL, 'น็อตมิลดำ M20 x 120 มิล พร้อมหัว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (919, 'NU-1-LMD250075-CG', 'NU', NULL, 'น็อตมิลดำ M25 x 75 มิล พร้อมหัว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (920, 'ST-1-LI2002001-CG', 'ST', NULL, 'เหล็กเอชบีม 200 x 200 x 12 มิล x 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (921, 'ST-1-LP5010012-CG', 'ST', NULL, 'เหล็กแผ่นดำ 5 x 10 ฟุต หนา 12 มิล  ', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (922, 'ST-1-LSP202030-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมโปร่ง 2 นิ้ว x 2 นิ้ว หนา 3 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (923, 'TO-1-FAN001600-CG', 'TO', NULL, 'พัดลมติดผนัง 16 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:42', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (924, 'TO-1-JULTREFB6-CG', 'TO', NULL, 'ไขควงตอกแฉก ขนาด 6 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (925, 'TO-1-MULTIPARA-CG', 'TO', NULL, 'เครื่องวัดค่าความเป็นกรดด่าง Multi-Parameter Bench Meter MODEL:PC700 EUTECH', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (926, 'ST-1-PAPD01218-CG', 'ST', NULL, 'แป๊บกลมดำ 1 1/4 นิ้ว หนา 1.8 มิล ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (927, 'EL-1-MAM080220-CG', 'EL', NULL, 'แมกเนติกคอนแทค SN-80 220V', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (928, 'EL-1-SOPV12075-CG', 'EL', NULL, 'สายคอนโทรล OPVC-JZ ขนาด 12 x 0.75 SQmm', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (929, 'CA-1-FAKAGEH70-CG', 'CA', NULL, 'ฝาข้างฝาสูบ EH 700', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (930, 'CA-1-PKVAIDT3L-CG', 'CA', NULL, 'ปะเก็นวาล์วไอดี โตโยต้า 3L', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (931, 'CA-1-GEARKOMAT-CG', 'CA', NULL, 'ชุดเกียร์เดินหน้า ถอยหลัง รถโฟล์คลิฟท์ โคมัทสุ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (932, 'CA-1-NVYTU5147-CG', 'CA', NULL, 'กิ๊บล็อค (บม7893)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (933, 'CA-1-PUMPHIDRO-CG', 'CA', NULL, 'ปั้มไฮดรอลิก KRP4-30ASSB (KAYABA)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (934, 'CA-1-SEIKEN150-CG', 'CA', NULL, 'ลูกยางเบรค SC15023R', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (935, 'CA-1-WL30HP630-CG', 'CA', NULL, 'แหวนลูกสูบ 30 HP PP-630', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (936, 'CH-1-TRISODIUM-CG', 'CH', NULL, 'ไตรโซเดียมฟอสเฟต Trisodium Phosphate', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (937, 'CO-1-OIHSUALAK-CG', 'CO', NULL, 'แผ่นเมทัสชีล หนา 0.47 ยาว 25 เมตร สีซิงค์', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (938, 'DR-1-AMMOBICAR-CG', 'DR', NULL, 'แอมโมเนียไบคาร์บอเนต 450 ml', '-', 0.00, '1256', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (939, 'EL-1-BATTAA036-CG', 'EL', NULL, 'Back up Battery 3.6 V/2.3AH Size AA for S7-400', '-', 0.00, '1254', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (940, 'EL-1-BG1503P36-CG', 'EL', NULL, 'เบรกเกอร์ 150 AT 150AF 3P KA36', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (941, 'EL-1-BG1603P15-CG', 'EL', NULL, 'เบรกเกอร์ 150A 3P NF160-SW', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (942, 'EL-1-DRYFIL03P-CG', 'EL', NULL, 'DRYER FILTER ถ้วยเหล็ก 3P-150-MT8-FI', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (943, 'EL-1-GLONGB250-CG', 'EL', NULL, 'กล่องใส่บัลลาสต์ 250 วัตต์', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (944, 'EL-1-VOLUM10K1-CG', 'EL', NULL, 'โวลุ่มปรับระดับ(VL) B10K', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (945, 'MA-1-ANTIFRIC0-CG', 'MA', NULL, 'SET OF ANTI FRICTION KSB PUMPS', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (946, 'MA-1-BOOTH20NE-CG', 'MA', NULL, 'สลักบูชยางยอย ขนาด 8นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (947, 'MA-1-FANM3HP00-CG', 'MA', NULL, 'ใบพัดลมใส่กับมอเตอร์ 3 HP (ตามตัวอย่าง)', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (948, 'MA-1-TAGS06013-CG', 'MA', NULL, 'ตะแกรง STL เบอร์ 60 ลวด 38 x 1.30 เมตร x ตัด', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (949, 'NU-1-GPL030025-CG', 'NU', NULL, 'สกรูเกลียวปล่อยหัวเหลี่ยม 3/8 นิ้ว ยาว 2 1/2 นิ้ว', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:43', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (950, 'NU-1-LMD200070-CG', 'NU', NULL, 'น็อตมิลดำ M20 x 70 มิล พร้อมหัว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (951, 'NU-1-NUTMO6250-CG', 'NU', NULL, 'น็อตขันมอเตอร์ ขนาด 6 มิล ยาว 250 มิล ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (952, 'OF-1-OFFICDK00-CG', 'OF', NULL, 'โต๊ะทำงาน', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (953, 'ST-1-LS415100T-CG', 'ST', NULL, 'ข้อลดสตีมเชื่อมมีตะเข็บ 1 1/2 นิ้ว x 1 นิ้ว SCH40', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (954, 'TO-1-LEGTOG06L-CG', 'TO', NULL, 'เหล็กตอกตัวเลขอารบิก ขนาด6 มิล(1/4นิ้ว) เลข1-0', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (955, 'BA-1-000608NSK-CG', 'BA', NULL, 'ลูกปืน NSK 608 DW', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (956, 'BE-1-C12100000-CG', 'BE', NULL, 'สายพานร่อง C ขนาด 121 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (957, 'EL-1-MOS404350-CG', 'EL', NULL, 'ตู้พลาสติกกันฝน MOS 404 ขนาด 350 x 515 x 185 มิล', '-', 0.00, '1270', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (958, 'BE-1-SPC630000-CG', 'BE', NULL, 'สายพานร่อง SPC ขนาด 6300 LW ', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (959, 'CA-1-FINA10LOR-CG', 'CA', NULL, 'ไฟหน้ารถสิบล้อ H4 24V 100W', '-', 0.00, '1290', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (960, 'CA-1-GRAJTTMTX-CG', 'CA', NULL, 'กระจกมองข้างรถ Toyota 2L ไมตี้เอ็กซ์ ข้างซ้าย ข้างขวา', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (961, 'CA-1-KYB51K012-CG', 'CA', NULL, 'ชุดซ่อมปั้มไฮดรอลิค KYB 51K012 ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (962, 'CA-1-LLOGHU620-CG', 'CA', NULL, 'ลูกปืนกากบาท GUH-62(รถสิบล้อ)', '-', 0.00, '1266', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (963, 'CA-1-SEIKEN353-CG', 'CA', NULL, 'ลูกยางเบรค SEIKEN SC80353R', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (964, 'CA-1-SLUGS4520-CG', 'CA', NULL, 'สลักตีนผี S45', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (965, 'CA-1-SOMKO1204-CG', 'CA', NULL, 'ชุดซ่อมกระบอกคว่ำหงายกลาง โคมัทสุ', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (966, 'EL-1-AIRCLEANE-CG', 'EL', NULL, 'น้ำยาแอร์ R-22 พร้อมถัง บรรจุ 13.6 KG', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (967, 'EL-1-AIRCO9000-CG', 'EL', NULL, 'เครื่องปรับอากาศติดผนัง 9000 BTU', '-', 0.00, '1259', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (968, 'EL-1-DSG033C50-CG', 'EL', NULL, 'โซลินอยด์วาล์วน้ำมันYUKENรุ่นDSG-03-3C2-A220-50', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (969, 'EL-1-ELDUCT020-CG', 'EL', NULL, 'รางเก็บสายไฟแบบโปร่งขนาด 20 x 20 มิล ยาว 2 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (970, 'EL-1-ELECONT02-CG', 'EL', NULL, 'ตู้คอนโทรลภายนอก กว้าง 200 มิล สูง 300 มิล ลึก 160มิล', '-', 0.00, '1277', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (971, 'EL-1-LOOKY0016-CG', 'EL', NULL, 'ลูกย่อย 16A 1P', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (972, 'EL-1-LOOKY0032-CG', 'EL', NULL, 'ลูกย่อย 32A 1P', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (973, 'MA-1-GASKET000-CG', 'MA', NULL, 'SET OF GASKET KSB PUMPS', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (974, 'MA-1-TUBE50835-CG', 'MA', NULL, 'TUBE BOILER STB O.D.50.8มิล หนา3.5มิล ยาว7เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:44', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (975, 'MA-1-VALID30HP-CG', 'MA', NULL, 'วาล์วไอดีปั๊มลม PUMA 30HP', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (976, 'OI-1-GAS5KILOP-CG', 'OI', NULL, 'ถังแก๊ส ขนาด 4 กิโลกรัม(ถังปิคนิค)', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (977, 'SE-1-OR0042500-CG', 'SE', NULL, 'โอริง 4 x 250 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (978, 'ST-1-LST010600-CG', 'ST', NULL, 'เหล็กสี่เหลี่ยมตัน 1 นิ้ว ยาว 6 เมตร', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (979, 'ST-1-PAPD11225-CG', 'ST', NULL, 'แป๊บกลมดำ 1 1/2 นิ้ว x 2.5 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (980, 'ST-1-PAPD11425-CG', 'ST', NULL, 'แป๊บกลมดำ 1 1/4 นิ้ว x 2.5 มิล', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (981, 'TO-1-CHUTK1214-CG', 'TO', NULL, 'ชุดไขควงแฉก 12-14นิ้ว', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (982, 'TO-1-KFUTLBP12-CG', 'TO', NULL, 'ไขควงตอกแบน ขนาด 12 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (983, 'CA-1-FREEJ0000-CG', 'CA', NULL, 'ฟรีจักรยาน (ตัวสเตอร์หลัง)', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (984, 'BE-1-C14200000-CG', 'BE', NULL, 'สายพานร่อง C ขนาด 142 นิ้ว', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (985, 'EL-1-THN400RHA-CG', 'EL', NULL, 'โอเวอร์โหลด TH-N400RH HEATER 250A', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (986, 'CA-1-GANGEARKO-CG', 'CA', NULL, 'แกนเกียร์ โคมัทสุ', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (987, 'CA-1-GOYUT1248-CG', 'CA', NULL, 'แผ่นล็อคตีนผี S45', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (988, 'CA-1-HIDRORICH-CG', 'CA', NULL, 'ท่อน้ำมันไฮดรอลิค ขนาด(เส้นผ่านศูนย์กลางใน) 28มิล', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (989, 'CA-1-KOHAN3L35-CG', 'CA', NULL, 'คอห่านบน โตโยต้า 3L', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (990, 'CA-1-KONGKU100-CG', 'CA', NULL, 'กรองเครื่อง รถสิบล้อ', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (991, 'CA-1-PKVAIST3L-CG', 'CA', NULL, 'ปะเก็นวาล์วไอเสีย โตโยต้า 3L', '-', 0.00, '1280', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (992, 'CA-1-SCREW102X-CG', 'CA', NULL, 'สกรูหัวเพลา TX', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (993, 'CA-1-SEALP30HP-CG', 'CA', NULL, 'ซีลเพลาข้อเหวี่ยง 30 HP PP-630#55-90-10', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (994, 'CA-1-VALE3LTOY-CG', 'CA', NULL, 'วาล์วน้ำ โตโยต้า 3L', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (995, 'CO-1-GONCH1200-CG', 'CO', NULL, 'กลอนประตูเหล็ก 12 นิ้ว', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (996, 'EL-1-HANPLA154-CG', 'EL', NULL, 'หางปลาแฉกหุ้มฉนวน ขนาด 1.5-4 มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (997, 'LA-1-GLASSL7CM-CG', 'LA', NULL, 'GLASS LUNNEL เส้นผ่านศูนย์กลาง 7cm.', '-', 0.00, '1293', 'Active', '2021-10-02 14:53:45', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (998, 'LA-1-OXYGENGO2-CG', 'LA', NULL, 'OXYGEN ULTRA HIGH PURITY GAS (O2)', '-', 0.00, '1271', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (999, 'MA-1-MOS84726L-CG', 'MA', NULL, 'หม้อกรองอากาศ ปั๊มลม PUMA 10 HP', '-', 0.00, '1287', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (1000, 'MA-1-PLAOMA30H-CG', 'MA', NULL, ' เพลาข้อเหวี่ยงปั๊มลม PUMA 30 HP', '-', 0.00, '1289', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (1001, 'MA-1-SHARP30HP-CG', 'MA', NULL, 'ชาร์พข้อเหวี่ยงปั๊มลม PUMA 30 HP', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (1002, 'MA-1-TAGS07010-CG', 'MA', NULL, 'ตะแกรง STL เบอร์ 70 ลวด 40 x 1 เมตร x ตัด', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (1003, 'MA-1-TAGS70013-CG', 'MA', NULL, 'ตะแกรง STL เบอร์ 70 ลวด 40 x 1.30 เมตร x ตัด', '-', 0.00, '1284', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (1004, 'MI-1-TUNGR0305-CG', 'MI', NULL, 'ถุงพลาสติกร้อนใส ขนาด 3x5 นิ้ว', '-', 0.00, '1247', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (1005, 'SE-1-OR1511SE3-CG', 'SE', NULL, 'โอริง ขนาด 1.5*11มิล', '-', 0.00, '1268', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');
INSERT INTO `ims_product` VALUES (1006, 'ST-1-HUAT00600-CG', 'ST', NULL, 'ฟุตวาล์วเหล็กเกลียวใน ขนาด 6 นิ้ว', '-', 0.00, '1262', 'Active', '2021-10-02 14:53:46', 'img/icon/product-001.png');

-- ----------------------------
-- Table structure for ims_provinces
-- ----------------------------
DROP TABLE IF EXISTS `ims_provinces`;
CREATE TABLE `ims_provinces`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `province_id` int(11) NOT NULL,
  `province_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `province_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `province_name_eng` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `geo_id` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Active',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 78 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_provinces
-- ----------------------------
INSERT INTO `ims_provinces` VALUES (1, 1, '10', 'กรุงเทพมหานคร', 'Bangkok', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (2, 2, '11', 'สมุทรปราการ', 'Samut Prakan', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (3, 3, '12', 'นนทบุรี', 'Nonthaburi', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (4, 4, '13', 'ปทุมธานี', 'Pathum Thani', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (5, 5, '14', 'พระนครศรีอยุธยา', 'Phra Nakhon Si Ayutthaya', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (6, 6, '15', 'อ่างทอง', 'Ang Thong', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (7, 7, '16', 'ลพบุรี', 'Loburi', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (8, 8, '17', 'สิงห์บุรี', 'Sing Buri', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (9, 9, '18', 'ชัยนาท', 'Chai Nat', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (10, 10, '19', 'สระบุรี', 'Saraburi', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (11, 11, '20', 'ชลบุรี', 'Chon Buri', 5, 'Active');
INSERT INTO `ims_provinces` VALUES (12, 12, '21', 'ระยอง', 'Rayong', 5, 'Active');
INSERT INTO `ims_provinces` VALUES (13, 13, '22', 'จันทบุรี', 'Chanthaburi', 5, 'Active');
INSERT INTO `ims_provinces` VALUES (14, 14, '23', 'ตราด', 'Trat', 5, 'Active');
INSERT INTO `ims_provinces` VALUES (15, 15, '24', 'ฉะเชิงเทรา', 'Chachoengsao', 5, 'Active');
INSERT INTO `ims_provinces` VALUES (16, 16, '25', 'ปราจีนบุรี', 'Prachin Buri', 5, 'Active');
INSERT INTO `ims_provinces` VALUES (17, 17, '26', 'นครนายก', 'Nakhon Nayok', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (18, 18, '27', 'สระแก้ว', 'Sa Kaeo', 5, 'Active');
INSERT INTO `ims_provinces` VALUES (19, 19, '30', 'นครราชสีมา', 'Nakhon Ratchasima', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (20, 20, '31', 'บุรีรัมย์', 'Buri Ram', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (21, 21, '32', 'สุรินทร์', 'Surin', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (22, 22, '33', 'ศรีสะเกษ', 'Si Sa Ket', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (23, 23, '34', 'อุบลราชธานี', 'Ubon Ratchathani', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (24, 24, '35', 'ยโสธร', 'Yasothon', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (25, 25, '36', 'ชัยภูมิ', 'Chaiyaphum', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (26, 26, '37', 'อำนาจเจริญ', 'Amnat Charoen', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (27, 27, '39', 'หนองบัวลำภู', 'Nong Bua Lam Phu', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (28, 28, '40', 'ขอนแก่น', 'Khon Kaen', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (29, 29, '41', 'อุดรธานี', 'Udon Thani', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (30, 30, '42', 'เลย', 'Loei', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (31, 31, '43', 'หนองคาย', 'Nong Khai', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (32, 32, '44', 'มหาสารคาม', 'Maha Sarakham', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (33, 33, '45', 'ร้อยเอ็ด', 'Roi Et', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (34, 34, '46', 'กาฬสินธุ์', 'Kalasin', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (35, 35, '47', 'สกลนคร', 'Sakon Nakhon', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (36, 36, '48', 'นครพนม', 'Nakhon Phanom', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (37, 37, '49', 'มุกดาหาร', 'Mukdahan', 3, 'Active');
INSERT INTO `ims_provinces` VALUES (38, 38, '50', 'เชียงใหม่', 'Chiang Mai', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (39, 39, '51', 'ลำพูน', 'Lamphun', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (40, 40, '52', 'ลำปาง', 'Lampang', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (41, 41, '53', 'อุตรดิตถ์', 'Uttaradit', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (42, 42, '54', 'แพร่', 'Phrae', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (43, 43, '55', 'น่าน', 'Nan', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (44, 44, '56', 'พะเยา', 'Phayao', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (45, 45, '57', 'เชียงราย', 'Chiang Rai', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (46, 46, '58', 'แม่ฮ่องสอน', 'Mae Hong Son', 1, 'Active');
INSERT INTO `ims_provinces` VALUES (47, 47, '60', 'นครสวรรค์', 'Nakhon Sawan', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (48, 48, '61', 'อุทัยธานี', 'Uthai Thani', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (49, 49, '62', 'กำแพงเพชร', 'Kamphaeng Phet', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (50, 50, '63', 'ตาก', 'Tak', 4, 'Active');
INSERT INTO `ims_provinces` VALUES (51, 51, '64', 'สุโขทัย', 'Sukhothai', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (52, 52, '65', 'พิษณุโลก', 'Phitsanulok', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (53, 53, '66', 'พิจิตร', 'Phichit', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (54, 54, '67', 'เพชรบูรณ์', 'Phetchabun', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (55, 55, '70', 'ราชบุรี', 'Ratchaburi', 4, 'Active');
INSERT INTO `ims_provinces` VALUES (56, 56, '71', 'กาญจนบุรี', 'Kanchanaburi', 4, 'Active');
INSERT INTO `ims_provinces` VALUES (57, 57, '72', 'สุพรรณบุรี', 'Suphan Buri', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (58, 58, '73', 'นครปฐม', 'Nakhon Pathom', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (59, 59, '74', 'สมุทรสาคร', 'Samut Sakhon', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (60, 60, '75', 'สมุทรสงคราม', 'Samut Songkhram', 2, 'Active');
INSERT INTO `ims_provinces` VALUES (61, 61, '76', 'เพชรบุรี', 'Phetchaburi', 4, 'Active');
INSERT INTO `ims_provinces` VALUES (62, 62, '77', 'ประจวบคีรีขันธ์', 'Prachuap Khiri Khan', 4, 'Active');
INSERT INTO `ims_provinces` VALUES (63, 63, '80', 'นครศรีธรรมราช', 'Nakhon Si Thammarat', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (64, 64, '81', 'กระบี่', 'Krabi', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (65, 65, '82', 'พังงา', 'Phangnga', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (66, 66, '83', 'ภูเก็ต', 'Phuket', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (67, 67, '84', 'สุราษฎร์ธานี', 'Surat Thani', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (68, 68, '85', 'ระนอง', 'Ranong', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (69, 69, '86', 'ชุมพร', 'Chumphon', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (70, 70, '90', 'สงขลา', 'Songkhla', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (71, 71, '91', 'สตูล', 'Satun', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (72, 72, '92', 'ตรัง', 'Trang', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (73, 73, '93', 'พัทลุง', 'Phatthalung', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (74, 74, '94', 'ปัตตานี', 'Pattani', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (75, 75, '95', 'ยะลา', 'Yala', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (76, 76, '96', 'นราธิวาส', 'Narathiwat', 6, 'Active');
INSERT INTO `ims_provinces` VALUES (77, 77, '97', 'บึงกาฬ', 'buogkan', 3, 'Active');

-- ----------------------------
-- Table structure for ims_purchase_detail
-- ----------------------------
DROP TABLE IF EXISTS `ims_purchase_detail`;
CREATE TABLE `ims_purchase_detail`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doc_date` date NULL DEFAULT NULL,
  `line_no` int(11) NOT NULL,
  `product_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `unit_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` double(11, 2) NOT NULL DEFAULT 0,
  `price` double(11, 2) NOT NULL DEFAULT 0,
  `create_date` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 61 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_purchase_detail
-- ----------------------------
INSERT INTO `ims_purchase_detail` VALUES (51, '2021-PRH-000001', '2021-09-27', 1, 'EL-BF0009', 'U-0001', 60.00, 1500.00, '2021-09-27 13:56:21');
INSERT INTO `ims_purchase_detail` VALUES (53, '2021-PRH-000001', '2021-09-27', 2, 'EL-MB002', 'U-0001', 20.00, 15000.00, '2021-09-27 14:01:29');
INSERT INTO `ims_purchase_detail` VALUES (54, '2021-PRH-000001', '2021-09-27', 3, 'EL-MB003', 'U-0001', 56.00, 16000.00, '2021-09-27 14:22:58');
INSERT INTO `ims_purchase_detail` VALUES (55, '2021-PRH-000002', '2021-09-27', 1, 'EL-BF0009', 'U-0001', 78.00, 1350.00, '2021-09-27 14:23:29');
INSERT INTO `ims_purchase_detail` VALUES (56, '2021-PRH-000002', '2021-09-27', 2, 'EL-MB004', 'U-0001', 50.00, 27000.00, '2021-09-29 16:14:03');
INSERT INTO `ims_purchase_detail` VALUES (57, '2021-PRH-000003', '2021-09-29', 1, 'EL-BF0009', 'U-0001', 50.00, 1500.00, '2021-09-29 16:18:02');
INSERT INTO `ims_purchase_detail` VALUES (58, '2021-PRH-000004', '2021-09-30', 1, 'EL-BF0009', 'U-0001', 5.00, 0.00, '2021-09-30 12:54:15');
INSERT INTO `ims_purchase_detail` VALUES (59, '2021-PRH-000004', '2021-09-30', 2, 'EL-MB001', 'U-0001', 10.00, 15000.00, '2021-09-30 13:59:50');
INSERT INTO `ims_purchase_detail` VALUES (60, '2021-PRH-000001', '2021-09-30', 1, 'EL-MB001', 'U-0001', 150.00, 11000.00, '2021-09-30 14:30:41');

-- ----------------------------
-- Table structure for ims_purchase_detail_temp
-- ----------------------------
DROP TABLE IF EXISTS `ims_purchase_detail_temp`;
CREATE TABLE `ims_purchase_detail_temp`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doc_date` date NULL DEFAULT NULL,
  `line_no` int(11) NOT NULL,
  `product_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `unit_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` double(11, 2) NOT NULL DEFAULT 0,
  `price` double(11, 2) NOT NULL DEFAULT 0,
  `create_date` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 87 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_purchase_detail_temp
-- ----------------------------
INSERT INTO `ims_purchase_detail_temp` VALUES (86, 'yF54OiP6AI8*tNH:1632987024796', '2021-09-30', 1, 'EL-MB001', 'U-0001', 150.00, 11200.00, '2021-09-30 14:30:38');

-- ----------------------------
-- Table structure for ims_purchase_master
-- ----------------------------
DROP TABLE IF EXISTS `ims_purchase_master`;
CREATE TABLE `ims_purchase_master`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doc_year` int(11) NULL DEFAULT NULL,
  `doc_runno` int(11) NULL DEFAULT NULL,
  `supplier_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `doc_date` date NULL DEFAULT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Active',
  `KeyAddData` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `create_date` timestamp(0) NULL DEFAULT current_timestamp(0),
  `update_date` timestamp(0) NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 48 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_purchase_master
-- ----------------------------
INSERT INTO `ims_purchase_master` VALUES (47, '2021-PRH-000001', 2021, 1, 'S-0002', '2021-09-30', 'Active', 'yF54OiP6AI8*tNH:1632987024796', '2021-09-30 14:30:40', '2021-09-30 14:30:40');

-- ----------------------------
-- Table structure for ims_supplier
-- ----------------------------
DROP TABLE IF EXISTS `ims_supplier`;
CREATE TABLE `ims_supplier`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `supplier_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_supplier
-- ----------------------------
INSERT INTO `ims_supplier` VALUES (1, 'S-0001', 'Grant', 'Africa', '574575373734', 'Active');
INSERT INTO `ims_supplier` VALUES (3, 'S-0002', 'David Dave', 'New Delhi', '11111111111', 'Active');
INSERT INTO `ims_supplier` VALUES (5, 'S-0005', 'ดูโฮม', '112', '0440656565', 'Active');
INSERT INTO `ims_supplier` VALUES (6, 'S-0006', 'ไมโครซอฟท์', 'USA', '10000', 'Active');
INSERT INTO `ims_supplier` VALUES (7, 'S-0007', 'GOOGLE', 'ENGLAND', '0112222', 'Active');

-- ----------------------------
-- Table structure for ims_unit
-- ----------------------------
DROP TABLE IF EXISTS `ims_unit`;
CREATE TABLE `ims_unit`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `unit_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 97 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_unit
-- ----------------------------
INSERT INTO `ims_unit` VALUES (1, 'U-0001', 'เครื่อง', 'Active');
INSERT INTO `ims_unit` VALUES (2, 'U-0002', 'แพ๊ค', 'Active');
INSERT INTO `ims_unit` VALUES (3, 'U-0003', 'กล่อง', 'Active');
INSERT INTO `ims_unit` VALUES (4, 'U-0004', 'เครื่อง - 4', 'Inactive');
INSERT INTO `ims_unit` VALUES (5, 'U-0005', 'เครื่อง - 5', 'Active');
INSERT INTO `ims_unit` VALUES (6, 'U-0006', 'เครื่อง - 6', 'Active');
INSERT INTO `ims_unit` VALUES (7, 'U-0007', 'เครื่อง - 7', 'Active');
INSERT INTO `ims_unit` VALUES (8, 'U-0008', 'เครื่อง - 8', 'Active');
INSERT INTO `ims_unit` VALUES (9, 'U-0009', 'เครื่อง - 9', 'Active');
INSERT INTO `ims_unit` VALUES (10, 'U-0010', 'เครื่อง - 10', 'Active');
INSERT INTO `ims_unit` VALUES (11, 'U-0011', 'เครื่อง - 11', 'Active');
INSERT INTO `ims_unit` VALUES (12, 'U-0012', 'เครื่อง - 12', 'Active');
INSERT INTO `ims_unit` VALUES (13, 'U-0013', 'เครื่อง - 13', 'Active');
INSERT INTO `ims_unit` VALUES (14, 'U-0014', 'เครื่อง - 14', 'Active');
INSERT INTO `ims_unit` VALUES (15, 'U-0015', 'เครื่อง - 15', 'Active');
INSERT INTO `ims_unit` VALUES (16, 'U-0016', 'เครื่อง - 16', 'Active');
INSERT INTO `ims_unit` VALUES (17, 'U-0017', 'เครื่อง - 17', 'Active');
INSERT INTO `ims_unit` VALUES (18, 'U-0018', 'เครื่อง - 18', 'Active');
INSERT INTO `ims_unit` VALUES (19, 'U-0019', 'เครื่อง - 19', 'Active');
INSERT INTO `ims_unit` VALUES (20, 'U-0020', 'เครื่อง - 20', 'Active');
INSERT INTO `ims_unit` VALUES (21, 'U-0021', 'นิ้ว', 'Active');
INSERT INTO `ims_unit` VALUES (22, 'U-0022', 'ลิตร', 'Active');
INSERT INTO `ims_unit` VALUES (23, 'U-0023', 'หลา', 'Active');
INSERT INTO `ims_unit` VALUES (24, 'U-0024', 'โหล', 'Active');
INSERT INTO `ims_unit` VALUES (25, 'U-0025', 'ขวด', 'Active');
INSERT INTO `ims_unit` VALUES (26, 'U-0026', 'อัน', 'Active');
INSERT INTO `ims_unit` VALUES (27, 'U-0027', 'หุน', 'Active');
INSERT INTO `ims_unit` VALUES (28, '1857', 'กะปุก', 'Active');
INSERT INTO `ims_unit` VALUES (29, '1246', 'กระสอบ', 'Active');
INSERT INTO `ims_unit` VALUES (30, '1851', 'แกลลอน', 'Active');
INSERT INTO `ims_unit` VALUES (31, '1852', 'คัน', 'Active');
INSERT INTO `ims_unit` VALUES (32, '1247', 'กิโลกรัม', 'Active');
INSERT INTO `ims_unit` VALUES (33, '1248', 'ถุงใหญ่', 'Active');
INSERT INTO `ims_unit` VALUES (34, '1249', 'ตัน', 'Active');
INSERT INTO `ims_unit` VALUES (35, '1250', 'BAR', 'Active');
INSERT INTO `ims_unit` VALUES (36, '1251', 'กระบอก', 'Active');
INSERT INTO `ims_unit` VALUES (37, '1252', 'กระป๋อง', 'Active');
INSERT INTO `ims_unit` VALUES (38, '1253', 'กล่อง', 'Active');
INSERT INTO `ims_unit` VALUES (39, '1254', 'ก้อน', 'Active');
INSERT INTO `ims_unit` VALUES (40, '1256', 'ขวด', 'Active');
INSERT INTO `ims_unit` VALUES (41, '1257', 'คิว', 'Active');
INSERT INTO `ims_unit` VALUES (42, '1258', 'คู่', 'Active');
INSERT INTO `ims_unit` VALUES (43, '1259', 'เครื่อง', 'Active');
INSERT INTO `ims_unit` VALUES (44, '1260', 'จุด', 'Active');
INSERT INTO `ims_unit` VALUES (45, '1261', 'ชิ้น', 'Active');
INSERT INTO `ims_unit` VALUES (46, '1262', 'ชุด', 'Active');
INSERT INTO `ims_unit` VALUES (47, '1263', 'ดวง', 'Active');
INSERT INTO `ims_unit` VALUES (48, '1264', 'ดอก', 'Active');
INSERT INTO `ims_unit` VALUES (49, '1266', 'ตลับ', 'Active');
INSERT INTO `ims_unit` VALUES (50, '1267', 'ตัน', 'Active');
INSERT INTO `ims_unit` VALUES (51, '1268', 'ตัว', 'Active');
INSERT INTO `ims_unit` VALUES (52, '1269', 'ตารางฟุต', 'Active');
INSERT INTO `ims_unit` VALUES (53, '1270', 'ตู้', 'Active');
INSERT INTO `ims_unit` VALUES (54, '1271', 'ถัง', 'Active');
INSERT INTO `ims_unit` VALUES (55, '1272', 'ถุง', 'Active');
INSERT INTO `ims_unit` VALUES (56, '1273', 'แถว', 'Active');
INSERT INTO `ims_unit` VALUES (57, '1274', 'ท่อ', 'Active');
INSERT INTO `ims_unit` VALUES (58, '1275', 'ท่อน', 'Active');
INSERT INTO `ims_unit` VALUES (59, '1276', 'แท่ง', 'Active');
INSERT INTO `ims_unit` VALUES (60, '1277', 'ใบ', 'Active');
INSERT INTO `ims_unit` VALUES (61, '1278', 'ปีบ', 'Active');
INSERT INTO `ims_unit` VALUES (62, '1279', 'ผืน', 'Active');
INSERT INTO `ims_unit` VALUES (63, '1280', 'แผ่น', 'Active');
INSERT INTO `ims_unit` VALUES (64, '1281', 'ฟุต', 'Active');
INSERT INTO `ims_unit` VALUES (65, '1283', 'มัด', 'Active');
INSERT INTO `ims_unit` VALUES (66, '1284', 'เมตร', 'Active');
INSERT INTO `ims_unit` VALUES (67, '1285', 'ล้อ', 'Active');
INSERT INTO `ims_unit` VALUES (68, '1286', 'ลิตร', 'Active');
INSERT INTO `ims_unit` VALUES (69, '1287', 'ลูก', 'Active');
INSERT INTO `ims_unit` VALUES (70, '1288', 'เล่ม', 'Active');
INSERT INTO `ims_unit` VALUES (71, '1289', 'เส้น', 'Active');
INSERT INTO `ims_unit` VALUES (72, '1290', 'หลอด', 'Active');
INSERT INTO `ims_unit` VALUES (73, '1291', 'ห่อ', 'Active');
INSERT INTO `ims_unit` VALUES (74, '1292', 'หัว', 'Active');
INSERT INTO `ims_unit` VALUES (75, '1293', 'อัน', 'Active');
INSERT INTO `ims_unit` VALUES (76, '1294', 'งาน', 'Active');
INSERT INTO `ims_unit` VALUES (77, '1295', 'แพ็ค', 'Active');
INSERT INTO `ims_unit` VALUES (78, '1730', 'เที่ยว', 'Active');
INSERT INTO `ims_unit` VALUES (79, '1737', 'รีม', 'Active');
INSERT INTO `ims_unit` VALUES (80, '1739', 'แผง', 'Active');
INSERT INTO `ims_unit` VALUES (81, '1740', 'เม็ด', 'Active');
INSERT INTO `ims_unit` VALUES (82, '1792', 'โหล', 'Active');
INSERT INTO `ims_unit` VALUES (83, '1793', 'ลัง', 'Active');
INSERT INTO `ims_unit` VALUES (84, '1822', 'กรัม', 'Active');
INSERT INTO `ims_unit` VALUES (85, '1823', 'กล่องเล็ก', 'Active');
INSERT INTO `ims_unit` VALUES (86, '1853', 'ซอง', 'Active');
INSERT INTO `ims_unit` VALUES (87, '1282', 'ม้วน', 'Active');
INSERT INTO `ims_unit` VALUES (88, '1856', 'โคม', 'Active');
INSERT INTO `ims_unit` VALUES (89, '1858', 'ขีด', 'Active');
INSERT INTO `ims_unit` VALUES (90, '1859', 'ลำ', 'Active');
INSERT INTO `ims_unit` VALUES (91, '1860', 'เรือน', 'Active');
INSERT INTO `ims_unit` VALUES (92, '1861', 'แฟ้ม', 'Active');
INSERT INTO `ims_unit` VALUES (93, '1862', 'บาน', 'Active');
INSERT INTO `ims_unit` VALUES (94, '1863', 'หีบ', 'Active');
INSERT INTO `ims_unit` VALUES (95, '1864', 'ป้าย', 'Active');
INSERT INTO `ims_unit` VALUES (96, '1265', 'ด้าม', 'Active');

-- ----------------------------
-- Table structure for ims_user
-- ----------------------------
DROP TABLE IF EXISTS `ims_user`;
CREATE TABLE `ims_user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `line_no` int(11) NULL DEFAULT NULL,
  `user_id` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `password` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `account_type` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `lang` enum('th','en') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'th',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ims_user
-- ----------------------------
INSERT INTO `ims_user` VALUES (1, 1, 'admin@myadmin.com', 'admin@myadmin.com', 'ADMIN', 'STOCK', '$2y$10$C0z1CwgBGUcCqIqlhcz65O1Zdi1H1YIiuqw/oGlzlj5IDdX/jHluC', 'admin', 'Active', 'img/icon/admin-001.png', 'th');
INSERT INTO `ims_user` VALUES (2, 2, 'user@myadmin.com', 'user@myadmin.com', 'USER', 'STOCK', '$2y$10$C0z1CwgBGUcCqIqlhcz65O1Zdi1H1YIiuqw/oGlzlj5IDdX/jHluC', 'user', 'Active', 'img/icon/user-001.png', 'en');
INSERT INTO `ims_user` VALUES (5, 3, 'User001@gmail.com', 'User001@gmail.com', 'User001', 'APPDATA1', '$2y$10$HPBYteBtDxT1a0O/zV2G4uuihCQle6ax0e81iJok8/a/hadP2ujQe', 'user', 'Active', 'img/icon/user-001.png', 'th');
INSERT INTO `ims_user` VALUES (6, 4, 'user003@rmail.com', 'user003@rmail.com', 'USER003', 'THAILAND', '$2y$10$HPBYteBtDxT1a0O/zV2G4uuihCQle6ax0e81iJok8/a/hadP2ujQe', 'user', 'Active', 'img/icon/user-001.png', 'th');
INSERT INTO `ims_user` VALUES (7, 5, 'admin001@myadmin.com', 'admin001@myadmin.com', 'Admin', 'Thai', '$2y$10$8VqAMP15iQYies0xqrA9r.otqBnbtiKtXGNxJpu2JlFPYEwYIABkq', 'user', 'Active', 'img/icon/user-001.png', 'th');

-- ----------------------------
-- Table structure for menu_main
-- ----------------------------
DROP TABLE IF EXISTS `menu_main`;
CREATE TABLE `menu_main`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `main_menu_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `label_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `target` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `sort` int(11) NULL DEFAULT 0,
  `privilege` enum('Admin','User','') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `data_target` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `aria_controls` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of menu_main
-- ----------------------------
INSERT INTO `menu_main` VALUES (1, 'M001', 'กำหนดระบบ', 'Initial System', '#', '', 'fa fa-cogs', 1, 'User', '#InitSystem', 'InitSystem');
INSERT INTO `menu_main` VALUES (2, 'M002', 'ทะเบียนหลัก', 'Master', '#', '', 'fa fa-list', 2, 'User', '#Master', 'Master');
INSERT INTO `menu_main` VALUES (3, 'M003', 'บันทึกข้อมูลหลัก', 'Main Data', '#', '', 'fa fa-table', 3, 'User', '#MainData', 'MainData');
INSERT INTO `menu_main` VALUES (4, 'M004', 'บันทึกเอกสาร', 'Document', '#', '', 'fa fa-tasks', 4, 'User', '#Document', 'Document');
INSERT INTO `menu_main` VALUES (5, 'M005', 'ค้นหาข้อมูล', 'Search Data', '#', NULL, 'fa fa-search', 5, 'User', '#SearchData', 'SearchData');

-- ----------------------------
-- Table structure for menu_sub
-- ----------------------------
DROP TABLE IF EXISTS `menu_sub`;
CREATE TABLE `menu_sub`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sub_menu_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `main_menu_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `label_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `target` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `sort` int(11) NULL DEFAULT 0,
  `privilege` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 37 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of menu_sub
-- ----------------------------
INSERT INTO `menu_sub` VALUES (1, 'S101', 'M001', 'สร้างบัญชีผู้ใช้', 'Create User Account', 'create-account.php', '', 'fa fa-user-plus', 1, 'Admin');
INSERT INTO `menu_sub` VALUES (2, 'S102', 'M001', 'จัดการผู้ใช้งานระบบ', 'User Management', 'manage-account.php', '', 'fa fa-user', 2, 'Admin');
INSERT INTO `menu_sub` VALUES (3, 'S103', 'M001', 'เปลี่ยนรหัสผ่าน', 'Change Password', 'change-password.php', '', 'fa fa-key', 3, 'User');
INSERT INTO `menu_sub` VALUES (10, 'S104', 'M001', 'จัดการเมนูหลักของระบบ', 'Main Menu ', 'manage-menu-main.php', '', 'fa fa-window-maximize', 4, 'Admin');
INSERT INTO `menu_sub` VALUES (11, 'S105', 'M001', 'จัดการเมนูหน้าจอของระบบ', 'Sub Menu And Screen', 'manage-menu-sub.php', '', 'fa fa-window-restore', 5, 'Admin');
INSERT INTO `menu_sub` VALUES (19, 'S106', 'M001', 'เปลี่ยนภาษา ', 'Change Language', 'change-language.php', NULL, 'fa fa-language', 8, 'User');
INSERT INTO `menu_sub` VALUES (24, 'S201', 'M002', 'ทะเบียนสินค้า-วัสดุ', 'Product-Part', 'manage-product.php', NULL, 'fa fa-th', 1, 'User');
INSERT INTO `menu_sub` VALUES (25, 'S202', 'M002', 'ทะเบียนหน่วยนับ', 'Unit Code', 'manage-unit.php', NULL, 'fa fa-th', 2, 'User');
INSERT INTO `menu_sub` VALUES (26, 'S203', 'M002', 'ทะเบียนกลุ่มสินค้า', 'Product Group', 'manage-product-group.php', NULL, 'fa fa-th', 3, 'User');
INSERT INTO `menu_sub` VALUES (27, 'S401', 'M004', 'เอกสารรายการขาย ', 'Order Document', 'manage-order.php', NULL, 'fa fa-list-alt', 1, 'User');
INSERT INTO `menu_sub` VALUES (28, 'S301', 'M003', 'ทะเบียนลูกค้า', 'Customer Master', 'manage_customer.php', NULL, 'fa fa-th', 1, 'User');
INSERT INTO `menu_sub` VALUES (30, 'S302', 'M003', 'ทะเบียนผู้ขาย', 'Supplier Master', 'manage_supplier.php', NULL, 'fa fa-th', 2, 'User');
INSERT INTO `menu_sub` VALUES (31, 'S204', 'M002', 'ทะเบียนยี่ห้อ', 'Brand Master', 'manage-brand.php', NULL, 'fa fa-th', 4, 'User');
INSERT INTO `menu_sub` VALUES (32, 'S402', 'M004', 'เอกสารการซื้อ-รับสินค้า', 'PR-PO Document', 'manage-purchase.php', NULL, 'fa fa-list-alt', 2, 'User');
INSERT INTO `menu_sub` VALUES (36, 'S501', 'M005', 'ค้นหาสินค้า', 'ค้นหาสินค้า', '#', NULL, 'fa fa-search', 0, 'Admin');

-- ----------------------------
-- View structure for vims_product
-- ----------------------------
DROP VIEW IF EXISTS `vims_product`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `vims_product` AS SELECT `ims_product`.`id` AS `id`, `ims_product`.`product_id` AS `product_id`, `ims_product`.`pgroup_id` AS `pgroup_id`, `ims_product`.`brand_id` AS `brand_id`, `ims_product`.`name_t` AS `name_t`, `ims_product`.`name_e` AS `name_e`, `ims_product`.`quantity` AS `quantity`, `ims_product`.`unit_id` AS `unit_id`, `ims_product`.`status` AS `status`, `ims_product`.`date` AS `date`, `ims_product`.`picture` AS `picture`, `ims_unit`.`unit_name` AS `unit_name`, `ims_pgroup`.`pgroup_name` AS `pgroup_name`, `ims_brand`.`brand_name` AS `brand_name` FROM (((`ims_product` left join `ims_unit` on(`ims_unit`.`unit_id` = `ims_product`.`unit_id`)) left join `ims_pgroup` on(`ims_pgroup`.`pgroup_id` = `ims_product`.`pgroup_id`)) left join `ims_brand` on(`ims_brand`.`brand_id` = `ims_product`.`brand_id`)) ;

-- ----------------------------
-- View structure for v_order_detail
-- ----------------------------
DROP VIEW IF EXISTS `v_order_detail`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_order_detail` AS SELECT `ord`.`id` AS `id`, `ord`.`doc_no` AS `doc_no`, `ord`.`doc_date` AS `doc_date`, `ord`.`line_no` AS `line_no`, `ord`.`product_id` AS `product_id`, `ord`.`unit_id` AS `unit_id`, `ord`.`quantity` AS `quantity`, `ord`.`price` AS `price`, `ord`.`create_date` AS `create_date`, `vpd`.`name_t` AS `product_name`, `unit`.`unit_name` AS `unit_name`, `ord`.`quantity`* `ord`.`price` AS `total_price` FROM ((`ims_order_detail` `ord` left join `vims_product` `vpd` on(`vpd`.`product_id` = `ord`.`product_id`)) left join `ims_unit` `unit` on(`unit`.`unit_id` = `ord`.`unit_id`)) ;

-- ----------------------------
-- View structure for v_order_detail_temp
-- ----------------------------
DROP VIEW IF EXISTS `v_order_detail_temp`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_order_detail_temp` AS SELECT `ord`.`id` AS `id`, `ord`.`doc_no` AS `doc_no`, `ord`.`doc_date` AS `doc_date`, `ord`.`line_no` AS `line_no`, `ord`.`product_id` AS `product_id`, `ord`.`unit_id` AS `unit_id`, `ord`.`quantity` AS `quantity`, `ord`.`price` AS `price`, `ord`.`create_date` AS `create_date`, `vpd`.`name_t` AS `product_name`, `unit`.`unit_name` AS `unit_name`, `ord`.`quantity`* `ord`.`price` AS `total_price` FROM ((`ims_order_detail_temp` `ord` left join `vims_product` `vpd` on(`vpd`.`product_id` = `ord`.`product_id`)) left join `ims_unit` `unit` on(`unit`.`unit_id` = `ord`.`unit_id`)) ;

-- ----------------------------
-- View structure for v_order_master
-- ----------------------------
DROP VIEW IF EXISTS `v_order_master`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_order_master` AS SELECT `ims_order_master`.`id` AS `id`, `ims_order_master`.`doc_no` AS `doc_no`, `ims_order_master`.`doc_year` AS `doc_year`, `ims_order_master`.`doc_runno` AS `doc_runno`, `ims_order_master`.`customer_id` AS `customer_id`, `ims_order_master`.`doc_date` AS `doc_date`, `ims_order_master`.`create_date` AS `create_date`, `ims_order_master`.`status` AS `status`, `ims_order_master`.`KeyAddData` AS `KeyAddData`, `ims_customer`.`f_name` AS `f_name` FROM (`ims_order_master` left join `ims_customer` on(`ims_customer`.`customer_id` = `ims_order_master`.`customer_id`)) ;

-- ----------------------------
-- View structure for v_purchase_detail
-- ----------------------------
DROP VIEW IF EXISTS `v_purchase_detail`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_purchase_detail` AS SELECT `prh`.`id` AS `id`, `prh`.`doc_no` AS `doc_no`, `prh`.`doc_date` AS `doc_date`, `prh`.`line_no` AS `line_no`, `prh`.`product_id` AS `product_id`, `prh`.`unit_id` AS `unit_id`, `prh`.`quantity` AS `quantity`, `prh`.`price` AS `price`, `prh`.`create_date` AS `create_date`, `vpd`.`name_t` AS `product_name`, `unit`.`unit_name` AS `unit_name`, `prh`.`quantity`* `prh`.`price` AS `total_price` FROM ((`ims_purchase_detail` `prh` left join `vims_product` `vpd` on(`vpd`.`product_id` = `prh`.`product_id`)) left join `ims_unit` `unit` on(`unit`.`unit_id` = `prh`.`unit_id`)) ;

-- ----------------------------
-- View structure for v_purchase_detail_temp
-- ----------------------------
DROP VIEW IF EXISTS `v_purchase_detail_temp`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_purchase_detail_temp` AS SELECT `prh`.`id` AS `id`, `prh`.`doc_no` AS `doc_no`, `prh`.`doc_date` AS `doc_date`, `prh`.`line_no` AS `line_no`, `prh`.`product_id` AS `product_id`, `prh`.`unit_id` AS `unit_id`, `prh`.`quantity` AS `quantity`, `prh`.`price` AS `price`, `prh`.`create_date` AS `create_date`, `vpd`.`name_t` AS `product_name`, `unit`.`unit_name` AS `unit_name`, `prh`.`quantity`* `prh`.`price` AS `total_price` FROM ((`ims_purchase_detail_temp` `prh` left join `vims_product` `vpd` on(`vpd`.`product_id` = `prh`.`product_id`)) left join `ims_unit` `unit` on(`unit`.`unit_id` = `prh`.`unit_id`)) ;

-- ----------------------------
-- View structure for v_purchase_master
-- ----------------------------
DROP VIEW IF EXISTS `v_purchase_master`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_purchase_master` AS SELECT `ims_purchase_master`.`id` AS `id`, `ims_purchase_master`.`doc_no` AS `doc_no`, `ims_purchase_master`.`doc_year` AS `doc_year`, `ims_purchase_master`.`doc_runno` AS `doc_runno`, `ims_purchase_master`.`supplier_id` AS `supplier_id`, `ims_purchase_master`.`doc_date` AS `doc_date`, `ims_purchase_master`.`create_date` AS `create_date`, `ims_purchase_master`.`status` AS `status`, `ims_purchase_master`.`KeyAddData` AS `KeyAddData`, `ims_supplier`.`supplier_name` AS `supplier_name` FROM (`ims_purchase_master` left join `ims_supplier` on(`ims_supplier`.`supplier_id` = `ims_purchase_master`.`supplier_id`)) ;

SET FOREIGN_KEY_CHECKS = 1;
