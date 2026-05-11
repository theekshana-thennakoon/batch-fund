-- Create Loans Table
CREATE TABLE IF NOT EXISTS `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `reason` text NOT NULL,
  `total` decimal(10, 2) NOT NULL,
  `paid` decimal(10, 2) NOT NULL DEFAULT 0,
  `balance` decimal(10, 2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
