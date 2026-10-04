-- =========================================================
-- MACRO ACCESS DATABASE
-- MariaDB / MySQL
-- =========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =========================================================
-- CREATE DATABASE
-- =========================================================

CREATE DATABASE IF NOT EXISTS `macro_access`
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE `macro_access`;

-- =========================================================
-- REMOVE OLD TABLES
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `test_records`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- TABLE: users
-- =========================================================

CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `full_name` varchar(255) DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `position` enum(
        'ADMIN',
        'CHIEF TECHNOLOGIST',
        'STAFF'
    ) NOT NULL DEFAULT 'STAFF',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `reset_token` varchar(255) DEFAULT NULL,
    `reset_requested` timestamp NULL DEFAULT NULL,
    `reset_approved` tinyint(1) NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
AUTO_INCREMENT=2;

-- =========================================================
-- DEFAULT ADMIN ACCOUNT
-- =========================================================

INSERT INTO `users`
(
    `id`,
    `username`,
    `full_name`,
    `password`,
    `position`,
    `created_at`
)
VALUES
(
    1,
    'admin',
    'Administrator',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'ADMIN',
    '2026-05-08 16:08:55'
);

-- =========================================================
-- TABLE: test_records
-- =========================================================

CREATE TABLE `test_records` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `client_name` varchar(100) NOT NULL,
    `age` int(3) DEFAULT NULL,
    `sex` char(1) DEFAULT NULL,
    `birth_date` date DEFAULT NULL,
    `company_name` varchar(150) DEFAULT NULL,

    `meth_result` enum(
        'NEGATIVE',
        'POSITIVE',
        'INVALID'
    ) NOT NULL DEFAULT 'NEGATIVE',

    `thc_result` enum(
        'NEGATIVE',
        'POSITIVE',
        'INVALID'
    ) NOT NULL DEFAULT 'NEGATIVE',

    `photo_path` varchar(255) DEFAULT NULL,
    `date_tested` datetime NOT NULL DEFAULT current_timestamp(),
    `added_by` int(11) DEFAULT NULL,
    `is_archived` tinyint(1) NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),

    KEY `idx_added_by` (`added_by`),
    KEY `idx_date_tested` (`date_tested`),
    KEY `idx_is_archived` (`is_archived`),

    CONSTRAINT `test_records_ibfk_1`
        FOREIGN KEY (`added_by`)
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
AUTO_INCREMENT=11;

-- =========================================================
-- SAMPLE TEST RECORDS
-- =========================================================

INSERT INTO `test_records`
(
    `client_name`,
    `age`,
    `sex`,
    `birth_date`,
    `company_name`,
    `meth_result`,
    `thc_result`,
    `date_tested`,
    `added_by`,
    `is_archived`
)
VALUES
(
    'Jepoy Dimagiba',
    25,
    'M',
    '1999-01-15',
    'Jollibee Foods Corp',
    'NEGATIVE',
    'NEGATIVE',
    NOW(),
    1,
    0
),
(
    'Marites Dela Cruz',
    42,
    'F',
    '1984-03-22',
    'SM Supermarket',
    'POSITIVE',
    'NEGATIVE',
    NOW(),
    1,
    0
),
(
    'Ramon Tolentino',
    35,
    'M',
    '1989-07-08',
    'Meralco',
    'NEGATIVE',
    'POSITIVE',
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    1,
    0
),
(
    'Teresita Macapagal',
    28,
    'F',
    '1996-08-14',
    'Philippine Airlines',
    'POSITIVE',
    'POSITIVE',
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    1,
    0
),
(
    'Roberto Samonte',
    50,
    'M',
    '1974-01-20',
    'San Miguel Corp',
    'NEGATIVE',
    'NEGATIVE',
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    1,
    0
),
(
    'Crisanta Reyes',
    31,
    'F',
    '1995-05-12',
    'Globe Telecom',
    'POSITIVE',
    'POSITIVE',
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    1,
    0
),
(
    'Andres Bonifacio',
    45,
    'M',
    '1979-11-30',
    'LBC Express',
    'NEGATIVE',
    'NEGATIVE',
    DATE_SUB(NOW(), INTERVAL 3 DAY),
    1,
    0
),
(
    'Luzviminda Hernandez',
    38,
    'F',
    '1986-09-25',
    'Puregold',
    'POSITIVE',
    'NEGATIVE',
    DATE_SUB(NOW(), INTERVAL 3 DAY),
    1,
    0
),
(
    'Gregorio Fernandez',
    29,
    'M',
    '1997-02-18',
    'BDO Unibank',
    'NEGATIVE',
    'POSITIVE',
    DATE_SUB(NOW(), INTERVAL 4 DAY),
    1,
    0
),
(
    'Herminia Santos',
    52,
    'F',
    '1972-07-04',
    'Mercury Drug',
    'POSITIVE',
    'POSITIVE',
    DATE_SUB(NOW(), INTERVAL 4 DAY),
    1,
    0
);

-- =========================================================
-- FINISH
-- =========================================================

COMMIT;

-- =========================================================
-- OPTIONAL CHECK
-- =========================================================

SELECT * FROM `users`;

SELECT * FROM `test_records`;
