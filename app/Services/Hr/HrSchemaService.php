<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

/**
 * Ensures HR master tables match the expected schema on any MySQL/MariaDB version.
 * Legacy deployments may have lookup tables without company_id/code columns.
 */
class HrSchemaService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function m0Ready(): bool
    {
        return $this->db->tableExists('hr_employee_types')
            && $this->db->fieldExists('code', 'hr_employee_types')
            && $this->db->fieldExists('company_id', 'hr_employee_types');
    }

    public function masterTablesReady(): bool
    {
        foreach (['hr_departments', 'hr_designations', 'hr_grades'] as $table) {
            if (! $this->db->tableExists($table)) {
                return false;
            }
            if (! $this->db->fieldExists('code', $table) || ! $this->db->fieldExists('name', $table)) {
                return false;
            }
        }

        return true;
    }

    public function leaveTypesReady(): bool
    {
        return $this->db->tableExists('hr_leave_types')
            && $this->db->fieldExists('company_id', 'hr_leave_types')
            && $this->db->fieldExists('code', 'hr_leave_types');
    }

    public function leaveOperationsReady(): bool
    {
        return $this->db->tableExists('hr_leave_requests')
            && $this->db->fieldExists('employee_id', 'hr_leave_requests')
            && $this->db->fieldExists('leave_type_id', 'hr_leave_requests');
    }

    public function employeeMasterReady(): bool
    {
        return $this->m0Ready()
            && $this->masterTablesReady()
            && $this->db->fieldExists('department_id', 'employees')
            && $this->db->fieldExists('designation_id', 'employees');
    }

    /**
     * Align employee masters, lookup seed tables, and legacy text backfills.
     */
    public function ensureAllSchemas(): void
    {
        $this->ensureEmployeeMasterSchema();
        $this->ensureLookupSchemas();
        $this->ensureLeaveOperationsSchema();
    }

    /** Align leave request/balance tables when legacy schema lacks employee_id. */
    public function ensureLeaveOperationsSchema(): void
    {
        $this->alignLookupTable('hr_leave_requests', $this->leaveRequestColumns(), 'employee_id');
        $this->alignLookupTable('hr_leave_balances', $this->leaveBalanceColumns(), 'employee_id');
        $this->alignLookupTable('hr_leave_request_history', $this->leaveHistoryColumns(), 'employee_id');
    }

    /**
     * Align master tables, then backfill codes and employee FK links from legacy text fields.
     */
    public function ensureEmployeeMasterSchema(): void
    {
        $this->createMasterTablesIfMissing();
        $this->alignLookupTable('hr_departments', $this->departmentColumns(), 'code');
        $this->alignLookupTable('hr_designations', $this->designationColumns(), 'code');
        $this->alignLookupTable('hr_grades', $this->gradeColumns(), 'code');
        $this->backfillMasterCodes('hr_departments');
        $this->backfillMasterCodes('hr_designations');
        $this->backfillMasterCodes('hr_grades');
        $this->dedupeMasterCodes('hr_departments');
        $this->dedupeMasterCodes('hr_designations');
        $this->dedupeMasterCodes('hr_grades');
        $this->migrateEmployeeTextToMasters();
    }

    /** Align all HR lookup tables used by seed INSERT statements. */
    public function ensureLookupSchemas(): void
    {
        foreach ($this->lookupTableColumns() as $table => $columns) {
            $critical = array_key_exists('company_id', $columns) ? 'company_id' : 'code';
            $this->alignLookupTable($table, $columns, $critical);
        }
    }

    private function createMasterTablesIfMissing(): void
    {
        $statements = [
            "CREATE TABLE IF NOT EXISTS `hr_departments` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `company_id` int(10) UNSIGNED DEFAULT NULL,
              `code` varchar(30) NOT NULL,
              `name` varchar(120) NOT NULL,
              `parent_id` int(10) UNSIGNED DEFAULT NULL,
              `is_active` tinyint(1) NOT NULL DEFAULT 1,
              `sort_order` int(11) NOT NULL DEFAULT 0,
              `created_at` datetime NOT NULL DEFAULT current_timestamp(),
              `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_hr_dept_code` (`company_id`,`code`),
              KEY `idx_hr_dept_company` (`company_id`),
              KEY `idx_hr_dept_parent` (`parent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            "CREATE TABLE IF NOT EXISTS `hr_designations` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `company_id` int(10) UNSIGNED DEFAULT NULL,
              `code` varchar(30) NOT NULL,
              `name` varchar(120) NOT NULL,
              `grade_id` int(10) UNSIGNED DEFAULT NULL,
              `is_active` tinyint(1) NOT NULL DEFAULT 1,
              `sort_order` int(11) NOT NULL DEFAULT 0,
              `created_at` datetime NOT NULL DEFAULT current_timestamp(),
              `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_hr_desig_code` (`company_id`,`code`),
              KEY `idx_hr_desig_company` (`company_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            "CREATE TABLE IF NOT EXISTS `hr_grades` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `company_id` int(10) UNSIGNED DEFAULT NULL,
              `code` varchar(30) NOT NULL,
              `name` varchar(120) NOT NULL,
              `level` int(11) NOT NULL DEFAULT 0,
              `is_active` tinyint(1) NOT NULL DEFAULT 1,
              `sort_order` int(11) NOT NULL DEFAULT 0,
              `created_at` datetime NOT NULL DEFAULT current_timestamp(),
              `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_hr_grade_code` (`company_id`,`code`),
              KEY `idx_hr_grade_company` (`company_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        ];

        foreach ($statements as $sql) {
            $this->db->query($sql);
        }
    }

    /**
     * @param  array<string, string>  $columns  column => ADD COLUMN definition (without name)
     */
    private function alignLookupTable(string $table, array $columns, ?string $criticalColumn = null): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        if ($criticalColumn !== null
            && ! $this->db->fieldExists($criticalColumn, $table)
            && (int) $this->db->table($table)->countAllResults() === 0
            && in_array($table, ['hr_departments', 'hr_designations', 'hr_grades'], true)
        ) {
            $this->db->query('DROP TABLE `' . $table . '`');
            $this->createMasterTablesIfMissing();

            return;
        }

        foreach ($columns as $column => $definition) {
            $this->addColumnIfMissing($table, $column, $definition);
        }
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        if (! $this->db->tableExists($table) || $this->db->fieldExists($column, $table)) {
            return;
        }

        $this->db->query('ALTER TABLE `' . $table . '` ADD COLUMN `' . $column . '` ' . $definition);
    }

    private function backfillMasterCodes(string $table): void
    {
        if (! $this->db->tableExists($table) || ! $this->db->fieldExists('code', $table) || ! $this->db->fieldExists('name', $table)) {
            return;
        }

        $this->db->query(
            "UPDATE `{$table}` SET `code` = LEFT(LOWER(REPLACE(REPLACE(TRIM(`name`),' ','_'),'/','_')), 30)
             WHERE `code` IS NULL OR TRIM(`code`) = ''"
        );
    }

    private function dedupeMasterCodes(string $table): void
    {
        if (! $this->db->tableExists($table) || ! $this->db->fieldExists('code', $table)) {
            return;
        }

        $this->db->query(
            "UPDATE `{$table}` t
             JOIN (
               SELECT `id`, ROW_NUMBER() OVER (PARTITION BY `company_id`, `code` ORDER BY `id`) AS rn
               FROM `{$table}`
             ) x ON x.id = t.id AND x.rn > 1
             SET t.`code` = LEFT(CONCAT(t.`code`, '_', t.`id`), 30)"
        );
    }

    private function migrateEmployeeTextToMasters(): void
    {
        if (! $this->db->tableExists('employees')) {
            return;
        }

        if ($this->db->tableExists('hr_departments') && $this->db->fieldExists('code', 'hr_departments')) {
            $this->db->query(
                'INSERT IGNORE INTO `hr_departments` (`company_id`,`code`,`name`)
                 SELECT DISTINCT e.company_id,
                   LEFT(LOWER(REPLACE(REPLACE(TRIM(e.department),\' \',\'_\'),\'/\',\'_\')), 30),
                   TRIM(e.department)
                 FROM `employees` e
                 WHERE TRIM(e.department) <> \'\''
            );

            if ($this->db->fieldExists('department_id', 'employees')) {
                $this->db->query(
                    'UPDATE `employees` e
                     JOIN `hr_departments` d
                       ON d.name COLLATE utf8mb4_unicode_ci = e.department COLLATE utf8mb4_unicode_ci
                       AND (d.company_id = e.company_id OR (d.company_id IS NULL AND e.company_id IS NULL))
                     SET e.department_id = d.id
                     WHERE e.department_id IS NULL AND TRIM(e.department) <> \'\''
                );
            }
        }

        if ($this->db->tableExists('hr_designations') && $this->db->fieldExists('code', 'hr_designations')) {
            $this->db->query(
                'INSERT IGNORE INTO `hr_designations` (`company_id`,`code`,`name`)
                 SELECT DISTINCT e.company_id,
                   LEFT(LOWER(REPLACE(REPLACE(TRIM(e.designation),\' \',\'_\'),\'/\',\'_\')), 30),
                   TRIM(e.designation)
                 FROM `employees` e
                 WHERE TRIM(e.designation) <> \'\''
            );

            if ($this->db->fieldExists('designation_id', 'employees')) {
                $this->db->query(
                    'UPDATE `employees` e
                     JOIN `hr_designations` d
                       ON d.name COLLATE utf8mb4_unicode_ci = e.designation COLLATE utf8mb4_unicode_ci
                       AND (d.company_id = e.company_id OR (d.company_id IS NULL AND e.company_id IS NULL))
                     SET e.designation_id = d.id
                     WHERE e.designation_id IS NULL AND TRIM(e.designation) <> \'\''
                );
            }
        }
    }

    /** @return array<string, array<string, string>> */
    private function lookupTableColumns(): array
    {
        return [
            'hr_employee_types'        => $this->employeeTypeColumns(),
            'hr_employment_sources'    => $this->employmentSourceColumns(),
            'hr_employee_statuses'     => $this->employeeStatusColumns(),
            'hr_leave_types'           => $this->leaveTypeColumns(),
            'hr_leave_policies'        => $this->leavePolicyColumns(),
            'hr_shifts'                => $this->shiftColumns(),
            'hr_document_categories'   => $this->documentCategoryColumns(),
            'hr_salary_components'     => $this->salaryComponentColumns(),
            'hr_settings'              => $this->settingsColumns(),
            'hr_onboarding_checklists' => $this->onboardingChecklistColumns(),
            'hr_clearance_checklists'  => $this->clearanceChecklistColumns(),
            'hr_approval_workflows'    => $this->approvalWorkflowColumns(),
        ];
    }

    /** @return array<string, string> */
    private function departmentColumns(): array
    {
        return [
            'company_id' => 'int(10) UNSIGNED DEFAULT NULL',
            'code'       => 'varchar(30) DEFAULT NULL',
            'name'       => 'varchar(120) NOT NULL DEFAULT \'\'',
            'parent_id'  => 'int(10) UNSIGNED DEFAULT NULL',
            'is_active'  => 'tinyint(1) NOT NULL DEFAULT 1',
            'sort_order' => 'int(11) NOT NULL DEFAULT 0',
            'created_at' => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at' => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function designationColumns(): array
    {
        return [
            'company_id' => 'int(10) UNSIGNED DEFAULT NULL',
            'code'       => 'varchar(30) DEFAULT NULL',
            'name'       => 'varchar(120) NOT NULL DEFAULT \'\'',
            'grade_id'   => 'int(10) UNSIGNED DEFAULT NULL',
            'is_active'  => 'tinyint(1) NOT NULL DEFAULT 1',
            'sort_order' => 'int(11) NOT NULL DEFAULT 0',
            'created_at' => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at' => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function gradeColumns(): array
    {
        return [
            'company_id' => 'int(10) UNSIGNED DEFAULT NULL',
            'code'       => 'varchar(30) DEFAULT NULL',
            'name'       => 'varchar(120) NOT NULL DEFAULT \'\'',
            'level'      => 'int(11) NOT NULL DEFAULT 0',
            'is_active'  => 'tinyint(1) NOT NULL DEFAULT 1',
            'sort_order' => 'int(11) NOT NULL DEFAULT 0',
            'created_at' => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at' => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function employeeTypeColumns(): array
    {
        return [
            'company_id' => 'int(10) UNSIGNED DEFAULT NULL',
            'code'       => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'       => 'varchar(120) NOT NULL DEFAULT \'\'',
            'description' => 'varchar(255) DEFAULT NULL',
            'sort_order' => 'int(11) NOT NULL DEFAULT 0',
            'is_active'  => 'tinyint(1) NOT NULL DEFAULT 1',
            'created_at' => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at' => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function employmentSourceColumns(): array
    {
        return [
            'company_id'           => 'int(10) UNSIGNED DEFAULT NULL',
            'code'                 => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'                 => 'varchar(120) NOT NULL DEFAULT \'\'',
            'payroll_responsibility' => 'enum(\'our_company\',\'supplier\',\'external\',\'consultant\',\'none\') NOT NULL DEFAULT \'our_company\'',
            'description'          => 'varchar(255) DEFAULT NULL',
            'sort_order'           => 'int(11) NOT NULL DEFAULT 0',
            'is_active'            => 'tinyint(1) NOT NULL DEFAULT 1',
            'created_at'           => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'           => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function employeeStatusColumns(): array
    {
        return [
            'company_id'        => 'int(10) UNSIGNED DEFAULT NULL',
            'code'              => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'              => 'varchar(120) NOT NULL DEFAULT \'\'',
            'legacy_status'     => 'enum(\'active\',\'on_leave\',\'inactive\') DEFAULT NULL',
            'allows_attendance' => 'tinyint(1) NOT NULL DEFAULT 1',
            'allows_leave'      => 'tinyint(1) NOT NULL DEFAULT 1',
            'allows_payroll'    => 'tinyint(1) NOT NULL DEFAULT 1',
            'is_active'         => 'tinyint(1) NOT NULL DEFAULT 1',
            'sort_order'        => 'int(11) NOT NULL DEFAULT 0',
            'created_at'        => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'        => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function leaveTypeColumns(): array
    {
        return [
            'company_id'        => 'int(10) UNSIGNED DEFAULT NULL',
            'code'              => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'              => 'varchar(120) NOT NULL DEFAULT \'\'',
            'is_paid'           => 'tinyint(1) NOT NULL DEFAULT 1',
            'requires_approval' => 'tinyint(1) NOT NULL DEFAULT 1',
            'max_days_per_year' => 'decimal(6,2) DEFAULT NULL',
            'allow_half_day'    => 'tinyint(1) NOT NULL DEFAULT 1',
            'carry_forward'     => 'tinyint(1) NOT NULL DEFAULT 0',
            'sort_order'        => 'int(11) NOT NULL DEFAULT 0',
            'is_active'         => 'tinyint(1) NOT NULL DEFAULT 1',
            'created_at'        => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'        => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function leavePolicyColumns(): array
    {
        return [
            'company_id'         => 'int(10) UNSIGNED DEFAULT NULL',
            'grade_id'           => 'int(10) UNSIGNED DEFAULT NULL',
            'employee_type_id'   => 'int(10) UNSIGNED DEFAULT NULL',
            'annual_entitlement' => 'decimal(6,2) NOT NULL DEFAULT 0.00',
            'accrual_per_month'  => 'decimal(6,2) DEFAULT NULL',
            'max_balance'        => 'decimal(6,2) DEFAULT NULL',
            'min_service_days'   => 'int(11) NOT NULL DEFAULT 0',
            'is_active'          => 'tinyint(1) NOT NULL DEFAULT 1',
            'created_at'         => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'         => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function shiftColumns(): array
    {
        return [
            'company_id'         => 'int(10) UNSIGNED DEFAULT NULL',
            'code'               => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'               => 'varchar(120) NOT NULL DEFAULT \'\'',
            'start_time'         => 'time NOT NULL DEFAULT \'08:00:00\'',
            'end_time'           => 'time NOT NULL DEFAULT \'17:00:00\'',
            'break_minutes'      => 'int(11) NOT NULL DEFAULT 0',
            'grace_in_minutes'   => 'int(11) NOT NULL DEFAULT 0',
            'grace_out_minutes'  => 'int(11) NOT NULL DEFAULT 0',
            'is_overnight'       => 'tinyint(1) NOT NULL DEFAULT 0',
            'is_active'          => 'tinyint(1) NOT NULL DEFAULT 1',
            'created_at'         => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'         => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function documentCategoryColumns(): array
    {
        return [
            'company_id'         => 'int(10) UNSIGNED DEFAULT NULL',
            'code'               => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'               => 'varchar(120) NOT NULL DEFAULT \'\'',
            'requires_expiry'    => 'tinyint(1) NOT NULL DEFAULT 0',
            'notify_days_before' => 'int(11) NOT NULL DEFAULT 30',
            'sort_order'         => 'int(11) NOT NULL DEFAULT 0',
            'is_active'          => 'tinyint(1) NOT NULL DEFAULT 1',
            'created_at'         => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'         => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function salaryComponentColumns(): array
    {
        return [
            'company_id'       => 'int(10) UNSIGNED DEFAULT NULL',
            'code'             => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'             => 'varchar(120) NOT NULL DEFAULT \'\'',
            'component_type'   => 'enum(\'earning\',\'deduction\',\'employer_contribution\') NOT NULL DEFAULT \'earning\'',
            'calculation_type' => 'enum(\'fixed\',\'percent_basic\',\'percent_gross\',\'formula\') NOT NULL DEFAULT \'fixed\'',
            'sort_order'       => 'int(11) NOT NULL DEFAULT 0',
            'is_active'        => 'tinyint(1) NOT NULL DEFAULT 1',
            'created_at'       => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'       => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function settingsColumns(): array
    {
        return [
            'company_id'    => 'int(10) UNSIGNED DEFAULT NULL',
            'setting_key'   => 'varchar(100) NOT NULL DEFAULT \'\'',
            'setting_value' => 'text DEFAULT NULL',
            'created_at'    => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'    => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function onboardingChecklistColumns(): array
    {
        return [
            'company_id'       => 'int(10) UNSIGNED DEFAULT NULL',
            'code'             => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'             => 'varchar(120) NOT NULL DEFAULT \'\'',
            'employee_type_id' => 'int(10) UNSIGNED DEFAULT NULL',
            'is_active'        => 'tinyint(1) NOT NULL DEFAULT 1',
            'sort_order'       => 'int(11) NOT NULL DEFAULT 0',
            'created_at'       => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'       => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function clearanceChecklistColumns(): array
    {
        return [
            'company_id'      => 'int(10) UNSIGNED DEFAULT NULL',
            'code'            => 'varchar(30) NOT NULL DEFAULT \'\'',
            'name'            => 'varchar(120) NOT NULL DEFAULT \'\'',
            'separation_type' => 'enum(\'resignation\',\'termination\',\'contract_end\',\'all\') NOT NULL DEFAULT \'all\'',
            'is_active'       => 'tinyint(1) NOT NULL DEFAULT 1',
            'created_at'      => 'datetime NOT NULL DEFAULT current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function approvalWorkflowColumns(): array
    {
        return [
            'company_id'           => 'int(10) UNSIGNED DEFAULT NULL',
            'name'                 => 'varchar(120) NOT NULL DEFAULT \'\'',
            'code'                 => 'varchar(40) NOT NULL DEFAULT \'\'',
            'module'               => 'varchar(40) NOT NULL DEFAULT \'other\'',
            'request_type'         => 'varchar(60) DEFAULT NULL',
            'operating_company_id' => 'int(10) UNSIGNED DEFAULT NULL',
            'department_id'        => 'int(10) UNSIGNED DEFAULT NULL',
            'grade_id'             => 'int(10) UNSIGNED DEFAULT NULL',
            'facility_id'          => 'int(10) UNSIGNED DEFAULT NULL',
            'min_amount'           => 'decimal(14,2) DEFAULT NULL',
            'max_amount'           => 'decimal(14,2) DEFAULT NULL',
            'is_active'            => 'tinyint(1) NOT NULL DEFAULT 1',
            'priority'             => 'int(11) NOT NULL DEFAULT 0',
            'created_at'           => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'           => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function leaveRequestColumns(): array
    {
        return [
            'employee_id'    => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'leave_type_id'  => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'start_date'     => 'date DEFAULT NULL',
            'end_date'       => 'date DEFAULT NULL',
            'days_requested' => 'decimal(5,2) NOT NULL DEFAULT 0.00',
            'half_day'       => 'enum(\'none\',\'first_half\',\'second_half\') NOT NULL DEFAULT \'none\'',
            'reason'         => 'text DEFAULT NULL',
            'status'         => 'enum(\'draft\',\'pending\',\'approved\',\'rejected\',\'cancelled\') NOT NULL DEFAULT \'pending\'',
            'requested_by'   => 'int(10) UNSIGNED DEFAULT NULL',
            'reviewed_by'    => 'int(10) UNSIGNED DEFAULT NULL',
            'reviewed_at'    => 'datetime DEFAULT NULL',
            'review_notes'   => 'varchar(255) DEFAULT NULL',
            'created_at'     => 'datetime NOT NULL DEFAULT current_timestamp()',
            'updated_at'     => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function leaveBalanceColumns(): array
    {
        return [
            'employee_id'     => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'leave_type_id'   => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'balance_year'    => 'smallint(4) NOT NULL DEFAULT 0',
            'opening_balance' => 'decimal(6,2) NOT NULL DEFAULT 0.00',
            'accrued'         => 'decimal(6,2) NOT NULL DEFAULT 0.00',
            'used'            => 'decimal(6,2) NOT NULL DEFAULT 0.00',
            'pending'         => 'decimal(6,2) NOT NULL DEFAULT 0.00',
            'adjusted'        => 'decimal(6,2) NOT NULL DEFAULT 0.00',
            'updated_at'      => 'datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
        ];
    }

    /** @return array<string, string> */
    private function leaveHistoryColumns(): array
    {
        return [
            'request_id'  => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'employee_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            'action'      => 'varchar(40) NOT NULL DEFAULT \'\'',
            'snapshot'    => 'longtext DEFAULT NULL',
            'changed_by'  => 'int(10) UNSIGNED DEFAULT NULL',
            'changed_at'  => 'datetime NOT NULL DEFAULT current_timestamp()',
        ];
    }
}
