<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            if (!Schema::hasColumn('templates', 'components')) {
                $table->json('components')->nullable()->after('content');
            }
            if (!Schema::hasColumn('templates', 'media_url')) {
                $table->string('media_url')->nullable()->after('components');
            }
            if (!Schema::hasColumn('templates', 'media_type')) {
                $table->string('media_type')->nullable()->after('media_url');
            }
            if (!Schema::hasColumn('templates', 'status')) {
                $table->enum('status', ['draft', 'active', 'archived'])->default('draft')->after('media_type');
            }
            if (!Schema::hasColumn('templates', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn(['components', 'media_url', 'media_type', 'status', 'description']);
        });
    }
};
