--
-- Rest Database
--
-- Driver: MySQL
-- ------------------------------


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` BIGINT(11) NOT NULL,
  `option_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Unique Option Name',
  `option_value` LONGTEXT COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_name` (`option_name`);

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` BIGINT(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` BIGINT(11) NOT NULL,
  `first_name` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `username` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` VARCHAR(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'sha1 string PasswordHash - (phpass by openwall)',
  `private_key` VARCHAR(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT  'Private Grant token API',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT '1990-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT 'use `1990-01-01 00:00:00` to prevent error sql time stamp zero value'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_2` (`username`,`email`),
  ADD KEY `username` (`username`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` BIGINT(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Table structure for table `users_meta`
--
CREATE TABLE `users_meta` (
  `id` BIGINT(11) NOT NULL,
  `user_id` BIGINT(11) NOT NULL,
  `meta_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_value` LONGTEXT COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `users_meta`
--
ALTER TABLE `users_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for table `users_meta`
--
ALTER TABLE `users_meta`
  MODIFY `id` BIGINT(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- MODULES
-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` BIGINT(11) NOT NULL,
  `user_id` BIGINT(11) NOT NULL COMMENT 'Relation for `users.id`',
  `title` VARCHAR(160) COLLATE utf8_unicode_ci NOT NULL,
  `slug`  VARCHAR(160) COLLATE utf8_unicode_ci NOT NULL,
  `instructions` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `status` INT(11) NOT NULL DEFAULT '1',
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT '1990-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT 'use `1990-01-01 00:00:00` to prevent error sql time stamp zero value'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE INDEX `unique_slug`(`slug`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;