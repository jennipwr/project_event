const connectDB = require("../database.js");
const upload = require("../config/multer");
const path = require('path'); // Tambahkan import ini
const fs = require('fs'); // Tambahkan import ini

async function generateRegistrationId(db) {
  let attempts = 0;
  const maxAttempts = 5;

  while (attempts < maxAttempts) {
    try {
      const [lastReg] = await db.execute(`
                SELECT id_registrasi FROM registrasi 
                WHERE id_registrasi LIKE 'REG-%' 
                ORDER BY id_registrasi DESC 
                LIMIT 1
            `);

      let nextNumber = 1;
      if (lastReg.length > 0) {
        const lastNumber = parseInt(lastReg[0].id_registrasi.split("-")[1]);
        nextNumber = lastNumber + 1;
      }

      const registrationId = `REG-${nextNumber.toString().padStart(3, "0")}`;
      const [existingId] = await db.execute(
        "SELECT id_registrasi FROM registrasi WHERE id_registrasi = ?",
        [registrationId]
      );

      if (existingId.length === 0) {
        return registrationId;
      }

      attempts++;
    } catch (error) {
      console.error("Error in generateRegistrationId:", error);
      attempts++;
    }
  }
  return `REG-${Date.now()}`;
}

function getStatusText(status) {
  const statusMap = {
    pending: "Menunggu Konfirmasi",
    approved: "Terkonfirmasi",
    declined: "Ditolak",
  };
  return statusMap[status] || status;
}

function getStatusClass(status) {
  const classMap = {
    pending: "warning",
    approved: "success",
    declined: "danger",
  };
  return classMap[status] || "secondary";
}

class RegistrasiController {
  async getEventForRegistration(req, res) {
    try {
      const { eventId } = req.params;
      const db = await connectDB();

      const [eventRows] = await db.execute(
        `
                SELECT e.*, p.name as organizer_name 
                FROM event e 
                LEFT JOIN pengguna p ON e.pengguna_id = p.id 
                WHERE e.id_event = ?
            `,
        [eventId]
      );

      if (eventRows.length === 0) {
        return res.status(404).json({
          success: false,
          message: "Event tidak ditemukan",
        });
      }

      const event = eventRows[0];

      const [sessionRows] = await db.execute(
        `
                SELECT * FROM event_sesi 
                WHERE event_id_event = ? 
                ORDER BY tanggal_sesi ASC, waktu_sesi ASC
            `,
        [eventId]
      );

      let priceRange = "Gratis";
      if (sessionRows.length > 0) {
        const prices = sessionRows.map((session) => session.biaya_sesi);
        const minPrice = Math.min(...prices);
        const maxPrice = Math.max(...prices);

        if (minPrice === 0 && maxPrice === 0) {
          priceRange = "Gratis";
        } else if (minPrice === maxPrice) {
          priceRange = `Rp ${minPrice.toLocaleString("id-ID")}`;
        } else {
          priceRange = `Rp ${minPrice.toLocaleString(
            "id-ID"
          )} - Rp ${maxPrice.toLocaleString("id-ID")}`;
        }
      }

      const formattedSessions = sessionRows.map((session) => ({
        ...session,
        formatted_date: new Date(session.tanggal_sesi).toLocaleDateString(
          "id-ID",
          {
            day: "numeric",
            month: "long",
            year: "numeric",
          }
        ),
        formatted_price:
          session.biaya_sesi === 0
            ? "Gratis"
            : `Rp ${session.biaya_sesi.toLocaleString("id-ID")}`,
        is_free: session.biaya_sesi === 0,
      }));

      res.json({
        success: true,
        data: {
          event: {
            ...event,
            price_range: priceRange,
          },
          sessions: formattedSessions,
        },
      });
    } catch (error) {
      console.error("Error in getEventForRegistration:", error);
      res.status(500).json({
        success: false,
        message: "Terjadi kesalahan server",
      });
    }
  }

  async processRegistration(req, res) {
    try {
      console.log("Registration request received:", req.body);

      const { pengguna_id, event_id, session_id } = req.body;

      if (!pengguna_id || !event_id || !session_id) {
        console.log("Validation failed - missing required fields:", {
          pengguna_id: !!pengguna_id,
          event_id: !!event_id,
          session_id: !!session_id,
        });

        return res.status(400).json({
          success: false,
          message: "Data tidak lengkap. Pastikan semua field wajib diisi.",
        });
      }

      const userId = pengguna_id;
      const eventId = parseInt(event_id);
      const sessionId = parseInt(session_id);

      if (!userId || isNaN(eventId) || isNaN(sessionId)) {
        console.log("Invalid data format:", { userId, eventId, sessionId });
        return res.status(400).json({
          success: false,
          message: "Format data tidak valid",
        });
      }

      let db;
      try {
        db = await connectDB();
        console.log("Database connection established");
      } catch (dbError) {
        console.error("Database connection failed:", dbError);
        return res.status(500).json({
          success: false,
          message: "Tidak dapat terhubung ke database",
        });
      }

      const [userRows] = await db.execute(
        "SELECT * FROM pengguna WHERE id = ?",
        [userId]
      );
      if (userRows.length === 0) {
        console.log("User not found:", userId);
        return res.status(404).json({
          success: false,
          message: "User tidak ditemukan",
        });
      }
      console.log("User found:", userRows[0].name);

      const [eventRows] = await db.execute(
        `
        SELECT e.*, p.name as organizer_name 
        FROM event e 
        LEFT JOIN pengguna p ON e.pengguna_id = p.id 
        WHERE e.id_event = ?
    `,
        [eventId]
      );
      
      if (eventRows.length === 0) {
        console.log("Event not found:", eventId);
        return res.status(404).json({
          success: false,
          message: "Event tidak ditemukan",
        });
      }
      
      const event = eventRows[0];
      const panitiaPengguna_id = event.pengguna_id;
      console.log("Event found:", event.nama_event, "Panitia ID:", panitiaPengguna_id);

      const [sessionRows] = await db.execute(
        "SELECT * FROM event_sesi WHERE id_sesi = ? AND event_id_event = ?",
        [sessionId, eventId]
      );
      if (sessionRows.length === 0) {
        console.log("Session not found:", { sessionId, eventId });
        return res.status(404).json({
          success: false,
          message: "Sesi tidak ditemukan",
        });
      }

      const session = sessionRows[0];
      console.log("Session found:", session.nama_sesi);

      const [existingRegistration] = await db.execute(
        `
            SELECT * FROM registrasi 
            WHERE pengguna_id = ? AND event_id_event = ? AND event_sesi_id_sesi = ?
        `,
        [userId, eventId, sessionId]
      );

      if (existingRegistration.length > 0) {
        console.log("User already registered for this session");
        return res.status(400).json({
          success: false,
          message: "Anda sudah terdaftar untuk sesi ini",
        });
      }

      const [registrationCount] = await db.execute(
        `
            SELECT COUNT(*) as count FROM registrasi 
            WHERE event_id_event = ? AND event_sesi_id_sesi = ? AND status IN ('pending', 'approved')
        `,
        [eventId, sessionId]
      );
      const currentCount = registrationCount[0].count;
      const maxCapacity = session.jumlah_peserta;

      console.log("Capacity check:", { currentCount, maxCapacity });

      if (currentCount >= maxCapacity) {
        return res.status(400).json({
          success: false,
          message: "Sesi sudah penuh",
        });
      }

      let status = "pending";
      let buktiTransaksiPath = null;
      const sessionPrice = parseFloat(session.biaya_sesi) || 0;

      console.log("Session price:", sessionPrice);

      if (sessionPrice === 0) {
        status = "approved";
        buktiTransaksiPath = null;
        console.log("Free session - auto approving");
      } else {
        if (!req.file) {
          return res.status(400).json({
            success: false,
            message: "Bukti transaksi diperlukan untuk sesi berbayar",
          });
        }
        buktiTransaksiPath = `uploads/bukti_transaksi/${req.file.filename}`;
        console.log("Paid session - payment proof received");
      }

      const registrationId = await generateRegistrationId(db);
      const timestamp = Date.now();
      const randomCode = Math.random().toString(36).substr(2, 6).toUpperCase();
      const qrcode = `QR-${eventId}-${sessionId}-${panitiaPengguna_id}-${timestamp}-${randomCode}`;
      
      console.log("Generated QR code:", qrcode);
      console.log("QR Code components:", {
        eventId: eventId,
        sessionId: sessionId,
        panitiaPengguna_id: panitiaPengguna_id,
        timestamp: timestamp,
        randomCode: randomCode
      });

      const [result] = await db.execute(
        `
            INSERT INTO registrasi (id_registrasi, status, bukti_transaksi, qrcode, pengguna_id, event_id_event, event_sesi_id_sesi, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        `,
        [
          registrationId,
          status,
          buktiTransaksiPath,
          qrcode,
          userId,
          eventId,
          sessionId,
        ]
      );

      console.log("Registration inserted with ID:", result.insertId);

      const [newRegistration] = await db.execute(
        `
            SELECT r.*, e.nama_event, es.nama_sesi, p.name as user_name,
                es.biaya_sesi, es.jumlah_peserta
            FROM registrasi r
            JOIN event e ON r.event_id_event = e.id_event
            JOIN event_sesi es ON es.id_sesi = r.event_sesi_id_sesi AND es.event_id_event = r.event_id_event
            JOIN pengguna p ON r.pengguna_id = p.id
            WHERE r.id_registrasi = ?
        `,
        [result.insertId]
      );

      const message =
        status === "approved"
          ? "Registrasi berhasil! Tiket Anda telah dikonfirmasi."
          : "Registrasi berhasil! Menunggu konfirmasi pembayaran dari panitia.";

      console.log("Registration successful:", {
        registrationId: result.insertId,
        status,
        message,
      });

      res.json({
        success: true,
        message: message,
        data: newRegistration[0],
      });
    } catch (error) {
      console.error("Error in processRegistration:", error);
      res.status(500).json({
        success: false,
        message: "Terjadi kesalahan server",
      });
    }
  }
  /**
   * Get user's registration history
   */
  async getRegistrationHistory(req, res) {
    try {
      const { userId } = req.params;
      console.log("Getting history for user ID:", userId);

      const db = await connectDB();
      const query = `
          SELECT 
              r.*,
              e.nama_event,
              e.poster,
              e.deskripsi,
              e.syarat_ketentuan,
              es.nama_sesi,
              es.tanggal_sesi,
              es.waktu_sesi,
              es.lokasi_sesi,
              es.biaya_sesi,
              p.name as organizer_name,
              s.file as sertifikat_file
          FROM registrasi r
          JOIN event e ON r.event_id_event = e.id_event
          JOIN event_sesi es ON r.event_sesi_id_sesi = es.id_sesi
          JOIN pengguna p ON e.pengguna_id = p.id
          LEFT JOIN kehadiran k ON k.registrasi_id_registrasi = r.id_registrasi
          LEFT JOIN sertifikat s ON s.kehadiran_id = k.id
          WHERE r.pengguna_id = ?
          ORDER BY r.created_at DESC
      `;

      console.log("Executing query:", query);
      console.log("With userId:", userId);

      const [registrations] = await db.execute(query, [userId]);

      console.log("Query result count:", registrations.length);
      console.log("Raw registrations:", registrations);

      const formattedRegistrations = registrations.map((reg) => ({
        ...reg,
        formatted_date: new Date(reg.tanggal_sesi).toLocaleDateString("id-ID", {
          day: "numeric",
          month: "long",
          year: "numeric",
        }),
        formatted_price:
          reg.biaya_sesi === 0
            ? "Gratis"
            : `Rp ${reg.biaya_sesi.toLocaleString("id-ID")}`,
        status_text: getStatusText(reg.status),
        status_class: getStatusClass(reg.status),
      }));

      console.log("Formatted registrations:", formattedRegistrations);

      res.json({
        success: true,
        data: formattedRegistrations,
      });
    } catch (error) {
      console.error("Error in getRegistrationHistory:", error);
      res.status(500).json({
        success: false,
        message: "Terjadi kesalahan server",
        error: error.message,
      });
    }
  }

    async getRegistrationById(req, res) {
        let db;
        try {
            const { registrationId } = req.params;
            
            console.log('=== REGISTRATION DETAIL API START ===');
            console.log('Registration ID:', registrationId);
            console.log('Registration ID type:', typeof registrationId);
            console.log('Request URL:', req.originalUrl);
            console.log('Request method:', req.method);
            
            if (!registrationId) {
                console.log('Missing registration ID');
                return res.status(400).json({
                    success: false,
                    message: "Registration ID is required"
                });
            }

            const regIdPattern = /^REG-\d{3,}$/;
            if (!regIdPattern.test(registrationId)) {
                console.log('Invalid registration ID format:', registrationId);
                return res.status(400).json({
                    success: false,
                    message: "Invalid registration ID format. Expected format: REG-XXX"
                });
            }
            
            console.log('Valid registration ID format:', registrationId);
            
            try {
                db = await connectDB();
                console.log('Database connection successful');
            } catch (dbError) {
                console.error('Database connection failed:', dbError);
                return res.status(500).json({
                    success: false,
                    message: "Database connection failed",
                    error: dbError.message
                });
            }

            console.log('Checking if registration exists...');
            const existsQuery = `SELECT id_registrasi, pengguna_id, status, event_id_event, event_sesi_id_sesi FROM registrasi WHERE id_registrasi = ?`;
            
            let existsResult;
            try {
                [existsResult] = await db.execute(existsQuery, [registrationId]);
                console.log('Exists query result:', existsResult);
            } catch (queryError) {
                console.error('Exists query failed:', queryError);
                return res.status(500).json({
                    success: false,
                    message: "Database query failed",
                    error: queryError.message
                });
            }
            
            if (existsResult.length === 0) {
                console.log('Registration not found with ID:', registrationId);
                return res.status(404).json({
                    success: false,
                    message: "Data registrasi tidak ditemukan"
                });
            }
            
            const basicReg = existsResult[0];
            console.log('Basic registration found:', basicReg);
            console.log('Executing complex query with JOINs...');
            const complexQuery = `
                SELECT 
                    r.*,
                    e.nama_event,
                    e.poster,
                    e.deskripsi,
                    es.nama_sesi,
                    es.tanggal_sesi,
                    es.waktu_sesi,
                    es.lokasi_sesi,
                    es.narasumber_sesi,
                    es.biaya_sesi,
                    p.name as organizer_name
                FROM registrasi r
                LEFT JOIN event e ON r.event_id_event = e.id_event
                LEFT JOIN event_sesi es ON r.event_sesi_id_sesi = es.id_sesi
                LEFT JOIN pengguna p ON e.pengguna_id = p.id
                WHERE r.id_registrasi = ?
            `;

            let complexResult;
            try {
                [complexResult] = await db.execute(complexQuery, [registrationId]);
                console.log('Complex query result count:', complexResult.length);
            } catch (complexError) {
                console.error('Complex query failed:', complexError);
                console.log('Using fallback with basic data...');
                const registration = {
                    ...basicReg,
                    nama_event: 'Event tidak ditemukan',
                    nama_sesi: 'Sesi tidak ditemukan',
                    formatted_date: 'Tanggal tidak tersedia',
                    formatted_price: 'Harga tidak tersedia',
                    status_text: this.getStatusText(basicReg.status),
                    status_class: this.getStatusClass(basicReg.status)
                };
                
                return res.json({
                    success: true,
                    data: registration,
                    warning: 'Some event details could not be loaded'
                });
            }

            if (complexResult.length === 0) {
                console.log('Complex query returned no results, this should not happen');
                return res.status(500).json({
                    success: false,
                    message: "Data integrity error - registration exists but joins failed"
                });
            }

            const registration = complexResult[0];
            console.log('Full registration data retrieved');
            try {
                registration.formatted_date = registration.tanggal_sesi 
                    ? new Date(registration.tanggal_sesi).toLocaleDateString("id-ID", {
                        day: "numeric",
                        month: "long",
                        year: "numeric",
                    })
                    : 'Tanggal tidak tersedia';
                
                registration.formatted_price = registration.biaya_sesi !== null && registration.biaya_sesi !== undefined
                    ? (registration.biaya_sesi === 0
                        ? "Gratis"
                        : `Rp ${registration.biaya_sesi.toLocaleString("id-ID")}`)
                    : 'Harga tidak tersedia';
                    
                registration.status_text = this.getStatusText(registration.status);
                registration.status_class = this.getStatusClass(registration.status);
            } catch (formatError) {
                console.error('Error formatting data:', formatError);
            }

            console.log('=== SUCCESS ===');
            console.log('Registration ID:', registration.id_registrasi);
            console.log('User ID:', registration.pengguna_id);
            console.log('Status:', registration.status);
            console.log('Event:', registration.nama_event);

            res.json({
                success: true,
                data: registration,
            });
            
        } catch (error) {
            console.error('=== CRITICAL ERROR in getRegistrationById ===');
            console.error('Error type:', error.constructor.name);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            console.error('Request params:', req.params);
            
            res.status(500).json({
                success: false,
                message: "Terjadi kesalahan server internal",
                error: process.env.NODE_ENV === 'development' ? error.message : 'Internal server error'
            });
        }
    }

    async getCertificate(req, res) {
    try {
        const { registrationId } = req.params;
        const db = await connectDB();

        const [certificateRows] = await db.execute(`
            SELECT s.file, s.created_at 
            FROM sertifikat s
            JOIN kehadiran k ON s.kehadiran_id = k.id
            WHERE k.registrasi_id_registrasi = ?
        `, [registrationId]);

        if (certificateRows.length === 0) {
            return res.status(404).json({
                success: false,
                message: "Sertifikat belum tersedia"
            });
        }

        const certificate = certificateRows[0];
        const certificateUrl = `${req.protocol}://${req.get('host')}/uploads/sertifikat/${certificate.file}`;

        res.json({
            success: true,
            certificate_url: certificateUrl,
            certificate_file: certificate.file
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: "Terjadi kesalahan server"
        });
    }
    }

    async reuploadPaymentProof(req, res) {
        try {
            const { registrationId } = req.params;
            const regIdPattern = /^REG-\d{3,}$/;
            if (!regIdPattern.test(registrationId)) {
                return res.status(400).json({
                    success: false,
                    message: "Invalid registration ID format. Expected format: REG-XXX"
                });
            }
        
            if (!req.file) {
                return res.status(400).json({
                    success: false,
                    message: "File bukti pembayaran harus diupload"
                });
            }

            const db = await connectDB();
            const checkQuery = `
                SELECT r.*, e.nama_event, es.nama_sesi 
                FROM registrasi r
                JOIN event e ON r.event_id_event = e.id_event
                JOIN event_sesi es ON r.event_sesi_id_sesi = es.id_sesi
                WHERE r.id_registrasi = ?
            `;
            
            const [registration] = await db.execute(checkQuery, [registrationId]);
            
            if (registration.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: "Registrasi tidak ditemukan"
                });
            }

            if (registration[0].status !== 'declined') {
                return res.status(400).json({
                    success: false,
                    message: "Registrasi ini tidak memerlukan upload ulang bukti pembayaran"
                });
            }

            const oldFilePath = registration[0].bukti_transaksi;
            if (oldFilePath) {
                const absolutePath = path.resolve(__dirname, '../', oldFilePath);
                fs.unlink(absolutePath, (err) => {
                    if (err) {
                        console.error("Error deleting old file:", err);
                    } else {
                        console.log("Old file deleted:", absolutePath);
                    }
                });
            }

            const updateQuery = `
                UPDATE registrasi 
                SET 
                    bukti_transaksi = ?, 
                    status = 'pending', 
                    updated_at = NOW()
                WHERE id_registrasi = ?
            `;
            
            await db.execute(updateQuery, [
                `uploads/bukti_transaksi/${req.file.filename}`,
                registrationId
            ]);

            res.json({
                success: true,
                message: "Bukti pembayaran berhasil diupload ulang",
            });
        } catch (error) {
            console.error("Error in reuploadPaymentProof:", error);
            res.status(500).json({
                success: false,
                message: "Terjadi kesalahan saat upload ulang bukti pembayaran",
                error: error.message
            });
        }
    }

  getStatusText(status) {
    const statusMap = {
      pending: "Menunggu Konfirmasi",
      approved: "Terkonfirmasi",
      declined: "Ditolak",
    };
    return statusMap[status] || status;
  }

  getStatusClass(status) {
    const classMap = {
      pending: "warning",
      approved: "success",
      declined: "danger",
    };
    return classMap[status] || "secondary";
  }
}

module.exports = new RegistrasiController();
