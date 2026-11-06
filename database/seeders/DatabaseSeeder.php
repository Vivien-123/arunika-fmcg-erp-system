<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * DatabaseSeeder — PT Arunika Distribusi Cepat
 *
 * Single source of truth: RunFmcgMasterSeed (php artisan db:master-seed).
 *
 * FmcgSeeder.php has been RETIRED (renamed to FmcgSeeder.php.bak) because:
 *   - Its schema definitions diverged from the authoritative RunFmcgMasterSeed DDL
 *   - It used old column names (wh_jkt_reserved, open_ap, track_lot) that no
 *     longer match the Eloquent models
 *   - RunFmcgMasterSeed is the canonical seed path per M2-W1-01 baseline
 *
 * To reset the database:
 *   php artisan db:master-seed          (recommended — full DDL + seed)
 *   php artisan fmcg:verify-checksum    (validate — should return 34/34 PASS)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Delegate to the Artisan command which handles DROP + CREATE + SEED
        // in a single idempotent operation.
        Artisan::call('db:master-seed');
        $this->command->getOutput()->write(Artisan::output());
    }
}
