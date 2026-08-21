-- ============================================================
-- Pet Care System Database
-- ============================================================
-- Database: pet_care_system
-- Created for: University Web Application Development Project
-- ============================================================

CREATE DATABASE IF NOT EXISTS pet_care_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pet_care_system;

-- ------------------------------------------------------------
-- Table: admins  (owned by Member 1)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: services  (owned by Member 2 / read by Members 3 & 4)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_name    VARCHAR(150)   NOT NULL,
    category        VARCHAR(100)   NOT NULL,
    target_pet_type VARCHAR(100)   NOT NULL,
    description     TEXT           NOT NULL,
    price           DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    image           VARCHAR(255)            DEFAULT NULL,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: appointments  (owned by Member 3)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id      INT UNSIGNED   NOT NULL,
    customer_name   VARCHAR(150)   NOT NULL,
    customer_email  VARCHAR(150)   NOT NULL,
    customer_phone  VARCHAR(20)             DEFAULT NULL,
    pet_name        VARCHAR(100)            DEFAULT NULL,
    appointment_date DATE          NOT NULL,
    appointment_time TIME          NOT NULL,
    status          ENUM('pending','confirmed','cancelled','completed')
                                   NOT NULL DEFAULT 'pending',
    notes           TEXT                    DEFAULT NULL,
    total_amount    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    payment_status  ENUM('pending','paid','refunded')
                                   NOT NULL DEFAULT 'pending',
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_service
        FOREIGN KEY (service_id) REFERENCES services(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed Data — Services
-- ============================================================
INSERT INTO services (service_name, category, target_pet_type, description, price, image) VALUES
(
    'Premium Dog Grooming',
    'Grooming',
    'Dog',
    'Complete grooming package including bathing, professional haircut, nail trimming, ear cleaning and coat conditioning. Your dog will look and feel their absolute best after this thorough grooming session with our experienced groomers.',
    3500.00,
    'dog-grooming.jpg'
),
(
    'Basic Dog Grooming',
    'Grooming',
    'Dog',
    'Essential grooming service covering bathing, blow-drying, brushing and basic nail trimming. Perfect for regular maintenance to keep your dog clean, comfortable and healthy.',
    1800.00,
    'basic-dog-grooming.jpg'
),
(
    'Cat Grooming',
    'Grooming',
    'Cat',
    'Gentle cat grooming service including bathing, deshedding treatment, nail clipping and coat brushing. Our groomers are specially trained to handle cats with care and patience for a stress-free experience.',
    2500.00,
    'cat-grooming.jpg'
),
(
    'Veterinary Checkup',
    'Veterinary',
    'Dog',
    'Comprehensive annual health examination for dogs conducted by our qualified veterinarians. Includes full physical assessment, vaccination review, parasite check, dental inspection and personalised health advice.',
    2000.00,
    'vet-checkup.jpg'
),
(
    'Cat Veterinary Checkup',
    'Veterinary',
    'Cat',
    'Thorough health examination for cats including physical assessment, vaccination status review, parasite screening and dietary guidance. Ensures your feline companion stays healthy and happy throughout the year.',
    1800.00,
    'cat-vet-checkup.jpg'
),
(
    'Pet Boarding',
    'Boarding',
    'Dog',
    'Safe and comfortable overnight boarding for your dog in our clean, supervised facility. Includes daily feeding, fresh water, outdoor exercise sessions, playtime and attentive care from our trained staff. Your dog will be in safe hands while you are away.',
    2500.00,
    'pet-boarding.jpg'
),
(
    'Cat Boarding',
    'Boarding',
    'Cat',
    'Quiet and cosy boarding service designed specifically for cats. Your cat will have a private, comfortable space with enrichment activities, regular feeding, fresh water and gentle social interaction from our cat-friendly staff.',
    2000.00,
    'cat-boarding.jpg'
),
(
    'Small Pet Veterinary Checkup',
    'Veterinary',
    'Other',
    'Health examination service for small pets including rabbits, guinea pigs, hamsters and birds. Our experienced vets provide thorough check-ups, dietary advice and any necessary treatments to keep your small companion in top health.',
    1500.00,
    'small-pet-vet.jpg'
);
