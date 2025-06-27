const connectDB = require('../database.js');

const scanController = {
    // Scan QR Code dan catat kehadiran
    async scanQrCode(req, res) {
        let connection;
        try {
            const { qrcode, event_id, event_sesi_id, scanned_by } = req.body;

            if (!qrcode || !event_id || !event_sesi_id) {
                return res.status(400).json({
                    success: false,
                    message: 'QR Code, Event ID, dan Event Sesi ID harus diisi'
                });
            }

            connection = await connectDB();

            // Cari registrasi berdasarkan QR code
            const [registrasiRows] = await connection.execute(
                'SELECT * FROM registrasi WHERE qrcode = ? AND status = "approved"',
                [qrcode]
            );

            if (registrasiRows.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'QR Code tidak valid atau registrasi belum disetujui'
                });
            }

            const registrasi = registrasiRows[0];

            // Validasi apakah registrasi sesuai dengan event yang di-scan
            if (registrasi.event_id_event != event_id) {
                return res.status(400).json({
                    success: false,
                    message: 'QR Code tidak valid untuk event ini'
                });
            }

            // Cek apakah sudah pernah scan untuk sesi ini
            const [kehadiranExists] = await connection.execute(
                'SELECT * FROM kehadiran WHERE registrasi_id_registrasi = ? AND registrasi_event_id_event = ? AND registrasi_event_sesi_id_sesi = ?',
                [registrasi.id_registrasi, event_id, event_sesi_id]
            );

            if (kehadiranExists.length > 0) {
                return res.status(400).json({
                    success: false,
                    message: 'Peserta sudah melakukan scan untuk sesi ini'
                });
            }

            // Ambil data pengguna untuk response
            const [penggunaRows] = await connection.execute(
                'SELECT name, email FROM pengguna WHERE id = ?',
                [registrasi.pengguna_id]
            );

            const pengguna = penggunaRows[0];

            // === Generate ID otomatis ===
            // === Generate ID otomatis untuk kolom `id` ===
            const [lastIdRows] = await connection.execute(`
                SELECT id FROM kehadiran 
                WHERE id IS NOT NULL 
                ORDER BY created_at DESC LIMIT 1
            `);

            let newId;
            if (lastIdRows.length > 0) {
                const lastId = lastIdRows[0].id; // contoh: KHD-042
                const lastNumber = parseInt(lastId.split('-')[1]) || 0;
                const nextNumber = lastNumber + 1;
                newId = `KHD-${String(nextNumber).padStart(3, '0')}`; // KHD-043
            } else {
                newId = 'KHD-001';
            }

            // Insert ke tabel kehadiran
            const [insertResult] = await connection.execute(`
                INSERT INTO kehadiran (
                    id,
                    created_at, 
                    updated_at, 
                    status_kehadiran, 
                    registrasi_id_registrasi, 
                    registrasi_pengguna_id, 
                    registrasi_event_id_event, 
                    registrasi_event_sesi_id_sesi
                ) VALUES (?, NOW(), NOW(), 'hadir', ?, ?, ?, ?)
            `, [
                newId,
                registrasi.id_registrasi,
                registrasi.pengguna_id,
                event_id,
                event_sesi_id
            ]);


            if (insertResult.affectedRows > 0) {
                return res.json({
                    success: true,
                    message: 'Kehadiran berhasil dicatat',
                    data: {
                        nama: pengguna.name,
                        email: pengguna.email,
                        waktu_scan: new Date().toLocaleString('id-ID')
                    }
                });
            } else {
                return res.status(500).json({
                    success: false,
                    message: 'Gagal mencatat kehadiran'
                });
            }

        } catch (error) {
            console.error('Error scan QR code:', error);
            return res.status(500).json({
                success: false,
                message: 'Terjadi kesalahan server'
            });
        }
    },

    // Get events berdasarkan user yang login (panitia)
    async getEventsByUser(req, res) {
        let connection;
        try {
            const { userId } = req.params;
            
            console.log('Getting events for userId:', userId);
            
            if (!userId) {
                return res.status(400).json({
                    success: false,
                    message: 'User ID diperlukan'
                });
            }

            connection = await connectDB();
            
            // Debug: cek semua events dulu
            const [allEvents] = await connection.execute('SELECT * FROM event LIMIT 5');
            console.log('Sample events in database:', allEvents);
            
            const [events] = await connection.execute(`
                SELECT 
                    e.id_event,
                    e.nama_event,
                    e.deskripsi,
                    e.syarat_ketentuan,
                    e.pengguna_id,
                    COUNT(es.id_sesi) as total_sesi
                FROM event e
                LEFT JOIN event_sesi es ON e.id_event = es.event_id_event
                WHERE e.pengguna_id = ?
                GROUP BY e.id_event, e.nama_event, e.deskripsi, e.syarat_ketentuan, e.pengguna_id
            `, [userId]);

            console.log('Events found for user', userId, ':', events);
            console.log('Total events found:', events.length);

            return res.json({
                success: true,
                data: events,
                debug: {
                    userId: userId,
                    totalFound: events.length,
                    sampleAllEvents: allEvents.length
                }
            });

        } catch (error) {
            console.error('Error get events by user:', error);
            return res.status(500).json({
                success: false,
                message: 'Gagal mengambil data event',
                error: error.message
            });
        }
    },

    // Get event sessions untuk dropdown
    async getEventSessions(req, res) {
        let connection;
        try {
            const { eventId } = req.params;
            
            connection = await connectDB();
            
            const [sessions] = await connection.execute(
                'SELECT id_sesi, nama_sesi, tanggal_sesi, waktu_sesi FROM event_sesi WHERE event_id_event = ? ORDER BY tanggal_sesi ASC, waktu_sesi ASC',
                [eventId]
            );

            return res.json({
                success: true,
                data: sessions
            });

        } catch (error) {
            console.error('Error get event sessions:', error);
            return res.status(500).json({
                success: false,
                message: 'Gagal mengambil data sesi event'
            });
        }
    }
};

module.exports = scanController;