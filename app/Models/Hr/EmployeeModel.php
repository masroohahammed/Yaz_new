<?php

namespace App\Models\Hr;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table            = 'employees';
    protected $primaryKey       = 'id';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'company_id', 'operating_company_id', 'user_id', 'facility_id', 'property_id', 'project_id',
        'cost_center_id', 'emp_code',
        'first_name', 'middle_name', 'last_name', 'name_ar',
        'gender', 'date_of_birth', 'nationality', 'marital_status',
        'personal_mobile', 'personal_email', 'current_address', 'permanent_address',
        'emergency_contact_name', 'emergency_contact_relationship', 'emergency_contact_phone',
        'photo_path',
        'department', 'department_id', 'designation', 'designation_id', 'grade_id',
        'employee_type_id', 'employment_source_id', 'status_id',
        'reporting_manager_id', 'secondary_manager_id', 'hr_manager_id',
        'shift_start', 'shift_end', 'shift_id', 'hourly_rate',
        'hire_date', 'joining_date', 'confirmation_date', 'probation_end_date', 'last_working_date',
        'wps_applicable', 'payroll_applicable', 'leave_applicable', 'attendance_applicable', 'overtime_applicable',
        'payroll_responsibility', 'current_employment_period_id',
        'qid_number', 'qid_issue_date', 'qid_expiry',
        'passport_number', 'passport_issue_date', 'passport_expiry', 'passport_country',
        'visa_number', 'visa_type', 'visa_issue_date', 'visa_expiry',
        'work_permit_number', 'work_permit_expiry',
        'health_card_number', 'health_card_expiry',
        'driving_licence_number', 'driving_licence_expiry',
        'bank_name', 'bank_account_number', 'iban', 'salary_frequency',
        'status', 'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'emp_code'    => 'required|max_length[20]',
        'designation' => 'required|max_length[100]',
        'department'  => 'required|max_length[100]',
    ];
}
