const connectDB = require('../database');
const fs = require('fs');
const path = require('path');

const sertifikatController = {
    // Ambil peserta yang sudah hadir untuk event & sesi tertentu
    async getPesertaHadir(req, res) {
        let connection;
        try {
            const { eventId, sesiId } = req.params;

            connection = await connectDB();
            const [rows] = await connection.execute(`
                SELECT 
                    k.id AS kehadiran_id,
                    p.name AS nama,
                    p.email,
                    r.qrcode,
                    k.registrasi_id_registrasi,
                    k.registrasi_pengguna_id,
                    k.registrasi_event_id_event,
                    k.registrasi_event_sesi_id_sesi,
                    s.file AS sertifikat_file,
                    s.id_sertifikat
                FROM kehadiran k
                INNER JOIN registrasi r ON k.registrasi_id_registrasi = r.id_registrasi
                INNER JOIN pengguna p ON r.pengguna_id = p.id
                LEFT JOIN sertifikat s ON s.kehadiran_id = k.id
                WHERE k.registrasi_event_id_event = ? AND k.registrasi_event_sesi_id_sesi = ?
            `, [eventId, sesiId]);

            res.json({
                success: true,
                data: rows
            });
        } catch (err) {
            console.error('Error getPesertaHadir:', err);
            res.status(500).json({ success: false, message: 'Gagal mengambil data peserta' });
        }
    },

    // Upload sertifikat
    async uploadSertifikat(req, res) {
        let connection;
        try {
            const {
                kehadiran_id,
                registrasi_id_registrasi,
                registrasi_pengguna_id,
                registrasi_event_id_event,
                registrasi_event_sesi_id_sesi
            } = req.body;

            if (!req.file) {
                return res.status(400).json({ success: false, message: 'File sertifikat belum diunggah' });
            }

            const filePath = req.file.filename;
            connection = await connectDB();

            // ✅ Validasi kehadiran
            const [cek] = await connection.execute(`
                SELECT * FROM kehadiran WHERE id = ?
            `, [kehadiran_id]);

            if (cek.length === 0) {
                return res.status(400).json({ success: false, message: 'Data kehadiran tidak ditemukan' });
            }

            const row = cek[0];

            // ✅ Generate ID Sertifikat duluan
            const [maxId] = await connection.execute(`
                SELECT MAX(CAST(SUBSTRING(id_sertifikat, 6) AS UNSIGNED)) AS max_id FROM sertifikat
            `);
            const nextId = (maxId[0].max_id || 0) + 1;
            const newId = `SRTF-${String(nextId).padStart(3, '0')}`; // Contoh: SRTF-001

            // ✅ Baru lakukan INSERT
            await connection.execute(`
                INSERT INTO sertifikat (
                    id_sertifikat,
                    file,
                    created_at,
                    updated_at,
                    kehadiran_id,
                    kehadiran_registrasi_id_registrasi,
                    kehadiran_registrasi_pengguna_id,
                    kehadiran_registrasi_event_id_event,
                    kehadiran_registrasi_event_sesi_id_sesi
                ) VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?)
            `, [
                newId,
                filePath,
                row.id,
                row.registrasi_id_registrasi,
                row.registrasi_pengguna_id,
                row.registrasi_event_id_event,
                row.registrasi_event_sesi_id_sesi
            ]);

            res.json({ success: true, message: 'Sertifikat berhasil diunggah' });

        } catch (err) {
            console.error('Error uploadSertifikat:', err);
            res.status(500).json({ success: false, message: 'Gagal mengunggah sertifikat' });
        }
    },

    async viewSertifikat(req, res) {
        try {
            const { filename } = req.params;
            const filePath = path.join(__dirname, '../uploads/sertifikat', filename);

            // Cek apakah file ada
            if (!fs.existsSync(filePath)) {
                return res.status(404).json({ success: false, message: 'File tidak ditemukan' });
            }

            // Tentukan content type berdasarkan ekstensi
            const ext = path.extname(filename).toLowerCase();
            let contentType = 'application/octet-stream';
            
            if (ext === '.pdf') contentType = 'application/pdf';
            else if (['.jpg', '.jpeg'].includes(ext)) contentType = 'image/jpeg';
            else if (ext === '.png') contentType = 'image/png';

            // Set header untuk view inline
            res.setHeader('Content-Type', contentType);
            res.setHeader('Content-Disposition', `inline; filename="${filename}"`);
            
            // Stream file
            const fileStream = fs.createReadStream(filePath);
            fileStream.pipe(res);
            
        } catch (err) {
            console.error('Error viewSertifikat:', err);
            res.status(500).json({ success: false, message: 'Gagal mengakses file' });
        }
    },

    async downloadSertifikat(req, res) {
        try {
            const { filename } = req.params;
            const filePath = path.join(__dirname, '../uploads/sertifikat', filename);

            // Cek apakah file ada
            if (!fs.existsSync(filePath)) {
                return res.status(404).json({ success: false, message: 'File tidak ditemukan' });
            }

            // Set header untuk download
            res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
            
            // Stream file
            const fileStream = fs.createReadStream(filePath);
            fileStream.pipe(res);
            
        } catch (err) {
            console.error('Error downloadSertifikat:', err);
            res.status(500).json({ success: false, message: 'Gagal mengunduh file' });
        }
    }
};

module.exports = sertifikatController;
