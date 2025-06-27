const express = require('express');
const router = express.Router();
const eventController = require('../controllers/eventController');
const multer = require('multer');
const path = require('path');

const connectDB = require('../database.js');

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'uploads/');
  },
  filename: (req, file, cb) => {
    const uniqueName = Date.now() + '-' + file.originalname;
    cb(null, uniqueName);
  }
});
const fileFilter = (req, file, cb) => {
  if (file.mimetype.startsWith('image/')) {
    cb(null, true);
  } else {
    cb(new Error('Only image files are allowed!'), false);
  }
};

const upload = multer({ 
  storage: storage,
  fileFilter: fileFilter,
  limits: {
    fileSize: 2 * 1024 * 1024
  }
});

router.post('/event', upload.single('poster'), eventController.createEvent);
router.put('/event/:eventId', upload.single('poster'), eventController.updateEvent);
router.get('/events/user/:userId', eventController.getEventsByUser);
router.get('/event/:eventId', eventController.getEventDetail);
router.get('/panitia/events/:eventId/detail', eventController.getEventDetail);
router.delete('/event/:eventId', eventController.deleteEvent);

router.use((error, req, res, next) => {
  if (error instanceof multer.MulterError) {
    if (error.code === 'LIMIT_FILE_SIZE') {
      return res.status(400).json({ message: 'File terlalu besar. Maksimal 2MB.' });
    }
  }
  
  if (error.message === 'Only image files are allowed!') {
    return res.status(400).json({ message: 'Hanya file gambar yang diizinkan!' });
  }
  
  res.status(500).json({ message: 'Terjadi kesalahan server', error: error.message });
});

router.get('/event/:id/stats', async (req, res) => {
    const eventId = req.params.id;
    try {
        // Dapatkan koneksi database
        const db = await connectDB();
        
        // Query untuk mendapatkan total kapasitas dari semua sesi event
        const capacityQuery = `
            SELECT 
                e.id_event,
                e.nama_event,
                COALESCE(SUM(es.jumlah_peserta), 0) as total_kapasitas
            FROM event e
            LEFT JOIN event_sesi es ON e.id_event = es.event_id_event
            WHERE e.id_event = ?
            GROUP BY e.id_event, e.nama_event
        `;
        
        // Query untuk mendapatkan jumlah peserta terdaftar
        const participantQuery = `
            SELECT 
                COUNT(DISTINCT r.id_registrasi) as total_peserta
            FROM registrasi r
            INNER JOIN event_sesi es ON r.event_sesi_id_sesi = es.id_sesi
            WHERE es.event_id_event = ? AND r.status = 'approved'
        `;
        
        // Jalankan kedua query
        const [capacityResult] = await db.execute(capacityQuery, [eventId]);
        const [participantResult] = await db.execute(participantQuery, [eventId]);
        
        const totalKapasitas = capacityResult[0]?.total_kapasitas || 0;
        const totalPeserta = participantResult[0]?.total_peserta || 0;
        const sisaKapasitas = totalKapasitas - totalPeserta;
        
        res.json({
            event_id: eventId,
            nama_event: capacityResult[0]?.nama_event || '',
            total_kapasitas: totalKapasitas,
            total_peserta: totalPeserta,
            sisa_kapasitas: Math.max(0, sisaKapasitas) // Pastikan tidak negatif
        });
        
    } catch (error) {
        console.error('Error getting event stats:', error);
        res.status(500).json({
            error: true,
            message: 'Gagal mengambil statistik event'
        });
    }
});

router.get('/event/:id/sessions-stats', async (req, res) => {
    const eventId = req.params.id;
    
    try {
        const db = await connectDB();
        
        const query = `
            SELECT 
                es.id_sesi,
                es.nama_sesi,
                es.jumlah_peserta as kapasitas,
                es.tanggal_sesi,
                es.waktu_sesi,
                es.lokasi_sesi,
                es.biaya_sesi,
                COALESCE(COUNT(r.id_registrasi), 0) as jumlah_terdaftar,
                (es.jumlah_peserta - COALESCE(COUNT(r.id_registrasi), 0)) as sisa_slot
            FROM event_sesi es
            LEFT JOIN registrasi r ON es.id_sesi = r.event_sesi_id_sesi 
                AND r.status = 'approved'
            WHERE es.event_id_event = ?
            GROUP BY es.id_sesi, es.nama_sesi, es.jumlah_peserta, 
                     es.tanggal_sesi, es.waktu_sesi, es.lokasi_sesi, es.biaya_sesi
            ORDER BY es.tanggal_sesi, es.waktu_sesi
        `;
        
        const [results] = await db.execute(query, [eventId]);
        
        res.json({
            event_id: eventId,
            sessions: results.map(session => ({
                id_sesi: session.id_sesi,
                nama_sesi: session.nama_sesi,
                kapasitas: session.kapasitas,
                jumlah_terdaftar: session.jumlah_terdaftar,
                sisa_slot: Math.max(0, session.sisa_slot),
                tanggal_sesi: session.tanggal_sesi,
                waktu_sesi: session.waktu_sesi,
                lokasi_sesi: session.lokasi_sesi,
                biaya_sesi: session.biaya_sesi,
                status_penuh: session.sisa_slot <= 0
            }))
        });
        
    } catch (error) {
        console.error('Error getting sessions stats:', error);
        res.status(500).json({
            error: true,
            message: 'Gagal mengambil statistik sesi'
        });
    }
});

router.get('/panitia/events/:id/detail', async (req, res) => {
    const eventId = req.params.id;
    
    try {
        const db = await connectDB();
        
        const eventQuery = `
            SELECT 
                e.*,
                u.nama as nama_penyelenggara,
                u.email as email_penyelenggara
            FROM event e
            LEFT JOIN users u ON e.pengguna_id = u.id
            WHERE e.id_event = ?
        `;
        
        const sessionsQuery = `
            SELECT 
                es.*,
                COALESCE(COUNT(r.id_registrasi), 0) as jumlah_terdaftar,
                (es.jumlah_peserta - COALESCE(COUNT(r.id_registrasi), 0)) as sisa_slot
            FROM event_sesi es
            LEFT JOIN registrasi r ON es.id_sesi = r.event_sesi_id_sesi 
                AND r.status = 'approved'
            WHERE es.event_id_event = ?
            GROUP BY es.id_sesi
            ORDER BY es.tanggal_sesi, es.waktu_sesi
        `;
        
        const [eventResult] = await db.execute(eventQuery, [eventId]);
        const [sessionsResult] = await db.execute(sessionsQuery, [eventId]);
        
        if (eventResult.length === 0) {
            return res.status(404).json({
                error: true,
                message: 'Event tidak ditemukan'
            });
        }
        
        const event = eventResult[0];
        
        const totalKapasitas = sessionsResult.reduce((sum, session) => sum + session.jumlah_peserta, 0);
        const totalPeserta = sessionsResult.reduce((sum, session) => sum + session.jumlah_terdaftar, 0);

        const sessionsWithStats = sessionsResult.map(session => ({
            ...session,
            jumlah_terdaftar: session.jumlah_terdaftar,
            sisa_slot: Math.max(0, session.sisa_slot),
            status_penuh: session.sisa_slot <= 0
        }));
        
        res.json({
            ...event,
            sessions: sessionsWithStats,
            total_kapasitas: totalKapasitas,
            total_peserta: totalPeserta,
            sisa_kapasitas: Math.max(0, totalKapasitas - totalPeserta)
        });
        
    } catch (error) {
        console.error('Error getting event detail:', error);
        res.status(500).json({
            error: true,
            message: 'Gagal mengambil detail event'
        });
    }
});

module.exports = router;
