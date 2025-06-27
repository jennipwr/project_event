const connectDB = require("../database.js");
const path = require("path");
const fs = require("fs");

class KeuanganController {
  async getRegistrations(req, res) {
    const db = await connectDB();
    try {
      const { status, event, date, page = 1, limit = 10 } = req.query;
      const offset = (page - 1) * limit;

      // Fixed query dengan nama kolom yang benar sesuai database
      let query = `
                SELECT 
                    r.id_registrasi,
                    u.name as user_name,
                    e.nama_event,
                    s.nama_sesi,
                    s.tanggal_sesi,
                    s.biaya_sesi,
                    r.status,
                    r.bukti_transaksi,
                    r.created_at,
                    r.event_id_event,
                    r.event_sesi_id_sesi,
                    r.pengguna_id
                FROM registrasi r
                LEFT JOIN pengguna u ON r.pengguna_id = u.id
                LEFT JOIN event e ON r.event_id_event = e.id_event
                LEFT JOIN event_sesi s ON r.event_sesi_id_sesi = s.id_sesi
                WHERE 1=1
            `;

      // Add filters
      const params = [];
      if (status) {
        query += ` AND r.status = ?`;
        params.push(status);
      }
      if (event) {
        query += ` AND e.id_event = ?`;
        params.push(event);
      }
      if (date) {
        query += ` AND DATE(s.tanggal_sesi) = ?`;
        params.push(date);
      }

      // Get total count for pagination
      const countQuery = `SELECT COUNT(*) as total FROM (${query}) as subquery`;
      const [countResult] = await db.query(countQuery, params);
      const total = countResult[0].total;

      // Add pagination
      query += ` ORDER BY r.created_at DESC LIMIT ? OFFSET ?`;
      params.push(parseInt(limit), parseInt(offset));

      console.log("Executing query:", query);
      console.log("With params:", params);

      // Execute final query
      const [registrations] = await db.query(query, params);

      console.log("Query result:", registrations);

      // Get statistics
      const [statistics] = await db.query(`
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined
                FROM registrasi
            `);

      const totalPages = Math.ceil(total / limit);

      res.json({
        success: true,
        data: {
          registrations,
          statistics: statistics[0],
          pagination: {
            current_page: parseInt(page),
            total_pages: totalPages,
            total: total,
            per_page: parseInt(limit),
            from: offset + 1,
            to: Math.min(offset + parseInt(limit), total),
          },
        },
      });
    } catch (error) {
      console.error("Error in getRegistrations:", error);
      res.status(500).json({
        success: false,
        message: "Terjadi kesalahan saat mengambil data registrasi",
        error: error.message,
      });
    }
  }

  async updateRegistrationStatus(req, res) {
    const db = await connectDB();
    try {
      const { registrationId } = req.params;
      const { action, reason } = req.body;

      let newStatus;
      switch (action) {
        case "approve":
          newStatus = "approved";
          break;
        case "decline":
          newStatus = "declined";
          break;
        default:
          return res.status(400).json({
            success: false,
            message: "Action tidak valid",
          });
      }

      // Check if registration exists first
      const [existingReg] = await db.query(
        "SELECT id_registrasi FROM registrasi WHERE id_registrasi = ?",
        [registrationId]
      );

      if (existingReg.length === 0) {
        return res.status(404).json({
          success: false,
          message: "Registrasi tidak ditemukan",
        });
      }

      await db.query(
        `UPDATE registrasi 
                 SET status = ?, 
                     alasan_penolakan = ?,
                     updated_at = NOW() 
                 WHERE id_registrasi = ?`,
        [newStatus, reason || null, registrationId]
      );

      res.json({
        success: true,
        message: `Registrasi berhasil ${
          newStatus === "approved" ? "disetujui" : "ditolak"
        }`,
      });
    } catch (error) {
      console.error("Error in updateRegistrationStatus:", error);
      res.status(500).json({
        success: false,
        message: "Terjadi kesalahan saat memperbarui status registrasi",
        error: error.message,
      });
    }
  }

  // Tambahan method untuk debug
  async testQuery(req, res) {
    const db = await connectDB();
    try {
      // Test basic queries
      const [registrasi] = await db.query("SELECT * FROM registrasi LIMIT 5");
      const [pengguna] = await db.query("SELECT * FROM pengguna LIMIT 5");
      const [events] = await db.query("SELECT * FROM event LIMIT 5");
      const [sesi] = await db.query("SELECT * FROM event_sesi LIMIT 5");

      res.json({
        success: true,
        data: {
          registrasi,
          pengguna,
          events,
          sesi,
        },
      });
    } catch (error) {
      console.error("Error in testQuery:", error);
      res.status(500).json({
        success: false,
        message: "Test query failed",
        error: error.message,
      });
    }
  }
}

module.exports = new KeuanganController();
