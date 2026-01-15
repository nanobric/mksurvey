<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedInteger('expected_monthly_volume')->nullable()->after('status');
            $table->string('volume_tier')->nullable()->after('expected_monthly_volume'); // small, medium, large, enterprise
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['expected_monthly_volume', 'volume_tier']);
        });
    }
};
