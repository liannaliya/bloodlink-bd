-- ============================================
-- BloodLink BD — Full Database Schema
-- Blood Donation Management System
-- ============================================

CREATE DATABASE IF NOT EXISTS bdms;
USE bdms;

-- ---- 1. DONORS TABLE ----
CREATE TABLE donors (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  phone         VARCHAR(15)  NOT NULL UNIQUE,
  email         VARCHAR(100),
  password      VARCHAR(255) NOT NULL,
  blood_group   ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  gender        ENUM('male','female','other') NOT NULL,
  date_of_birth DATE NOT NULL,
  weight        DECIMAL(5,1) NOT NULL,
  district      VARCHAR(50)  NOT NULL,
  area          VARCHAR(100) NOT NULL,
  address       TEXT,
  last_donation DATE,
  next_eligible DATE,
  is_active     TINYINT(1)  DEFAULT 1,
  is_verified   TINYINT(1)  DEFAULT 0,
  med_notes     TEXT,
  created_at    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
);

-- ---- 2. DONATIONS TABLE ----
CREATE TABLE donations (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  donor_id    INT NOT NULL,
  donated_on  DATE NOT NULL,
  location    VARCHAR(150),
  notes       TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
);

-- ---- 3. BLOOD REQUESTS TABLE ----
CREATE TABLE blood_requests (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  patient_name  VARCHAR(100) NOT NULL,
  contact_phone VARCHAR(15)  NOT NULL,
  blood_group   ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  hospital      VARCHAR(150) NOT NULL,
  district      VARCHAR(50)  NOT NULL,
  urgency       ENUM('critical','urgent','planned') NOT NULL,
  units_needed  INT DEFAULT 1,
  required_by   DATETIME,
  notes         TEXT,
  status        ENUM('active','closed') DEFAULT 'active',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- 4. ADMINS TABLE ----
CREATE TABLE admins (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(50)  NOT NULL UNIQUE,
  phone      VARCHAR(15)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- SAMPLE DATA ----
INSERT INTO admins (username, phone, password)
VALUES ('admin', '01700000000', '$2y$10$eImiTXuWVxfM37uY4JANjQ==');

INSERT INTO donors
  (full_name, phone, email, password, blood_group, gender,
   date_of_birth, weight, district, area, last_donation, next_eligible,
   is_active, is_verified)
VALUES
  ('Mohammad Rahim','01711111111','rahim@email.com',
   '$2y$10$eImiTXuWVxfM37uY4JANjQ==',
   'A+','male','1990-05-10',65,'Dhaka','Mirpur',
   '2025-12-01','2026-03-01',1,1),
  ('Fatema Begum','01722222222','fatema@email.com',
   '$2y$10$eImiTXuWVxfM37uY4JANjQ==',
   'A+','female','1995-08-20',55,'Dhaka','Gulshan',
   '2025-11-15','2026-02-13',1,1),
  ('Karim Hossain','01733333333','karim@email.com',
   '$2y$10$eImiTXuWVxfM37uY4JANjQ==',
   'B+','male','1988-03-15',70,'Dhaka','Uttara',
   '2025-10-20','2026-01-18',1,1),
  ('Rafiqul Islam','01755555555','rafiq@email.com',
   '$2y$10$eImiTXuWVxfM37uY4JANjQ==',
   'O-','male','1985-11-01',72,'Dhaka','Dhanmondi',
   '2025-08-25','2025-11-23',1,1);

INSERT INTO blood_requests
  (patient_name, contact_phone, blood_group, hospital, district,
   urgency, units_needed, status)
VALUES
  ('Mohammad Alam','01811111111','A+',
   'Dhaka Medical College','Dhaka','critical',2,'active'),
  ('Fatema Islam','01822222222','O-',
   'Square Hospital','Dhaka','urgent',1,'active'),
  ('Karim Uddin','01833333333','B+',
   'Ibn Sina Hospital','Dhaka','planned',2,'closed');