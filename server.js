require('dotenv').config();
const path = require('path');
const express = require('express');
const pool = require('./db');
const proveedoresRoutes = require('./src/routes/proveedores.routes');

const app = express();
const PORT = Number(process.env.PORT || 3000);

app.use(express.json({ limit: '20kb' }));
app.use(express.static(path.join(__dirname, 'public')));
app.use('/api/proveedores', proveedoresRoutes);

// Respuesta para rutas API que no existen.
app.use('/api', (req, res) => {
  res.status(404).json({ mensaje: 'Ruta API no encontrada.' });
});

// Manejo centralizado de errores.
app.use((error, req, res, next) => {
  console.error('Error del servidor:', error);
  res.status(500).json({ mensaje: 'Ocurrió un error interno. Revisa la terminal del servidor.' });
});

async function iniciarServidor() {
  try {
    await pool.query('SELECT 1');
    console.log('Conexión con MySQL establecida.');

    app.listen(PORT, () => {
      console.log(`Módulo de proveedores disponible en http://localhost:${PORT}`);
    });
  } catch (error) {
    console.error('No se pudo conectar con MySQL. Verifica el archivo .env y que MySQL esté iniciado.');
    console.error(error.message);
    process.exit(1);
  }
}

iniciarServidor();
