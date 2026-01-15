<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template Masters - Creados por Admin/Expertos
        Schema::create('template_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category', ['welcome', 'promo', 'reminder', 'survey', 'otp', 'transactional', 'newsletter']);
            $table->enum('channel', ['sms', 'whatsapp', 'email']);
            $table->text('content'); // Contenido con placeholders
            $table->json('structure')->nullable(); // Bloques del template
            $table->json('editable_fields'); // Campos que el cliente puede editar
            $table->json('variables'); // Variables disponibles
            $table->string('preview_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Client Templates - Personalizaciones por cliente
        Schema::create('client_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_id')->constrained('template_masters')->onDelete('cascade');
            $table->string('name');
            $table->json('customizations'); // Valores personalizados del cliente
            $table->string('media_url')->nullable(); // Logo/imagen del cliente
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamps();
            
            $table->unique(['client_id', 'name']);
        });

        // Agregar relación en campaigns
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'client_template_id')) {
                $table->foreignId('client_template_id')->nullable()->after('template_id')
                    ->constrained('client_templates')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_template_id');
        });
        Schema::dropIfExists('client_templates');
        Schema::dropIfExists('template_masters');
    }
};
