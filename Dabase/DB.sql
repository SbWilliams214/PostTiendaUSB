CREATE DATABASE IF NOT EXISTS DBUSB
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE DBUSB;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS empleados (
    id_empleado INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    rol VARCHAR(50) NOT NULL DEFAULT 'cajero',
    fecha_ingreso DATE DEFAULT (CURRENT_DATE),
    estatus VARCHAR(20) NOT NULL DEFAULT 'activo',
    CONSTRAINT fk_empleados_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NULL,
    email VARCHAR(100) NULL,
    direccion VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

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
    estatus VARCHAR(20) NOT NULL DEFAULT 'activo',
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

CREATE TABLE IF NOT EXISTS caja (
    id_caja INT AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT NOT NULL,
    monto_inicial DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    monto_esperado DECIMAL(10, 2) DEFAULT 0.00,
    monto_real DECIMAL(10, 2) DEFAULT NULL,
    fecha_apertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATETIME DEFAULT NULL,
    estatus VARCHAR(20) DEFAULT 'abierta',
    CONSTRAINT fk_caja_empleados FOREIGN KEY (id_empleado)
        REFERENCES empleados(id_empleado)
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    tipo_pedido ENUM('tienda', 'plataforma') NOT NULL DEFAULT 'tienda',
    id_usuario INT NULL,
    monto_qr DECIMAL(10, 2) DEFAULT 0.00,
    monto_efectivo DECIMAL(10, 2) DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    ganancia DECIMAL(10, 2) DEFAULT 0.00,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    estatus VARCHAR(50) DEFAULT 'completado',
    CONSTRAINT fk_pedidos_usuarios FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS reportes (
    id_reporte INT AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT NOT NULL,
    tipo_reporte VARCHAR(100) NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    parametros TEXT NULL,
    CONSTRAINT fk_reportes_empleados FOREIGN KEY (id_empleado)
        REFERENCES empleados(id_empleado)
        ON UPDATE CASCADE
) ENGINE=InnoDB;