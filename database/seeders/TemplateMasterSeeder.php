<?php

namespace Database\Seeders;

use App\Models\TemplateMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemplateMasterSeeder extends Seeder
{
    public function run(): void
    {
        $masters = [
            // === SMS ===
            [
                'name' => 'Bienvenida Simple',
                'slug' => 'welcome-sms',
                'description' => 'Mensaje de bienvenida breve y efectivo',
                'category' => 'welcome',
                'channel' => 'sms',
                'content' => 'Hola {{nombre}}, bienvenido a {{empresa}}. Estamos encantados de tenerte. Responde AYUDA si necesitas asistencia.',
                'editable_fields' => ['empresa', 'mensaje_extra'],
                'variables' => ['nombre', 'empresa'],
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Código OTP',
                'slug' => 'otp-sms',
                'description' => 'Código de verificación de un solo uso',
                'category' => 'otp',
                'channel' => 'sms',
                'content' => '{{empresa}}: Tu código de verificación es {{codigo}}. Válido por 5 minutos. No compartas este código.',
                'editable_fields' => ['empresa'],
                'variables' => ['codigo', 'empresa'],
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Recordatorio Cita',
                'slug' => 'reminder-sms',
                'description' => 'Recordatorio para citas o eventos',
                'category' => 'reminder',
                'channel' => 'sms',
                'content' => '📅 Recordatorio: {{nombre}}, tu cita en {{empresa}} es el {{fecha}} a las {{hora}}. Confirma respondiendo SI.',
                'editable_fields' => ['empresa'],
                'variables' => ['nombre', 'fecha', 'hora', 'empresa'],
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Confirmación Pedido',
                'slug' => 'order-sms',
                'description' => 'Confirmación de compra o pedido',
                'category' => 'transactional',
                'channel' => 'sms',
                'content' => '✅ {{empresa}}: Pedido #{{numero}} confirmado. Total: ${{total}}. Entrega estimada: {{fecha}}. Rastrea en: {{link}}',
                'editable_fields' => ['empresa'],
                'variables' => ['numero', 'total', 'fecha', 'link', 'empresa'],
                'is_featured' => false,
                'sort_order' => 4,
            ],

            // === WHATSAPP ===
            [
                'name' => 'Bienvenida con Imagen',
                'slug' => 'welcome-whatsapp',
                'description' => 'Bienvenida premium con logo e imagen',
                'category' => 'welcome',
                'channel' => 'whatsapp',
                'content' => "¡Hola {{nombre}}! 👋\n\nBienvenido a {{empresa}}.\n\n{{mensaje}}\n\nEstamos aquí para ayudarte. ¡Escríbenos cuando lo necesites!",
                'structure' => [
                    ['type' => 'image', 'editable' => true],
                    ['type' => 'text', 'editable' => true],
                    ['type' => 'button', 'text' => 'Ver más', 'editable' => true],
                ],
                'editable_fields' => ['empresa', 'mensaje', 'imagen', 'boton_texto', 'boton_url'],
                'variables' => ['nombre', 'empresa', 'mensaje'],
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Promoción con Descuento',
                'slug' => 'promo-whatsapp',
                'description' => 'Promoción con imagen y call-to-action',
                'category' => 'promo',
                'channel' => 'whatsapp',
                'content' => "🎉 ¡Hola {{nombre}}!\n\n{{empresa}} tiene algo especial para ti:\n\n{{mensaje}}\n\n🔥 {{descuento}}% de descuento\n⏰ Válido hasta: {{fecha}}\n\n👇 Aprovecha ahora",
                'structure' => [
                    ['type' => 'image', 'editable' => true],
                    ['type' => 'text', 'editable' => true],
                    ['type' => 'button', 'text' => 'Comprar ahora', 'editable' => true],
                ],
                'editable_fields' => ['empresa', 'mensaje', 'descuento', 'fecha', 'imagen', 'boton_url'],
                'variables' => ['nombre', 'empresa', 'mensaje', 'descuento', 'fecha'],
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Recordatorio con Ubicación',
                'slug' => 'reminder-whatsapp',
                'description' => 'Recordatorio con dirección y mapa',
                'category' => 'reminder',
                'channel' => 'whatsapp',
                'content' => "📅 {{nombre}}, te recordamos tu cita:\n\n🏢 {{empresa}}\n📍 {{direccion}}\n🗓️ {{fecha}} a las {{hora}}\n\n¿Necesitas reagendar? Responde CAMBIAR",
                'editable_fields' => ['empresa', 'direccion'],
                'variables' => ['nombre', 'empresa', 'direccion', 'fecha', 'hora'],
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Encuesta NPS',
                'slug' => 'survey-whatsapp',
                'description' => 'Encuesta de satisfacción rápida',
                'category' => 'survey',
                'channel' => 'whatsapp',
                'content' => "Hola {{nombre}} 👋\n\n¿Cómo fue tu experiencia con {{empresa}}?\n\nResponde con un número del 1 al 10:\n1️⃣ = Muy mal\n5️⃣ = Regular\n🔟 = Excelente\n\n¡Tu opinión nos ayuda a mejorar!",
                'editable_fields' => ['empresa'],
                'variables' => ['nombre', 'empresa'],
                'is_featured' => false,
                'sort_order' => 4,
            ],

            // === EMAIL (texto para referencia) ===
            [
                'name' => 'Email Bienvenida',
                'slug' => 'welcome-email',
                'description' => 'Email de onboarding para nuevos usuarios',
                'category' => 'welcome',
                'channel' => 'email',
                'content' => "Bienvenido a {{empresa}}, {{nombre}}!\n\nEstamos emocionados de tenerte con nosotros.\n\n{{mensaje}}\n\nPrimeros pasos:\n1. Completa tu perfil\n2. Explora nuestros servicios\n3. Contacta a soporte si necesitas ayuda\n\nSaludos,\nEl equipo de {{empresa}}",
                'editable_fields' => ['empresa', 'mensaje', 'logo'],
                'variables' => ['nombre', 'empresa', 'mensaje'],
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Newsletter Mensual',
                'slug' => 'newsletter-email',
                'description' => 'Newsletter con noticias y actualizaciones',
                'category' => 'newsletter',
                'channel' => 'email',
                'content' => "Novedades de {{empresa}} - {{mes}}\n\nHola {{nombre}},\n\n{{contenido}}\n\nGracias por ser parte de nuestra comunidad.\n\nSaludos,\n{{empresa}}",
                'editable_fields' => ['empresa', 'contenido', 'logo'],
                'variables' => ['nombre', 'empresa', 'mes', 'contenido'],
                'is_featured' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($masters as $master) {
            TemplateMaster::firstOrCreate(
                ['slug' => $master['slug']],
                array_merge($master, ['is_active' => true])
            );
        }

        $this->command->info("✅ " . count($masters) . " Template Masters creados");
    }
}
