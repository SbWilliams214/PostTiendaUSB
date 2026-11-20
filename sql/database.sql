-- ============================================================
-- ProyectUSB — POS & Catálogo Web
-- Base de datos: proyect_usb
-- Motor: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Script idempotente (DROP IF EXISTS antes de cada CREATE)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `proyect_usb`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `proyect_usb`;

-- ============================================================
-- DROP ALL TABLES (reverse dependency order)
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `detalle_promociones`;
DROP TABLE IF EXISTS `promociones`;
DROP TABLE IF EXISTS `detalle_pago`;
DROP TABLE IF EXISTS `carrito`;
DROP TABLE IF EXISTS `reportes`;
DROP TABLE IF EXISTS `caja`;
DROP TABLE IF EXISTS `pass_clientes`;
DROP TABLE IF EXISTS `pass_usuarios`;
DROP TABLE IF EXISTS `empleados`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `inventario`;
DROP TABLE IF EXISTS `proveedores`;
DROP TABLE IF EXISTS `configuraciones`;
DROP TABLE IF EXISTS `tipo_reportes`;
DROP TABLE IF EXISTS `metodo_pago`;
DROP TABLE IF EXISTS `tipo_pedido`;
DROP TABLE IF EXISTS `clientes`;
DROP TABLE IF EXISTS `usuario`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `compania_marca`;
DROP TABLE IF EXISTS `categoria`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. TABLAS DE CATÁLOGO / INVENTARIO
-- ============================================================

-- CATEGORIA
CREATE TABLE `categoria` (
  `id`          INT           NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(100)  NOT NULL,
  `descripcion` TEXT          NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- COMPANIA_MARCA
CREATE TABLE `compania_marca` (
  `id`          INT           NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(150)  NOT NULL,
  `pais_origen` VARCHAR(100)  NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROVEEDORES
CREATE TABLE `proveedores` (
  `id`          INT           NOT NULL AUTO_INCREMENT,
  `id_compania` INT           NULL,
  `nombre`      VARCHAR(150)  NOT NULL,
  `telefono`    VARCHAR(20)   NULL,
  `email`       VARCHAR(100)  NULL,
  `direccion`   TEXT          NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_proveedores_compania` (`id_compania`),
  CONSTRAINT `fk_proveedores_compania`
    FOREIGN KEY (`id_compania`) REFERENCES `compania_marca`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INVENTARIO
CREATE TABLE `inventario` (
  `id`               INT            NOT NULL AUTO_INCREMENT,
  `nombre`           VARCHAR(200)   NOT NULL,
  `descripcion`      TEXT           NULL,
  `cantidad`         INT            NOT NULL DEFAULT 0,
  `precio_proveedor` DECIMAL(10,2)  NULL,
  `precio_venta`     DECIMAL(10,2)  NULL,
  `umbral_alerta`    INT            NOT NULL DEFAULT 5,
  `codigo_barras`    VARCHAR(50)    NULL,
  `ruta_image`       VARCHAR(255)   NULL,
  `id_categoria`     INT            NULL,
  `id_proveedor`     INT            NULL,
  `id_compania`      INT            NULL,
  `status`           ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_inventario_codigo_barras` (`codigo_barras`),
  INDEX `idx_inventario_categoria`  (`id_categoria`),
  INDEX `idx_inventario_proveedor`  (`id_proveedor`),
  INDEX `idx_inventario_compania`   (`id_compania`),
  INDEX `idx_inventario_status`     (`status`),
  CONSTRAINT `fk_inventario_categoria`
    FOREIGN KEY (`id_categoria`) REFERENCES `categoria`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_inventario_proveedor`
    FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_inventario_compania`
    FOREIGN KEY (`id_compania`) REFERENCES `compania_marca`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TABLAS DE USUARIOS / EMPLEADOS / CLIENTES
-- ============================================================

-- ROLES
CREATE TABLE `roles` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(50)  NOT NULL,
  `descripcion` TEXT         NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- USUARIO
CREATE TABLE `usuario` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `nombre`         VARCHAR(150) NOT NULL,
  `username`       VARCHAR(50)  NOT NULL,
  `email`          VARCHAR(100) NULL,
  `telefono`       VARCHAR(20)  NULL,
  `fecha_creacion` DATE         NOT NULL DEFAULT (CURRENT_DATE),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_usuario_username` (`username`),
  UNIQUE INDEX `uq_usuario_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASS_USUARIOS
CREATE TABLE `pass_usuarios` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `id_usuario`    INT          NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `ultimo_cambio` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_pass_usuarios_usuario` (`id_usuario`),
  CONSTRAINT `fk_pass_usuarios_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EMPLEADOS
CREATE TABLE `empleados` (
  `id`                 INT   NOT NULL AUTO_INCREMENT,
  `id_usuario`         INT   NOT NULL,
  `id_rol`             INT   NOT NULL,
  `fecha_contratacion` DATE  NULL,
  `status`             ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id`),
  INDEX `idx_empleados_usuario` (`id_usuario`),
  INDEX `idx_empleados_rol`     (`id_rol`),
  INDEX `idx_empleados_status`  (`status`),
  CONSTRAINT `fk_empleados_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_empleados_rol`
    FOREIGN KEY (`id_rol`) REFERENCES `roles`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CLIENTES
CREATE TABLE `clientes` (
  `id`        INT          NOT NULL AUTO_INCREMENT,
  `nombre`    VARCHAR(150) NOT NULL,
  `telefono`  VARCHAR(20)  NULL,
  `email`     VARCHAR(100) NULL,
  `direccion` TEXT         NULL,
  `status`    ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_clientes_email` (`email`),
  INDEX `idx_clientes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASS_CLIENTES
CREATE TABLE `pass_clientes` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `id_cliente`    INT          NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `ultimo_cambio` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_pass_clientes_cliente` (`id_cliente`),
  CONSTRAINT `fk_pass_clientes_cliente`
    FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TABLAS DE PEDIDOS / CARRITO / PAGOS
-- ============================================================

-- TIPO_PEDIDO
CREATE TABLE `tipo_pedido` (
  `id`     INT         NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PEDIDOS
CREATE TABLE `pedidos` (
  `id`                 INT            NOT NULL AUTO_INCREMENT,
  `id_cliente_empleado` INT           NULL,
  `id_tipo_pedido`     INT            NULL,
  `codigo_pedido`      VARCHAR(20)    NOT NULL,
  `total_ganancia`     DECIMAL(10,2)  NULL,
  `fecha`              DATE           NULL,
  `hora`               TIME           NULL,
  `status`             ENUM('pendiente','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_pedidos_codigo` (`codigo_pedido`),
  INDEX `idx_pedidos_tipo`   (`id_tipo_pedido`),
  INDEX `idx_pedidos_status` (`status`),
  INDEX `idx_pedidos_fecha`  (`fecha`),
  CONSTRAINT `fk_pedidos_tipo`
    FOREIGN KEY (`id_tipo_pedido`) REFERENCES `tipo_pedido`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CARRITO (detalle de productos por pedido)
CREATE TABLE `carrito` (
  `id`              INT           NOT NULL AUTO_INCREMENT,
  `id_pedido`       INT           NOT NULL,
  `id_producto`     INT           NOT NULL,
  `cantidad`        INT           NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal`        DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_carrito_pedido`   (`id_pedido`),
  INDEX `idx_carrito_producto` (`id_producto`),
  CONSTRAINT `fk_carrito_pedido`
    FOREIGN KEY (`id_pedido`) REFERENCES `pedidos`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_carrito_producto`
    FOREIGN KEY (`id_producto`) REFERENCES `inventario`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- METODO_PAGO
CREATE TABLE `metodo_pago` (
  `id`     INT         NOT NULL AUTO_INCREMENT,
  `metodo` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DETALLE_PAGO
CREATE TABLE `detalle_pago` (
  `id`             INT           NOT NULL AUTO_INCREMENT,
  `id_pedido`      INT           NOT NULL,
  `id_metodo_pago` INT           NOT NULL,
  `monto_pagado`   DECIMAL(10,2) NOT NULL,
  `referencia`     VARCHAR(100)  NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_detalle_pago_pedido` (`id_pedido`),
  INDEX `idx_detalle_pago_metodo` (`id_metodo_pago`),
  CONSTRAINT `fk_detalle_pago_pedido`
    FOREIGN KEY (`id_pedido`) REFERENCES `pedidos`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_pago_metodo`
    FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodo_pago`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. CAJA
-- ============================================================

CREATE TABLE `caja` (
  `id`             INT           NOT NULL AUTO_INCREMENT,
  `id_empleado`    INT           NOT NULL,
  `monto_inicial`  DECIMAL(10,2) NOT NULL,
  `monto_final`    DECIMAL(10,2) NULL,
  `fecha_apertura` DATE          NOT NULL,
  `hora_apertura`  TIME          NOT NULL,
  `fecha_cierre`   DATE          NULL,
  `hora_cierre`    TIME          NULL,
  `status`         ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  PRIMARY KEY (`id`),
  INDEX `idx_caja_empleado` (`id_empleado`),
  INDEX `idx_caja_status`   (`status`),
  CONSTRAINT `fk_caja_empleado`
    FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. PROMOCIONES
-- ============================================================

CREATE TABLE `promociones` (
  `id`                 INT            NOT NULL AUTO_INCREMENT,
  `id_empleado`        INT            NULL,
  `nombre`             VARCHAR(200)   NOT NULL,
  `descripcion`        TEXT           NULL,
  `precio`             DECIMAL(10,2)  NULL,
  `ruta_imagen`        VARCHAR(255)   NULL,
  `status`             ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `status_plataforma`  ENUM('activo','inactivo') NOT NULL DEFAULT 'inactivo',
  PRIMARY KEY (`id`),
  INDEX `idx_promociones_empleado` (`id_empleado`),
  INDEX `idx_promociones_status`   (`status`),
  CONSTRAINT `fk_promociones_empleado`
    FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DETALLE_PROMOCIONES
CREATE TABLE `detalle_promociones` (
  `id`                   INT           NOT NULL AUTO_INCREMENT,
  `id_promocion`         INT           NOT NULL,
  `id_producto`          INT           NOT NULL,
  `precio_unitario_promo` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_det_promo_promocion` (`id_promocion`),
  INDEX `idx_det_promo_producto`  (`id_producto`),
  CONSTRAINT `fk_det_promo_promocion`
    FOREIGN KEY (`id_promocion`) REFERENCES `promociones`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_det_promo_producto`
    FOREIGN KEY (`id_producto`) REFERENCES `inventario`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. REPORTES
-- ============================================================

-- TIPO_REPORTES
DROP TABLE IF EXISTS `tipo_reportes`;
CREATE TABLE `tipo_reportes` (
  `id`     INT          NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `value`  VARCHAR(50)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- REPORTES
DROP TABLE IF EXISTS `reportes`;
CREATE TABLE `reportes` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `id_empleado`      INT          NULL,
  `id_tipo_reporte`  INT          NULL,
  `fecha_generacion` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archivo_ruta`     VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_reportes_empleado` (`id_empleado`),
  INDEX `idx_reportes_tipo`     (`id_tipo_reporte`),
  CONSTRAINT `fk_reportes_empleado`
    FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_reportes_tipo`
    FOREIGN KEY (`id_tipo_reporte`) REFERENCES `tipo_reportes`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. CONFIGURACIONES
-- ============================================================

DROP TABLE IF EXISTS `configuraciones`;
CREATE TABLE `configuraciones` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `clave`       VARCHAR(50)  NOT NULL,
  `valor`       TEXT         NULL,
  `descripcion` VARCHAR(200) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_configuraciones_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. SEED DATA
-- ============================================================

-- Roles
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
  (1, 'Admin',  'Administrador del sistema'),
  (2, 'Cajero', 'Cajero punto de venta');

-- Tipo de pedido
INSERT INTO `tipo_pedido` (`id`, `nombre`) VALUES
  (1, 'Web'),
  (2, 'Presencial');

-- Método de pago
INSERT INTO `metodo_pago` (`id`, `metodo`) VALUES
  (1, 'Efectivo'),
  (2, 'QR');

-- Tipo de reportes
INSERT INTO `tipo_reportes` (`id`, `nombre`, `value`) VALUES
  (1, 'Ventas Diarias', 'ventas_diarias'),
  (2, 'Inventario',     'inventario'),
  (3, 'Ganancias',      'ganancias');

-- Categorías de ejemplo
INSERT INTO `categoria` (`nombre`, `descripcion`) VALUES
  ('Bebidas',      'Refrescos, jugos, aguas y bebidas en general'),
  ('Snacks',       'Botanas, frituras, galletas y dulces'),
  ('Electrónica',  'Dispositivos electrónicos, accesorios y gadgets'),
  ('Hogar',        'Artículos para el hogar y limpieza');

-- ============================================================
-- 9. USUARIO ADMINISTRADOR POR DEFECTO
-- ============================================================

-- Usuario base
INSERT INTO `usuario` (`id`, `nombre`, `username`, `email`, `telefono`, `fecha_creacion`) VALUES
  (1, 'Administrador', 'admin', 'admin@pos.local', '0000', CURRENT_DATE);

-- Contraseña del admin
-- ⚠  El hash de abajo es un placeholder.
--    Generar el hash real en PHP con:
--      $hash = password_hash('admin123', PASSWORD_DEFAULT);
--    y luego actualizar esta fila, o bien insertar desde un script PHP de setup.
INSERT INTO `pass_usuarios` (`id_usuario`, `password_hash`) VALUES
  (1, '$2y$10$exampleHashPlaceholder.ReplaceWithRealPasswordHashGeneratedViaPHP');

-- Empleado administrador
INSERT INTO `empleados` (`id`, `id_usuario`, `id_rol`, `fecha_contratacion`, `status`) VALUES
  (1, 1, 1, CURRENT_DATE, 'activo');

-- ============================================================
-- 10. CONFIGURACIONES INICIALES
-- ============================================================

INSERT INTO `configuraciones` (`clave`, `valor`, `descripcion`) VALUES
  ('qr_general', '', 'Ruta imagen QR para pagos');

-- ============================================================
-- Fin del script
-- ============================================================
