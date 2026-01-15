<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('request_id')->unique(); // Idempotencia
            $table->string('external_id')->nullable()->index();
            $table->string('name');
            $table->string('template_id')->nullable();
            
            // Routing
            $table->enum('channel', ['sms', 'whatsapp', 'email'])->default('sms');
            $table->string('route_tier')->default('sandbox'); // sandbox, official
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->integer('validity_seconds')->default(3600);
            
            // Status tracking
            $table->enum('status', [
                'received',
                'scheduled',
                'processing',
                'paused_by_schedule',
                'paused_by_user',
                'completed',
                'failed',
                'cancelled'
            ])->default('received');
            
            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->string('timezone')->default('America/Mexico_City');
            $table->string('on_timeout_policy')->default('resume'); // resume, cancel
            
            // Execution timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Counters
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            
            // JSON data
            $table->json('content_vars')->nullable();
            $table->json('tags')->nullable();
            
            // Reporting
            $table->string('callback_url')->nullable();
            
            // Security
            $table->string('authorization_pin')->nullable();
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['status', 'scheduled_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
