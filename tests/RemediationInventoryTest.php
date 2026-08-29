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
}
