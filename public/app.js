const API_PROVEEDORES = '/api/proveedores';

const tabla = document.querySelector('#tablaProveedores');
const contador = document.querySelector('#contador');
const buscar = document.querySelector('#buscar');
const mensaje = document.querySelector('#mensaje');
const dialogo = document.querySelector('#dialogoProveedor');
const formulario = document.querySelector('#formProveedor');

const campos = {
  id: document.querySelector('#id_proveedor'),
  nombre: document.querySelector('#nombre'),
  telefono: document.querySelector('#telefono'),
  email: document.querySelector('#email'),
  direccion: document.querySelector('#direccion')
};

function escaparHTML(valor) {
  return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  })[caracter]);
}

function mostrarMensaje(texto, tipo = 'exito') {
  mensaje.textContent = texto;
  mensaje.className = `mensaje ${tipo}`;
  mensaje.hidden = false;
  window.clearTimeout(mostrarMensaje.temporizador);
  mostrarMensaje.temporizador = window.setTimeout(() => {
    mensaje.hidden = true;
  }, 4500);
}

async function solicitar(url, opciones = {}) {
  const respuesta = await fetch(url, {
    ...opciones,
    headers: {
      ...(opciones.body ? { 'Content-Type': 'application/json' } : {}),
      ...opciones.headers
    }
  });

  const contenido = respuesta.status === 204 ? null : await respuesta.json();
  if (!respuesta.ok) {
    throw new Error(contenido?.mensaje || 'No se pudo completar la operación.');
  }
  return contenido;
}

function renderizarProveedores(proveedores) {
  contador.textContent = `${proveedores.length} ${proveedores.length === 1 ? 'proveedor' : 'proveedores'}`;

  if (proveedores.length === 0) {
    tabla.innerHTML = '<tr><td colspan="6" class="estado-tabla">No se encontraron proveedores.</td></tr>';
    return;
  }

  tabla.innerHTML = proveedores.map(proveedor => `
    <tr>
      <td>${Number(proveedor.id_proveedor)}</td>
      <td class="nombre-proveedor">${escaparHTML(proveedor.nombre)}</td>
      <td>${proveedor.telefono ? escaparHTML(proveedor.telefono) : '<span class="texto-vacio">Sin teléfono</span>'}</td>
      <td>${proveedor.email ? escaparHTML(proveedor.email) : '<span class="texto-vacio">Sin correo</span>'}</td>
      <td>${proveedor.direccion ? escaparHTML(proveedor.direccion) : '<span class="texto-vacio">Sin dirección</span>'}</td>
      <td>
        <div class="acciones">
          <button class="boton-accion" type="button" data-accion="editar" data-id="${Number(proveedor.id_proveedor)}">Editar</button>
          <button class="boton-accion boton-eliminar" type="button" data-accion="eliminar" data-id="${Number(proveedor.id_proveedor)}">Eliminar</button>
        </div>
      </td>
    </tr>
  `).join('');
}

async function cargarProveedores() {
  tabla.innerHTML = '<tr><td colspan="6" class="estado-tabla">Cargando proveedores...</td></tr>';
  try {
    const termino = buscar.value.trim();
    const url = termino
      ? `${API_PROVEEDORES}?buscar=${encodeURIComponent(termino)}`
      : API_PROVEEDORES;
    const proveedores = await solicitar(url);
    renderizarProveedores(proveedores);
  } catch (error) {
    tabla.innerHTML = '<tr><td colspan="6" class="estado-tabla">No se pudieron cargar los proveedores.</td></tr>';
    mostrarMensaje(error.message, 'error');
  }
}

function limpiarFormulario() {
  formulario.reset();
  campos.id.value = '';
  document.querySelector('#tituloFormulario').textContent = 'Nuevo proveedor';
  document.querySelector('#btnGuardar').textContent = 'Guardar proveedor';
}

function abrirFormularioNuevo() {
  limpiarFormulario();
  dialogo.showModal();
  campos.nombre.focus();
}

async function abrirFormularioEdicion(id) {
  try {
    const proveedor = await solicitar(`${API_PROVEEDORES}/${id}`);
    limpiarFormulario();
    campos.id.value = proveedor.id_proveedor;
    campos.nombre.value = proveedor.nombre ?? '';
    campos.telefono.value = proveedor.telefono ?? '';
    campos.email.value = proveedor.email ?? '';
    campos.direccion.value = proveedor.direccion ?? '';
    document.querySelector('#tituloFormulario').textContent = 'Editar proveedor';
    document.querySelector('#btnGuardar').textContent = 'Guardar cambios';
    dialogo.showModal();
    campos.nombre.focus();
  } catch (error) {
    mostrarMensaje(error.message, 'error');
  }
}

async function eliminarProveedor(id) {
  const confirmar = window.confirm(
    '¿Seguro que deseas eliminar este proveedor? Los productos relacionados no se eliminarán; quedarán sin proveedor asignado.'
  );
  if (!confirmar) return;

  try {
    const resultado = await solicitar(`${API_PROVEEDORES}/${id}`, { method: 'DELETE' });
    mostrarMensaje(resultado.mensaje);
    await cargarProveedores();
  } catch (error) {
    mostrarMensaje(error.message, 'error');
  }
}

document.querySelector('#btnNuevo').addEventListener('click', abrirFormularioNuevo);
document.querySelector('#btnCerrar').addEventListener('click', () => dialogo.close());
document.querySelector('#btnCancelar').addEventListener('click', () => dialogo.close());

buscar.addEventListener('input', () => {
  window.clearTimeout(buscar.temporizador);
  buscar.temporizador = window.setTimeout(cargarProveedores, 250);
});

tabla.addEventListener('click', event => {
  const boton = event.target.closest('button[data-accion]');
  if (!boton) return;

  const id = Number(boton.dataset.id);
  if (!Number.isInteger(id) || id <= 0) return;

  if (boton.dataset.accion === 'editar') abrirFormularioEdicion(id);
  if (boton.dataset.accion === 'eliminar') eliminarProveedor(id);
});

formulario.addEventListener('submit', async event => {
  event.preventDefault();

  const id = campos.id.value;
  const datos = {
    nombre: campos.nombre.value.trim(),
    telefono: campos.telefono.value.trim(),
    email: campos.email.value.trim(),
    direccion: campos.direccion.value.trim()
  };

  const botonGuardar = document.querySelector('#btnGuardar');
  botonGuardar.disabled = true;
  botonGuardar.textContent = 'Guardando...';

  try {
    const resultado = await solicitar(
      id ? `${API_PROVEEDORES}/${id}` : API_PROVEEDORES,
      {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(datos)
      }
    );

    dialogo.close();
    limpiarFormulario();
    mostrarMensaje(resultado.mensaje);
    await cargarProveedores();
  } catch (error) {
    mostrarMensaje(error.message, 'error');
  } finally {
    botonGuardar.disabled = false;
    botonGuardar.textContent = campos.id.value ? 'Guardar cambios' : 'Guardar proveedor';
  }
});

cargarProveedores();
