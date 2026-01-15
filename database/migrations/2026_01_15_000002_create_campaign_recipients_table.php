<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            
            // Recipient info
            $table->string('to'); // E.164 format: +526561234567
            $table->string('tracking_id')->nullable(); // Para anonimización
            $table->json('params')->nullable(); // Variables personalizadas por recipient
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'queued',
                'sent',
                'delivered',
                'read',
                'failed',
                'undelivered'
            ])->default('pending');
            
            // Provider response
            $table->string('provider_sid')->nullable(); // Twilio SID
            $table->string('provider_status')->nullable(); // Raw status from provider
            $table->text('error_message')->nullable();
            $table->string('error_code')->nullable();
            
            // Timestamps
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for webhook updates and queries
            $table->index('provider_sid');
            $table->index(['campaign_id', 'status']);
            $table->index('tracking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};
