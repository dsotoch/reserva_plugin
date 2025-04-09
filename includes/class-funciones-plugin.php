<?php
function obtener_precio_producto_por_nombre($nombre)
{
    global $wpdb;

    // Vaciar completamente el carrito antes de buscar el producto
    WC()->cart->empty_cart(true);

    // Buscar el ID del producto por su nombre
    $producto_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'product' LIMIT 1",
            $nombre
        )
    );

    // Si no se encuentra el producto, retornar false
    if (!$producto_id) {
        return false;
    }

    // Obtener el objeto del producto de WooCommerce
    $producto = wc_get_product($producto_id);
    if (!$producto) {
        return false;
    }

    // Agregar el nuevo producto al carrito
    WC()->cart->add_to_cart($producto_id, 1);

    // Retornar el precio del producto
    return $producto->get_price();
}



function obtener_precio_desde_cliente_ajax()
{
    if (!isset($_GET['nombre'])) {
        wp_send_json_error("Falta el nombre del producto");
    }

    $nombre_producto = sanitize_text_field($_GET['nombre']);
    $precio = obtener_precio_producto_por_nombre($nombre_producto);

    if ($precio !== false) {
        wp_send_json_success(['precio' => $precio]);
    } else {
        wp_send_json_error('Producto no encontrado');
    }
}

// Habilitar AJAX para usuarios no autenticados
add_action('wp_ajax_nopriv_obtenerPrecio', 'obtener_precio_desde_cliente_ajax');
// Habilitar AJAX para usuarios autenticados
add_action('wp_ajax_obtenerPrecio', 'obtener_precio_desde_cliente_ajax');


function cambiar_estado_cita()
{
    global $wpdb;

    // Verificar que los datos se reciban correctamente
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        wp_send_json_error("Datos incompletos.");
        return;
    }

    $id = intval($_POST['id']);
    $estado = sanitize_text_field($_POST['estado']);
    $tabla = $wpdb->prefix . 'reservalo_ordenes';

    // Verificar si la orden existe
    $existe = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$tabla} WHERE id = %d LIMIT 1", $id));

    if ($existe) {
        $wpdb->update(
            $tabla,
            ['estado' => $estado],  // Datos a actualizar
            ['id' => $id],  // Condición WHERE
            ['%s'],  // Tipo de dato (string)
            ['%d']   // Tipo de dato (int)
        );

        wp_send_json_success("Orden modificada correctamente.");
    } else {
        wp_send_json_error("No se encontró la orden.");
    }

    wp_die(); // Finalizar el proceso AJAX
}

// Habilitar AJAX para usuarios autenticados y no autenticados
add_action('wp_ajax_cambiarEstado', 'cambiar_estado_cita');
add_action('wp_ajax_nopriv_cambiarEstado', 'cambiar_estado_cita');

function agregar_ajaxurl_a_script() {
    ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <?php
}
add_action('wp_head', 'agregar_ajaxurl_a_script');

add_action('wp_ajax_recargar_checkout', 'recargar_checkout');
add_action('wp_ajax_nopriv_recargar_checkout', 'recargar_checkout');


function recargar_checkout() {
    echo do_shortcode('[woocommerce_checkout]');
}
