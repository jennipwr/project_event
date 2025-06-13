const connectDB = require("../database");

// Method untuk mendapatkan semua event untuk tampilan home
exports.getAllEvents = async (req, res) => {
  console.log("=== DEBUG: getAllEvents called ===");
  let db;

  try {
    console.log("1. Connecting to database...");
    db = await connectDB();
    console.log("2. Database connected successfully");

    // Query yang diperbaiki - pisahkan untuk menghindari masalah dengan LEFT JOIN
    const query = `
      SELECT 
        e.id_event,
        e.nama_event,
        e.poster,
        e.deskripsi,
        e.syarat_ketentuan,
        e.pengguna_id,
        p.name as nama_penyelenggara,
        p.email as email_penyelenggara
      FROM event e
      LEFT JOIN pengguna p ON e.pengguna_id = p.id
      WHERE p.role_id_role = 2
    `;

    console.log("3. Executing main query...");
    console.log("Query:", query);

    const [events] = await db.query(query);
    console.log("4. Query executed, found events:", events.length);
    console.log("5. All events data:", events);

    // Cek apakah ada event
    if (!events || events.length === 0) {
      console.log("6. No events found, returning empty array");
      return res.status(200).json({
        error: false,
        message: "Tidak ada event yang ditemukan",
        events: [],
      });
    }

    console.log("7. Processing sessions for each event...");
    // Untuk setiap event, ambil data sesi dan hitung statistik
    const eventsWithSessions = await Promise.all(
      events.map(async (event, index) => {
        console.log(
          `Processing event ${index + 1}/${events.length}: ${event.nama_event} (ID: ${event.id_event})`
        );

        const sessionsQuery = `
          SELECT 
            id_sesi,
            nama_sesi,
            waktu_sesi,
            narasumber_sesi,
            lokasi_sesi,
            tanggal_sesi,
            biaya_sesi,
            jumlah_peserta
          FROM event_sesi 
          WHERE event_id_event = ?
          ORDER BY tanggal_sesi ASC, waktu_sesi ASC
        `;

        const [sessions] = await db.query(sessionsQuery, [event.id_event]);
        console.log(`  - Found ${sessions.length} sessions for event ${event.nama_event}`);

        // Hitung statistik
        let total_sesi = sessions.length;
        let harga_terendah = null;
        let tanggal_terdekat = null;

        if (sessions.length > 0) {
          const prices = sessions.map(s => parseFloat(s.biaya_sesi) || 0);
          harga_terendah = Math.min(...prices);
          
          const dates = sessions.map(s => new Date(s.tanggal_sesi)).filter(d => !isNaN(d));
          if (dates.length > 0) {
            tanggal_terdekat = new Date(Math.min(...dates));
          }
        }

        return {
          ...event,
          sessions: sessions || [],
          total_sesi,
          harga_terendah,
          tanggal_terdekat
        };
      })
    );

    console.log("8. All events processed, sending response");
    console.log("Final events count:", eventsWithSessions.length);
    console.log("Sample processed event:", eventsWithSessions[0]);

    res.status(200).json({
      error: false,
      message: "Data event berhasil diambil",
      events: eventsWithSessions,
    });
  } catch (error) {
    console.error("=== ERROR in getAllEvents ===");
    console.error("Error details:", error);
    console.error("Stack trace:", error.stack);

    res.status(500).json({
      error: true,
      message: "Gagal mengambil data event",
      details: process.env.NODE_ENV === "development" ? error.message : undefined,
    });
  } 
};

// Debug method untuk cek data lebih detail
exports.debugEvents = async (req, res) => {
  console.log("=== DEBUG: debugEvents called ===");
  let db;

  try {
    db = await connectDB();

    // Cek semua panitia
    const panitiaQuery = "SELECT id, name, email, role_id_role FROM pengguna WHERE role_id_role = 2";
    const [panitiaUsers] = await db.query(panitiaQuery);
    console.log("Panitia users:", panitiaUsers);

    // Cek semua event
    const allEventsQuery = "SELECT id_event, nama_event, pengguna_id FROM event";
    const [allEvents] = await db.query(allEventsQuery);
    console.log("All events:", allEvents);

    // Cek event dengan join
    const eventWithJoinQuery = `
      SELECT 
        e.id_event,
        e.nama_event,
        e.pengguna_id,
        p.id as user_id,
        p.name as nama_penyelenggara,
        p.role_id_role
      FROM event e
      LEFT JOIN pengguna p ON e.pengguna_id = p.id
    `;
    const [eventsWithJoin] = await db.query(eventWithJoinQuery);
    console.log("Events with join:", eventsWithJoin);

    // Cek sessions
    const sessionsQuery = "SELECT id_sesi, nama_sesi, event_id_event, biaya_sesi FROM event_sesi";
    const [allSessions] = await db.query(sessionsQuery);
    console.log("All sessions:", allSessions);

    res.status(200).json({
      success: true,
      message: "Debug data retrieved",
      data: {
        panitia_users: panitiaUsers,
        all_events: allEvents,
        events_with_join: eventsWithJoin,
        all_sessions: allSessions
      }
    });
  } catch (error) {
    console.error("Error in debugEvents:", error);
    res.status(500).json({
      success: false,
      message: "Debug failed",
      error: error.message
    });
  }
};

// Method lainnya tetap sama...
exports.testConnection = async (req, res) => {
  console.log("=== DEBUG: testConnection called ===");
  let db;

  try {
    console.log("1. Testing database connection...");
    db = await connectDB();
    console.log("2. Database connected successfully");

    // Test query sederhana
    const testQuery = "SELECT COUNT(*) as total_events FROM event";
    const [result] = await db.query(testQuery);
    console.log("3. Test query result:", result);

    // Cek tabel pengguna
    const userQuery =
      "SELECT COUNT(*) as total_users, COUNT(CASE WHEN role_id_role = 2 THEN 1 END) as panitia_count FROM pengguna";
    const [userResult] = await db.query(userQuery);
    console.log("4. User query result:", userResult);

    // Cek event dengan join
    const eventQuery = `
      SELECT 
        e.id_event,
        e.nama_event,
        p.name as nama_penyelenggara,
        p.role_id_role
      FROM event e
      LEFT JOIN pengguna p ON e.pengguna_id = p.id
      LIMIT 5
    `;
    const [eventResult] = await db.query(eventQuery);
    console.log("5. Event with join result:", eventResult);

    res.status(200).json({
      success: true,
      message: "Database connection test successful",
      data: {
        total_events: result[0].total_events,
        total_users: userResult[0].total_users,
        panitia_count: userResult[0].panitia_count,
        sample_events: eventResult,
      },
    });
  } catch (error) {
    console.error("=== ERROR in testConnection ===");
    console.error("Error details:", error);

    res.status(500).json({
      success: false,
      message: "Database connection test failed",
      error: error.message,
    });
  }
};

exports.getEventDetail = async (req, res) => {
  console.log("=== DEBUG: getEventDetail called ===");
  console.log("Event ID:", req.params.eventId);

  let db;

  try {
    db = await connectDB();
    const { eventId } = req.params;

    if (!eventId || isNaN(eventId)) {
      return res.status(400).json({
        error: true,
        message: "ID event tidak valid",
      });
    }

    const eventQuery = `
      SELECT 
        e.id_event,
        e.nama_event,
        e.poster,
        e.deskripsi,
        e.syarat_ketentuan,
        e.pengguna_id,
        p.name as nama_penyelenggara,
        p.email as email_penyelenggara
      FROM event e
      LEFT JOIN pengguna p ON e.pengguna_id = p.id
      WHERE e.id_event = ? AND p.role_id_role = 2
      LIMIT 1
    `;

    const [eventRows] = await db.query(eventQuery, [eventId]);
    console.log("Event detail result:", eventRows);

    if (!eventRows || eventRows.length === 0) {
      return res.status(404).json({
        error: true,
        message: "Event tidak ditemukan",
      });
    }

    const event = eventRows[0];

    const sessionsQuery = `
      SELECT 
        id_sesi,
        nama_sesi,
        waktu_sesi,
        narasumber_sesi,
        lokasi_sesi,
        tanggal_sesi,
        biaya_sesi,
        jumlah_peserta,
        event_id_event
      FROM event_sesi 
      WHERE event_id_event = ?
      ORDER BY tanggal_sesi ASC, waktu_sesi ASC
    `;

    const [sessionRows] = await db.query(sessionsQuery, [eventId]);
    console.log("Event sessions result:", sessionRows);

    const response = {
      error: false,
      message: "Detail event berhasil diambil",
      ...event,
      sessions: sessionRows || [],
    };

    return res.status(200).json(response);
  } catch (error) {
    console.error("Error in getEventDetail:", error);
    return res.status(500).json({
      error: true,
      message: "Gagal mengambil detail event",
      details: process.env.NODE_ENV === "development" ? error.message : undefined,
    });
  }
};

exports.searchEvents = async (req, res) => {
  console.log("=== DEBUG: searchEvents called ===");
  console.log("Search query:", req.query.q);

  let db;

  try {
    db = await connectDB();
    const { q } = req.query;

    if (!q || q.trim() === "") {
      return res.status(400).json({
        error: true,
        message: "Query pencarian tidak boleh kosong",
      });
    }

    const searchQuery = `
      SELECT 
        e.id_event,
        e.nama_event,
        e.poster,
        e.deskripsi,
        e.syarat_ketentuan,
        e.pengguna_id,
        p.name as nama_penyelenggara,
        p.email as email_penyelenggara
      FROM event e
      LEFT JOIN pengguna p ON e.pengguna_id = p.id
      WHERE p.role_id_role = 2 
        AND (e.nama_event LIKE ? OR e.deskripsi LIKE ? OR p.name LIKE ?)
    `;

    const searchTerm = `%${q}%`;
    const [events] = await db.query(searchQuery, [searchTerm, searchTerm, searchTerm]);
    console.log("Search results:", events.length, "events found");

    // Untuk setiap event, ambil juga data sesi-sesinya
    const eventsWithSessions = await Promise.all(
      events.map(async (event) => {
        const sessionsQuery = `
          SELECT 
            id_sesi,
            nama_sesi,
            waktu_sesi,
            narasumber_sesi,
            lokasi_sesi,
            tanggal_sesi,
            biaya_sesi,
            jumlah_peserta
          FROM event_sesi 
          WHERE event_id_event = ?
          ORDER BY tanggal_sesi ASC, waktu_sesi ASC
        `;

        const [sessions] = await db.query(sessionsQuery, [event.id_event]);

        return {
          ...event,
          sessions: sessions || [],
        };
      })
    );

    res.status(200).json({
      error: false,
      message: `Ditemukan ${eventsWithSessions.length} event`,
      events: eventsWithSessions,
      query: q,
    });
  } catch (error) {
    console.error("Error searching events:", error);
    res.status(500).json({
      error: true,
      message: "Gagal mencari event",
      details: process.env.NODE_ENV === "development" ? error.message : undefined,
    });
  }
};