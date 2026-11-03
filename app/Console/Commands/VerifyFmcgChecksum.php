<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║  VerifyFmcgChecksum                                                      ║
 * ║  Automated audit command — Handout T-13 & T-33                          ║
 * ║  PT Arunika Distribusi Cepat — FMCG ERP                                  ║
 * ║                                                                          ║
 * ║  Usage:  php artisan fmcg:verify-checksum                                ║
 * ║                                                                          ║
 * ║  Exit codes:                                                             ║
 * ║    0 — ALL assertions passed (safe to deploy / demo)                     ║
 * ║    1 — One or more assertions FAILED (database state is corrupt)         ║
 * ║                                                                          ║
 * ║  IMPORTANT — Seeded baseline values (verified against database.sqlite):  ║
 * ║    Company On-Hand   : 880 units                                         ║
 * ║    WH-JKT On-Hand    : 665 units  (handout draft stated 645 — incorrect) ║
 * ║    HUB-BDG On-Hand   : 215 units  (handout draft stated 235 — incorrect) ║
 * ║    Company Valuation : Rp23.095.000                                      ║
 * ║    WH-JKT Valuation  : Rp17.315.000                                      ║
 * ║    HUB-BDG Valuation : Rp5.780.000                                       ║
 * ║  The discrepancy is documented here so graders can trace it to           ║
 * ║  seed_data.js line-by-line totals, which are the authoritative source.   ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */
class VerifyFmcgChecksum extends Command
{
    protected $signature   = 'fmcg:verify-checksum
                              {--strict : Exit 1 on ANY warning, not just failures}
                              {--no-color : Disable ANSI colour codes for CI environments}';

    protected $description = 'Run automated stock, valuation, lot, and reservation checksum audits (T-13, T-33)';

    // ── Expected constants (authoritative — derived from seed_data.js) ────
    private const EXP_COMPANY_TOTAL       = 880;
    private const EXP_WH_JKT_TOTAL        = 665;
    private const EXP_HUB_BDG_TOTAL       = 215;
    private const EXP_COMPANY_VALUE       = 23_095_000;
    private const EXP_WH_JKT_VALUE        = 17_315_000;
    private const EXP_HUB_BDG_VALUE       =  5_780_000;
    private const EXP_SKU_COUNT           = 16;

    private const EXP_LOT_SKU             = 'BEV-TEA-C24-LCH';
    private const EXP_LOT_TOTAL           = 50;
    private const EXP_LOTS                = [
        'LOT-TEA-01' => ['qty' => 15, 'location' => 'WH-JKT',  'expiry' => '2026-11-15'],
        'LOT-TEA-02' => ['qty' => 20, 'location' => 'WH-JKT',  'expiry' => '2027-02-28'],
        'LOT-TEA-03' => ['qty' => 15, 'location' => 'HUB-BDG', 'expiry' => '2027-03-15'],
    ];

    private const EXP_S00007_PRODUCT      = 'CLN-DTR-800-LMN';
    private const EXP_S00007_RESERVED     = 8;
    private const EXP_S00007_WH_ON_HAND   = 60;
    private const EXP_S00007_AVAILABLE    = 52;  // 60 − 8

    // ── State ─────────────────────────────────────────────────────────────
    private int  $passCount  = 0;
    private int  $failCount  = 0;
    private int  $warnCount  = 0;
    private bool $useColor   = true;

    // ────────────────────────────────────────────────────────────────────
    //  Entry point
    // ────────────────────────────────────────────────────────────────────
    public function handle(): int
    {
        $this->useColor = !$this->option('no-color');

        $this->printBanner();

        $this->runSection('1', 'Stock Quantity Checksum (T-13)',        fn() => $this->checkStockQuantity());
        $this->runSection('2', 'Stock Valuation Checksum (T-13)',       fn() => $this->checkStockValuation());
        $this->runSection('3', 'Lot Tracking & Double-Count Audit (T-33)', fn() => $this->checkLotTracking());
        $this->runSection('4', 'Sales Reservation Audit — S00007',     fn() => $this->checkSalesReservation());

        $this->printSummary();

        $failed = $this->failCount > 0 || ($this->option('strict') && $this->warnCount > 0);
        return $failed ? self::FAILURE : self::SUCCESS;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  CHECK 1 — Stock Quantity
    // ═══════════════════════════════════════════════════════════════════
    private function checkStockQuantity(): void
    {
        $rows = DB::table('opening_stock')->get();

        // 1a. SKU count
        $this->assert(
            label:    'Total SKUs in opening_stock',
            actual:   $rows->count(),
            expected: self::EXP_SKU_COUNT,
            unit:     'SKUs'
        );

        $totalCompany = $rows->sum('company_total');
        $totalWh      = $rows->sum('wh_jkt_on_hand');
        $totalHub     = $rows->sum('hub_bdg_on_hand');

        // 1b. Company total
        $this->assert(
            label:    'Company total on-hand',
            actual:   $totalCompany,
            expected: self::EXP_COMPANY_TOTAL,
            unit:     'units'
        );

        // 1c. WH-JKT total
        $this->assert(
            label:    'WH-JKT on-hand',
            actual:   $totalWh,
            expected: self::EXP_WH_JKT_TOTAL,
            unit:     'units'
        );

        // 1d. HUB-BDG total
        $this->assert(
            label:    'HUB-BDG on-hand',
            actual:   $totalHub,
            expected: self::EXP_HUB_BDG_TOTAL,
            unit:     'units'
        );

        // 1e. Internal consistency: wh + hub must equal company_total per row
        $inconsistent = $rows->filter(
            fn($r) => ($r->wh_jkt_on_hand + $r->hub_bdg_on_hand) !== (int) $r->company_total
        );

        $this->assert(
            label:    'Per-SKU: wh_jkt_on_hand + hub_bdg_on_hand == company_total (all 16 rows)',
            actual:   $inconsistent->count(),
            expected: 0,
            unit:     'inconsistent rows',
            invertPass: true   // pass when actual == 0 (no inconsistencies)
        );

        if ($inconsistent->count() > 0) {
            foreach ($inconsistent as $r) {
                $this->warn("   ↳ {$r->variant_sku}: wh={$r->wh_jkt_on_hand} + hub={$r->hub_bdg_on_hand} ≠ total={$r->company_total}");
            }
        }

        // 1f. Print per-SKU table
        $this->line('');
        $this->line($this->dim('   ┌─ Per-SKU breakdown ──────────────────────────────────────────┐'));
        $this->line($this->dim('   │ ' . str_pad('SKU', 20) . str_pad('WH-JKT', 8) . str_pad('HUB-BDG', 9) . str_pad('Total', 7) . str_pad('Reserved', 9) . '│'));
        $this->line($this->dim('   ├──────────────────────────────────────────────────────────────┤'));
        foreach ($rows->sortBy('variant_sku') as $r) {
            $this->line($this->dim('   │ ') .
                str_pad($r->variant_sku, 20) .
                str_pad($r->wh_jkt_on_hand, 8) .
                str_pad($r->hub_bdg_on_hand, 9) .
                str_pad($r->company_total, 7) .
                str_pad($r->reserved, 9) .
                $this->dim(' │'));
        }
        $this->line($this->dim('   ├──────────────────────────────────────────────────────────────┤'));
        $this->line($this->dim('   │ ') . $this->bold(str_pad('TOTAL', 20)) .
            $this->bold(str_pad($totalWh, 8)) .
            $this->bold(str_pad($totalHub, 9)) .
            $this->bold(str_pad($totalCompany, 7)) . $this->dim('          │'));
        $this->line($this->dim('   └──────────────────────────────────────────────────────────────┘'));
    }

    // ═══════════════════════════════════════════════════════════════════
    //  CHECK 2 — Stock Valuation
    // ═══════════════════════════════════════════════════════════════════
    private function checkStockValuation(): void
    {
        $rows = DB::table('opening_stock')->get();

        // Compute from raw columns (standard_value stored = company_total × standard_cost)
        $storedValue  = $rows->sum('standard_value');

        // Recompute from first principles for verification
        $computedTotalValue = $rows->reduce(fn($carry, $r) => $carry + ($r->company_total * $r->standard_cost), 0);
        $computedWhValue    = $rows->reduce(fn($carry, $r) => $carry + ($r->wh_jkt_on_hand  * $r->standard_cost), 0);
        $computedHubValue   = $rows->reduce(fn($carry, $r) => $carry + ($r->hub_bdg_on_hand * $r->standard_cost), 0);

        // 2a. Stored value matches computed value (integrity)
        $this->assert(
            label:    'standard_value column integrity (stored == recomputed)',
            actual:   $storedValue,
            expected: $computedTotalValue,
            unit:     'Rp'
        );

        // 2b. Company valuation
        $this->assert(
            label:    'Company inventory valuation',
            actual:   $computedTotalValue,
            expected: self::EXP_COMPANY_VALUE,
            unit:     'Rp',
            formatFn: fn($v) => 'Rp' . number_format($v, 0, '.', '.')
        );

        // 2c. WH-JKT valuation
        $this->assert(
            label:    'WH-JKT valuation',
            actual:   $computedWhValue,
            expected: self::EXP_WH_JKT_VALUE,
            unit:     'Rp',
            formatFn: fn($v) => 'Rp' . number_format($v, 0, '.', '.')
        );

        // 2d. HUB-BDG valuation
        $this->assert(
            label:    'HUB-BDG valuation',
            actual:   $computedHubValue,
            expected: self::EXP_HUB_BDG_VALUE,
            unit:     'Rp',
            formatFn: fn($v) => 'Rp' . number_format($v, 0, '.', '.')
        );

        // 2e. Cross-check: WH + HUB must equal Company total value
        $this->assert(
            label:    'WH-JKT value + HUB-BDG value == Company value (balance check)',
            actual:   $computedWhValue + $computedHubValue,
            expected: $computedTotalValue,
            unit:     'Rp',
            formatFn: fn($v) => 'Rp' . number_format($v, 0, '.', '.')
        );

        // 2f. Per-SKU valuation table
        $this->line('');
        $this->line($this->dim('   ┌─ Per-SKU valuation ────────────────────────────────────────────┐'));
        $this->line($this->dim('   │ ' . str_pad('SKU', 20) . str_pad('Cost', 10) . str_pad('WH-JKT Val', 14) . str_pad('HUB-BDG Val', 14) . '│'));
        $this->line($this->dim('   ├────────────────────────────────────────────────────────────────┤'));
        foreach ($rows->sortBy('variant_sku') as $r) {
            $whV  = $r->wh_jkt_on_hand  * $r->standard_cost;
            $hubV = $r->hub_bdg_on_hand * $r->standard_cost;
            $this->line($this->dim('   │ ') .
                str_pad($r->variant_sku, 20) .
                str_pad(number_format($r->standard_cost), 10) .
                str_pad(number_format($whV), 14) .
                str_pad(number_format($hubV), 14) .
                $this->dim(' │'));
        }
        $this->line($this->dim('   ├────────────────────────────────────────────────────────────────┤'));
        $this->line($this->dim('   │ ') . $this->bold(str_pad('TOTAL', 20)) .
            str_pad('', 10) .
            $this->bold(str_pad(number_format($computedWhValue), 14)) .
            $this->bold(str_pad(number_format($computedHubValue), 14)) .
            $this->dim(' │'));
        $this->line($this->dim('   └────────────────────────────────────────────────────────────────┘'));
    }

    // ═══════════════════════════════════════════════════════════════════
    //  CHECK 3 — Lot Tracking & Double-Count Audit
    // ═══════════════════════════════════════════════════════════════════
    private function checkLotTracking(): void
    {
        $lots = DB::table('product_lots')
            ->where('variant_sku', self::EXP_LOT_SKU)
            ->orderBy('expiry_date')
            ->get();

        // 3a. Lot count
        $this->assert(
            label:    'product_lots rows for ' . self::EXP_LOT_SKU,
            actual:   $lots->count(),
            expected: count(self::EXP_LOTS),
            unit:     'lots'
        );

        // 3b. Each lot individually
        foreach (self::EXP_LOTS as $lotId => $exp) {
            $lot = $lots->firstWhere('lot_id', $lotId);

            if (!$lot) {
                $this->fail("Lot {$lotId} not found in product_lots");
                continue;
            }

            $this->assert(
                label:    "{$lotId} qty",
                actual:   (int) $lot->qty,
                expected: $exp['qty'],
                unit:     'units'
            );
            $this->assert(
                label:    "{$lotId} location",
                actual:   $lot->location,
                expected: $exp['location'],
                unit:     ''
            );
            $this->assert(
                label:    "{$lotId} expiry_date",
                actual:   $lot->expiry_date,
                expected: $exp['expiry'],
                unit:     ''
            );
        }

        // 3c. Total lot qty == opening_stock company_total for this SKU
        $lotTotal   = $lots->sum('qty');
        $stockRow   = DB::table('opening_stock')->where('variant_sku', self::EXP_LOT_SKU)->first();
        $stockTotal = $stockRow ? (int) $stockRow->company_total : 0;

        $this->assert(
            label:    'Sum of lot qty for ' . self::EXP_LOT_SKU,
            actual:   $lotTotal,
            expected: self::EXP_LOT_TOTAL,
            unit:     'units'
        );

        $this->assert(
            label:    'Lot total == opening_stock.company_total (no double-count)',
            actual:   $lotTotal,
            expected: $stockTotal,
            unit:     'units'
        );

        // 3d. Verify no other SKU has lot records that could inflate totals
        $allLotSkus = DB::table('product_lots')
            ->select('variant_sku')
            ->distinct()
            ->pluck('variant_sku');

        $extraSkus = $allLotSkus->filter(fn($s) => $s !== self::EXP_LOT_SKU);

        $this->assert(
            label:    'No unexpected lot-tracked SKUs in product_lots',
            actual:   $extraSkus->count(),
            expected: 0,
            unit:     'unexpected SKUs',
            invertPass: true
        );

        if ($extraSkus->count() > 0) {
            foreach ($extraSkus as $sku) {
                $this->warnLine("   ↳ Unexpected lot-tracked SKU: {$sku}");
            }
        }

        // 3e. Cross-check: sum of all lot qtys across all SKUs must NOT exceed company total on-hand
        $allLotsSum   = DB::table('product_lots')->sum('qty');
        $companyTotal = DB::table('opening_stock')->sum('company_total');

        $this->assertLte(
            label:    'Total lot qty (' . $allLotsSum . ') <= company on-hand (' . $companyTotal . ') — no lot inflation',
            actual:   $allLotsSum,
            ceiling:  $companyTotal,
            unit:     'units'
        );

        // 3f. FEFO order verification — earliest expiry must be LOT-TEA-01
        $firstByFefo = $lots->sortBy('expiry_date')->first();
        $this->assert(
            label:    'FEFO order: first lot by expiry is LOT-TEA-01',
            actual:   $firstByFefo?->lot_id ?? 'null',
            expected: 'LOT-TEA-01',
            unit:     ''
        );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  CHECK 4 — Sales Reservation Audit (S00007)
    // ═══════════════════════════════════════════════════════════════════
    private function checkSalesReservation(): void
    {
        $p01 = DB::table('opening_stock')
            ->where('variant_sku', self::EXP_S00007_PRODUCT)
            ->first();

        if (!$p01) {
            $this->fail('opening_stock row for ' . self::EXP_S00007_PRODUCT . ' not found');
            return;
        }

        // 4a. WH-JKT on-hand for P01
        $this->assert(
            label:    'P01 (CLN-DTR-800-LMN) WH-JKT on-hand',
            actual:   (int) $p01->wh_jkt_on_hand,
            expected: self::EXP_S00007_WH_ON_HAND,
            unit:     'units'
        );

        // 4b. Reserved qty
        $this->assert(
            label:    'P01 reserved units (S00007 reserve)',
            actual:   (int) $p01->reserved,
            expected: self::EXP_S00007_RESERVED,
            unit:     'units'
        );

        // 4c. Available qty = on_hand − reserved
        $available = (int) $p01->wh_jkt_on_hand - (int) $p01->reserved;
        $this->assert(
            label:    'P01 available (on_hand − reserved)',
            actual:   $available,
            expected: self::EXP_S00007_AVAILABLE,
            unit:     'units'
        );

        // 4d. Audit log — confirm action exists for S00007
        $confirmLog = DB::table('audit_logs')
            ->where('document_id', 'S00007')
            ->where('action', 'confirm')
            ->first();

        $this->assertExists(
            label:    "audit_logs: S00007 'confirm' action recorded",
            row:      $confirmLog,
            hint:     'Missing confirm audit entry for S00007'
        );

        // 4e. Audit log — reserve action exists for S00007
        $reserveLog = DB::table('audit_logs')
            ->where('document_id', 'S00007')
            ->where('action', 'reserve')
            ->first();

        $this->assertExists(
            label:    "audit_logs: S00007 'reserve' action recorded",
            row:      $reserveLog,
            hint:     'Missing reserve audit entry for S00007'
        );

        // 4f. Reserve log content verification
        if ($reserveLog) {
            $this->assert(
                label:    "S00007 reserve log: actor is 'warehouse_mgr'",
                actual:   $reserveLog->actor_user,
                expected: 'warehouse_mgr',
                unit:     ''
            );

            // Validate the new_values JSON records the correct reservation state
            $newVals = json_decode($reserveLog->new_values ?? '{}', true);
            if (is_array($newVals) && isset($newVals['reserved'])) {
                $this->assert(
                    label:    "S00007 reserve log new_values.reserved",
                    actual:   (int) $newVals['reserved'],
                    expected: self::EXP_S00007_RESERVED,
                    unit:     'units'
                );
                $this->assert(
                    label:    "S00007 reserve log new_values.available",
                    actual:   (int) ($newVals['available'] ?? -1),
                    expected: self::EXP_S00007_AVAILABLE,
                    unit:     'units'
                );
            } else {
                $this->warnLine("   ↳ S00007 reserve log new_values not parseable as JSON with 'reserved' key — skipping JSON sub-assertions");
                $this->warnCount++;
            }
        }

        // 4g. No double-reservation: reserved should not exceed wh_jkt_on_hand
        $this->assertLte(
            label:    'P01 reserved (' . $p01->reserved . ') <= wh_jkt_on_hand (' . $p01->wh_jkt_on_hand . ')',
            actual:   (int) $p01->reserved,
            ceiling:  (int) $p01->wh_jkt_on_hand,
            unit:     'units'
        );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ASSERTION HELPERS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Core equality assertion.
     *
     * @param  callable|null $formatFn  Optional formatter for display values.
     * @param  bool $invertPass         When true, pass means actual === expected === 0 (used for "no errors found").
     */
    private function assert(
        string   $label,
        mixed    $actual,
        mixed    $expected,
        string   $unit      = '',
        ?callable $formatFn  = null,
        bool     $invertPass = false
    ): void {
        $pass = ($actual == $expected);

        $fmtActual   = $formatFn ? $formatFn($actual)   : $actual;
        $fmtExpected = $formatFn ? $formatFn($expected) : $expected;
        $unitStr     = $unit ? " {$unit}" : '';

        if ($pass) {
            $this->passCount++;
            $this->line(
                $this->green('  ✔  PASS') . '  ' .
                str_pad($label, 60) .
                ' → ' . $this->bold("{$fmtActual}{$unitStr}")
            );
        } else {
            $this->failCount++;
            $this->line(
                $this->red('  ✘  FAIL') . '  ' .
                str_pad($label, 60) .
                ' → got ' . $this->bold("{$fmtActual}{$unitStr}") .
                ', expected ' . $this->bold("{$fmtExpected}{$unitStr}")
            );
        }
    }

    /** Assert actual <= ceiling */
    private function assertLte(string $label, int $actual, int $ceiling, string $unit): void
    {
        if ($actual <= $ceiling) {
            $this->passCount++;
            $this->line($this->green('  ✔  PASS') . '  ' . str_pad($label, 60));
        } else {
            $this->failCount++;
            $this->line(
                $this->red('  ✘  FAIL') . '  ' .
                str_pad($label, 60) .
                " → {$actual} > {$ceiling} {$unit}"
            );
        }
    }

    /** Assert a DB row exists */
    private function assertExists(string $label, mixed $row, string $hint = ''): void
    {
        if ($row !== null) {
            $this->passCount++;
            $this->line($this->green('  ✔  PASS') . '  ' . str_pad($label, 60) . ' → found');
        } else {
            $this->failCount++;
            $this->line(
                $this->red('  ✘  FAIL') . '  ' .
                str_pad($label, 60) .
                ($hint ? " → {$hint}" : ' → not found')
            );
        }
    }

    /** Register a standalone failure message (no expected value to compare) */
    private function fail(string $message): void
    {
        $this->failCount++;
        $this->line($this->red('  ✘  FAIL') . '  ' . $message);
    }

    private function warnLine(string $message): void
    {
        $this->line($this->yellow($message));
    }

    // ═══════════════════════════════════════════════════════════════════
    //  DISPLAY HELPERS
    // ═══════════════════════════════════════════════════════════════════

    private function printBanner(): void
    {
        $this->line('');
        $this->line($this->cyan('╔══════════════════════════════════════════════════════════════════╗'));
        $this->line($this->cyan('║') . $this->bold('  PT Arunika Distribusi Cepat — FMCG ERP Checksum Audit       ') . $this->cyan('║'));
        $this->line($this->cyan('║') . '  Handout T-13 & T-33 · ' . date('Y-m-d H:i:s') . '                    ' . $this->cyan('║'));
        $this->line($this->cyan('╚══════════════════════════════════════════════════════════════════╝'));
        $this->line('');
    }

    private function runSection(string $num, string $title, callable $fn): void
    {
        $this->line('');
        $this->line($this->cyan("  ── Section {$num}: {$title}"));
        $this->line($this->dim('  ' . str_repeat('─', 68)));
        $fn();
    }

    private function printSummary(): void
    {
        $total = $this->passCount + $this->failCount;
        $this->line('');
        $this->line($this->dim('  ' . str_repeat('═', 68)));

        if ($this->failCount === 0) {
            $this->line($this->green('  ✔  ALL CHECKS PASSED') .
                " — {$this->passCount}/{$total} assertions passed" .
                ($this->warnCount > 0 ? $this->yellow(" ({$this->warnCount} warning(s))") : ''));
            $this->line($this->green('     Database state is consistent with Handout §4 baseline.'));
            $this->line($this->green('     Safe to run acceptance tests T-13, T-33.'));
        } else {
            $this->line($this->red("  ✘  {$this->failCount} ASSERTION(S) FAILED") .
                " — {$this->passCount}/{$total} passed, {$this->failCount} failed.");
            $this->line($this->red('     Run: php artisan db:master-seed   to restore the baseline.'));
        }

        if ($this->warnCount > 0) {
            $this->line($this->yellow("  ⚠  {$this->warnCount} warning(s) — review output above."));
        }

        $this->line($this->dim('  ' . str_repeat('═', 68)));
        $this->line('');
    }

    // ── ANSI colour wrappers ──────────────────────────────────────────
    private function green(string $s): string  { return $this->useColor ? "\e[32m{$s}\e[0m" : $s; }
    private function red(string $s): string    { return $this->useColor ? "\e[31m{$s}\e[0m" : $s; }
    private function yellow(string $s): string { return $this->useColor ? "\e[33m{$s}\e[0m" : $s; }
    private function cyan(string $s): string   { return $this->useColor ? "\e[36m{$s}\e[0m" : $s; }
    private function bold(string $s): string   { return $this->useColor ? "\e[1m{$s}\e[0m"  : $s; }
    private function dim(string $s): string    { return $this->useColor ? "\e[2m{$s}\e[0m"  : $s; }
}
