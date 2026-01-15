<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            
            // Request info
            $table->string('method', 10); // GET, POST, etc
            $table->string('endpoint'); // /api/v1/campaigns/send
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            // Request/Response
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->integer('response_status')->nullable();
            $table->json('response_body')->nullable();
            
            // Performance
            $table->float('duration_ms')->nullable(); // Tiempo de respuesta
            
            // Status
            $table->enum('status', ['success', 'error', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('created_at');
            $table->index(['client_id', 'created_at']);
            $table->index('endpoint');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
