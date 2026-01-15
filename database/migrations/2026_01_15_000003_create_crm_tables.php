<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de Planes/Productos
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Básico", "Pro", "Enterprise"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Límites
            $table->unsignedInteger('monthly_sms_limit')->default(0);
            $table->unsignedInteger('monthly_whatsapp_limit')->default(0);
            $table->unsignedInteger('monthly_email_limit')->default(0);
            $table->unsignedInteger('max_campaigns_per_month')->default(0);
            $table->unsignedInteger('max_recipients_per_campaign')->default(0);
            
            // Precios
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->string('currency', 3)->default('MXN');
            
            // Features
            $table->json('features')->nullable(); // {"api_access": true, "priority_support": false, ...}
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });

        // Tabla de Clientes (Empresas)
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la empresa
            $table->string('legal_name')->nullable(); // Razón social
            $table->string('rfc')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            
            // Dirección
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('México');
            $table->string('postal_code')->nullable();
            
            // API Access
            $table->string('api_token', 80)->unique()->nullable();
            $table->timestamp('api_token_expires_at')->nullable();
            
            // Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'trial'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            
            // Metadata
            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla de Suscripciones (Relación Client-Plan)
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            
            // Período
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Status
            $table->enum('status', ['active', 'cancelled', 'expired', 'past_due'])->default('active');
            
            // Uso actual del período
            $table->unsignedInteger('sms_used')->default(0);
            $table->unsignedInteger('whatsapp_used')->default(0);
            $table->unsignedInteger('email_used')->default(0);
            $table->unsignedInteger('campaigns_used')->default(0);
            
            // Reset de contadores
            $table->timestamp('usage_resets_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['client_id', 'status']);
        });

        // Tabla de Usuarios del Cliente
        Schema::create('client_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            
            // Rol dentro del cliente
            $table->enum('role', ['admin', 'manager', 'operator', 'viewer'])->default('operator');
            
            // Permisos específicos
            $table->json('permissions')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_users');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('plans');
    }
};
