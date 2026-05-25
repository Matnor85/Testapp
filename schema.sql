CREATE DATABASE IF NOT EXISTS atm_db
    CHARACTER SET utf8mb4
        COLLATE utf8mb4_swedish_ci;

USE atm_db;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100)    NOT NULL,
    card_number   CHAR(16)        NOT NULL,          -- 16-siffrig kortnummer   Kan behövas att flytta till accounts
    pin_hash      VARCHAR(255)    NOT NULL,          -- bcrypt-hash             Kan behövas att flytta till accounts 
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_card_number (card_number)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS accounts (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED    NOT NULL,
    account_type  ENUM('checking','savings', 'fixed', 'credit') NOT NULL DEFAULT 'checking',
    credit_limit  DECIMAL(12,2)  NULL DEFAULT NULL,  -- NULL för icke-kreditkonton
    interest_rate DECIMAL(5,2)    NOT NULL DEFAULT 0.00, -- årlig ränta i procent
    locked_until  DATE            NULL DEFAULT NULL,       -- datum när bindningstiden går ut (NULL för checking/savings)
    active        TINYINT(1)      NOT NULL DEFAULT 1,    -- 1 = aktivt konto, 0 = inaktivt
    numbers_of_trys INT UNSIGNED    NOT NULL DEFAULT 0,    -- antal misslyckade försök att använda kortet
    balance       DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP, -- när kontot skapades 

    PRIMARY KEY (id),
    CONSTRAINT fk_accounts_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    type            ENUM('deposit','withdrawal','transfer', 'bill_payment') NOT NULL,
    amount          DECIMAL(12,2)   NOT NULL CHECK (amount > 0),
    from_account_id INT UNSIGNED    NULL,            -- NULL vid insättning
    to_account_id   INT UNSIGNED    NULL,            -- NULL vid uttag
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT fk_tx_from
        FOREIGN KEY (from_account_id) REFERENCES accounts (id)
        ON DELETE SET NULL,
    CONSTRAINT fk_tx_to
        FOREIGN KEY (to_account_id) REFERENCES accounts (id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bills (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    account_id    INT UNSIGNED    NOT NULL,
    description   VARCHAR(255)    NOT NULL,          -- t.ex. "Elräkning april"
    amount        DECIMAL(12,2)   NOT NULL CHECK (amount > 0),
    due_date      DATE            NOT NULL,
    paid          TINYINT(1)      NOT NULL DEFAULT 0,
    paid_at       TIMESTAMP       NULL DEFAULT NULL, -- sätts när fakturan betalas
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT fk_bills_account
        FOREIGN KEY (account_id) REFERENCES accounts (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_log (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NULL,                -- NULL = ej inloggad
    action      VARCHAR(100)    NOT NULL,            -- t.ex. "login_success", "withdrawal"
    description TEXT            NULL,                -- valfri extra info
    ip_address  VARCHAR(45)     NOT NULL,            -- stöder både IPv4 och IPv6
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB;