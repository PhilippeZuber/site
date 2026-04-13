# Datenbankänderungen für Job-Features

Führe folgende SQL-Statements in phpMyAdmin aus:

## 1. Jobs-Tabelle erweitern

```sql
ALTER TABLE `jobs` 
ADD COLUMN `valid_until` DATE NOT NULL DEFAULT (CURDATE() + INTERVAL 30 DAY),
ADD COLUMN `last_reminder_sent` DATE DEFAULT NULL,
ADD COLUMN `renewal_token` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `status` ENUM('pending', 'approved') DEFAULT 'pending',
ADD COLUMN `approval_token` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `creator_id` INT UNSIGNED DEFAULT NULL,
ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
```

## 2. User-Tabelle erweitern

```sql
ALTER TABLE `user` 
ADD COLUMN `approved_jobs_count` INT DEFAULT 0;
```

## 3. Vorhandene Records update (falls vorhanden)

```sql
-- Markiere alle bestehenden Jobs als genehmigt
UPDATE `jobs` SET status = 'approved', valid_until = DATE_ADD(NOW(), INTERVAL 90 DAY) WHERE status IS NULL;

-- Setze approved_jobs_count für User mit bestehenden Jobs
UPDATE `user` u SET approved_jobs_count = (SELECT COUNT(*) FROM jobs j WHERE j.creator_id = u.user_id AND j.status = 'approved');
```



## 4. Words-Tabelle: Ausmalbild-Spalte

```sql
ALTER TABLE `words` ADD COLUMN `image_ausmalbild` VARCHAR(255) NOT NULL DEFAULT '';
```



---

## 5. Tabelle fuer importierte PDF-Kontakte

```sql
CREATE TABLE `job_pdf_contacts` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(255) NOT NULL,
	`source_page_url` VARCHAR(500) NOT NULL,
	`source_pdf_url` VARCHAR(500) NOT NULL,
	`context_snippet` VARCHAR(500) DEFAULT NULL,
	`status` ENUM('new','contacted','unsubscribed','invalid') DEFAULT 'new',
	`first_seen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`last_seen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`created_by` INT UNSIGNED DEFAULT NULL,
	`notes` TEXT,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```



---

## 6. Tabelle fuer Newsletter-Versand-Log

```sql
CREATE TABLE `newsletter_send_log` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`sent_by` INT UNSIGNED DEFAULT NULL,
	`recipient_email` VARCHAR(255) NOT NULL,
	`recipient_type` ENUM('user','job_contact','test') NOT NULL,
	`recipient_ref_id` INT DEFAULT NULL,
	`recipient_mode` ENUM('users','jobs','both','test') NOT NULL,
	`subject` VARCHAR(255) NOT NULL,
	`template_file` VARCHAR(255) NOT NULL,
	`success` TINYINT(1) NOT NULL DEFAULT 0,
	`error_message` VARCHAR(500) DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_sent_at` (`sent_at`),
	KEY `idx_recipient_email` (`recipient_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

**Status:** ✅ In Produktion (ausgeführt am 13.04.2026)
