<?php
if (!defined('ABSPATH')) {
    exit;
}

function mostrar_metodos_pago_dentro_formulario($content)
{
    $pagina_id = intval(get_option('reservalo_id_select_paginas', 0)); // ID de la página configurada
    $formulario_id = get_option('reservalo_id_formulario', ''); // ID del formulario configurado

    if (is_page($pagina_id) && !empty($formulario_id)) {
        // Agregamos el checkout oculto dentro del formulario
        $checkout = '<div id="checkout-oculto" style="display: none;">' . do_shortcode('[woocommerce_checkout]') . '</div>';
        
        // Insertamos el checkout DENTRO del formulario con jQuery
        $script = "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var formulario = document.querySelector('form[id=\"$formulario_id\"]');
                if (formulario) {
                    var checkout = document.getElementById('checkout-oculto');
                    formulario.appendChild(checkout); // Inserta el checkout dentro del formulario
                }
            });
        </script>";

        return $content . $checkout . $script;
    }

    return $content;
}

add_filter('the_content', 'mostrar_metodos_pago_dentro_formulario');



function cambiar_texto_boton_finalizar_pedido($text)
{
    return 'Confirmar y pagar'; // Cambia este texto por el que desees
}
add_filter('woocommerce_order_button_text', 'cambiar_texto_boton_finalizar_pedido');




function hacer_campos_checkout_opcionales($fields)
{
    // Valores por defecto para facturación
    $valores_por_defecto_billing = [
        'billing_first_name' => 'Nombre',
        'billing_last_name' => 'Apellido',
        'billing_address_1' => 'Calle  123',
        'billing_city' => 'Ciudad',
        'billing_postcode' => '00000',
        'billing_phone' => '999999999',
        'billing_email' => 'correo@ejemplo.com',
    ];

    foreach ($fields['billing'] as $key => $field) {
        $fields['billing'][$key]['required'] = false;

        if (isset($valores_por_defecto_billing[$key])) {
            $fields['billing'][$key]['default'] = $valores_por_defecto_billing[$key];
        }
    }

    // Valores por defecto para envío (si usas campos de envío distintos)
    $valores_por_defecto_shipping = [
        'shipping_first_name' => 'Nombre',
        'shipping_last_name' => 'Apellido',
        'shipping_address_1' => 'Calle Envío 456',
        'shipping_city' => 'Ciudad Envío',
        'shipping_postcode' => '11111',
    ];

    foreach ($fields['shipping'] as $key => $field) {
        $fields['shipping'][$key]['required'] = false;

        if (isset($valores_por_defecto_shipping[$key])) {
            $fields['shipping'][$key]['default'] = $valores_por_defecto_shipping[$key];
        }
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'hacer_campos_checkout_opcionales');




add_action('woocommerce_thankyou', 'reservalo_registrar_pedido_en_bd');

function reservalo_registrar_pedido_en_bd($order_id)
{
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    global $wpdb;

    // Obtener datos del cliente
    $nombre = $order->get_billing_first_name() ;
    $correo = $order->get_billing_email();
    $celular = $order->get_billing_phone();
    $fecha = date('Y-m-d H:i:s');

    // Obtener productos comprados
    $productos = [];
    $costo_total = 0;

    foreach ($order->get_items() as $item) {
        $productos[] = $item->get_name();
        $costo_total += $item->get_total(); // Suma de precios
    }

    $servicio = implode(', ', $productos);
    $estado = 'pendiente'; // Estado actual del pedido

    // Insertar en la base de datos personalizada
    $tabla_nombre = $wpdb->prefix . 'reservalo_ordenes';
    $wpdb->insert(
        $tabla_nombre,
        [
            'nombre'   => $nombre,
            'correo'   => $correo,
            'celular'  => $celular,
            'servicio' => $servicio,
            'costo'    => $costo_total,
            'fecha'    => $fecha,
            'estado'   => $estado,
        ],
        ['%s', '%s', '%s', '%s', '%f', '%s', '%s']
    );
}



function enviar_email_personalizado_tras_pago($order_id) {
    if (!$order_id) {
        return;
    }

    // Obtener el pedido
    $order = wc_get_order($order_id);

    // Obtener datos del cliente
    $cliente_email = $order->get_billing_email();
    $cliente_nombre = $order->get_billing_first_name();

    // Obtener el logo de la tienda
    $logo_url = get_theme_mod('custom_logo'); // Obtiene el ID del logo
    $logo = wp_get_attachment_image_src($logo_url, 'full');

    // Construir el HTML del correo
    $asunto = "¡Gracias por tu Reserva, $cliente_nombre!";

    $mensaje = '<html><body>';
    if ($logo) {
        $mensaje .= '<p><img src="' . esc_url($logo[0]) . '" alt="Logo de la tienda" style="max-width: 200px;"></p>';
    }
    $mensaje .= "<p>Hola <strong>$cliente_nombre</strong>,</p>";
    $mensaje .= "<p>Gracias por confiar en nosotros 🤝. ¡Te esperamos en Habilitate Medicina Física y Rehabilitación! 😊👐.</p>";
    $mensaje .= "<p>📍 Elias Aguirre 605 piso 9, Miraflores</p>";
    $mensaje .= "<p>🚘 Contamos con estacionamiento</p>";
    $mensaje .= "<p>📲 961853565</p>";
    $mensaje .= "<p>Saludos.</p>";
    $mensaje .= '</body></html>';

    // Encabezados del correo
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Enviar el correo
    wp_mail($cliente_email, $asunto, $mensaje, $headers);
}

add_action('woocommerce_order_status_processing', 'enviar_email_personalizado_tras_pago');
add_action('woocommerce_order_status_completed', 'enviar_email_personalizado_tras_pago');

