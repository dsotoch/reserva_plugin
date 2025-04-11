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
    const nombre = document.querySelector(`#${objeto_ajax.id_servicio}`);
    reservalo_form_cita.querySelector('button').disabled = true;


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
                    mostrar_ocultar_checkout();
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

    function mostrar_ocultar_checkout() {
        const checkout = jQuery("#checkout-oculto");

            
            checkout.slideDown();
        
    }










});
