document.addEventListener("DOMContentLoaded", function () {
    let precio = '';
    const input_buscar_reservalo = document.getElementById('input_buscar_reservalo');
    if (input_buscar_reservalo) {
        const tabla_reservalo = document.getElementById('tabla_reservalo');
        input_buscar_reservalo.addEventListener('keyup', () => {
            let filtro = input_buscar_reservalo.value.toLowerCase();
            let filas = tabla_reservalo.getElementsByTagName('tr');

            for (let i = 1; i < filas.length; i++) { // Omitimos la primera fila (encabezado)
                let celdas = filas[i].getElementsByTagName('td');
                if (celdas.length > 1) {
                    let nombre = celdas[0].textContent.toLowerCase(); // Primera columna (Nombre)
                    let correo = celdas[1].textContent.toLowerCase(); // Segunda columna (Correo)

                    if (nombre.includes(filtro) || correo.includes(filtro)) {
                        filas[i].style.display = ''; // Mostrar fila si coincide
                    } else {
                        filas[i].style.display = 'none'; // Ocultar fila si no coincide
                    }
                }
            }
        });
    }
    const reservalo_admin_selects = document.querySelectorAll("#reservalo_admin_select");


    if (reservalo_admin_selects.length > 0) {
        reservalo_admin_selects.forEach(select => {
            select.addEventListener("change", function () {
                let id = this.dataset.id; // Obtener el ID de la reserva
                let nuevo_estado = this.value; // Obtener el estado seleccionado
                if (id && nuevo_estado) {
                    // Llamar a la función AJAX para actualizar el estado
                    cambiarEstadoReserva(id, nuevo_estado);
                } else {
                    console.error("ID o estado no válido.");
                }
            });
        });
    }
    function cambiarEstadoReserva(ide, estado) {
        fetch(`${objeto_ajax.ajax_url}?action=cambiarEstado`, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                id: ide,
                estado: estado
            })
        })
            .then(response => response.json()) // Convertir la respuesta a JSON
            .then(data => {
                if (data.success) {
                    alert("Estado actualizado correctamente.");
                    location.reload();
                } else {
                    alert("Error: " + data.data);
                }
            })
            .catch(error => {
                alert("Hubo un problema con la solicitud.");
                console.error("Error:", error);
            });
    }



    if (typeof objeto_ajax === "undefined") {
        console.error("Error: objeto_ajax no está definido.");
        return;
    }

    const reservalo_form_cita = document.querySelector(`#${objeto_ajax.id_formulario}`);
    if (reservalo_form_cita) {
        reservalo_form_cita.querySelector('button').disabled = true;

    }

    const nombre = document.querySelector(`#${objeto_ajax.id_servicio}`);


    function buscarPrecio() {
        fetch(`${objeto_ajax.ajax_url}?action=obtenerPrecio&nombre=${encodeURIComponent(nombre.value)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`${objeto_ajax.id_precio}`).value = data.data.precio;
                    precio = data.data.precio;
                    reservalo_form_cita.querySelector('button').disabled = false;

                } else {
                    precio = '';
                    document.getElementById(`${objeto_ajax.id_precio}`).value = '';
                    alert("Producto no encontrado");
                }
            })
            .catch(error => console.error("Error al obtener el precio:", error));
    }

    if (nombre) {
        nombre.addEventListener('change', buscarPrecio);
    }

    if (reservalo_form_cita) {
        reservalo_form_cita.addEventListener('submit', (e) => {
            e.preventDefault();
            let datosCliente = {
                nombre: document.getElementById(objeto_ajax.id_cliente).value,
                celular: document.getElementById(objeto_ajax.id_celular).value,
                email: document.getElementById(objeto_ajax.id_correo).value
            };
            if (precio == '') {
                alert("No has seleccionado un servicio");
                return;
            }
            if (datosCliente.nombre == '' || datosCliente.celular == "" || datosCliente.email == "") {
                alert("Completa todos los campos para reservar tu cita.");
                return;
            }
            nombre.disabled = true;
            mostrar_ocultar_checkout();
            actualizar_valores_pedido();
        });
    }

    function actualizar_valores_pedido() {
        // Verifica si los elementos existen antes de llenarlos
        let nombreCliente = document.querySelector("#billing_first_name");
        let celularCliente = document.querySelector("#billing_phone");
        let emailCliente = document.querySelector("#billing_email");

        // Datos de prueba (puedes obtener estos valores desde una API, formulario previo, etc.)
        let datosCliente = {
            nombre: document.getElementById(objeto_ajax.id_cliente).value,
            celular: document.getElementById(objeto_ajax.id_celular).value,
            email: document.getElementById(objeto_ajax.id_correo).value
        };

        // Asignar valores si los inputs existen
        if (nombreCliente) nombreCliente.value = datosCliente.nombre;
        if (celularCliente) celularCliente.value = datosCliente.celular;
        if (emailCliente) emailCliente.value = datosCliente.email;
    }

    function seleccionarSegundoValorCuandoEsteListo(selector) {
        const maxIntentos = 10;
        let intentos = 0;

        const interval = setInterval(function () {
            const $select = jQuery(selector);
            const options = $select.find('option');

            if (options.length > 1) {
                $select.val(options.eq(1).val()).trigger('change');
                clearInterval(interval);
            }

            intentos++;
            if (intentos >= maxIntentos) {
                clearInterval(interval); // Detener luego de varios intentos
            }
        }, 300); // Verifica cada 300ms
    }

    jQuery(document).ready(function () {
        const isReservarCita = window.location.pathname.includes('/reservar-cita');
        console.log("¿Está en /reservar-cita? =>", isReservarCita);
    
        if (isReservarCita && !sessionStorage.getItem('reservarCitaRecargada')) {
            console.log("Marcando como recargada...");
            sessionStorage.setItem('reservarCitaRecargada', 'true');
    
            setTimeout(() => {
                console.log("Recargando página...");
                location.reload();
            }, 500);
        }
    
        // Limpiar la bandera solo si estamos en otra ruta
        if (!isReservarCita && sessionStorage.getItem('reservarCitaRecargada')) {
            sessionStorage.removeItem('reservarCitaRecargada');
            console.log("Se limpió la bandera de recarga.");
        }
        var checkExist = setInterval(function () {
            if (jQuery('#checkout-oculto').length) {
                jQuery('#checkout-oculto').addClass('ocultar-campos');
                clearInterval(checkExist);
            }
        }, 100); // revisa cada 100ms
    });
    

   

    function mostrar_ocultar_checkout() {
        
        const checkout = jQuery("#checkout-oculto");
        checkout.removeClass("ocultar-campos");
        jQuery('body').trigger('update_checkout');

        setTimeout(() => {
            const $departamento = jQuery('#billing_departamento');

            if ($departamento.length && $departamento.find('option').length > 1) {
                // Seleccionar segundo valor (index 1)
                $departamento.val($departamento.find('option').eq(1).val()).trigger('change');

                // Seleccionar provincia cuando esté lista
                seleccionarSegundoValorCuandoEsteListo('#billing_provincia');

                // Seleccionar distrito con delay para asegurar que provincia haya cargado por AJAX
                setTimeout(() => {
                    seleccionarSegundoValorCuandoEsteListo('#billing_distrito');
                }, 1000);


            }

        }, 300); // Delay leve para asegurar que los campos se hayan renderizado completamente
        jQuery('.woocommerce-billing-fields').addClass('ocultar-campos');

    }



















});

