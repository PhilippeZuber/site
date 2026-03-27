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

---

**Status:** ⏳ Warte auf manuelle Ausführung in phpMyAdmin

---

## 4. Words-Tabelle: Ausmalbild-Spalte

```sql
ALTER TABLE `words` ADD COLUMN `image_ausmalbild` VARCHAR(255) NOT NULL DEFAULT '';
```

**Status:** ⏳ Warte auf manuelle Ausführung in phpMyAdmin
