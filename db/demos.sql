
CREATE TABLE `ims_brand` (
  `id` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `bname` varchar(250) NOT NULL,
  `status` enum('active','inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ims_brand`
--

INSERT INTO `ims_brand` (`id`, `categoryid`, `bname`, `status`) VALUES
(2, 1, 'Apple', 'active'),
(4, 2, 'Samsung', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `ims_category`
--

CREATE TABLE `ims_category` (
  `categoryid` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `status` enum('active','inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ims_category`
--

INSERT INTO `ims_category` (`categoryid`, `name`, `status`) VALUES
(1, 'Mobile', 'active'),
(2, 'Laptop', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `ims_customer`
--

CREATE TABLE `ims_customer` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `mobile` int(50) NOT NULL,
  `balance` double(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ims_customer`
--

INSERT INTO `ims_customer` (`id`, `name`, `address`, `mobile`, `balance`) VALUES
(1, 'Smith', 'London,UK', 123456789, 120000.00),
(2, 'Adam', 'Newyork, USA', 987654321, 200000.00),
(4, 'Garry', 'Paris, France', 2147483647, 560000.00);

-- --------------------------------------------------------

--
-- Table structure for table `ims_order`
--

CREATE TABLE `ims_order` (
  `order_id` int(11) NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `total_shipped` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ims_order`
--

INSERT INTO `ims_order` (`order_id`, `product_id`, `total_shipped`, `customer_id`, `order_date`) VALUES
(2, '2', 50, 4, '2019-04-15 19:16:33'),
(3, '1', 20, 2, '2019-04-16 17:48:29');

-- --------------------------------------------------------

--
-- Table structure for table `ims_product`
--

CREATE TABLE `ims_product` (
  `pid` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `brandid` int(11) NOT NULL,
  `pname` varchar(300) NOT NULL,
  `model` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(150) NOT NULL,
  `base_price` double(10,2) NOT NULL,
  `tax` decimal(4,2) NOT NULL,
  `minimum_order` double(10,2) NOT NULL,
  `supplier` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ims_product`
--

INSERT INTO `ims_product` (`pid`, `categoryid`, `brandid`, `pname`, `model`, `description`, `quantity`, `unit`, `base_price`, `tax`, `minimum_order`, `supplier`, `status`, `date`) VALUES
(1, 1, 2, 'iPhone 6', 'm-6s', 'iphone 6s mobile', 10, 'Nos', 30000.00, '5.00', 1.00, 1, 'active', '2019-04-14'),
(2, 2, 4, 'samsung laptop', 'sm-03', 'ddsgds', 5, 'Nos', 35000.00, '2.00', 1.00, 1, 'active', '0000-00-00'),
(3, 1, 2, 'iphone 7', 'ph-07', 'iphone 7 mobile', 13, 'Nos', 65000.00, '2.00', 1.00, 1, 'active', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `ims_purchase`
--

CREATE TABLE `ims_purchase` (
  `purchase_id` int(11) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ims_purchase`
--

INSERT INTO `ims_purchase` (`purchase_id`, `supplier_id`, `product_id`, `quantity`, `purchase_date`) VALUES
(5, '1', '3', '45', '2019-04-15 18:46:36'),
(6, '3', '2', '13', '2019-04-15 18:55:51'),
(7, '4', '1', '5', '2019-04-15 18:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `ims_supplier`
--

CREATE TABLE `ims_supplier` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(200) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `status` enum('active','inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ims_supplier`
--

INSERT INTO `ims_supplier` (`supplier_id`, `supplier_name`, `mobile`, `address`, `status`) VALUES
(1, 'Grant', '574575373734', 'Africa', 'active'),
(3, 'David d', '11111111111', 'New Delhi', 'active'),
(4, 'Warner', '55555555555', 'Australia', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `ims_user`
--

CREATE TABLE `ims_user` (
  `userid` int(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` enum('admin','member') NOT NULL,
  `status` enum('Active','Inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ims_user`
--

INSERT INTO `ims_user` (`userid`, `email`, `password`, `name`, `type`, `status`) VALUES
(1, 'admin@phpzag.com', '202cb962ac59075b964b07152d234b70', 'phpzag', 'admin', 'Active'),
(2, 'laeeq@phpag.com', '202cb962ac59075b964b07152d234b70', 'Laeeq khan', 'member', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ims_brand`
--
ALTER TABLE `ims_brand`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ims_category`
--
ALTER TABLE `ims_category`
  ADD PRIMARY KEY (`categoryid`);

--
-- Indexes for table `ims_customer`
--
ALTER TABLE `ims_customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ims_order`
--
ALTER TABLE `ims_order`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `ims_product`
--
ALTER TABLE `ims_product`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `ims_purchase`
--
ALTER TABLE `ims_purchase`
  ADD PRIMARY KEY (`purchase_id`);

--
-- Indexes for table `ims_supplier`
--
ALTER TABLE `ims_supplier`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `ims_user`
--
ALTER TABLE `ims_user`
  ADD PRIMARY KEY (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ims_brand`
--
ALTER TABLE `ims_brand`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `ims_category`
--
ALTER TABLE `ims_category`
  MODIFY `categoryid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `ims_customer`
--
ALTER TABLE `ims_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `ims_order`
--
ALTER TABLE `ims_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `ims_product`
--
ALTER TABLE `ims_product`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `ims_purchase`
--
ALTER TABLE `ims_purchase`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `ims_supplier`
--
ALTER TABLE `ims_supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `ims_user`
--
ALTER TABLE `ims_user`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

