// ===== Datos de Productos =====
const productos = [
    { id: 1, nombre: "Papas Fritas", emoji: "🍟", precio: 1.50 },
    { id: 2, nombre: "Refresco", emoji: "🥤", precio: 1.00 },
    { id: 3, nombre: "Hamburguesa", emoji: "🍔", precio: 3.50 },
    { id: 4, nombre: "Hot Dog", emoji: "🌭", precio: 2.00 },
    { id: 5, nombre: "Pizza", emoji: "🍕", precio: 2.50 },
    { id: 6, nombre: "Nachos", emoji: "🧀", precio: 2.00 },
    { id: 7, nombre: "Palomitas", emoji: "🍿", precio: 1.50 },
    { id: 8, nombre: "Donas", emoji: "🍩", precio: 1.00 },
    { id: 9, nombre: "Helado", emoji: "🍦", precio: 1.75 },
    { id: 10, nombre: "Jugo Natural", emoji: "🧃", precio: 1.25 },
    { id: 11, nombre: "Sandwich", emoji: "🥪", precio: 2.50 },
    { id: 12, nombre: "Galletas", emoji: "🍪", precio: 0.75 },
];

// ===== Estado del Carrito =====
let carrito = [];

// ===== Referencias al DOM =====
const listaProductos = document.getElementById("lista-productos");
const carritoItems = document.getElementById("carrito-items");
const totalPrecio = document.getElementById("total-precio");
const btnCobrar = document.getElementById("btn-cobrar");
const btnLimpiar = document.getElementById("btn-limpiar");
const modalCobro = document.getElementById("modal-cobro");
const resumenVenta = document.getElementById("resumen-venta");
const pagoCliente = document.getElementById("pago-cliente");
const cambioMonto = document.getElementById("cambio-monto");
const btnConfirmar = document.getElementById("btn-confirmar");
const btnCancelar = document.getElementById("btn-cancelar");
const notificacion = document.getElementById("notificacion");

// ===== Renderizar Productos =====
function renderizarProductos() {
    listaProductos.innerHTML = productos
        .map(
            (p) => `
        <div class="producto-card" onclick="agregarAlCarrito(${p.id})">
            <div class="producto-emoji">${p.emoji}</div>
            <div class="producto-nombre">${p.nombre}</div>
            <div class="producto-precio">$${p.precio.toFixed(2)}</div>
        </div>
    `
        )
        .join("");
}

// ===== Agregar al Carrito =====
function agregarAlCarrito(productoId) {
    const producto = productos.find((p) => p.id === productoId);
    if (!producto) return;

    const itemExistente = carrito.find((item) => item.id === productoId);

    if (itemExistente) {
        itemExistente.cantidad++;
    } else {
        carrito.push({ ...producto, cantidad: 1 });
    }

    actualizarCarrito();
}

// ===== Modificar Cantidad =====
function cambiarCantidad(productoId, cambio) {
    const item = carrito.find((item) => item.id === productoId);
    if (!item) return;

    item.cantidad += cambio;

    if (item.cantidad <= 0) {
        carrito = carrito.filter((item) => item.id !== productoId);
    }

    actualizarCarrito();
}

// ===== Actualizar Vista del Carrito =====
function actualizarCarrito() {
    if (carrito.length === 0) {
        carritoItems.innerHTML =
            '<p class="carrito-vacio">El carrito está vacío</p>';
        btnCobrar.disabled = true;
    } else {
        carritoItems.innerHTML = carrito
            .map(
                (item) => `
            <div class="carrito-item">
                <div class="carrito-item-info">
                    <div class="carrito-item-nombre">${item.emoji} ${item.nombre}</div>
                    <div class="carrito-item-precio">$${item.precio.toFixed(2)} c/u</div>
                </div>
                <div class="carrito-item-controles">
                    <button class="btn-menos" onclick="cambiarCantidad(${item.id}, -1)">−</button>
                    <span class="carrito-item-cantidad">${item.cantidad}</span>
                    <button class="btn-mas" onclick="cambiarCantidad(${item.id}, 1)">+</button>
                </div>
                <div class="carrito-item-subtotal">$${(item.precio * item.cantidad).toFixed(2)}</div>
            </div>
        `
            )
            .join("");
        btnCobrar.disabled = false;
    }

    const total = calcularTotal();
    totalPrecio.textContent = `$${total.toFixed(2)}`;
}

// ===== Calcular Total =====
function calcularTotal() {
    return carrito.reduce(
        (sum, item) => sum + item.precio * item.cantidad,
        0
    );
}

// ===== Limpiar Carrito =====
function limpiarCarrito() {
    carrito = [];
    actualizarCarrito();
}

// ===== Abrir Modal de Cobro =====
function abrirModalCobro() {
    const total = calcularTotal();

    // Generar resumen
    resumenVenta.innerHTML =
        carrito
            .map(
                (item) => `
        <div class="resumen-item">
            <span>${item.emoji} ${item.nombre} x${item.cantidad}</span>
            <span>$${(item.precio * item.cantidad).toFixed(2)}</span>
        </div>
    `
            )
            .join("") +
        `
        <div class="resumen-total">
            <span>Total</span>
            <span>$${total.toFixed(2)}</span>
        </div>
    `;

    pagoCliente.value = "";
    cambioMonto.textContent = "$0.00";
    modalCobro.style.display = "flex";
    pagoCliente.focus();
}

// ===== Cerrar Modal =====
function cerrarModal() {
    modalCobro.style.display = "none";
}

// ===== Calcular Cambio =====
function calcularCambio() {
    const total = calcularTotal();
    const pago = parseFloat(pagoCliente.value) || 0;
    const cambio = pago - total;

    cambioMonto.textContent = `$${Math.max(0, cambio).toFixed(2)}`;
    cambioMonto.style.color = cambio >= 0 ? "#27ae60" : "#e74c3c";

}

// ===== Confirmar Venta =====
function confirmarVenta() {
    const total = calcularTotal();
    const pago = parseFloat(pagoCliente.value) || 0;

    if (pago < total) {
        mostrarNotificacion("⚠️ El monto recibido es insuficiente", "error");
        return;
    }

    const cambio = pago - total;
    cerrarModal();
    limpiarCarrito();
    mostrarNotificacion(
        `✅ Venta realizada — Cambio: $${cambio.toFixed(2)}`
    );
}

// ===== Mostrar Notificación =====
function mostrarNotificacion(mensaje, tipo = "exito") {
    notificacion.textContent = mensaje;
    notificacion.style.background =
        tipo === "error" ? "#e74c3c" : "#27ae60";
    notificacion.style.display = "block";

    setTimeout(() => {
        notificacion.style.display = "none";
    }, 3000);
}

// ===== Event Listeners =====
btnCobrar.addEventListener("click", abrirModalCobro);
btnLimpiar.addEventListener("click", limpiarCarrito);
btnConfirmar.addEventListener("click", confirmarVenta);
btnCancelar.addEventListener("click", cerrarModal);
pagoCliente.addEventListener("input", calcularCambio);

// Cerrar modal con Escape
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") cerrarModal();
});

// ===== Inicializar =====
renderizarProductos();

function DatosPedido(a) {

}