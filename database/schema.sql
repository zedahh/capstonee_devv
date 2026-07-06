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