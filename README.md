# Módulo de proveedores - DBUSB

Módulo CRUD (crear, listar, buscar, editar y eliminar) para la tabla `proveedores` de la base de datos `DBUSB`.

## Tecnologías

- Frontend: HTML, CSS y JavaScript.
- Backend: Node.js + Express.
- Base de datos: MySQL mediante `mysql2`.

## 1. Requisitos

- Node.js en una versión LTS compatible.
- MySQL en ejecución.
- La base de datos `DBUSB` creada y con la tabla `proveedores`.

Comprueba que Node y npm estén instalados:

```bash
node -v
npm -v
```

## 2. Configurar la conexión a MySQL

Desde la carpeta del proyecto, copia `.env.example` a `.env`:

En Linux/macOS:

```bash
cp .env.example .env
```

En PowerShell:

```powershell
Copy-Item .env.example .env
```

Abre `.env` y coloca las credenciales reales de tu MySQL. Ejemplo:

```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=tu_contraseña
DB_NAME=DBUSB
PORT=3000
```

Si tu usuario `root` no tiene contraseña, deja `DB_PASSWORD=` vacío. No compartas el archivo `.env` ni lo subas a GitHub.

## 3. Instalar dependencias

```bash
npm install
```

## 4. Iniciar el módulo

Modo normal:

```bash
npm start
```

Modo desarrollo (reinicia el servidor al detectar cambios):

```bash
npm run dev
```

Abre en el navegador:

```text
http://localhost:3000
```

## 5. Rutas del backend

| Método | Ruta | Función |
|---|---|---|
| GET | `/api/proveedores` | Listar todos los proveedores |
| GET | `/api/proveedores?buscar=texto` | Buscar por nombre, correo o teléfono |
| GET | `/api/proveedores/:id` | Consultar un proveedor |
| POST | `/api/proveedores` | Registrar un proveedor |
| PUT | `/api/proveedores/:id` | Actualizar un proveedor |
| DELETE | `/api/proveedores/:id` | Eliminar un proveedor |

### Ejemplo de JSON para crear o actualizar

```json
{
  "nombre": "Distribuidora Central",
  "telefono": "70700000",
  "email": "contacto@distribuidora.com",
  "direccion": "Av. Principal #123"
}
```

`nombre` es obligatorio. Los otros tres campos son opcionales y se guardan como `NULL` cuando se dejan vacíos.

## 6. Cómo está organizado

```text
modulo-proveedores/
├── db.js
├── server.js
├── package.json
├── .env.example
├── .gitignore
├── README.md
├── src/
│   └── routes/
│       └── proveedores.routes.js
└── public/
    ├── index.html
    ├── styles.css
    └── app.js
```

- `db.js`: crea el pool de conexiones con MySQL.
- `server.js`: levanta Express y sirve el frontend.
- `proveedores.routes.js`: contiene el CRUD y las validaciones del backend.
- `index.html`: estructura de la interfaz.
- `styles.css`: estilos visuales.
- `app.js`: consulta la API y actualiza la interfaz.

## Nota sobre la eliminación

Tu clave foránea `fk_productos_proveedores` usa `ON DELETE SET NULL`. Por eso, al eliminar un proveedor, MySQL mantiene los productos y establece su `id_proveedor` en `NULL`.

## Recomendaciones antes de producción

Este módulo es una base funcional para desarrollo local. Antes de publicarlo, añade autenticación y autorización, protección CSRF cuando corresponda, límites de peticiones, registro de auditoría y configuración segura de CORS si frontend y backend se alojan en dominios distintos.
