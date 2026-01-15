<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Plan Base
        $plan = Plan::firstOrCreate(
            ['slug' => 'basico'],
            [
                'name' => 'Plan Básico',
                'description' => 'Plan ideal para pequeñas empresas',
                'monthly_sms_limit' => 1000,
                'monthly_whatsapp_limit' => 500,
                'monthly_email_limit' => 2000,
                'price_monthly' => 499.00,
                'currency' => 'MXN',
                'features' => [
                    'sms' => true,
                    'whatsapp' => true,
                    'email' => true,
                    'api_access' => true,
                    'templates' => 10,
                ],
                'is_active' => true,
            ]
        );

        $this->command->info("✅ Plan creado: {$plan->name}");

        // 2. Crear Cliente Demo
        $client = Client::firstOrCreate(
            ['email' => 'demo@test.com'],
            [
                'name' => 'Cliente Demo',
                'phone' => '+526561234567',
                'rfc' => 'DEMO123456XXX',
                'industry' => 'Tecnología',
                'status' => 'active',
                'api_token' => hash('sha256', Str::random(60)),
                'volume_tier' => 'small',
            ]
        );

        $this->command->info("✅ Cliente creado: {$client->name}");
        $this->command->info("   API Token: {$client->api_token}");

        // 3. Crear Suscripción
        Subscription::firstOrCreate(
            ['client_id' => $client->id, 'status' => 'active'],
            [
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
                'usage_resets_at' => now()->endOfMonth(),
            ]
        );

        $this->command->info("✅ Suscripción activa");

        // 4. Crear Templates de ejemplo
        $this->createDemoTemplates();
    }

    protected function createDemoTemplates(): void
    {
        // Template SMS simple
        Template::firstOrCreate(
            ['code' => 'welcome-sms'],
            [
                'name' => 'Bienvenida SMS',
                'description' => 'Mensaje de bienvenida para nuevos clientes',
                'channel' => 'sms',
                'content' => 'Hola {{nombre}}, bienvenido a {{empresa}}. Tu código de verificación es: {{codigo}}',
                'components' => [
                    ['type' => 'text', 'content' => 'Hola {{nombre}}, bienvenido a {{empresa}}.'],
                    ['type' => 'text', 'content' => 'Tu código de verificación es: {{codigo}}'],
                ],
                'variables' => ['nombre', 'empresa', 'codigo'],
                'status' => 'active',
            ]
        );

        // Template WhatsApp con imagen
        Template::firstOrCreate(
            ['code' => 'promo-whatsapp'],
            [
                'name' => 'Promoción WhatsApp',
                'description' => 'Promoción con imagen para WhatsApp',
                'channel' => 'whatsapp',
                'content' => '🎉 ¡Hola {{nombre}}! Tenemos una oferta especial para ti. {{mensaje}} Válido hasta: {{fecha}}',
                'components' => [
                    ['type' => 'image', 'url' => '', 'alt' => 'Imagen promocional'],
                    ['type' => 'text', 'content' => '🎉 ¡Hola {{nombre}}!'],
                    ['type' => 'text', 'content' => 'Tenemos una oferta especial para ti.'],
                    ['type' => 'text', 'content' => '{{mensaje}}'],
                    ['type' => 'text', 'content' => 'Válido hasta: {{fecha}}'],
                    ['type' => 'button', 'text' => 'Ver oferta', 'url' => '{{link}}'],
                ],
                'variables' => ['nombre', 'mensaje', 'fecha', 'link'],
                'media_type' => 'image',
                'status' => 'active',
            ]
        );

        // Template recordatorio
        Template::firstOrCreate(
            ['code' => 'reminder-sms'],
            [
                'name' => 'Recordatorio Cita',
                'description' => 'Recordatorio de cita médica/servicio',
                'channel' => 'sms',
                'content' => '📅 Recordatorio: {{nombre}}, tu cita es el {{fecha}} a las {{hora}}. Dirección: {{direccion}}',
                'components' => [
                    ['type' => 'text', 'content' => '📅 Recordatorio:'],
                    ['type' => 'text', 'content' => '{{nombre}}, tu cita es el {{fecha}} a las {{hora}}.'],
                    ['type' => 'text', 'content' => 'Dirección: {{direccion}}'],
                ],
                'variables' => ['nombre', 'fecha', 'hora', 'direccion'],
                'status' => 'active',
            ]
        );

        $this->command->info("✅ 3 Templates de ejemplo creados");
    }
}
