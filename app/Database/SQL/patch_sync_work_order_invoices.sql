-- Sync work_orders.invoice_id from existing invoices (fixes "Create Invoice" showing on WOs that already have invoices)
-- Run once on production after deploying workflow fix.

UPDATE work_orders w
INNER JOIN (
    SELECT i.work_order_id, MAX(i.id) AS invoice_id
    FROM invoices i
    WHERE i.work_order_id IS NOT NULL
      AND i.status NOT IN ('cancelled', 'void')
      AND (i.deleted_at IS NULL OR i.deleted_at = '0000-00-00 00:00:00')
    GROUP BY i.work_order_id
) latest ON latest.work_order_id = w.id
SET w.invoice_id = latest.invoice_id
WHERE w.invoice_id IS NULL OR w.invoice_id = 0;
