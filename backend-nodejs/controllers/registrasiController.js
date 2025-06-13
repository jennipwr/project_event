const connectDB = require('../database.js');

async function generateRegistrationId(db) {
    let attempts = 0;
    const maxAttempts = 5;
    
    while (attempts < maxAttempts) {
        try {
            // Ambil ID terakhir dari database
            const [lastReg] = await db.execute(`
                SELECT id_registrasi FROM registrasi 
                WHERE id_registrasi LIKE 'REG-%' 
                ORDER BY id_registrasi DESC 
                LIMIT 1
            `);
            
            let nextNumber = 1;
            if (lastReg.length > 0) {
                // Extract number dari format REG-001
                const lastNumber = parseInt(lastReg[0].id_registrasi.split('-')[1]);
                nextNumber = lastNumber + 1;
            }
            
            const registrationId = `REG-${nextNumber.toString().padStart(3, '0')}`;
            
            // Cek apakah ID sudah ada (untuk memastikan unique)
            const [existingId] = await db.execute('SELECT id_registrasi FROM registrasi WHERE id_registrasi = ?', [registrationId]);
            
            if (existingId.length === 0) {
                return registrationId;
            }
            
            attempts++;
        } catch (error) {
            console.error('Error in generateRegistrationId:', error);
            attempts++;
        }
    }
    
    // Fallback jika semua attempts gagal
    return `REG-${Date.now()}`;
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'Menunggu Konfirmasi',
        'approved': 'Terkonfirmasi',
        'declined': 'Ditolak'
    };
    return statusMap[status] || status;
}

function getStatusClass(status) {
    const classMap = {
        'pending': 'warning',
        'approved': 'success',
        'declined': 'danger'
    };
    return classMap[status] || 'secondary';
}

class RegistrasiController {
    /**
     * Get event details with sessions for registration
     */
    async getEventForRegistration(req, res) {
        try {
            const { eventId } = req.params;
            const db = await connectDB();

            // Get event details
            const [eventRows] = await db.execute(`
                SELECT e.*, p.name as organizer_name 
                FROM event e 
                LEFT JOIN pengguna p ON e.pengguna_id = p.id 
                WHERE e.id_event = ?
            `, [eventId]);

            if (eventRows.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'Event tidak ditemukan'
                });
            }

            const event = eventRows[0];

            // Get event sessions
            const [sessionRows] = await db.execute(`
                SELECT * FROM event_sesi 
                WHERE event_id_event = ? 
                ORDER BY tanggal_sesi ASC, waktu_sesi ASC
            `, [eventId]);

            // Calculate price range
            let priceRange = 'Gratis';
            if (sessionRows.length > 0) {
                const prices = sessionRows.map(session => session.biaya_sesi);
                const minPrice = Math.min(...prices);
                const maxPrice = Math.max(...prices);
                
                if (minPrice === 0 && maxPrice === 0) {
                    priceRange = 'Gratis';
                } else if (minPrice === maxPrice) {
                    priceRange = `Rp ${minPrice.toLocaleString('id-ID')}`;
                } else {
                    priceRange = `Rp ${minPrice.toLocaleString('id-ID')} - Rp ${maxPrice.toLocaleString('id-ID')}`;
                }
            }

            // Format sessions
            const formattedSessions = sessionRows.map(session => ({
                ...session,
                formatted_date: new Date(session.tanggal_sesi).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }),
                formatted_price: session.biaya_sesi === 0 ? 'Gratis' : `Rp ${session.biaya_sesi.toLocaleString('id-ID')}`,
                is_free: session.biaya_sesi === 0
            }));

            res.json({
                success: true,
                data: {
                    event: {
                        ...event,
                        price_range: priceRange
                    },
                    sessions: formattedSessions
                }
            });

        } catch (error) {
            console.error('Error in getEventForRegistration:', error);
            res.status(500).json({
                success: false,
                message: 'Terjadi kesalahan server'
            });
        }
    }

    /**
     * Process ticket registration
     */
    async processRegistration(req, res) {
    try {
        console.log('Registration request received:', req.body);
        
        const { pengguna_id, event_id, session_id, bukti_transaksi } = req.body;
        
        // Validate required fields
        if (!pengguna_id || !event_id || !session_id) {
            console.log('Validation failed - missing required fields:', {
                pengguna_id: !!pengguna_id,
                event_id: !!event_id,
                session_id: !!session_id
            });
            
            return res.status(400).json({
                success: false,
                message: 'Data tidak lengkap. Pastikan semua field wajib diisi.'
            });
        }

        const userId = pengguna_id; // tetap string
        const eventId = parseInt(event_id); // ini boleh parseInt karena id_event adalah integer
        const sessionId = parseInt(session_id);

        if (!userId || isNaN(eventId) || isNaN(sessionId)) {
            console.log('Invalid data format:', { userId, eventId, sessionId });
            return res.status(400).json({
                success: false,
                message: 'Format data tidak valid'
            });
        }

        let db;
        try {
            db = await connectDB();
            console.log('Database connection established');
        } catch (dbError) {
            console.error('Database connection failed:', dbError);
            return res.status(500).json({
                success: false,
                message: 'Tidak dapat terhubung ke database'
            });
        }

        // Check if user exists
        const [userRows] = await db.execute('SELECT * FROM pengguna WHERE id = ?', [userId]);
        if (userRows.length === 0) {
            console.log('User not found:', userId);
            return res.status(404).json({
                success: false,
                message: 'User tidak ditemukan'
            });
        }
        console.log('User found:', userRows[0].name);

        // Check if event exists
        const [eventRows] = await db.execute(`
        SELECT e.*, p.name as organizer_name 
        FROM event e 
        LEFT JOIN pengguna p ON e.pengguna_id = p.id 
        WHERE e.id_event = ?
    `, [eventId]);

        // Check if session exists
        const [sessionRows] = await db.execute(
            'SELECT * FROM event_sesi WHERE id_sesi = ? AND event_id_event = ?', 
            [sessionId, eventId]
        );
        if (sessionRows.length === 0) {
            console.log('Session not found:', { sessionId, eventId });
            return res.status(404).json({
                success: false,
                message: 'Sesi tidak ditemukan'
            });
        }

        const session = sessionRows[0];
        console.log('Session found:', session.nama_sesi);

        // Check if user already registered for this session
        const [existingRegistration] = await db.execute(`
            SELECT * FROM registrasi 
            WHERE pengguna_id = ? AND event_id_event = ? AND event_sesi_id_sesi = ?
        `, [userId, eventId, sessionId]);

        if (existingRegistration.length > 0) {
            console.log('User already registered for this session');
            return res.status(400).json({
                success: false,
                message: 'Anda sudah terdaftar untuk sesi ini'
            });
        }

        // Check session capacity
        const [registrationCount] = await db.execute(`
            SELECT COUNT(*) as count FROM registrasi 
            WHERE event_id_event = ? AND event_sesi_id_sesi = ? AND status IN ('pending', 'approved')
        `, [eventId, sessionId]);
        const currentCount = registrationCount[0].count;
        const maxCapacity = session.jumlah_peserta;
        
        console.log('Capacity check:', { currentCount, maxCapacity });

        if (currentCount >= maxCapacity) {
            return res.status(400).json({
                success: false,
                message: 'Sesi sudah penuh'
            });
        }

        // Determine status based on session price
        let status = 'pending';
        let buktiTransaksiPath = bukti_transaksi || null;
        const sessionPrice = parseFloat(session.biaya_sesi) || 0;

        console.log('Session price:', sessionPrice);

        if (sessionPrice === 0) {
            // Free session - auto approve
            status = 'approved';
            buktiTransaksiPath = null;
            console.log('Free session - auto approving');
        } else {
            // Paid session - require proof of payment
            if (!bukti_transaksi) {
                return res.status(400).json({
                    success: false,
                    message: 'Bukti transaksi diperlukan untuk sesi berbayar'
                });
            }
            console.log('Paid session - payment proof required');
        }

        const registrationId = await generateRegistrationId(db);
        // Generate QR Code
        const qrcode = `QR-${Date.now()}-${Math.random().toString(36).substr(2, 9).toUpperCase()}`;
        console.log('Generated QR code:', qrcode);

        // Insert registration - FIX: Include session_id in INSERT
        const [result] = await db.execute(`
            INSERT INTO registrasi (id_registrasi, status, bukti_transaksi, qrcode, pengguna_id, event_id_event, event_sesi_id_sesi, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        `, [registrationId, status, buktiTransaksiPath, qrcode, userId, eventId, sessionId]);

        console.log('Registration inserted with ID:', result.insertId);

        // Get registration details for response
        const [newRegistration] = await db.execute(`
            SELECT r.*, e.nama_event, es.nama_sesi, p.name as user_name,
                es.biaya_sesi, es.jumlah_peserta
            FROM registrasi r
            JOIN event e ON r.event_id_event = e.id_event
            JOIN event_sesi es ON es.id_sesi = r.event_sesi_id_sesi AND es.event_id_event = r.event_id_event
            JOIN pengguna p ON r.pengguna_id = p.id
            WHERE r.id_registrasi = ?
        `, [result.insertId]);

        const message = status === 'approved' 
            ? 'Registrasi berhasil! Tiket Anda telah dikonfirmasi.'
            : 'Registrasi berhasil! Menunggu konfirmasi pembayaran dari panitia.';

        console.log('Registration successful:', {
            registrationId: result.insertId,
            status,
            message
        });

        res.json({
            success: true,
            message: message,
            data: newRegistration[0]
        });

    } catch (error) {
        console.error('Error in processRegistration:', {
            message: error.message,
            stack: error.stack,
            body: req.body,
            sql: error.sql || 'No SQL',
            code: error.code || 'No code'
        });

        res.status(500).json({
            success: false,
            message: errorMessage,
            error_code: error.code || 'UNKNOWN_ERROR'
        });
    }
    }
    /**
     * Get user's registration history
     */
    async getRegistrationHistory(req, res) {
    try {
        const { userId } = req.params;
        console.log('Getting history for user ID:', userId);
        
        const db = await connectDB();

        // PERBAIKAN: Gunakan event_sesi_id_sesi bukan session_id
        const query = `
            SELECT 
                r.*,
                e.nama_event,
                e.poster,
                es.nama_sesi,
                es.tanggal_sesi,
                es.waktu_sesi,
                es.lokasi_sesi,
                es.biaya_sesi,
                p.name as organizer_name
            FROM registrasi r
            JOIN event e ON r.event_id_event = e.id_event
            JOIN event_sesi es ON r.event_sesi_id_sesi = es.id_sesi
            JOIN pengguna p ON e.pengguna_id = p.id
            WHERE r.pengguna_id = ?
            ORDER BY r.created_at DESC
        `;
        
        console.log('Executing query:', query);
        console.log('With userId:', userId);

        const [registrations] = await db.execute(query, [userId]);
        
        console.log('Query result count:', registrations.length);
        console.log('Raw registrations:', registrations);

        // Format the data
        const formattedRegistrations = registrations.map(reg => ({
            ...reg,
            formatted_date: new Date(reg.tanggal_sesi).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }),
            formatted_price: reg.biaya_sesi === 0 ? 'Gratis' : `Rp ${reg.biaya_sesi.toLocaleString('id-ID')}`,
            status_text: getStatusText(reg.status),        // Hapus this.
            status_class: getStatusClass(reg.status)       // Hapus this.
        }));

        console.log('Formatted registrations:', formattedRegistrations);

        res.json({
            success: true,
            data: formattedRegistrations
        });

    } catch (error) {
        console.error('Error in getRegistrationHistory:', error);
        res.status(500).json({
            success: false,
            message: 'Terjadi kesalahan server',
            error: error.message
        });
    }
}

    /**
     * Get registration details by ID
     */
    async getRegistrationById(req, res) {
        try {
            const { registrationId } = req.params;
            const db = await connectDB();

            // PERBAIKAN: Gunakan event_sesi_id_sesi bukan session_id
            const query = `
                SELECT 
                    r.*,
                    e.nama_event,
                    e.poster,
                    es.nama_sesi,
                    es.tanggal_sesi,
                    es.waktu_sesi,
                    es.lokasi_sesi,
                    es.biaya_sesi,
                    p.name as organizer_name
                FROM registrasi r
                JOIN event e ON r.event_id_event = e.id_event
                JOIN event_sesi es ON r.event_sesi_id_sesi = es.id_sesi
                JOIN pengguna p ON e.pengguna_id = p.id
                WHERE r.pengguna_id = ?
                ORDER BY r.created_at DESC
            `;

            const [registrations] = await db.execute(query, [registrationId]);

            if (registrations.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'Registrasi tidak ditemukan'
                });
            }

            const registration = registrations[0];
            registration.formatted_date = new Date(registration.tanggal_sesi).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            registration.formatted_price = registration.biaya_sesi === 0 ? 'Gratis' : `Rp ${registration.biaya_sesi.toLocaleString('id-ID')}`;
            registration.status_text = this.getStatusText(registration.status);
            registration.status_class = this.getStatusClass(registration.status);

            res.json({
                success: true,
                data: registration
            });

        } catch (error) {
            console.error('Error in getRegistrationById:', error);
            res.status(500).json({
                success: false,
                message: 'Terjadi kesalahan server'
            });
        }
    }

    /**
     * Helper method to get status text
     */
    getStatusText(status) {
        const statusMap = {
            'pending': 'Menunggu Konfirmasi',
            'approved': 'Terkonfirmasi',
            'declined': 'Ditolak'
        };
        return statusMap[status] || status;
    }

    /**
     * Helper method to get status CSS class
     */
    getStatusClass(status) {
        const classMap = {
            'pending': 'warning',
            'approved': 'success',
            'declined': 'danger'
        };
        return classMap[status] || 'secondary';
    }
    }

module.exports = new RegistrasiController();