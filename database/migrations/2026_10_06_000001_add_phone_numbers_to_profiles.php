<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $tables = [
        'users' => 'phone_number',
        'employees' => 'phone_number',
        'institutes' => 'phone',
        'educators' => 'phone',
        'vendors' => 'phone',
        'consultants' => 'phone',
        'service_providers' => 'phone',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $legacyColumn) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'phone_numbers')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($legacyColumn, $table) {
                if (Schema::hasColumn($table, $legacyColumn)) {
                    $blueprint->json('phone_numbers')->nullable()->after($legacyColumn);
                } else {
                    $blueprint->json('phone_numbers')->nullable();
                }
            });
        }

        foreach ($this->tables as $table => $legacyColumn) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'phone_numbers')) {
                continue;
            }

            DB::table($table)
                ->whereNotNull($legacyColumn)
                ->where($legacyColumn, '!=', '')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, $legacyColumn) {
                    foreach ($rows as $row) {
                        $digits = preg_replace('/\D+/', '', (string) $row->{$legacyColumn}) ?? '';
                        if ($digits === '') {
                            continue;
                        }

                        DB::table($table)->where('id', $row->id)->update([
                            'phone_numbers' => json_encode([$digits]),
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->tables) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'phone_numbers')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('phone_numbers');
                });
            }
        }
    }
};
