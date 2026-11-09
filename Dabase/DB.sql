-- 1. Crear la base de datos (puedes cambiar el nombre si prefieres)
CREATE DATABASE IF NOT EXISTS psp_ventas 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE psp_ventas;

-- 2. Tabla Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    estatus VARCHAR(20) DEFAULT 'activo'
) ENGINE=InnoDB;

-- 3. Tabla Proveedores
CREATE TABLE IF NOT EXISTS proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NULL,
    email VARCHAR(100) NULL,
    direccion VARCHAR(255) NULL
) ENGINE=InnoDB;

-- 4. Tabla Categorías
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 5. Tabla Productos
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    cantidad INT DEFAULT 0,
    precio_proveedor DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    precio_venta DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    umbral_alerta INT DEFAULT 5,
    codigo_barras VARCHAR(50) NULL,
    ruta_imagen VARCHAR(255) NULL,
    id_categoria INT NULL,
    id_proveedor INT NULL,
    CONSTRAINT fk_productos_categorias FOREIGN KEY (id_categoria) 
        REFERENCES categorias(id_categoria) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE,
    CONSTRAINT fk_productos_proveedores FOREIGN KEY (id_proveedor) 
        REFERENCES proveedores(id_proveedor) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 6. Tabla Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    monto_qr DECIMAL(10, 2) DEFAULT 0.00,
    monto_efectivo DECIMAL(10, 2) DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    ganancia DECIMAL(10, 2) DEFAULT 0.00,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    estatus VARCHAR(50) DEFAULT 'completado',
    CONSTRAINT fk_pedidos_usuarios FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 7. Tabla Carrito (Detalle del pedido)
CREATE TABLE IF NOT EXISTS carrito (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    nombre_producto VARCHAR(150) NOT NULL,
    precio_item DECIMAL(10, 2) NOT NULL,
    cantidad INT NOT NULL,
    CONSTRAINT fk_carrito_pedidos FOREIGN KEY (id_pedido) 
        REFERENCES pedidos(id_pedido) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_carrito_productos FOREIGN KEY (id_producto) 
        REFERENCES productos(id_producto) 
        ON UPDATE CASCADE
) ENGINE=InnoDB;