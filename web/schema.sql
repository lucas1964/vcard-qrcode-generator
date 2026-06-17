-- Eseguire una volta sul database prima del primo avvio
-- mysql -u utente_db -p nome_database < schema.sql

CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(255) NOT NULL UNIQUE,
    is_admin   TINYINT(1)  NOT NULL DEFAULT 0,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS profiles (
    user_id    INT UNSIGNED PRIMARY KEY,
    first_name VARCHAR(100) DEFAULT '',
    last_name  VARCHAR(100) DEFAULT '',
    org        VARCHAR(150) DEFAULT '',
    title      VARCHAR(150) DEFAULT '',
    tel_work   VARCHAR(50)  DEFAULT '',
    tel_cell   VARCHAR(50)  DEFAULT '',
    email      VARCHAR(255) DEFAULT '',
    web        VARCHAR(255) DEFAULT '',
    street     VARCHAR(255) DEFAULT '',
    city       VARCHAR(100) DEFAULT '',
    province   VARCHAR(100) DEFAULT '',
    zip        VARCHAR(20)  DEFAULT '',
    country    VARCHAR(100) DEFAULT '',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS otp_tokens (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64)  NOT NULL,
    expires_at DATETIME    NOT NULL,
    used       TINYINT(1)  NOT NULL DEFAULT 0,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS logs (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    event      VARCHAR(50)  NOT NULL,
    detail     JSON         NULL,
    ip         VARCHAR(45)  NOT NULL DEFAULT '',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event),
    INDEX idx_user  (user_id),
    INDEX idx_time  (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
