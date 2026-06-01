<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('consultant_services')) {
            return;
        }

        Schema::table('consultant_services', function (Blueprint $table) {
            if (! Schema::hasColumn('consultant_services', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location');
            }

            if (! Schema::hasColumn('consultant_services', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            if (! Schema::hasColumn('consultant_services', 'image_path')) {
                $table->string('image_path')->nullable()->after('is_online');
            }
        });

        if (Schema::hasColumn('consultant_services', 'images') && Schema::hasColumn('consultant_services', 'image_path')) {
            DB::table('consultant_services')
                ->whereNull('image_path')
                ->whereNotNull('images')
                ->orderBy('id')
                ->select(['id', 'images'])
                ->chunkById(100, function ($services): void {
                    foreach ($services as $service) {
                        $images = json_decode((string) $service->images, true);
                        $imagePath = is_array($images) ? ($images[0] ?? null) : null;

                        if (is_string($imagePath) && $imagePath !== '') {
                            DB::table('consultant_services')
                                ->where('id', $service->id)
                                ->update(['image_path' => $imagePath]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('consultant_services') || ! Schema::hasColumn('consultant_services', 'images')) {
            return;
        }

        Schema::table('consultant_services', function (Blueprint $table) {
            foreach (['image_path', 'longitude', 'latitude'] as $column) {
                if (Schema::hasColumn('consultant_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
