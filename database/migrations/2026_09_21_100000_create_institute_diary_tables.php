<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('institute_diary_holidays')) {
            Schema::create('institute_diary_holidays', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('holiday_type', 40)->default('general');
                $table->date('start_date');
                $table->date('end_date');
                $table->text('description')->nullable();
                $table->string('academic_year', 20);
                $table->boolean('is_recurring')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['institute_id', 'academic_year', 'start_date'], 'inst_diary_holidays_list_idx');
            });
        } elseif (! $this->indexExists('institute_diary_holidays', 'inst_diary_holidays_list_idx')) {
            Schema::table('institute_diary_holidays', function (Blueprint $table) {
                $table->index(['institute_id', 'academic_year', 'start_date'], 'inst_diary_holidays_list_idx');
            });
        }

        if (! Schema::hasTable('institute_leave_rules')) {
            Schema::create('institute_leave_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
                $table->string('leave_type');
                $table->unsignedSmallInteger('allowed_days')->default(0);
                $table->json('applicable_to')->nullable();
                $table->boolean('is_paid')->default(true);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['institute_id', 'sort_order'], 'inst_leave_rules_list_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_leave_rules');
        Schema::dropIfExists('institute_diary_holidays');
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $tableName = $connection->getTablePrefix().$table;
        $database = $connection->getDatabaseName();

        $rows = $connection->select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $tableName, $indexName]
        );

        return $rows !== [];
    }
};
