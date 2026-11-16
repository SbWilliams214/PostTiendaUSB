const express = require('express');
const pool = require('../../db');

const router = express.Router();

// Normaliza y valida los datos que llegan desde el frontend.
function validarProveedor(body) {
  const nombre = typeof body.nombre === 'string' ? body.nombre.trim() : '';
  const telefono = typeof body.telefono === 'string' ? body.telefono.trim() : '';
  const email = typeof body.email === 'string' ? body.email.trim() : '';
  const direccion = typeof body.direccion === 'string' ? body.direccion.trim() : '';

  if (!nombre) {
    return { error: 'El nombre del proveedor es obligatorio.' };
  }
  if (nombre.length > 150) {
    return { error: 'El nombre no puede superar los 150 caracteres.' };
  }
  if (telefono.length > 30) {
    return { error: 'El teléfono no puede superar los 30 caracteres.' };
  }
  if (email.length > 100) {
    return { error: 'El correo no puede superar los 100 caracteres.' };
  }
  if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return { error: 'El formato del correo electrónico no es válido.' };
  }
  if (direccion.length > 255) {
    return { error: 'La dirección no puede superar los 255 caracteres.' };
  }

  return {
    data: {
      nombre,
      telefono: telefono || null,
      email: email || null,
      direccion: direccion || null
    }
  };
}

// GET /api/proveedores?buscar=texto - listar y buscar proveedores.
router.get('/', async (req, res, next) => {
  try {
    const buscar = typeof req.query.buscar === 'string' ? req.query.buscar.trim() : '';

    let sql = `
      SELECT id_proveedor, nombre, telefono, email, direccion
      FROM proveedores
    `;
    const params = [];

    if (buscar) {
      sql += ' WHERE nombre LIKE ? OR email LIKE ? OR telefono LIKE ?';
      const termino = `%${buscar}%`;
      params.push(termino, termino, termino);
    }

    sql += ' ORDER BY nombre ASC';

    const [proveedores] = await pool.execute(sql, params);
    res.json(proveedores);
  } catch (error) {
    next(error);
  }
});

// GET /api/proveedores/:id - obtener un proveedor por su ID.
router.get('/:id', async (req, res, next) => {
  try {
    const id = Number(req.params.id);
    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ mensaje: 'El ID del proveedor no es válido.' });
    }

    const [rows] = await pool.execute(
      `SELECT id_proveedor, nombre, telefono, email, direccion
       FROM proveedores WHERE id_proveedor = ?`,
      [id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ mensaje: 'Proveedor no encontrado.' });
    }

    res.json(rows[0]);
  } catch (error) {
    next(error);
  }
});

// POST /api/proveedores - registrar un proveedor.
router.post('/', async (req, res, next) => {
  try {
    const validacion = validarProveedor(req.body || {});
    if (validacion.error) {
      return res.status(400).json({ mensaje: validacion.error });
    }

    const { nombre, telefono, email, direccion } = validacion.data;
    const [resultado] = await pool.execute(
      `INSERT INTO proveedores (nombre, telefono, email, direccion)
       VALUES (?, ?, ?, ?)`,
      [nombre, telefono, email, direccion]
    );

    res.status(201).json({
      mensaje: 'Proveedor registrado correctamente.',
      id_proveedor: resultado.insertId
    });
  } catch (error) {
    next(error);
  }
});

// PUT /api/proveedores/:id - actualizar un proveedor.
router.put('/:id', async (req, res, next) => {
  try {
    const id = Number(req.params.id);
    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ mensaje: 'El ID del proveedor no es válido.' });
    }

    const validacion = validarProveedor(req.body || {});
    if (validacion.error) {
      return res.status(400).json({ mensaje: validacion.error });
    }

    const { nombre, telefono, email, direccion } = validacion.data;
    const [resultado] = await pool.execute(
      `UPDATE proveedores
       SET nombre = ?, telefono = ?, email = ?, direccion = ?
       WHERE id_proveedor = ?`,
      [nombre, telefono, email, direccion, id]
    );

    if (resultado.affectedRows === 0) {
      return res.status(404).json({ mensaje: 'Proveedor no encontrado.' });
    }

    res.json({ mensaje: 'Proveedor actualizado correctamente.' });
  } catch (error) {
    next(error);
  }
});

// DELETE /api/proveedores/:id - eliminar un proveedor.
router.delete('/:id', async (req, res, next) => {
  try {
    const id = Number(req.params.id);
    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ mensaje: 'El ID del proveedor no es válido.' });
    }

    const [resultado] = await pool.execute(
      'DELETE FROM proveedores WHERE id_proveedor = ?',
      [id]
    );

    if (resultado.affectedRows === 0) {
      return res.status(404).json({ mensaje: 'Proveedor no encontrado.' });
    }

    res.json({ mensaje: 'Proveedor eliminado correctamente.' });
  } catch (error) {
    next(error);
  }
});

module.exports = router;
