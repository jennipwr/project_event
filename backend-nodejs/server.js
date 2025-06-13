require('dotenv').config();
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const path = require('path');

const app = express();

// Middleware umum
app.use(cors({
    origin: process.env.FRONTEND_URL || 'http://localhost:8000', // URL Laravel Anda
    credentials: true
}));
app.use(express.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

// Folder untuk menyimpan poster dan akses statis
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Import routes
const AuthRoutes = require('./routes/AuthRoutes');
const RoleRoutes = require('./routes/RoleRoutes');
const eventRoutes = require('./routes/eventRoutes');
const registerRoutes = require('./routes/registerRoutes');
const registrasiRoutes = require('./routes/registrasiRoutes');
const guestRoutes = require('./routes/guestRoutes'); // TAMBAHAN BARU
const detailRoutes = require('./routes/detailRoutes');

app.use('/api/registrasi', registrasiRoutes);
app.use('/api/detail', detailRoutes);
// Gunakan routes - TANPA DUPLIKASI
app.use('/api', AuthRoutes);
app.use('/api', RoleRoutes);
app.use('/api', registerRoutes);
app.use('/api', guestRoutes); // TAMBAHAN BARU - untuk guest/public routes
// app.use('/api', detailRoutes);


// Mount eventRoutes ke dua path yang berbeda
app.use('/api', eventRoutes);                    // Untuk route /api/event, /api/events/user/:userId
app.use('/api/panitia', eventRoutes);           // Untuk route /api/panitia/events/:eventId/detail

app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).json({
        success: false,
        message: 'Something went wrong!'
    });
});

// Mulai server
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Server berjalan di http://localhost:${PORT}`);
});