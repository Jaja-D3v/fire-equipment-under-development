-- Chemical Spill Kit branch-material lifecycle migration.
-- Reviewed against SQL_FIles/FEMS-DB.sql: material_id is UNSIGNED INT and
-- the existing table has no lifecycle columns. Run once through the normal database deployment process.
-- This migration is intentionally not executed by the application.

ALTER TABLE chemical_spill_kit_materials_tbl
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER branch,
    ADD COLUMN retired_at DATETIME NULL DEFAULT NULL AFTER is_active,
    ADD KEY idx_spill_material_branch_active (branch, is_active);

-- No inspection or checklist table is modified. Existing inspection history,
-- checklist snapshots, and approval records remain structurally unchanged.

