<?php
// Salir si WordPress no lo está llamando directamente
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Si creaste tablas personalizadas, puedes eliminarlas
global $wpdb;
$tabla_nombre = $wpdb->prefix . 'reservalo_ordenes';

$wpdb->query("DROP TABLE IF EXISTS {$tabla_nombre}");


