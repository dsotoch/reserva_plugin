<?php

/**
 * Plugin Name: Reservalo
 * Plugin URI: https://techubperu.com
 * Description: El plugin Gestión de Reservas y Pagos en WooCommerce facilita la administración de citas y reservas en línea. Permite a los usuarios seleccionar su cita desde un formulario en la misma página donde se muestra el checkout de WooCommerce, simplificando el proceso de pago. Además, incluye opciones avanzadas para filtrar reservas y cambiar su estado según la disponibilidad o confirmación del pago.
 * Version: 1.0
 * Author: Diego Soto 
 * Author URI: https://techubperu.com
 * License: GPL v2 or later
 * Text Domain: mi-plugin-woocommerce
 */

if (!defined('ABSPATH')) {
    exit; // Evitar acceso directo
}

// Definir constante del plugin
define('MI_PLUGIN_WC_PATH', plugin_dir_path(__FILE__));
function reservalo_crear_tabla()
{
    global $wpdb;
    $tabla_nombre = $wpdb->prefix . 'reservalo_ordenes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $tabla_nombre (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        correo VARCHAR(150) NOT NULL,
        celular VARCHAR(20) NOT NULL,
        servicio VARCHAR(255) NOT NULL,
        costo DECIMAL(10,2) NOT NULL,
        fecha DATETIME NOT NULL,
        estado ENUM('pendiente','completado', 'cancelado') NOT NULL DEFAULT 'pendiente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);
}

register_activation_hook(__FILE__, 'reservalo_crear_tabla');

$includes = [
    'includes/class-reserva-plugin.php',
    'includes/class-conf-plugin.php',
    'includes/class-funciones-plugin.php',

];

foreach ($includes as $file) {
    $file_path = MI_PLUGIN_WC_PATH . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("Error: No se encontró el archivo {$file_path}");
    }
}


// Inicializar el plugin
function mi_plugin_wc_init()
{
    new reservalo();
}

add_action('plugins_loaded', 'mi_plugin_wc_init');

function cargar_estilos_admin()
{
    // Cargar CSS
    wp_enqueue_style('reservalo-estilos', plugins_url('assets/css/estilos.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'cargar_estilos_admin');

add_action('wp_enqueue_scripts', 'cargar_estilos_admin');

function cargar_scripts()
{
    wp_enqueue_script('reservalo-funciones', plugins_url('assets/js/funciones.js', __FILE__), ['jquery'], false, true);

    // Obtener opciones y asegurarse de que existan
    $localize_data = [
        'ajax_url' => admin_url('admin-ajax.php'),
        'id_formulario' => get_option('reservalo_id_formulario', ''),
        'id_precio' => get_option('reservalo_id_precio', ''),
        'id_servicio' => get_option('reservalo_id_select_servicio', ''),
        'id_cliente' => get_option('reservalo_id_cliente', ''),
        'id_correo' => get_option('reservalo_id_correo', ''),
        'id_celular' => get_option('reservalo_id_celular', ''),
    ];

    // Enviar datos a JavaScript
    wp_localize_script('reservalo-funciones', 'objeto_ajax', $localize_data);
}
add_action('admin_enqueue_scripts', 'cargar_scripts');

add_action('wp_enqueue_scripts', 'cargar_scripts');
