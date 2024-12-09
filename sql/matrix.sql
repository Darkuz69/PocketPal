-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 09, 2024 at 02:49 PM
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
-- Database: `matrix`
--

-- --------------------------------------------------------

--
-- Table structure for table `Account`
--

CREATE TABLE `Account` (
  `AccountID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Allowance`
--

CREATE TABLE `Allowance` (
  `AllowanceID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `SourceID` int(11) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `AllowanceAmount` double(10,2) NOT NULL,
  `AllowanceDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AllowanceSource`
--

CREATE TABLE `AllowanceSource` (
  `SourceID` int(11) NOT NULL,
  `SourceName` varchar(50) NOT NULL,
  `Description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `AllowanceSource`
--

INSERT INTO `AllowanceSource` (`SourceID`, `SourceName`, `Description`) VALUES
(1, 'Family Support', 'Money provided by parents, guardians, or relatives.'),
(2, 'Part-Time Jobs', 'Earnings from working in retail, food service, tutoring, or similar roles.'),
(3, 'Scholarships', 'Financial awards for academic, athletic, or artistic achievements.'),
(4, 'Government Assistance', 'Grants, subsidies, or stipends from government programs.'),
(5, 'Financial Aid', 'Need-based assistance from schools or organizations.'),
(6, 'Savings', 'Personal funds saved or set aside for daily and educational expenses.'),
(7, 'Small Businesses', 'Income from entrepreneurial activities like selling goods or services.');

-- --------------------------------------------------------

--
-- Table structure for table `Expense`
--

CREATE TABLE `Expense` (
  `ExpenseID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `ExpenseCategoryID` int(11) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `ExpenseAmount` double(10,2) NOT NULL,
  `ExpenseDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ExpenseCategory`
--

CREATE TABLE `ExpenseCategory` (
  `ExpenseCategoryID` int(11) NOT NULL,
  `CategoryName` varchar(50) DEFAULT NULL,
  `Description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ExpenseCategory`
--

INSERT INTO `ExpenseCategory` (`ExpenseCategoryID`, `CategoryName`, `Description`) VALUES
(1, 'Tuition and Fees', 'Costs directly related to academic enrollment and course participation'),
(2, 'Housing', 'Expenses for living accommodations, including rent and utilities'),
(3, 'Food', 'Meal costs, including groceries, dining out, and meal plans'),
(4, 'Textbooks and School Supplies', 'Required learning materials and academic resources'),
(5, 'Transportation', 'Costs for getting to and from school and other activities'),
(6, 'Personal Care and Health', 'Medical expenses, hygiene products, and wellness-related costs'),
(7, 'Technology', 'Electronic devices, software, and digital tools for academic use'),
(8, 'Entertainment', 'Recreational activities and leisure expenses'),
(9, 'Clothing', 'Apparel and accessories'),
(10, 'Academic Extras', 'Additional academic-related expenses beyond core supplies'),
(11, 'Miscellaneous', 'Unclassified or unexpected expenses that do not fit into other categories');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `UserID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `MiddleInitial` varchar(1) DEFAULT NULL,
  `Suffix` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Account`
--
ALTER TABLE `Account`
  ADD PRIMARY KEY (`AccountID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `Allowance`
--
ALTER TABLE `Allowance`
  ADD PRIMARY KEY (`AllowanceID`),
  ADD KEY `AccountID` (`AccountID`),
  ADD KEY `SourceID` (`SourceID`);

--
-- Indexes for table `AllowanceSource`
--
ALTER TABLE `AllowanceSource`
  ADD PRIMARY KEY (`SourceID`);

--
-- Indexes for table `Expense`
--
ALTER TABLE `Expense`
  ADD PRIMARY KEY (`ExpenseID`),
  ADD KEY `AccountID` (`AccountID`),
  ADD KEY `ExpenseCategoryID` (`ExpenseCategoryID`);

--
-- Indexes for table `ExpenseCategory`
--
ALTER TABLE `ExpenseCategory`
  ADD PRIMARY KEY (`ExpenseCategoryID`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Account`
--
ALTER TABLE `Account`
  MODIFY `AccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Allowance`
--
ALTER TABLE `Allowance`
  MODIFY `AllowanceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `AllowanceSource`
--
ALTER TABLE `AllowanceSource`
  MODIFY `SourceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Expense`
--
ALTER TABLE `Expense`
  MODIFY `ExpenseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ExpenseCategory`
--
ALTER TABLE `ExpenseCategory`
  MODIFY `ExpenseCategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Account`
--
ALTER TABLE `Account`
  ADD CONSTRAINT `Account_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`);

--
-- Constraints for table `Allowance`
--
ALTER TABLE `Allowance`
  ADD CONSTRAINT `Allowance_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `Account` (`AccountID`),
  ADD CONSTRAINT `Allowance_ibfk_2` FOREIGN KEY (`SourceID`) REFERENCES `AllowanceSource` (`SourceID`);

--
-- Constraints for table `Expense`
--
ALTER TABLE `Expense`
  ADD CONSTRAINT `Expense_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `Account` (`AccountID`),
  ADD CONSTRAINT `Expense_ibfk_2` FOREIGN KEY (`ExpenseCategoryID`) REFERENCES `ExpenseCategory` (`ExpenseCategoryID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
