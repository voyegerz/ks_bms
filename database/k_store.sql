-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 05:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `k_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `account_id` int(11) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_holder_name` varchar(255) NOT NULL,
  `account_number` varchar(255) NOT NULL,
  `ifsc_code` varchar(50) DEFAULT NULL,
  `branch_address` text DEFAULT NULL,
  `is_default` int(1) NOT NULL DEFAULT 0 COMMENT '1 = Default, 0 = Not Default',
  `chart_of_account_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`account_id`, `bank_name`, `account_holder_name`, `account_number`, `ifsc_code`, `branch_address`, `is_default`, `chart_of_account_id`) VALUES
(4, 'CASH', 'NK', '1234567891', '34HBG321', '', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `billed_challan_items`
--

CREATE TABLE `billed_challan_items` (
  `id` int(11) NOT NULL,
  `challan_item_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `billed_quantity` int(11) NOT NULL,
  `billed_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `brand_active` int(11) NOT NULL DEFAULT 0,
  `brand_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_active`, `brand_status`) VALUES
(1, 'Example Brand', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categories_id` int(11) NOT NULL,
  `categories_name` varchar(255) NOT NULL,
  `categories_active` int(11) NOT NULL DEFAULT 0,
  `categories_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categories_id`, `categories_name`, `categories_active`, `categories_status`) VALUES
(1, 'Example Category', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `challans`
--

CREATE TABLE `challans` (
  `challan_id` int(11) NOT NULL,
  `challan_date` date NOT NULL,
  `challan_no` varchar(255) DEFAULT NULL,
  `party_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL COMMENT 'Optional: Links to an existing order if challan is generated from one',
  `user_id` int(11) NOT NULL COMMENT 'User who created the challan',
  `challan_status` int(11) NOT NULL DEFAULT 0 COMMENT '0: Pending, 1: Dispatched, 2: Delivered, 3: Cancelled, 4: Billed',
  `remarks` text DEFAULT NULL COMMENT 'Any additional notes or instructions for the challan'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `challans`
--

INSERT INTO `challans` (`challan_id`, `challan_date`, `challan_no`, `party_id`, `order_id`, `user_id`, `challan_status`, `remarks`) VALUES
(1, '2025-11-10', 'EXAM/1', 1, NULL, 1, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `challan_items`
--

CREATE TABLE `challan_items` (
  `challan_item_id` int(11) NOT NULL,
  `challan_id` int(11) NOT NULL COMMENT 'Foreign key to the challans table',
  `product_id` int(11) NOT NULL COMMENT 'Foreign key to the product table',
  `quantity` varchar(255) NOT NULL COMMENT 'Quantity of the product dispatched in this challan',
  `size` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `challan_item_status` int(11) NOT NULL DEFAULT 0 COMMENT '0: Pending, 1: Dispatched'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `challan_items`
--

INSERT INTO `challan_items` (`challan_item_id`, `challan_id`, `product_id`, `quantity`, `size`, `remarks`, `challan_item_status`) VALUES
(1, 1, 1, '1', NULL, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `account_id` int(11) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_type` enum('Asset','Liability','Equity','Revenue','Expense') NOT NULL,
  `party_id` int(11) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `chart_of_accounts`
--

INSERT INTO `chart_of_accounts` (`account_id`, `account_name`, `account_type`, `party_id`, `is_active`) VALUES
(1, 'Example Party', 'Asset', 1, 1),
(4, 'CASH (7891)', 'Asset', NULL, 1),
(5, 'Cash on Hand', 'Asset', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `journal_entry_lines`
--

CREATE TABLE `journal_entry_lines` (
  `line_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `journal_entry_lines`
--

INSERT INTO `journal_entry_lines` (`line_id`, `voucher_id`, `account_id`, `debit`, `credit`) VALUES
(10, 6, 4, 100.00, 0.00),
(11, 6, 5, 0.00, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `journal_vouchers`
--

CREATE TABLE `journal_vouchers` (
  `voucher_id` int(11) NOT NULL,
  `voucher_no` int(11) NOT NULL,
  `voucher_date` date NOT NULL,
  `description` text NOT NULL COMMENT 'Narration for the entire entry',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `journal_vouchers`
--

INSERT INTO `journal_vouchers` (`voucher_id`, `voucher_no`, `voucher_date`, `description`, `user_id`, `created_at`) VALUES
(6, 1, '2025-11-10', 'Opening Balence set', 1, '2025-11-10 15:25:50');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `other_po_no` varchar(255) DEFAULT NULL,
  `other_po_date` date DEFAULT NULL,
  `transport_name` varchar(255) DEFAULT NULL,
  `transport_gst_no` varchar(20) DEFAULT NULL,
  `ref_challan_nos` varchar(255) DEFAULT NULL,
  `order_date` date NOT NULL,
  `party_id` int(11) DEFAULT NULL,
  `sub_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_type` int(11) NOT NULL COMMENT '1=Cheque, 2=Cash, 3=Credit Card, 4=UPI, 5=Debit Card, 6=Netbanking',
  `payment_status` int(11) NOT NULL,
  `payment_place` int(11) NOT NULL,
  `order_status` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `invoice_no`, `other_po_no`, `other_po_date`, `transport_name`, `transport_gst_no`, `ref_challan_nos`, `order_date`, `party_id`, `sub_total`, `vat`, `total_amount`, `discount`, `grand_total`, `paid`, `due`, `payment_type`, `payment_status`, `payment_place`, `order_status`, `user_id`) VALUES
(1, '1', '', NULL, 'Example Transport', '1234567890', NULL, '2025-11-10', 1, 100.00, 18.00, 118.00, 0.00, 118.00, 118.00, 0.00, 2, 1, 1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `quantity` varchar(255) NOT NULL,
  `size` varchar(255) DEFAULT NULL,
  `rate` varchar(255) NOT NULL,
  `total` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `order_item_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `product_id`, `quantity`, `size`, `rate`, `total`, `remarks`, `order_item_status`) VALUES
(1, 1, 1, '1', NULL, '100', '100.00', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `partys`
--

CREATE TABLE `partys` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `contact_no` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `shipping_addr` text DEFAULT NULL,
  `billing_addr` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `partys`
--

INSERT INTO `partys` (`id`, `name`, `gstin`, `contact_no`, `email`, `shipping_addr`, `billing_addr`) VALUES
(1, 'Example Party', '12345678901', '1234567890', 'Example@gmail.com', 'Example Shipping Address', 'Example Address');

-- --------------------------------------------------------

--
-- Table structure for table `party_ledger`
--

CREATE TABLE `party_ledger` (
  `id` int(11) NOT NULL,
  `party_id` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `description` varchar(255) NOT NULL,
  `transaction_type` enum('Invoice','Payment','Credit Note','Debit Note','Opening Balance') NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `debit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `hsn` varchar(20) DEFAULT NULL,
  `product_image` text NOT NULL,
  `brand_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `rate` varchar(255) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `manage_stock` int(1) NOT NULL DEFAULT 1 COMMENT '1 = Yes, 0 = No'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `hsn`, `product_image`, `brand_id`, `categories_id`, `quantity`, `rate`, `active`, `status`, `manage_stock`) VALUES
(1, 'Example Product', '1234', '../assests/images/photo_default.png', 1, 1, '0', '100', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `po_number` varchar(255) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `sub_total` decimal(10,2) NOT NULL,
  `vat` decimal(10,2) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `paid` decimal(10,2) NOT NULL,
  `due` decimal(10,2) NOT NULL,
  `payment_status` int(11) NOT NULL COMMENT '1=Full, 2=Advance, 3=None',
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `purchase_item_id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `quotation_id` int(11) NOT NULL,
  `quotation_date` date NOT NULL,
  `party_id` int(11) NOT NULL,
  `sub_total` decimal(10,2) NOT NULL,
  `vat` decimal(10,2) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `quotation_status` int(11) NOT NULL DEFAULT 1 COMMENT '1=Pending, 2=Approved, 3=Rejected',
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `quotation_item_id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `rate` varchar(255) NOT NULL,
  `total` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `contact_no` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `cgst` decimal(5,2) DEFAULT NULL COMMENT 'Central GST rate percentage',
  `sgst` decimal(5,2) DEFAULT NULL COMMENT 'State GST rate percentage',
  `company_addr` text DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `contact_no` varchar(255) DEFAULT NULL,
  `company_bank_details` text DEFAULT NULL COMMENT 'Stores formatted bank details text',
  `state_name` varchar(255) DEFAULT NULL,
  `state_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `company_name`, `gstin`, `cgst`, `sgst`, `company_addr`, `company_email`, `contact_no`, `company_bank_details`, `state_name`, `state_code`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '', 'MOKSHA ENTERPRISE', '24THVPS6487L1ZN', 9.00, 9.00, 'PLOT NO. 1/P/1,  NAKODA ESTATE -2, SATHROTA ROAD, PRATAPPURA (PART), Halol, Panchmahals, 389350', 'nirajkumaruiflow@gmail.com', '+919904797336', 'A/c Holder\'s Name: MOKSHA ENTERPRISE\r\nBank Name: KOTAK MAHINDRA BANK LTD\r\nA/c No.: 9999911162\r\nBranch & IFS Code: HALOL & KKBK0000844', 'Gujarat', '24'),
(11, 'nk', '7220d65820839700b6c9ae74f87b48e0', 'nirajkumaruiflow@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `fk_bank_chart_of_account_id` (`chart_of_account_id`);

--
-- Indexes for table `billed_challan_items`
--
ALTER TABLE `billed_challan_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_billed_challan_item_id` (`challan_item_id`),
  ADD KEY `fk_billed_order_item_id` (`order_item_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categories_id`);

--
-- Indexes for table `challans`
--
ALTER TABLE `challans`
  ADD PRIMARY KEY (`challan_id`),
  ADD UNIQUE KEY `unique_party_challan` (`party_id`,`challan_no`),
  ADD KEY `fk_challans_order_id` (`order_id`),
  ADD KEY `fk_challans_user_id` (`user_id`);

--
-- Indexes for table `challan_items`
--
ALTER TABLE `challan_items`
  ADD PRIMARY KEY (`challan_item_id`),
  ADD KEY `fk_challan_items_challan_id` (`challan_id`),
  ADD KEY `fk_challan_items_product_id` (`product_id`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  ADD PRIMARY KEY (`line_id`),
  ADD KEY `fk_line_voucher_id` (`voucher_id`),
  ADD KEY `fk_line_account_id` (`account_id`);

--
-- Indexes for table `journal_vouchers`
--
ALTER TABLE `journal_vouchers`
  ADD PRIMARY KEY (`voucher_id`),
  ADD UNIQUE KEY `voucher_no` (`voucher_no`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `fk_orders_party_id` (`party_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`);

--
-- Indexes for table `partys`
--
ALTER TABLE `partys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_gstin` (`gstin`);

--
-- Indexes for table `party_ledger`
--
ALTER TABLE `party_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ledger_party_id` (`party_id`),
  ADD KEY `fk_ledger_order_id` (`order_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `fk_purchase_supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`purchase_item_id`),
  ADD KEY `fk_purchase_item_purchase_id` (`purchase_id`),
  ADD KEY `fk_purchase_item_product_id` (`product_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`quotation_id`),
  ADD KEY `fk_quote_party_id` (`party_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`quotation_item_id`),
  ADD KEY `fk_quote_item_quote_id` (`quotation_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `billed_challan_items`
--
ALTER TABLE `billed_challan_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categories_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `challans`
--
ALTER TABLE `challans`
  MODIFY `challan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `challan_items`
--
ALTER TABLE `challan_items`
  MODIFY `challan_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  MODIFY `line_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `journal_vouchers`
--
ALTER TABLE `journal_vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `partys`
--
ALTER TABLE `partys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `party_ledger`
--
ALTER TABLE `party_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `purchase_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `quotation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `quotation_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD CONSTRAINT `fk_bank_chart_of_account_id` FOREIGN KEY (`chart_of_account_id`) REFERENCES `chart_of_accounts` (`account_id`) ON DELETE SET NULL;

--
-- Constraints for table `billed_challan_items`
--
ALTER TABLE `billed_challan_items`
  ADD CONSTRAINT `fk_billed_challan_item_id` FOREIGN KEY (`challan_item_id`) REFERENCES `challan_items` (`challan_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_billed_order_item_id` FOREIGN KEY (`order_item_id`) REFERENCES `order_item` (`order_item_id`) ON DELETE CASCADE;

--
-- Constraints for table `challans`
--
ALTER TABLE `challans`
  ADD CONSTRAINT `fk_challans_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_challans_party_id` FOREIGN KEY (`party_id`) REFERENCES `partys` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_challans_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `challan_items`
--
ALTER TABLE `challan_items`
  ADD CONSTRAINT `fk_challan_items_challan_id` FOREIGN KEY (`challan_id`) REFERENCES `challans` (`challan_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_challan_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  ADD CONSTRAINT `fk_line_account_id` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`account_id`),
  ADD CONSTRAINT `fk_line_voucher_id` FOREIGN KEY (`voucher_id`) REFERENCES `journal_vouchers` (`voucher_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_party_id` FOREIGN KEY (`party_id`) REFERENCES `partys` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `party_ledger`
--
ALTER TABLE `party_ledger`
  ADD CONSTRAINT `fk_ledger_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ledger_party_id` FOREIGN KEY (`party_id`) REFERENCES `partys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchase_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `fk_purchase_item_product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_purchase_item_purchase_id` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`purchase_id`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `fk_quote_party_id` FOREIGN KEY (`party_id`) REFERENCES `partys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `fk_quote_item_quote_id` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`quotation_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
