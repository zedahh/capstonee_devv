-- Barangay Santa Ines Health Monitoring System
-- Residents table (Chapter 3) + audit_logs support

CREATE TABLE IF NOT EXISTS residents (
    resident_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    qr_code         VARCHAR(64) NOT NULL UNIQUE,
    first_name      VARCHAR(100) NOT NULL,
    middle_name     VARCHAR(100) NULL,
    last_name       VARCHAR(100) NOT NULL,
    suffix          VARCHAR(10) NULL,
    birth_date      DATE NOT NULL,
    gender          ENUM('Male', 'Female') NOT NULL,
    purok           TINYINT UNSIGNED NOT NULL,
    address_line    VARCHAR(255) NULL,
    contact_number  VARCHAR(20) NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_residents_purok CHECK (purok BETWEEN 1 AND 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_residents_purok ON residents (purok);
CREATE INDEX idx_residents_name ON residents (last_name, first_name);

-- Referenced by RA 10173 audit logging convention (log_action() helper)
CREATE TABLE IF NOT EXISTS audit_logs (
    audit_log_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NULL,
    action          VARCHAR(50) NOT NULL,
    table_name      VARCHAR(64) NOT NULL,
    record_id       INT UNSIGNED NULL,
    details         TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_audit_logs_table_record ON audit_logs (table_name, record_id);


-- Users table for login (Administrator and BHW roles)
CREATE TABLE IF NOT EXISTS users (
    user_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    full_name       VARCHAR(150) NOT NULL,
    role            ENUM('administrator', 'bhw') NOT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Maternal Health Monitoring (Chapter 3, Figure 3.19)
CREATE TABLE IF NOT EXISTS maternal_records (
    maternal_record_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id         INT UNSIGNED NOT NULL,
    lmp_date            DATE NULL COMMENT 'Last menstrual period',
    edd_date             DATE NULL COMMENT 'Expected delivery date',
    gravida              TINYINT UNSIGNED NULL COMMENT 'Number of pregnancies',
    para                 TINYINT UNSIGNED NULL COMMENT 'Number of births',
    health_conditions    TEXT NULL,
    monitoring_status    ENUM('Ongoing', 'High-risk', 'Delivered', 'Postpartum') NOT NULL DEFAULT 'Ongoing',
    created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prenatal_checkups (
    checkup_id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    maternal_record_id   INT UNSIGNED NOT NULL,
    checkup_date         DATE NOT NULL,
    findings             TEXT NULL,
    next_checkup_date    DATE NULL,
    recorded_by          INT UNSIGNED NULL,
    created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (maternal_record_id) REFERENCES maternal_records(maternal_record_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Infant Monitoring (Chapter 3, Figure 3.20)
CREATE TABLE IF NOT EXISTS infant_records (
    infant_record_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id          INT UNSIGNED NOT NULL,
    mother_resident_id   INT UNSIGNED NULL,
    birth_weight_kg      DECIMAL(4,2) NULL,
    birth_length_cm      DECIMAL(4,1) NULL,
    monitoring_status    ENUM('Normal', 'Underweight', 'At risk') NOT NULL DEFAULT 'Normal',
    created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id),
    FOREIGN KEY (mother_resident_id) REFERENCES residents(resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS growth_monitoring (
    visit_id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    infant_record_id     INT UNSIGNED NOT NULL,
    visit_date           DATE NOT NULL,
    weight_kg            DECIMAL(4,2) NOT NULL,
    height_cm            DECIMAL(4,1) NULL,
    notes                TEXT NULL,
    recorded_by          INT UNSIGNED NULL,
    created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (infant_record_id) REFERENCES infant_records(infant_record_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Vaccination Records (Chapter 3, Figure 3.21) — infant vaccinations only, per scope
CREATE TABLE IF NOT EXISTS vaccination_records (
    vaccination_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    infant_record_id     INT UNSIGNED NOT NULL,
    vaccine_name         VARCHAR(100) NOT NULL,
    date_administered    DATE NOT NULL,
    administered_by      INT UNSIGNED NULL,
    notes                TEXT NULL,
    created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (infant_record_id) REFERENCES infant_records(infant_record_id),
    FOREIGN KEY (administered_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Disease and Illness Case Recording (Chapter 3, Figure 3.22)
CREATE TABLE IF NOT EXISTS disease_cases (
    case_id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resident_id       INT UNSIGNED NOT NULL,
    disease_name      VARCHAR(100) NOT NULL,
    date_reported     DATE NOT NULL,
    status            ENUM('Active', 'Under monitoring', 'Recovered') NOT NULL DEFAULT 'Active',
    notes             TEXT NULL,
    recorded_by       INT UNSIGNED NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_disease_name_date ON disease_cases (disease_name, date_reported);


-- Public Announcement Management (Chapter 3)
CREATE TABLE IF NOT EXISTS announcements (
    announcement_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title              VARCHAR(150) NOT NULL,
    content            TEXT NOT NULL,
    target_purok       VARCHAR(20) NOT NULL DEFAULT 'All',
    is_active          TINYINT(1) NOT NULL DEFAULT 1,
    posted_by          INT UNSIGNED NULL,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;