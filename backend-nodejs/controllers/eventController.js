const connectDB = require('../database.js');

exports.createEvent = async (req, res) => {
  const db = await connectDB();
  // Debug: Log req.body untuk melihat struktur data
  console.log('req.body:', req.body);
  console.log('req.file:', req.file);

  // Safely extract data from req.body
  const nama_event = req.body.nama_event;
  const deskripsi = req.body.deskripsi;
  const syarat_ketentuan = req.body.syarat_ketentuan;
  const sessions = req.body.sessions;
  const pengguna_id = req.body.pengguna_id;

  // Validate required fields
  if (!nama_event || !deskripsi || !pengguna_id) {
    return res.status(400).json({ 
      message: 'Missing required fields',
      received: {
        nama_event: !!nama_event,
        deskripsi: !!deskripsi,
        pengguna_id: !!pengguna_id
      }
    });
  }

  let posterPath = null;
  if (req.file) {
    posterPath = req.file.path;
  }

  try {
    // Simpan event
    const insertEventQuery = `
      INSERT INTO event (nama_event, deskripsi, syarat_ketentuan, poster, pengguna_id)
      VALUES (?, ?, ?, ?, ?)
    `;
    const result = await db.query(insertEventQuery, [
      nama_event,
      deskripsi,
      syarat_ketentuan,
      posterPath,
      pengguna_id
    ]);

    console.log('Database result:', result);
    
    // PERBAIKAN: Handle result structure yang benar
    let eventId;
    
    // Jika result adalah array (seperti yang terlihat di log)
    if (Array.isArray(result)) {
      const resultHeader = result[0];
      if (resultHeader?.insertId) {
        eventId = resultHeader.insertId;
      }
    } 
    // Jika result langsung berupa object
    else if (result?.insertId) {
      eventId = result.insertId;
    }
    // Fallback: coba ambil dari lastInsertRowid
    else if (result?.lastInsertRowid) {
      eventId = result.lastInsertRowid;
    }
    
    console.log('Event ID:', eventId);
    
    if (!eventId) {
      throw new Error('Failed to get event ID after insertion');
    }

    // Handle sessions data
    let sessionsArray = [];
    
    if (sessions) {
      try {
        // Handle both string and array cases
        sessionsArray = typeof sessions === 'string' ? JSON.parse(sessions) : sessions;
        
        // Ensure it's an array
        if (!Array.isArray(sessionsArray)) {
          sessionsArray = [];
        }
      } catch (parseError) {
        console.error('Error parsing sessions:', parseError);
        sessionsArray = [];
      }
    }

    // Simpan sessions only if we have valid sessions
    if (sessionsArray.length > 0) {
      for (const session of sessionsArray) {
        // Validate session data before inserting
        if (session && session.nama_sesi && session.narasumber_sesi && session.waktu_sesi) {
          await db.query(
            `INSERT INTO event_sesi (event_id_event, nama_sesi, narasumber_sesi, tanggal_sesi, waktu_sesi, lokasi_sesi, jumlah_peserta, biaya_sesi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
            [eventId, session.nama_sesi, session.narasumber_sesi, session.tanggal_sesi, session.waktu_sesi, session.lokasi_sesi, session.jumlah_peserta, session.biaya_sesi]
          );
        }
      }
    }

    res.status(201).json({ 
      message: 'Event berhasil dibuat',
      eventId: eventId,
      sessionsCreated: sessionsArray.length
    });
  } catch (error) {
    console.error('Error creating event:', error);
    res.status(500).json({ 
      message: 'Gagal menyimpan event', 
      error: error.message 
    });
  }
};

// Method baru untuk mendapatkan daftar event berdasarkan user
exports.getEventsByUser = async (req, res) => {
  const db = await connectDB();
  const { userId } = req.params;

  try {
    const query = `
      SELECT 
        e.*,
        COUNT(es.id_sesi) as total_sesi,
        (SELECT COUNT(*) FROM event_sesi er WHERE er.event_id_event = e.id_event) as total_peserta_terdaftar
      FROM event e
      LEFT JOIN event_sesi es ON e.id_event = es.event_id_event
      WHERE e.pengguna_id = ?
      GROUP BY e.id_event
      ORDER BY e.created_at DESC, e.tanggal DESC
    `;
    
    const events = await db.query(query, [userId]);
    
    res.status(200).json(events);
  } catch (error) {
    console.error('Error fetching user events:', error);
    res.status(500).json({ 
      message: 'Gagal mengambil data event', 
      error: error.message 
    });
  }
};

// Method untuk mendapatkan detail event beserta sesi
exports.getEventsByUser = async (req, res) => {
  const db = await connectDB();
  const { userId } = req.params;

  try {
    const query = `
      SELECT 
        e.*,
        COUNT(es.id_sesi) as total_sesi
      FROM event e
      LEFT JOIN event_sesi es ON e.id_event = es.event_id_event
      WHERE e.pengguna_id = ?
      GROUP BY e.id_event, e.nama_event, e.deskripsi, e.syarat_ketentuan, e.poster, e.pengguna_id
    `;
    
    const events = await db.query(query, [userId]);
    
    res.status(200).json(events);
  } catch (error) {
    console.error('Error fetching user events:', error);
    res.status(500).json({ 
      message: 'Gagal mengambil data event', 
      error: error.message 
    });
  }
};

// Method untuk mendapatkan detail event beserta sesi
exports.getEventDetail = async (req, res) => {
  console.log('req.params:', req.params);
  let db;
  
  try {
    console.log('Getting event detail for ID:', req.params.eventId);
    
    db = await connectDB();
    const { eventId } = req.params;

    if (!eventId || isNaN(eventId)) {
      return res.status(400).json({ 
        error: true,
        message: 'ID event tidak valid' 
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
      WHERE e.id_event = ?
      LIMIT 1
    `;

    const [eventRows] = await db.query(eventQuery, [eventId]);
    console.log('Event query result:', eventRows);

    if (!eventRows || eventRows.length === 0) {
      return res.status(404).json({ 
        error: true,
        message: 'Event tidak ditemukan' 
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
      ORDER BY waktu_sesi ASC
    `;

    const [sessionRows] = await db.query(sessionsQuery, [eventId]);
    console.log('Sessions query result:', sessionRows);

    const response = {
      error: false,
      ...event,
      sessions: sessionRows || []
    };

    console.log('Final response:', response);
    return res.status(200).json(response);

  } catch (error) {
    console.error('Error in getEventDetail:', error);
    return res.status(500).json({
      error: true,
      message: 'Gagal mengambil detail event',
      details: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  } 
};


// Method untuk menghapus event
exports.deleteEvent = async (req, res) => {
  const db = await connectDB();
  const { eventId } = req.params;
  const { pengguna_id } = req.body;

  try {
    // Cek apakah event milik user yang sedang login
    const checkQuery = `SELECT * FROM event WHERE id_event = ? AND pengguna_id = ?`;
    const eventCheck = await db.query(checkQuery, [eventId, pengguna_id]);
    
    if (!eventCheck || eventCheck.length === 0) {
      return res.status(403).json({ message: 'Anda tidak memiliki akses untuk menghapus event ini' });
    }
    
    // Cek apakah sudah ada yang mendaftar
    const registrationCheck = await db.query(
      `SELECT COUNT(*) as total FROM event_sesi WHERE event_id_event = ?`, 
      [eventId]
    );
    
    if (registrationCheck[0].total > 0) {
      return res.status(400).json({ 
        message: 'Tidak dapat menghapus event yang sudah memiliki peserta terdaftar' 
      });
    }
    
    // Hapus sessions terlebih dahulu
    await db.query(`DELETE FROM event_sesi WHERE event_id_event = ?`, [eventId]);
    
    // Hapus event
    await db.query(`DELETE FROM event WHERE id_event = ?`, [eventId]);
    
    res.status(200).json({ message: 'Event berhasil dihapus' });
  } catch (error) {
    console.error('Error deleting event:', error);
    res.status(500).json({ 
      message: 'Gagal menghapus event', 
      error: error.message 
    });
  }
};

exports.updateEvent = async (req, res) => {
  const db = await connectDB();
  const { eventId } = req.params;
  
  console.log('Update Event Request:', {
    eventId,
    body: req.body,
    file: req.file ? req.file.filename : 'No file'
  });

  const {
    nama_event, deskripsi, syarat_ketentuan, jumlah_peserta, sessions, pengguna_id
  } = req.body;

  // Validate required fields
  if (!nama_event || !deskripsi || !pengguna_id) {
    console.error('Missing required fields:', { nama_event, deskripsi, pengguna_id });
    return res.status(400).json({ 
      message: 'Missing required fields',
      received: { nama_event, deskripsi, pengguna_id }
    });
  }

  try {
    // Check if event belongs to user
    const checkQuery = `SELECT * FROM event WHERE id_event = ? AND pengguna_id = ?`;
    const eventCheck = await db.query(checkQuery, [eventId, pengguna_id]);
    
    console.log('Event check result:', eventCheck);
    
    if (!eventCheck || eventCheck.length === 0) {
      return res.status(403).json({ message: 'Unauthorized access or event not found' });
    }

    let posterPath = null;
    if (req.file) {
      posterPath = req.file.path;
    }

    // PERBAIKAN: Fix SQL query syntax
    let updateEventQuery;
    let updateParams;

    if (posterPath) {
      updateEventQuery = `
        UPDATE event SET 
          nama_event = ?, 
          deskripsi = ?, 
          syarat_ketentuan = ?, 
          poster = ?
        WHERE id_event = ? AND pengguna_id = ?
      `;
      updateParams = [nama_event, deskripsi, syarat_ketentuan, posterPath, eventId, pengguna_id];
    } else {
      updateEventQuery = `
        UPDATE event SET 
          nama_event = ?, 
          deskripsi = ?, 
          syarat_ketentuan = ?
        WHERE id_event = ? AND pengguna_id = ?
      `;
      updateParams = [nama_event, deskripsi, syarat_ketentuan, eventId, pengguna_id];
    }

    console.log('Executing query:', updateEventQuery);
    console.log('With params:', updateParams);

    const updateResult = await db.query(updateEventQuery, updateParams);
    console.log('Update result:', updateResult);

    // Handle sessions update
    let sessionsArray = [];
    if (sessions) {
      try {
        sessionsArray = typeof sessions === 'string' ? JSON.parse(sessions) : sessions;
        if (!Array.isArray(sessionsArray)) {
          sessionsArray = [];
        }
      } catch (parseError) {
        console.error('Error parsing sessions:', parseError);
        return res.status(400).json({ 
          message: 'Invalid sessions format', 
          error: parseError.message 
        });
      }
    }

    // Delete existing sessions and insert new ones
    console.log('Deleting existing sessions for event:', eventId);
    await db.query(`DELETE FROM event_sesi WHERE event_id_event = ?`, [eventId]);

    // Insert updated sessions
    if (sessionsArray.length > 0) {
      console.log('Inserting', sessionsArray.length, 'sessions');
      for (let i = 0; i < sessionsArray.length; i++) {
        const session = sessionsArray[i];
        if (session && session.nama_sesi && session.narasumber_sesi && session.waktu_sesi) {
          try {
            await db.query(
              `INSERT INTO event_sesi (event_id_event, nama_sesi, narasumber_sesi, tanggal_sesi, waktu_sesi, lokasi_sesi, jumlah_peserta, biaya_sesi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
              [
                eventId, 
                session.nama_sesi, 
                session.narasumber_sesi, 
                session.tanggal_sesi, 
                session.waktu_sesi, 
                session.lokasi_sesi, 
                session.jumlah_peserta || 0, 
                session.biaya_sesi || 0
              ]
            );
          } catch (sessionError) {
            console.error(`Error inserting session ${i}:`, sessionError);
            return res.status(500).json({ 
              message: `Error inserting session ${i + 1}`, 
              error: sessionError.message 
            });
          }
        } else {
          console.warn(`Skipping invalid session ${i}:`, session);
        }
      }
    }

    res.status(200).json({ 
      message: 'Event berhasil diupdate',
      eventId: eventId,
      sessionsUpdated: sessionsArray.length
    });

  } catch (error) {
    console.error('Error updating event:', error);
    res.status(500).json({ 
      message: 'Gagal mengupdate event', 
      error: error.message,
      stack: process.env.NODE_ENV === 'development' ? error.stack : undefined
    });
  }
};