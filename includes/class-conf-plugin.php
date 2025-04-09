<?php

if (!defined('ABSPATH')) {
    exit; // Evita acceso directo
}

class reservalo
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'reservalo_menu']);
        add_action('admin_init', [$this, 'registrar_opciones']); // Llamar correctamente la función dentro de la clase
    }

    public function reservalo_menu()
    {
        add_menu_page(
            'Reservalo Configuración',
            'Reservalo',
            'manage_options',
            'reservalo-settings',
            [$this, 'reservalo_settings_page'],
            'dashicons-store',
            56
        );
    }

    public function reservalo_settings_page()
    {

?>
        <div class="wrap">
            <h1>Configuración de Reservalo</h1>
            <?php settings_errors(); // Muestra mensajes de éxito o error 
            ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('reservalo_opciones');
                do_settings_sections('reservalo-config');
                submit_button();
                ?>
            </form>
        </div>
<?php
        $this->administracion_reservalo();
    }

    public function registrar_opciones()
    {
        register_setting('reservalo_opciones', 'reservalo_id_select_paginas');
        register_setting('reservalo_opciones', 'reservalo_id_formulario');
        register_setting('reservalo_opciones', 'reservalo_id_select_servicio');
        register_setting('reservalo_opciones', 'reservalo_id_precio');
        register_setting('reservalo_opciones', 'reservalo_id_cliente');
        register_setting('reservalo_opciones', 'reservalo_id_correo');
        register_setting('reservalo_opciones', 'reservalo_id_celular');

        add_settings_section('reservalo_seccion', 'Opciones de Reserva', null, 'reservalo-config');

        add_settings_field('reservalo_id_select_paginas', 'Id de la Pagina del Formulario', [$this, 'reservalo_id_select_paginas_callback'], 'reservalo-config', 'reservalo_seccion');
        add_settings_field('reservalo_id_formulario', 'Id del Formulario', [$this, 'reservalo_id_formulario_callback'], 'reservalo-config', 'reservalo_seccion');
        add_settings_field('reservalo_id_select_servicio', 'Id del selector del servicio del Formulario', [$this, 'reservalo_id_select_servicio_callback'], 'reservalo-config', 'reservalo_seccion');
        add_settings_field('reservalo_id_precio', 'Id del campo precio del servicio dentro del Formulario', [$this, 'reservalo_id_precio_callback'], 'reservalo-config', 'reservalo_seccion');
        add_settings_field('reservalo_id_cliente', 'Id del campo nombre del cliente dentro del Formulario', [$this, 'reservalo_id_cliente_callback'], 'reservalo-config', 'reservalo_seccion');
        add_settings_field('reservalo_id_correo', 'Id del campo correo dentro del Formulario', [$this, 'reservalo_id_correo_callback'], 'reservalo-config', 'reservalo_seccion');
        add_settings_field('reservalo_id_celular', 'Id del campo celular dentro del Formulario', [$this, 'reservalo_id_celular_callback'], 'reservalo-config', 'reservalo_seccion');
    }

    public function reservalo_id_cliente_callback()
    {
        $valor = get_option('reservalo_id_cliente', '');
        echo '<input type="text" name="reservalo_id_cliente" id="reservalo_id_cliente" class="regular-text" value="' . esc_attr($valor) . '" placeholder="" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;"/>';
    }

    public function reservalo_id_celular_callback()
    {
        $valor = get_option('reservalo_id_celular', '');
        echo '<input type="text" name="reservalo_id_celular" id="reservalo_id_celular" class="regular-text" value="' . esc_attr($valor) . '" placeholder="" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;"/>';
    }

    public function reservalo_id_correo_callback()
    {
        $valor = get_option('reservalo_id_correo', '');
        echo '<input type="text" name="reservalo_id_correo" id="reservalo_id_correo" class="regular-text" value="' . esc_attr($valor) . '" placeholder="" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;"/>';
    }

    public function reservalo_id_select_paginas_callback()
    {
        $paginas = get_pages();
        $valor = get_option('reservalo_id_select_paginas', '');

        echo '<select name="reservalo_id_select_paginas" id="reservalo_id_select_paginas" class="regular-text">';
        echo '<option value="">-- Seleccionar Página --</option>';

        foreach ($paginas as $pagina) {
            $selected = ($pagina->ID == $valor) ? 'selected' : '';
            echo '<option value="' . esc_attr($pagina->ID) . '" ' . $selected . '>' . esc_html($pagina->post_title) . '</option>';
        }

        echo '</select>';
    }

    public function reservalo_id_formulario_callback()
    {
        $valor = get_option('reservalo_id_formulario', '');
        echo '<input type="text" name="reservalo_id_formulario" id="reservalo_id_formulario" class="regular-text" value="' . esc_attr($valor) . '" placeholder="Identificador para mostrar los métodos de pago" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;"/>';
    }

    public function reservalo_id_select_servicio_callback()
    {
        $valor = get_option('reservalo_id_select_servicio', '');
        echo '<input type="text" name="reservalo_id_select_servicio" id="reservalo_id_select_servicio" class="regular-text" value="' . esc_attr($valor) . '" placeholder="Identificador para seleccionar el servicio" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;"/>';
    }

    public function reservalo_id_precio_callback()
    {
        $valor = get_option('reservalo_id_precio', '');
        echo '<input type="text" name="reservalo_id_precio" id="reservalo_id_precio" class="regular-text" value="' . esc_attr($valor) . '" placeholder="Identificador del campo para colocar precio del servicio" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;"/>';
    }

    public function administracion_reservalo()
    {
        echo '<hr/>';
        echo '<div class="wrap">
        <h2>Panel de Administración Reservalo</h2>
        
        <input type="text" id="input_buscar_reservalo" name="search_user" placeholder="Buscar por nombre o correo...">
        
        <h3>Resultados de la búsqueda</h3>
        <table id="tabla_reservalo" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Celular</th>
                    <th>Servicio</th>
                    <th>Costo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Editar</th>

                </tr>
            </thead>
            <tbody>';

        // Obtener los datos de la base de datos
        $datos = $this->traer_datos_reservas();

        if (!empty($datos)) {
            foreach ($datos as $reserva) {
                echo '<tr>
                    <td>' . esc_html($reserva['nombre']) . '</td>
                    <td>' . esc_html($reserva['correo']) . '</td>
                    <td>' . esc_html($reserva['celular']) . '</td>
                    <td>' . esc_html($reserva['servicio']) . '</td>
                    <td>S/ ' . esc_html($reserva['costo']) . '</td>
                    <td>' . esc_html($reserva['fecha']) . '</td>
                    <td>' . esc_html($reserva['estado']) . '</td>
                     <td>
                        <select id="reservalo_admin_select" class="estado-reserva" data-id="' . esc_attr($reserva['id']) . '">
                            <option value="pendiente" ' . selected($reserva['estado'], 'pendiente', false) . '>Pendiente</option>
                            <option value="completado" ' . selected($reserva['estado'], 'completado', false) . '>Completado</option>
                            <option value="cancelado" ' . selected($reserva['estado'], 'cancelado', false) . '>Cancelado</option>
                        </select>
                    </td>
                </tr>';
            }
        } else {
            echo '<tr><td colspan="8" style="text-align:center;">No hay reservas registradas.</td></tr>';
        }

        echo '</tbody></table></div>';
    }

    private function traer_datos_reservas()
    {
        global $wpdb;
        $tabla_nombre = $wpdb->prefix . 'reservalo_ordenes';

        // Obtener los datos de la tabla
        $resultados = $wpdb->get_results("SELECT * FROM $tabla_nombre", ARRAY_A);

        return $resultados;
    }
}
