<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('service_provider_services')) {
            return;
        }

        Schema::table('service_provider_services', function (Blueprint $table) {
            if (! Schema::hasColumn('service_provider_services', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location');
            }

            if (! Schema::hasColumn('service_provider_services', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            if (! Schema::hasColumn('service_provider_services', 'image_path')) {
                $table->string('image_path')->nullable()->after('is_online');
            }
        });

        if (Schema::hasColumn('service_provider_services', 'images') && Schema::hasColumn('service_provider_services', 'image_path')) {
            DB::table('service_provider_services')
                ->whereNull('image_path')
                ->whereNotNull('images')
                ->orderBy('id')
                ->select(['id', 'images'])
                ->chunkById(100, function ($services): void {
                    foreach ($services as $service) {
                        $images = json_decode((string) $service->images, true);
                        $imagePath = is_array($images) ? ($images[0] ?? null) : null;

                        if (is_string($imagePath) && $imagePath !== '') {
                            DB::table('service_provider_services')
                                ->where('id', $service->id)
                                ->update(['image_path' => $imagePath]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_provider_services') || ! Schema::hasColumn('service_provider_services', 'images')) {
            return;
        }

        Schema::table('service_provider_services', function (Blueprint $table) {
            foreach (['image_path', 'longitude', 'latitude'] as $column) {
                if (Schema::hasColumn('service_provider_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
