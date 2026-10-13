-- Mini-CRM schema (MySQL 5.6+)
SET NAMES utf8;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','operator') NOT NULL DEFAULT 'operator',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS stores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS topic1 (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS topic2 (
  id INT AUTO_INCREMENT PRIMARY KEY,
  topic1_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  CONSTRAINT fk_topic2_topic1 FOREIGN KEY (topic1_id) REFERENCES topic1(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('call','email') NOT NULL DEFAULT 'call',
  topic1_id INT DEFAULT NULL,
  topic2_id INT DEFAULT NULL,
  store_id INT DEFAULT NULL,
  employee_id INT DEFAULT NULL,
  occurred_at DATETIME NOT NULL,
  justified TINYINT(1) NOT NULL DEFAULT 0,
  justify_comment TEXT,
  description TEXT,
  status ENUM('open','in_progress','closed') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_occurred_at (occurred_at),
  CONSTRAINT fk_tickets_topic1 FOREIGN KEY (topic1_id) REFERENCES topic1(id) ON DELETE SET NULL,
  CONSTRAINT fk_tickets_topic2 FOREIGN KEY (topic2_id) REFERENCES topic2(id) ON DELETE SET NULL,
  CONSTRAINT fk_tickets_store FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE SET NULL,
  CONSTRAINT fk_tickets_employee FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(64) NOT NULL,
  entity_id INT NOT NULL,
  action VARCHAR(64) NOT NULL,
  field VARCHAR(128) DEFAULT NULL,
  value TEXT,
  user_id INT DEFAULT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS mail_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  imap_host VARCHAR(255),
  imap_port INT,
  imap_tls TINYINT(1) DEFAULT 1,
  smtp_host VARCHAR(255),
  smtp_port INT,
  smtp_tls TINYINT(1) DEFAULT 1,
  username VARCHAR(255),
  password_enc TEXT,
  from_email VARCHAR(255) DEFAULT NULL,
  recipients TEXT DEFAULT NULL,
  active TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(128) NOT NULL PRIMARY KEY,
  setting_value TEXT,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Таблица категорий инцидентов
CREATE TABLE IF NOT EXISTS incident_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Таблица инцидентов
CREATE TABLE IF NOT EXISTS incidents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL,
  started_at DATETIME NOT NULL,
  finished_at DATETIME DEFAULT NULL,
  inc_number_so VARCHAR(255) DEFAULT NULL,
  potential_impact TEXT DEFAULT NULL,
  category_id INT DEFAULT NULL,
  bot_notification TEXT DEFAULT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_status (status),
  INDEX idx_started_at (started_at),
  INDEX idx_created_at (created_at),
  CONSTRAINT fk_incidents_category FOREIGN KEY (category_id) REFERENCES incident_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS email_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(64) NOT NULL,
  related_id INT DEFAULT NULL,
  recipients TEXT NOT NULL,
  status ENUM('success','error') NOT NULL,
  error_message TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_type_related (type, related_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

