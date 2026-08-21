-- ============================================================
-- Pet Care System - Database Schema
-- University Web Application Development Project
-- Member 1: Core Auth & Database Schema
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS `pet_care_system`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `pet_care_system`;

-- ============================================================
-- Table: admins
-- Stores administrator accounts (passwords are always hashed)
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `username`   VARCHAR(50)  NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: services
-- Stores all pet care services offered
-- ============================================================
CREATE TABLE IF NOT EXISTS `services` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `service_name`    VARCHAR(100) NOT NULL,
    `category`        VARCHAR(50)  NOT NULL,
    `target_pet_type` VARCHAR(50)  NOT NULL,
    `description`     TEXT         NOT NULL,
    `price`           DECIMAL(10,2) NOT NULL,
    `image`           VARCHAR(255)  NULL,
    `created_at`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: appointments
-- Stores customer appointment bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS `appointments` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `service_id`       INT           NOT NULL,
    `customer_name`    VARCHAR(100)  NOT NULL,
    `customer_email`   VARCHAR(150)  NOT NULL,
    `customer_phone`   VARCHAR(20)   NOT NULL,
    `pet_name`         VARCHAR(100)  NOT NULL,
    `breed`            VARCHAR(100)  NOT NULL,
    `age`              VARCHAR(20)   NOT NULL,
    `appointment_date` DATE          NOT NULL,
    `booking_status`   VARCHAR(50)   NOT NULL DEFAULT 'Pending',
    `created_at`       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_appointments_service`
        FOREIGN KEY (`service_id`) REFERENCES `services`(`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: payments
-- Stores payment records linked to appointments
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id`        INT            NOT NULL,
    `amount`                DECIMAL(10,2)  NOT NULL,
    `payment_method`        VARCHAR(50)    NOT NULL,
    `transaction_reference` VARCHAR(100)   NULL,
    `payment_status`        VARCHAR(50)    NOT NULL DEFAULT 'Pending',
    `paid_at`               TIMESTAMP      NULL,
    CONSTRAINT `fk_payments_appointment`
        FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Sample Services
-- Added with INSERT IGNORE to prevent duplicates on re-run
-- ============================================================
INSERT IGNORE INTO `services`
    (`id`, `service_name`, `category`, `target_pet_type`, `description`, `price`, `image`)
VALUES
(1,
 'Premium Dog Grooming',
 'Grooming',
 'Dog',
 'A comprehensive grooming session for your dog including a relaxing bath with premium shampoo, blow-dry, coat trimming, nail clipping, ear cleaning, and a fresh bandana finish. Keeps your dog looking and feeling their best.',
 3500.00,
 'dog-grooming.jpg'),

(2,
 'Cat Grooming',
 'Grooming',
 'Cat',
 'A gentle and thorough grooming service tailored for cats, including a soothing bath, blow-dry, coat brushing and trimming, nail trimming, and ear cleaning. Our experienced groomers handle even shy cats with care.',
 3000.00,
 'cat-grooming.jpg'),

(3,
 'Veterinary Checkup for Dogs',
 'Veterinary',
 'Dog',
 'A complete health examination for your dog conducted by our licensed veterinarian. Includes physical assessment, vital signs check, vaccination review, dietary advice, and a written health report for your records.',
 2500.00,
 'vet-checkup-dog.jpg'),

(4,
 'Pet Boarding',
 'Boarding',
 'Dog',
 'Safe and comfortable overnight boarding for your dog while you are away. Includes a clean private kennel, 3 meals per day, fresh water, supervised play sessions, and daily health checks by our trained staff.',
 4500.00,
 'pet-boarding.jpg'),

(5,
 'Cat Veterinary Checkup',
 'Veterinary',
 'Cat',
 'A thorough health examination for your cat by our licensed veterinarian. Covers full physical assessment, dental check, parasite screening, vaccination status review, and personalised diet and care advice.',
 2500.00,
 'vet-checkup-cat.jpg');
