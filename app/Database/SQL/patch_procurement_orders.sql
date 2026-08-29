-- ============================================================
-- Patch: procurement_orders compatibility + po_payments table
-- The Finance controller (accountsPayable) references:
--   1. `procurement_orders`  → real table is `purchase_orders`
--   2. `po_payments`         → does not exist, created below
--   3. `payment_status` / `due_date` columns on purchase_orders → added below
-- Run ONCE on your database.
-- ============================================================

-- Step 1: Add missing columns to purchase_orders (safe if already exist)
ALTER TABLE `purchase_orders`
    ADD COLUMN IF NOT EXISTS `payment_status` ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid' AFTER `status`,
    ADD COLUMN IF NOT EXISTS `due_date` DATE DEFAULT NULL AFTER `delivery_date`;

-- Step 2: Create po_payments table (AP payments journal)
CREATE TABLE IF NOT EXISTS `po_payments` (
    `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `po_id`      INT UNSIGNED     NOT NULL,
    `amount`     DECIMAL(12,2)    NOT NULL,
    `method`     VARCHAR(60)      NOT NULL DEFAULT 'bank_transfer',
    `reference`  VARCHAR(120)     DEFAULT NULL,
    `paid_by`    INT UNSIGNED     DEFAULT NULL,
    `paid_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_po_payments_po_id` (`po_id`),
    CONSTRAINT `fk_po_payments_po` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 3: Create compatibility VIEW so legacy code using `procurement_orders` still works
CREATE OR REPLACE VIEW `procurement_orders` AS
    SELECT
        id,
        company_id,
        po_number,
        vendor_id,
        delivery_date            AS due_date,
        total_amount,
        status,
        payment_status,
        notes,
        created_by,
        created_at,
        updated_at,
        approved_by,
        approved_at
    FROM `purchase_orders`;

-- ============================================================
-- DONE. The view `procurement_orders` now satisfies any code
-- that still references the old name, while purchase_orders
-- remains the canonical table.
-- ============================================================
