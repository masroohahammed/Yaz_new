<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Static inventory checks for the FM ERP remediation.
 * These tests do not need a live database.
 */
final class RemediationInventoryTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__);
    }

    public function testPortalTenantPropertyIsNullableArray(): void
    {
        $src = file_get_contents($this->root . '/app/Controllers/Portal.php');
        $this->assertNotFalse($src);
        $this->assertMatchesRegularExpression('/private \?array \$_tenant\s*=\s*null;/', $src);
        $this->assertStringContainsString('private bool $_tenantResolved', $src);
        $this->assertStringNotContainsString('$_tenant = false', $src);
    }

    public function testEmployeeFormHasNoUnmatchedEndifAfterCostCenter(): void
    {
        $src = file_get_contents($this->root . '/app/Views/employees/partials/form_employment.php');
        $this->assertNotFalse($src);
        $this->assertStringContainsString("\$options['costCenters'] ?? []", $src);
        $this->assertDoesNotMatchRegularExpression('/Cost Center[\s\S]{0,400}<\?php endif; \?>\s*<div class="col-md-6">\s*<label class="form-label">Department/', $src);
    }

    public function testDatabaseConfigHasNoHardcodedPassword(): void
    {
        $src = file_get_contents($this->root . '/app/Config/Database.php');
        $this->assertNotFalse($src);
        $this->assertStringNotContainsString('5(ud1757', $src);
        $this->assertStringContainsString("env('database.default.password'", $src);
        $this->assertStringContainsString("ENVIRONMENT !== 'production'", $src);
    }

    public function testLoginThrottleIsRegistered(): void
    {
        $filters = file_get_contents($this->root . '/app/Config/Filters.php');
        $routes  = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString('LoginThrottleFilter::class', $filters);
        $this->assertStringContainsString("'filter' => 'loginThrottle'", $routes);
    }

    public function testSensitiveModulesAreRouted(): void
    {
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        foreach ([
            "group('finance-bank'",
            "group('finance-petty'",
            "hr/employees",
            "hr/expenses",
            "hr/performance",
            "hr/assets",
            "reports/pm",
            "ai/predictive",
            "JobCards::edit",
            "JobCards::addMaterial",
            "Leases::applyPenalties",
            "Leases::savePrint",
            "Tenants::blacklist",
            "Landlords::uploadDoc",
            "Landlords::dismissReminder",
        ] as $needle) {
            $this->assertStringContainsString($needle, $routes, "Missing route wiring: {$needle}");
        }
    }

    public function testObsoleteActionUrlsAreGoneFromLiveViews(): void
    {
        $views = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root . '/app/Views')
        );
        $bad = [];
        $skip = ['finance/contracts', 'hr/contracts'];
        foreach ($views as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $src = file_get_contents($file->getPathname());
            foreach ([
                "contracts/view/",
                "contracts/edit/",
                "landlords/edit/",
                "landlords/view/",
                "tenants/edit/",
                "tenants/view/",
                "rent-payments/",
            ] as $obsolete) {
                $ignore = false;
                foreach ($skip as $ok) {
                    if (str_contains($src, $ok . '/view/') || str_contains($src, $ok . '/edit/')) {
                        $srcWithoutFinance = str_replace($ok . '/view/', '', str_replace($ok . '/edit/', '', $src));
                        if (! str_contains($srcWithoutFinance, $obsolete)) {
                            $ignore = true;
                        }
                    }
                }
                if ($ignore) {
                    continue;
                }
                if (str_contains($src, $obsolete) && ! str_contains($src, 'finance/' . $obsolete) && ! str_contains($src, 'hr/' . $obsolete)) {
                    $rel = substr($file->getPathname(), strlen($this->root) + 1);
                    if (str_contains($rel, 'finance/') || str_contains($rel, 'employees/partials/tab_contracts')) {
                        continue;
                    }
                    $bad[] = $rel . ' => ' . $obsolete;
                }
            }
        }
        $this->assertSame([], $bad, "Obsolete URLs remain:\n" . implode("\n", $bad));
    }

    public function testLeftoverBackupConfigsRemoved(): void
    {
        $this->assertFileDoesNotExist($this->root . '/app/Config/Routes--.php');
        $this->assertFileDoesNotExist($this->root . '/app/Config/Routes_additions.php.bak');
        $this->assertFileDoesNotExist($this->root . '/app/Config/Autoload--.php');
    }

    public function testSupersededControllersRemoved(): void
    {
        foreach ([
            'Contract.php', 'Landlord.php', 'Tenant.php', 'CollectorApp.php',
            'Complaints.php', 'PmModule.php', 'Home.php', 'WorkOrdersExtended.php',
            'TenantPortal.php',
        ] as $file) {
            $this->assertFileDoesNotExist($this->root . '/app/Controllers/' . $file, $file . ' should be removed after porting');
        }
        $this->assertFileExists($this->root . '/app/Controllers/FinanceBank.php');
        $this->assertFileExists($this->root . '/app/Controllers/FinancePettyCash.php');
        $this->assertFileExists($this->root . '/app/Controllers/PmReports.php');
        $this->assertFileExists($this->root . '/app/Controllers/Hr/HrEmployees.php');
    }

    public function testMenuHrefTargetsAreRegistered(): void
    {
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertNotFalse($routes);

        $menus = [
            $this->root . '/app/Config/Menu.php',
            $this->root . '/app/Config/PmMenu.php',
            $this->root . '/app/Config/FmMenu.php',
            $this->root . '/app/Config/HrMenu.php',
            $this->root . '/app/Config/FinanceMenu.php',
        ];
        $hrefs = [];
        foreach ($menus as $file) {
            $src = file_get_contents($file);
            preg_match_all("/'(?:href|url)'\s*=>\s*'([^']+)'/", $src, $m);
            foreach ($m[1] as $href) {
                $hrefs[$href] = basename($file);
            }
        }

        $missing = [];
        foreach ($hrefs as $href => $file) {
            $first = explode('/', $href)[0];
            if ($first === '') {
                continue;
            }
            if (
                ! str_contains($routes, "'" . $href . "'")
                && ! str_contains($routes, "get('" . $first)
                && ! str_contains($routes, "group('" . $first . "'")
            ) {
                $missing[] = $file . ' => ' . $href;
            }
        }

        $this->assertSame([], $missing, "Menu URLs missing from Routes.php:\n" . implode("\n", $missing));
    }

    public function testCompatibilityAliasesRegistered(): void
    {
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        foreach ([
            "get('leases'",
            "get('rent-payments'",
            "get('complaints'",
            "get('landlord-payouts'",
            "get('units'",
            "Units::all",
        ] as $needle) {
            $this->assertStringContainsString($needle, $routes, "Missing alias/route: {$needle}");
        }
    }

    public function testControllerMethodsExistForNewRoutes(): void
    {
        $map = [
            'Leases.php'     => ['applyPenalties', 'savePrint', 'renewForm', 'terminateForm', 'amendmentForm'],
            'Landlords.php'  => ['uploadDoc', 'deleteDoc', 'dismissReminder', 'payouts', 'markPaid'],
            'Tenants.php'    => ['blacklist', 'unblacklist', 'action'],
            'JobCards.php'   => ['edit', 'update', 'addMaterial'],
            'Units.php'      => ['all'],
            'Hr/HrEmployees.php' => ['edit', 'update', 'status'],
        ];
        foreach ($map as $file => $methods) {
            $src = file_get_contents($this->root . '/app/Controllers/' . $file);
            foreach ($methods as $method) {
                $this->assertMatchesRegularExpression(
                    '/function\s+' . preg_quote($method, '/') . '\s*\(/',
                    $src,
                    "{$file} missing {$method}()"
                );
            }
        }
    }

    public function testPmCrudServiceIsUsedByLiveControllers(): void
    {
        $this->assertFileExists($this->root . '/app/Controllers/PmModules.php');
        $src = file_get_contents($this->root . '/app/Controllers/PmModules.php');
        $this->assertStringContainsString('PmCrudService', $src);
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString("PmModules::index", $routes);
        $this->assertStringContainsString("'commission-rules'", file_get_contents($this->root . '/app/Controllers/PmModules.php'));
        $this->assertStringContainsString('sales/commission-rules', file_get_contents($this->root . '/app/Controllers/PmModules.php'));
        $this->assertStringContainsString("HrEmployees::edit", $routes);
        $this->assertStringContainsString("Api\\V1\\WorkOrders::index", $routes);
        $this->assertStringContainsString("Api\\V1\\Invoices::index", $routes);
    }

    public function testLegacyApiIsDeprecatedProxy(): void
    {
        $wo = file_get_contents($this->root . '/app/Controllers/Api/WorkOrders.php');
        $this->assertStringContainsString('Api\\V1\\WorkOrders', $wo);
        $this->assertStringContainsString('Deprecation', $wo);
        $fin = file_get_contents($this->root . '/app/Controllers/Api/Finance.php');
        $this->assertStringContainsString('Api\\V1\\Invoices', $fin);
        $this->assertStringContainsString('Deprecation', $fin);
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString("group('api/v1'", $routes);
        $this->assertStringContainsString("group('api/legacy'", $routes);
    }

    public function testFinanceTotalsServiceIsSingleSourceOfTruth(): void
    {
        $this->assertFileExists($this->root . '/app/Services/FinanceTotalsService.php');
        $src = file_get_contents($this->root . '/app/Services/FinanceTotalsService.php');
        foreach (['invoiceTotals', 'paymentTotals', 'syncOverdueInvoices'] as $method) {
            $this->assertStringContainsString("function {$method}", $src);
        }
        $this->assertStringContainsString("status IN ('cancelled','void','voided')", $src);
        $dash = file_get_contents($this->root . '/app/Controllers/Dashboard.php');
        $this->assertStringContainsString('FinanceTotalsService', $dash);
        $this->assertStringContainsString('workOrderTotals', $dash);
        $this->assertStringNotContainsString('(clone $lc)', $dash);
    }

    public function testMysqlPatchIsIdempotentAndDocumented(): void
    {
        $patch = $this->root . '/database/patches/fm-erp-complete.sql';
        $this->assertFileExists($patch);
        $sql = file_get_contents($patch);
        $this->assertStringContainsString('fm_add_index_if_missing', $sql);
        $this->assertStringContainsString('tenant_blacklist_history', $sql);
        $this->assertStringContainsString('maintenance_request_history', $sql);
        $this->assertStringContainsString('idx_inv_company', $sql);
        $this->assertStringContainsString('deposit_date', $sql);
        $this->assertStringContainsString('clearance_date', $sql);
        $this->assertStringContainsString('management_fee', $sql);
        $this->assertStringContainsString('idx_chq_facility_date', $sql);
        $this->assertStringContainsString('contract_kind', $sql);
        $this->assertFileExists($this->root . '/app/Database/Migrations/2026-08-29-100600_ChequeDatesAndExpenseCategories.php');
        $this->assertFileExists($this->root . '/app/Database/Migrations/2026-08-29-100700_ParkingLeaseContractColumns.php');
        $this->assertFileExists($this->root . '/database/patches/2026-08-29-fm-erp-remediation.sql');
    }

    public function testParkingContractUsesOfficialLegalText(): void
    {
        $this->assertFileExists($this->root . '/app/Services/ParkingContractService.php');
        $this->assertFileExists($this->root . '/app/Views/leases/parking_contract_print.php');
        $print = file_get_contents($this->root . '/app/Views/leases/parking_contract_print.php');
        foreach ([
            'Parking Space Lease Agreement',
            'عقد ايجار موقف تحت مبنى العقار',
            'Article Four: General Terms',
            'البند الرابع: الشروط العامة',
            'col-en',
            'col-ar',
            'DM Sans',
            'Cairo',
            '_doc_letterhead',
            '_doc_footer',
        ] as $needle) {
            $this->assertStringContainsString($needle, $print);
        }
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString("get('units/(:num)/parking-contract/print'", $routes);
        $this->assertStringContainsString("post('units/(:num)/parking-contract/save'", $routes);
        $this->assertStringContainsString("post('units/(:num)/parking-contract/generate-sign-link'", $routes);
        $this->assertFileExists($this->root . '/app/Services/CompanyBrandingService.php');
        $this->assertStringContainsString('function fm_company_branding', file_get_contents($this->root . '/app/Helpers/fm_helper.php'));
        $svc = file_get_contents($this->root . '/app/Services/ParkingContractService.php');
        $this->assertStringContainsString('header_title_deed_no', $svc);
        $this->assertStringContainsString('parking_owner_name_ar', $svc);
        $settings = file_get_contents($this->root . '/app/Views/settings/index.php');
        $this->assertStringContainsString('company_cr', $settings);
        $this->assertStringContainsString('company_po_box', $settings);
    }

    public function testLandlordReportsUseHashTabsAndShortCode(): void
    {
        $view = file_get_contents($this->root . '/app/Views/pm_reports/landlord.php');
        $this->assertStringContainsString('fm-entity-tabs', $view);
        $this->assertStringContainsString('hashchange', $view);
        $this->assertStringContainsString('short_code', $view);
        $this->assertFileExists($this->root . '/app/Database/Migrations/2026-08-29-100800_LandlordShortCode.php');
        $patch = file_get_contents($this->root . '/database/patches/fm-erp-complete.sql');
        $this->assertStringContainsString('short_code', $patch);
    }

    public function testFacilityListScopeUsesFacilitiesIdColumn(): void
    {
        $base = file_get_contents($this->root . '/app/Controllers/BaseController.php');
        $this->assertStringContainsString('function scopedFacilitiesList', $base);
        $this->assertStringContainsString("scopeFacilities(\$q, 'id')", $base);
        $leases = file_get_contents($this->root . '/app/Controllers/Leases.php');
        $this->assertStringContainsString('scopedFacilitiesList', $leases);
        $this->assertStringContainsString('applyLeaseFacilityScope', $leases);
    }

    public function testCompanyLogoPathSupportsUploadsCompanies(): void
    {
        $helper = file_get_contents($this->root . '/app/Helpers/fm_helper.php');
        $this->assertStringContainsString('function fm_logo_resolve_path', $helper);
        $this->assertStringContainsString("'companies'", $helper);
        $letter = file_get_contents($this->root . '/app/Views/layouts/_doc_letterhead.php');
        $this->assertStringContainsString('company_phone', $letter);
        $this->assertStringContainsString('Tel:', $letter);
        $print = file_get_contents($this->root . '/app/Views/leases/print.php');
        $this->assertStringContainsString('_doc_letterhead', $print);
        $this->assertFileExists($this->root . '/public/assets/css/fm-workspace-ui.css');
    }

    public function testNoKitchenPosModuleWasInvented(): void
    {
        $this->assertFileDoesNotExist($this->root . '/app/Controllers/Kitchen.php');
        $this->assertFileDoesNotExist($this->root . '/app/Controllers/Pos.php');
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringNotContainsString("Kitchen::", $routes);
        $this->assertStringNotContainsString("Pos::", $routes);
    }

    public function testLandlordReportsReuseExistingTables(): void
    {
        $this->assertFileExists($this->root . '/app/Services/LandlordReportService.php');
        $this->assertFileExists($this->root . '/app/Views/pm_reports/landlord.php');
        $svc = file_get_contents($this->root . '/app/Services/LandlordReportService.php');
        foreach (['lease_payments', 'cheques', 'expenses', 'lease_contracts', 'maintenance_requests', "where('landlord_id'"] as $needle) {
            $this->assertStringContainsString($needle, $svc);
        }
        $this->assertStringNotContainsString('CREATE TABLE', $svc);
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString("PmReports::landlord", $routes);
        $this->assertStringContainsString("PmReports::landlordExport", $routes);
        $ctrl = file_get_contents($this->root . '/app/Controllers/PmReports.php');
        $this->assertStringContainsString('function scopedActiveFacilities', $ctrl);
        $this->assertStringContainsString('function landlordExport', $ctrl);
        $this->assertStringContainsString('function tabularExport', $ctrl);
        $this->assertStringContainsString("format === 'pdf'", $ctrl);
        $this->assertStringContainsString("format === 'excel'", $ctrl);
        $menu = file_get_contents($this->root . '/app/Config/PmMenu.php');
        $this->assertStringContainsString('reports/pm/landlord', $menu);
        $cheques = file_get_contents($this->root . '/app/Controllers/Cheques.php');
        $this->assertStringContainsString('deposit_date', $cheques);
        $this->assertStringContainsString('clearance_date', $cheques);
        $this->assertFileExists($this->root . '/app/Support/PmExpenseCategories.php');
        $cats = file_get_contents($this->root . '/app/Support/PmExpenseCategories.php');
        foreach (['insurance', 'municipality', 'cleaning', 'security', 'management_fee'] as $cat) {
            $this->assertStringContainsString("'" . $cat . "'", $cats);
        }
    }

    public function testEntityQrScanRoutesAndDocumentsTabs(): void
    {
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        foreach ([
            "scan/property/",
            "scan/unit/",
            "get('scan'",
            'Facilities::qrcode',
            'Units::qrcode',
        ] as $needle) {
            $this->assertStringContainsString($needle, $routes);
        }
        $this->assertFileExists($this->root . '/app/Services/EntityQrService.php');
        $this->assertFileExists($this->root . '/app/Controllers/EntityScan.php');
        $this->assertFileExists($this->root . '/app/Controllers/Scan.php');
        $this->assertFileExists($this->root . '/app/Views/entity_scan/landing.php');
        $this->assertFileExists($this->root . '/app/Views/scan/index.php');
        $facilityView = file_get_contents($this->root . '/app/Views/facilities/view.php');
        $unitView = file_get_contents($this->root . '/app/Views/units/view.php');
        $this->assertStringContainsString('tab-documents', $facilityView);
        $this->assertStringContainsString('tab-qr', $facilityView);
        $this->assertStringContainsString('tab-documents', $unitView);
        $this->assertStringContainsString('tab-qr', $unitView);
        $patch = file_get_contents($this->root . '/database/patches/fm-erp-complete.sql');
        $this->assertStringContainsString('qr_scan_logs', $patch);
        $this->assertStringContainsString('qr_token', $patch);
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString('public/maintenance', $routes);
        $this->assertStringContainsString('public/inspections', $routes);
        $this->assertFileExists($this->root . '/app/Controllers/PublicMaintenance.php');
        $this->assertFileExists($this->root . '/app/Controllers/PublicEntity.php');
    }

    public function testUnitChecklistsAutoIncrementFix(): void
    {
        $this->assertFileExists($this->root . '/app/Database/Migrations/2026-08-29-121000_UnitChecklistsAutoIncrement.php');
        $this->assertFileExists($this->root . '/app/Database/AutoIncrementRepair.php');
        $inspections = file_get_contents($this->root . '/app/Controllers/Inspections.php');
        $units = file_get_contents($this->root . '/app/Controllers/Units.php');
        $this->assertStringContainsString('AutoIncrementRepair::ensure', $inspections);
        $this->assertStringContainsString('AutoIncrementRepair::ensure', $units);
        $patch = file_get_contents($this->root . '/database/patches/fm-erp-complete.sql');
        $this->assertStringContainsString('unit_checklists', $patch);
        $this->assertStringContainsString('AUTO_INCREMENT', $patch);
    }

    public function testInspectionUiUsesInteractiveWorkspacePatterns(): void
    {
        $css = file_get_contents($this->root . '/public/assets/css/fm-workspace-ui.css');
        foreach (['inspection-type-card', 'inspection-condition-btn', 'inspection-progress-bar', 'inspection-row-clickable'] as $cls) {
            $this->assertStringContainsString('.' . $cls, $css);
        }
        $index = file_get_contents($this->root . '/app/Views/inspections/index.php');
        $checklist = file_get_contents($this->root . '/app/Views/inspections/checklist.php');
        $form = file_get_contents($this->root . '/app/Views/inspections/form.php');
        $view = file_get_contents($this->root . '/app/Views/inspections/view.php');
        $partial = file_get_contents($this->root . '/app/Views/partials/inspection_reports_table.php');
        $this->assertStringContainsString('table-registry', $index);
        $this->assertStringContainsString('kpi-card', $index);
        $this->assertStringContainsString('inspection-condition-btn', $checklist);
        $this->assertStringContainsString('inspection-type-card', $form);
        $this->assertStringContainsString('Area Breakdown', $view);
        $this->assertStringContainsString('inspection-row-clickable', $partial);
        $unitChecklist = file_get_contents($this->root . '/app/Views/units/checklist.php');
        $this->assertStringContainsString('checklistProgressBar', $unitChecklist);
        $this->assertStringContainsString('area_photos', $checklist);
        $this->assertStringContainsString('existing_photos[]', $checklist);
        $this->assertStringContainsString('multiple', $checklist);
        $photoSvc = file_get_contents($this->root . '/app/Services/InspectionPhotoService.php');
        $this->assertStringContainsString('normalizePhotoEntry', $photoSvc);
        $this->assertStringContainsString('storeAreaUploads', $photoSvc);
        $this->assertStringContainsString('inspection-area-photo-thumb', $view);
        $print = file_get_contents($this->root . '/app/Views/inspections/print.php');
        $this->assertStringContainsString('Area Breakdown', $print);
        $this->assertStringContainsString('$photos', $print);
        $inspections = file_get_contents($this->root . '/app/Controllers/Inspections.php');
        $this->assertStringContainsString('InspectionPhotoService::storeAreaUploads', $inspections);
        $this->assertStringContainsString('normalizePhotoEntry', $inspections);
        $this->assertStringContainsString("'photos'", $inspections);
        $this->assertStringContainsString("'priorities'", $inspections);
        $this->assertStringContainsString('submitBtn.disabled', $form);
    }

    public function testPublicMaintenanceUsesExplicitScopeFilters(): void
    {
        $this->assertFileExists($this->root . '/app/Services/MaintenanceScopeQuery.php');
        $service = file_get_contents($this->root . '/app/Services/MaintenanceScopeQuery.php');
        $this->assertStringContainsString('mr.unit_id = ?', $service);
        $this->assertStringContainsString('mr.facility_id = ?', $service);
        $this->assertStringContainsString('$db->query($sql, $params)', $service);
        $this->assertStringContainsString('insertRequest', $service);
        $this->assertFileExists($this->root . '/app/Controllers/PublicMaintenance.php');
        $controller = file_get_contents($this->root . '/app/Controllers/PublicMaintenance.php');
        $this->assertStringContainsString('extends Controller', $controller);
        $this->assertStringNotContainsString('extends BaseController', $controller);
        $this->assertStringContainsString('MaintenanceScopeQuery::listRecords', $controller);
        $this->assertStringContainsString('renderPropertyMaintenance', $controller);
        $this->assertStringContainsString('MAINTENANCE_BUILD', $controller);
        $this->assertStringContainsString('listForUser', $service);
        $this->assertFileExists($this->root . '/app/Controllers/MaintenanceList.php');
        $this->assertStringContainsString('MaintenanceList::index', file_get_contents($this->root . '/app/Config/Routes.php'));
        $this->assertStringContainsString("'url' => 'maintenance/list'", file_get_contents($this->root . '/app/Config/PmMenu.php'));
        $this->assertStringContainsString('MaintenanceScopeQuery::listForUser', file_get_contents($this->root . '/app/Controllers/Helpdesk.php'));
        $this->assertStringContainsString("'2026-08-29-8'", $controller);
        $this->assertStringContainsString("getGet('ping')", $controller);
        $this->assertStringContainsString('X-FM-Maintenance-Build', $controller);
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString("\$routes->get('maintenance',                 'PublicMaintenance::index');", $routes);
        $this->assertStringContainsString('PublicMaintenance::ping', $routes);
        $this->assertStringContainsString('PublicMaintenance::submit', $routes);
        $this->assertStringContainsString('maintenance-ping', $routes);
        $this->assertStringNotContainsString("\$routes->get('maintenance',                   'Helpdesk::index');", $routes);
        $this->assertStringContainsString("'url' => 'maintenance/list'", file_get_contents($this->root . '/app/Config/FmMenu.php'));
        $this->assertStringNotContainsString('applyMaintenanceScope', $controller);
        $this->assertStringNotContainsString('loadMaintenanceRecords', $controller);
        $this->assertStringNotContainsString('loadUnitsForFacility', $controller);
        $entity = file_get_contents($this->root . '/app/Controllers/PublicEntity.php');
        $this->assertStringNotContainsString('function maintenance', $entity);
        $view = file_get_contents($this->root . '/app/Views/public/maintenance.php');
        $this->assertStringContainsString('fm-maintenance-build', $view);
        $this->assertStringContainsString('form_open_multipart', $view);
        $this->assertStringContainsString('requester_name', $view);
        $this->assertStringContainsString('category', $view);
    }

    public function testPropertyMaintenanceTabHasCreateButton(): void
    {
        $view = file_get_contents($this->root . '/app/Views/facilities/view.php');
        $this->assertStringContainsString('id="tab-maintenance"', $view);
        $this->assertStringContainsString('New Maintenance Request', $view);
        $this->assertStringContainsString('maintenanceCreateUrl', $view);
        $this->assertStringNotContainsString("<?php endif; ?>\n\n  <!-- Documents Tab -->\n  <div class=\"tab-pane fade\" id=\"tab-maintenance\">", $view);
    }

    public function testLiveFormPostsAvoidOrphanStoreRoutes(): void
    {
        $pairs = [
            'app/Views/crm/form.php'             => ["base_url('crm/store')", "base_url('crm')"],
            'app/Views/cheques/form.php'         => ["base_url('cheques/store')", "base_url('cheques')"],
            'app/Views/payments/form.php'        => ["base_url('payments/store')", "base_url('payments')"],
            'app/Views/outgoing_cheques/form.php'=> ["base_url('outgoing-cheques/store')", "base_url('outgoing-cheques')"],
        ];
        foreach ($pairs as $file => [$bad, $good]) {
            $src = file_get_contents($this->root . '/' . $file);
            $this->assertNotFalse($src, $file);
            $this->assertStringNotContainsString($bad, $src, $file . ' still uses orphan /store POST');
            $this->assertStringContainsString($good, $src, $file . ' should POST to collection root');
        }
    }

    public function testProcurementAndCollectorHubLinksAreRouted(): void
    {
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString("get('procurement/rfq'", $routes);
        $this->assertStringContainsString("get('procurement/orders'", $routes);
        $this->assertStringContainsString("post('collector/assign'", $routes);
        $hub = file_get_contents($this->root . '/app/Views/procurement/workflow_hub.php');
        $this->assertStringContainsString("base_url('purchase-orders')", $hub);
        $this->assertStringNotContainsString("procurement/orders", $hub);
    }

    public function testAssetScanLogServiceRepairsAutoIncrement(): void
    {
        $src = file_get_contents($this->root . '/app/Services/AssetScanLogService.php');
        $this->assertStringContainsString('AutoIncrementRepair::ensure', $src);
        $this->assertStringContainsString("assetId <= 0", $src);
        $qr = file_get_contents($this->root . '/app/Services/QrScanLogService.php');
        $this->assertStringContainsString('AutoIncrementRepair::ensure', $qr);
    }

    public function testPropertyInspectionAreasAndPriorities(): void
    {
        require_once $this->root . '/app/Services/InspectionAreaService.php';
        $areas = \App\Services\InspectionAreaService::propertyAreas();
        $this->assertContains('Roof', $areas);
        $this->assertContains('Basement', $areas);
        $this->assertContains('Garden / Landscaping', $areas);
        $this->assertContains('critical', \App\Services\InspectionAreaService::priorities());
        $url = \App\Services\InspectionAreaService::createUrl(['facility_id' => 12]);
        $this->assertStringContainsString('pm-inspections/create', $url);
        $this->assertStringContainsString('scope=property', $url);
        $this->assertStringContainsString('property_id=12', $url);
    }

    public function testInspectionsControllerSupportsPropertyAndAssetScopes(): void
    {
        $src = file_get_contents($this->root . '/app/Controllers/Inspections.php');
        $this->assertStringContainsString('InspectionAreaService', $src);
        $this->assertStringContainsString("'scope_type'", $src);
        $this->assertStringContainsString("'floor_label'", $src);
        $this->assertStringContainsString("'priorities'", $src);
        $this->assertStringContainsString('inspectionFacilityExpr', $src);
        $this->assertStringContainsString("fieldExists('facility_id', self::TABLE)", $src);
        $migration = file_get_contents($this->root . '/app/Database/Migrations/2026-08-30-140000_PropertyInspectionColumns.php');
        $this->assertStringContainsString('scope_type', $migration);
        $this->assertStringContainsString('asset_scan_logs', $migration);
    }

    public function testQrScanRoutesDirectlyToPmInspectionsCreate(): void
    {
        $entity = file_get_contents($this->root . '/app/Controllers/EntityScan.php');
        $asset  = file_get_contents($this->root . '/app/Controllers/AssetScan.php');
        $public = file_get_contents($this->root . '/app/Controllers/PublicEntity.php');
        $trait  = file_get_contents($this->root . '/app/Controllers/Traits/QrInspectionRedirectTrait.php');
        $auth   = file_get_contents($this->root . '/app/Controllers/Auth.php');
        $this->assertStringContainsString('QrInspectionRedirectTrait', $entity);
        $this->assertStringContainsString('QrInspectionRedirectTrait', $asset);
        $this->assertStringContainsString('resolveQrScanRedirect', $entity);
        $this->assertStringContainsString('redirectToInspectionUrl', $trait);
        $this->assertStringContainsString('redirectToInspectionUrl', $public);
        $this->assertStringContainsString('postLoginRedirect', $auth);
        $this->assertStringContainsString('redirect_after_login', $auth);
        $checklist = file_get_contents($this->root . '/app/Views/inspections/checklist.php');
        $this->assertStringContainsString('item_priority[]', $checklist);
        $this->assertStringContainsString('item_status[]', $checklist);
        $this->assertStringContainsString('critical', $checklist);
    }

    public function testPropertyInspectionSqlPatchHasNoStoredProcedure(): void
    {
        $patch = file_get_contents($this->root . '/database/patches/2026-08-30-property-inspections.sql');
        $complete = file_get_contents($this->root . '/database/patches/fm-erp-complete.sql');
        $this->assertStringNotContainsString('fm_add_column_if_missing', $patch);
        $this->assertStringNotContainsString('fm_add_column_if_missing', $complete);
        $this->assertStringContainsString('ADD COLUMN IF NOT EXISTS `facility_id`', $patch);
        $this->assertStringContainsString('ADD COLUMN IF NOT EXISTS `scope_type`', $patch);
    }

    public function testSidebarUsesSessionCompanyBranding(): void
    {
        $sidebar = file_get_contents($this->root . '/app/Views/layouts/_sidebar.php');
        $this->assertStringContainsString("session()->get('company_id')", $sidebar);
        $this->assertStringContainsString('fm_company_branding', $sidebar);
        $this->assertStringContainsString('sidebar-brand-logo', $sidebar);

        $css = file_get_contents($this->root . '/public/assets/css/fm-theme-shell.css');
        $this->assertStringContainsString('width: 100%', $css);
        $this->assertStringContainsString('height: 40px', $css);

        $createUser = file_get_contents($this->root . '/app/Views/settings/create_user.php');
        $this->assertStringContainsString('name="company_id"', $createUser);

        $settings = file_get_contents($this->root . '/app/Controllers/Settings.php');
        $this->assertStringContainsString("'company_id' => (int) \$this->request->getPost('company_id')", $settings);
    }

    public function testPmDashboardExpiryLinksAndSignatureRoutes(): void
    {
        $dash = file_get_contents($this->root . '/app/Views/dashboard/pm_dashboard.php');
        $this->assertStringContainsString('reports/pm/leases?expiring=1', $dash);
        $this->assertStringNotContainsString('finance/contracts', $dash);
        $this->assertStringContainsString('fm-clickable-row', $dash);
        $this->assertStringContainsString("base_url('contracts/'", $dash);

        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString('PublicContractSign::show', $routes);
        $this->assertStringContainsString('Leases::generateSignLink', $routes);
        $this->assertStringContainsString('Leases::regenerateSignLink', $routes);
        $this->assertStringContainsString('Leases::downloadSignedPdf', $routes);
        $this->assertStringContainsString('Leases::whatsappShareSigned', $routes);
    }

    public function testLeasePrintAndParkingRenewIncludeTenantQidAndSignature(): void
    {
        $print = file_get_contents($this->root . '/app/Views/leases/print.php');
        $this->assertStringContainsString('QID / Passport', $print);
        $this->assertStringContainsString('tenantSignatureB64', $print);

        $show = file_get_contents($this->root . '/app/Views/leases/show.php');
        $this->assertStringContainsString('parking-print', $show);
        $this->assertStringContainsString('_lease_signature_panel', $show);

        $unitView = file_get_contents($this->root . '/app/Views/units/view.php');
        $this->assertStringContainsString('fm_unit_renew_url', $unitView);
        $this->assertStringContainsString('_lease_signature_panel', $unitView);
        $this->assertStringContainsString('parking-contract', $unitView);

        $helper = file_get_contents($this->root . '/app/Helpers/fm_helper.php');
        $this->assertStringContainsString('fm_signature_migration_sql', $helper);

        $leases = file_get_contents($this->root . '/app/Controllers/Leases.php');
        $this->assertStringContainsString('{{tenant_qid}}', $leases);
        $this->assertStringContainsString('parking-print', $leases);

        $sync = file_get_contents($this->root . '/app/Services/UnitLeaseSyncService.php');
        $this->assertStringContainsString('dedupeActiveLeases', $sync);

        $sigPatch = file_get_contents($this->root . '/database/patches/2026-09-02-lease-contract-signature.sql');
        $this->assertStringContainsString('tenant_signature_path', $sigPatch);
        $this->assertStringContainsString('signature_token', $sigPatch);
    }

    public function testParkingContractOptionalPhotos(): void
    {
        $photoPatch = file_get_contents($this->root . '/database/patches/2026-09-04-parking-contract-photos.sql');
        $this->assertStringContainsString('photos_json', $photoPatch);

        $photoSvc = file_get_contents($this->root . '/app/Services/ParkingContractPhotoService.php');
        $this->assertStringContainsString('MAX_PHOTOS = 3', $photoSvc);
        $this->assertStringContainsString('uploads/parking_contracts', $photoSvc);

        $form = file_get_contents($this->root . '/app/Views/leases/parking_contract_form.php');
        $this->assertStringContainsString('enctype="multipart/form-data"', $form);
        $this->assertStringContainsString('name="contract_photos[]"', $form);

        $doc = file_get_contents($this->root . '/app/Views/leases/partials/parking_contract_document.php');
        $this->assertStringContainsString('contract_photos', $doc);
        $this->assertStringContainsString('ParkingContractPhotoService::photoSrc', $doc);

        $trait = file_get_contents($this->root . '/app/Controllers/Traits/ParkingContractTrait.php');
        $this->assertStringContainsString('syncParkingContractPhotos', $trait);

        $leases = file_get_contents($this->root . '/app/Controllers/Leases.php');
        $this->assertStringContainsString("'photos_json'", $leases);
    }

    public function testParkingContractSaveSignLinkAndRenewArchive(): void
    {
        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString('Units::parkingContractSave', $routes);
        $this->assertStringContainsString('Units::parkingContractGenerateSignLink', $routes);

        $units = file_get_contents($this->root . '/app/Controllers/Units.php');
        $this->assertStringContainsString('function parkingContractSave', $units);
        $this->assertStringContainsString('function parkingContractGenerateSignLink', $units);
        $this->assertStringContainsString('saveParkingContractData', $units);

        $trait = file_get_contents($this->root . '/app/Controllers/Traits/ParkingContractTrait.php');
        $this->assertStringContainsString('function saveParkingContractData', $trait);
        $this->assertStringContainsString('function renewParkingLease', $trait);
        $this->assertStringContainsString('function archiveParkingContractDocument', $trait);
        $this->assertStringContainsString('function persistParkingFormSnapshot', $trait);
        $this->assertStringContainsString('function upsertParkingLeaseFromForm', $trait);
        $this->assertStringContainsString('parkingContractPdfFilename', $trait);
        $this->assertStringContainsString('autoSnapshotPdf', $trait);
        $this->assertStringContainsString('function resolveParkingTenantId', $trait);
        $this->assertStringContainsString("'doc_type'    => 'parking_contract'", $trait);

        $svc = file_get_contents($this->root . '/app/Services/ParkingContractService.php');
        $this->assertStringContainsString('mergeSavedParkingForm', $svc);
        $this->assertStringContainsString('applyRenewalDates', $svc);
        $this->assertStringContainsString('ContractRenewalService', $svc);
        $this->assertFileExists($this->root . '/app/Services/ContractRenewalService.php');
        $this->assertStringContainsString("'parking_form'", $svc);

        $print = file_get_contents($this->root . '/app/Views/leases/parking_contract_print.php');
        $this->assertStringContainsString('snapshotPdf', $print);
        $this->assertStringContainsString('html2pdf', $print);
        $this->assertStringContainsString('parkingContractSnapshot', $print);
        $this->assertStringContainsString('bilingual-row', file_get_contents($this->root . '/app/Views/leases/partials/parking_contract_document.php'));
        $this->assertFileExists($this->root . '/app/Views/leases/partials/_parking_tenant_sign_box.php');

        $form = file_get_contents($this->root . '/app/Views/leases/parking_contract_form.php');
        $this->assertStringContainsString('Save contract data', $form);
        $this->assertStringContainsString('Save &amp; generate signing link', $form);
        $this->assertStringContainsString('name="lease_contract_id"', $form);
        $this->assertStringContainsString('name="tenant_name"', $form);
        $this->assertStringContainsString('required', $form);
        $this->assertStringContainsString('renew=1', file_get_contents($this->root . '/app/Views/units/view.php'));

        $leases = file_get_contents($this->root . '/app/Controllers/Leases.php');
        $this->assertStringContainsString('saveUrl', $leases);
        $this->assertStringContainsString('signLinkUrl', $leases);
        $this->assertStringContainsString('saveParkingContractData', $leases);
    }

    public function testPublicContractSignShowsFullDocumentWithSignIn(): void
    {
        $signView = file_get_contents($this->root . '/app/Views/public/contract_sign.php');
        $this->assertStringContainsString('standard_contract_document', $signView);
        $this->assertStringContainsString('parking_contract_document', $signView);
        $this->assertStringContainsString('_doc_letterhead', file_get_contents($this->root . '/app/Views/leases/partials/standard_contract_document.php'));
        $this->assertStringContainsString('Sign in', $signView);
        $this->assertStringContainsString('sign-submit-bar', $signView);
        $this->assertStringContainsString('og:title', $signView);
        $this->assertStringContainsString('og:description', $signView);
        $this->assertStringContainsString('_tenant_signature_slot', file_get_contents($this->root . '/app/Views/leases/partials/standard_contract_document.php'));

        $sigPad = file_get_contents($this->root . '/public/assets/js/signature-pad.js');
        $this->assertStringContainsString('pointerdown', $sigPad);
        $this->assertStringContainsString('requestAnimationFrame', $sigPad);
        $this->assertStringContainsString('fitCanvas', $sigPad);

        $sigCss = file_get_contents($this->root . '/public/assets/css/contract-signature.css');
        $this->assertStringContainsString('--contract-sig-height: 120px', $sigCss);
        $this->assertStringContainsString('--contract-sig-height: 168px', $sigCss);
        $this->assertStringContainsString('contract-signature.css', $signView);
        $this->assertStringContainsString('html2pdf', $signView);
        $this->assertStringContainsString('contractSignSnapshot', $signView);
        $this->assertStringContainsString('downloadContractPdf', $signView);
        $this->assertStringContainsString('display: table', file_get_contents($this->root . '/public/assets/css/contract-signature.css'));
        $this->assertStringContainsString('bilingual-row', file_get_contents($this->root . '/app/Views/leases/partials/standard_contract_document.php'));

        $controller = file_get_contents($this->root . '/app/Controllers/PublicContractSign.php');
        $this->assertStringContainsString('LeaseContractDocumentService', $controller);

        $auth = file_get_contents($this->root . '/app/Controllers/Auth.php');
        $this->assertStringContainsString('captureLoginRedirect', $auth);
    }

    public function testPropertyScopedAccessEnforcement(): void
    {
        $sidebar = file_get_contents($this->root . '/app/Views/layouts/_sidebar.php');
        $this->assertStringNotContainsString('sidebar-workspace-badge', $sidebar);
        $this->assertStringNotContainsString('Property Management', $sidebar);

        $ufs = file_get_contents($this->root . '/app/Services/UserFacilityService.php');
        $this->assertStringContainsString('hasCompanyWideAccess', $ufs);
        $this->assertStringContainsString('COMPANY_WIDE_ROLES', $ufs);
        $this->assertStringContainsString('ensureTableAutoIncrement', $ufs);
        $this->assertStringContainsString('syncUserPropertyAssignments', $ufs);

        $scope = file_get_contents($this->root . '/app/Services/CompanyScopeService.php');
        $this->assertStringContainsString('hasCompanyWideAccess', $scope);

        $facilities = file_get_contents($this->root . '/app/Controllers/Facilities.php');
        $this->assertStringContainsString('assertFacilityAccess', $facilities);
        $this->assertStringContainsString('syncPropertyManagers', $facilities);
        $this->assertStringContainsString('syncPropertyStaff', $facilities);
        $this->assertStringContainsString('property_manager_ids', $facilities);
        $this->assertStringContainsString('real_estate_manager_ids', $facilities);

        $settings = file_get_contents($this->root . '/app/Controllers/Settings.php');
        $this->assertStringContainsString('syncUserAccessFields', $settings);
        $this->assertStringContainsString('syncUserFacilities', $settings);
        $this->assertStringContainsString('syncUserPropertyAssignments', $settings);
        $this->assertStringContainsString('tenant-signature-anchor', file_get_contents($this->root . '/app/Views/leases/partials/_tenant_signature_slot.php'));

        $tenants = file_get_contents($this->root . '/app/Controllers/Tenants.php');
        $this->assertStringContainsString('scopeTenants', $tenants);
        $this->assertStringContainsString('assertTenantAccess', $tenants);

        $landlords = file_get_contents($this->root . '/app/Controllers/Landlords.php');
        $this->assertStringContainsString('scopeLandlords', $landlords);
        $this->assertStringContainsString('assertLandlordAccess', $landlords);

        $sigSvc = file_get_contents($this->root . '/app/Services/ContractSignatureService.php');
        $this->assertStringContainsString('regenerateSigningLink', $sigSvc);
        $this->assertStringContainsString('clearSignature', $sigSvc);
    }

    public function testKpiVisibilityPermissionAndViewGuards(): void
    {
        $rbac = file_get_contents($this->root . '/app/Services/RbacService.php');
        $this->assertStringContainsString("'ui.kpi'", $rbac);
        $this->assertStringContainsString('canViewKpis', $rbac);
        $this->assertStringContainsString('Show KPI widgets on pages', $rbac);

        $helper = file_get_contents($this->root . '/app/Helpers/fm_helper.php');
        $this->assertStringContainsString('fm_can_view_kpis', $helper);

        foreach ([
            'app/Views/dashboard/pm_dashboard.php',
            'app/Views/units/index.php',
            'app/Views/workorders/index.php',
            'app/Views/facilities/view.php',
        ] as $viewPath) {
            $src = file_get_contents($this->root . '/' . $viewPath);
            $this->assertStringContainsString('fm_can_view_kpis()', $src, "KPI guard missing in {$viewPath}");
        }

        $reports = file_get_contents($this->root . '/app/Controllers/Reports.php');
        $this->assertStringContainsString('reports.kpi', $reports);
        $this->assertStringContainsString('dashboard.kpi', $reports);
    }

    public function testUserFacilitiesAutoIncrementPatchExists(): void
    {
        $patch = file_get_contents($this->root . '/database/patches/2026-09-04-user-facilities-autoincrement.sql');
        $this->assertStringContainsString('AUTO_INCREMENT', $patch);
        $this->assertStringContainsString('user_facilities', $patch);
        $this->assertStringContainsString('user_property_assignments', $patch);
        $this->assertStringContainsString('property_manager', $patch);
    }

    public function testPropertyStaffMultiAssignForm(): void
    {
        $form = file_get_contents($this->root . '/app/Views/facilities/create.php');
        $this->assertStringContainsString('property_manager_ids[]', $form);
        $this->assertStringContainsString('real_estate_manager_ids[]', $form);
        $this->assertStringContainsString('landlord_user_ids[]', $form);

        $pas = file_get_contents($this->root . '/app/Services/PropertyAssignmentService.php');
        $this->assertStringContainsString('syncPropertyStaff', $pas);
        $this->assertStringContainsString('staffIdsForFacility', $pas);
    }

    public function testUserAccessFieldsRoleAwareUi(): void
    {
        $partial = file_get_contents($this->root . '/app/Views/settings/partials/user_access_fields.php');
        $this->assertStringContainsString('property_manager', $partial);
        $this->assertStringContainsString('real_estate_manager', $partial);
        $this->assertStringContainsString('companyWide', $partial);
    }

    public function testRemediationRestoreManifestAndVerifyEndpoint(): void
    {
        $build = file_get_contents($this->root . '/public/BUILD.json');
        $this->assertStringContainsString('contract_sign_bilingual_layout', $build);
        $this->assertStringContainsString('unit_expiry_dates_display', $build);
        $this->assertStringContainsString('digital_signature_and_signing_links', $build);

        $check = file_get_contents($this->root . '/app/Controllers/RemediationCheck.php');
        $this->assertStringContainsString('PublicContractSign.php', $check);
        $this->assertStringContainsString('UnitTenancyService.php', $check);

        $routes = file_get_contents($this->root . '/app/Config/Routes.php');
        $this->assertStringContainsString('remediation-check', $routes);
        $this->assertStringContainsString('RemediationCheck::index', $routes);

        $script = file_get_contents($this->root . '/scripts/verify-remediation.sh');
        $this->assertStringContainsString('ContractSignatureService.php', $script);
        $this->assertStringContainsString('fm-erp-complete.sql', $script);
    }

    public function testSignatureUiOnAllContractSurfaces(): void
    {
        $index = file_get_contents($this->root . '/app/Views/leases/index.php');
        $this->assertStringContainsString('generate-sign-link', $index);
        $this->assertStringContainsString('Sign</th>', $index);

        $show = file_get_contents($this->root . '/app/Views/leases/show.php');
        $this->assertStringContainsString('Send for signature', $show);
        $this->assertStringContainsString('_lease_signature_panel', $show);

        $form = file_get_contents($this->root . '/app/Views/leases/form.php');
        $this->assertStringContainsString('_lease_signature_panel', $form);

        $unitView = file_get_contents($this->root . '/app/Views/units/view.php');
        $this->assertStringContainsString('_lease_signature_panel', $unitView);
        $this->assertStringNotContainsString('isParkingUnit) && !empty($activeLeaseContract', $unitView);

        $panel = file_get_contents($this->root . '/app/Views/partials/_lease_signature_panel.php');
        $this->assertStringContainsString('signatureReady', $panel);
        $this->assertStringContainsString('Generate signing link', $panel);
    }

    public function testUnitsContractsAutoIncrementPatchExists(): void
    {
        $patch = file_get_contents($this->root . '/database/patches/2026-09-05-units-contracts-autoincrement.sql');
        $this->assertStringContainsString('AUTO_INCREMENT', $patch);
        $this->assertStringContainsString('units', $patch);
        $this->assertStringContainsString('contracts', $patch);

        $complete = file_get_contents($this->root . '/database/patches/fm-erp-complete.sql');
        $this->assertStringContainsString('-- 18) units / contracts AUTO_INCREMENT', $complete);
    }

    public function testContractRenewalDateDefaultsAndExpiryDisplay(): void
    {
        $this->assertFileExists($this->root . '/app/Services/ContractRenewalService.php');

        $helper = file_get_contents($this->root . '/app/Helpers/fm_helper.php');
        $this->assertStringContainsString('fm_renewal_date_defaults', $helper);
        $this->assertStringContainsString('fm_contract_days_until', $helper);

        $show = file_get_contents($this->root . '/app/Views/leases/show.php');
        $this->assertStringContainsString('fm_renewal_date_defaults', $show);
        $this->assertStringContainsString('contract_date', $show);

        $workflow = file_get_contents($this->root . '/app/Views/contracts/_workflow_form.php');
        $this->assertStringContainsString('fm_renewal_date_defaults', $workflow);

        $unitView = file_get_contents($this->root . '/app/Views/units/view.php');
        $this->assertStringContainsString('fm_contract_days_until', $unitView);
        $this->assertStringContainsString('Contract expired', $unitView);

        $this->assertFileExists($this->root . '/app/Services/UnitExpiryService.php');
        $this->assertFileExists($this->root . '/app/Views/partials/_unit_contract_expiry.php');
        $facView = file_get_contents($this->root . '/app/Views/facilities/view.php');
        $this->assertStringContainsString('_unit_contract_expiry', $facView);
        $pmDash = file_get_contents($this->root . '/app/Views/dashboard/pm_dashboard.php');
        $this->assertStringContainsString('unitExpiryAlerts', $pmDash);

        $renewSvc = new \App\Services\ContractRenewalService();
        $defaults = $renewSvc->renewalPeriodDefaults('2024-01-01', '2024-12-31', 12);
        $this->assertSame(date('Y-m-d'), $defaults['contract_date']);
        $this->assertGreaterThan(strtotime('today'), strtotime($defaults['start_date']));
    }

    public function testUnitTenancyServiceGuardsDuplicateAndVacantOnly(): void
    {
        $this->assertFileExists($this->root . '/app/Services/UnitTenancyService.php');

        $svc = file_get_contents($this->root . '/app/Services/UnitTenancyService.php');
        $this->assertStringContainsString('unitNumberTaken', $svc);
        $this->assertStringContainsString('unitIsVacant', $svc);
        $this->assertStringContainsString('fm_insert_row_id', $svc);
        $this->assertStringContainsString('vacantUnitsForFacility', $svc);

        $units = file_get_contents($this->root . '/app/Controllers/Units.php');
        $this->assertStringContainsString('UnitTenancyService', $units);
        $this->assertStringContainsString('unitNumberTaken', $units);
        $this->assertStringContainsString('insertUnit', $units);

        $leases = file_get_contents($this->root . '/app/Controllers/Leases.php');
        $this->assertStringContainsString('vacantOnlyMessage', $leases);
        $this->assertStringContainsString('vacant_only', $leases);
        $this->assertStringContainsString('markUnitOccupied', $leases);

        $form = file_get_contents($this->root . '/app/Views/leases/form.php');
        $this->assertStringContainsString('vacant_only=1', $form);
    }
}
