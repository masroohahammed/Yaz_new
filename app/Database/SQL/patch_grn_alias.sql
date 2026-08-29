-- ============================================================
-- Patch: goods_received_notes compatibility view
-- The codebase uses table 'grn' but one query referenced
-- 'goods_received_notes'. The controller has been fixed to use
-- 'grn' directly. This view is provided as a safety fallback
-- in case any other external query or report tool references
-- the old name.
-- ============================================================

CREATE OR REPLACE VIEW `goods_received_notes` AS
    SELECT * FROM `grn`;
