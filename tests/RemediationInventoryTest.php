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
}
