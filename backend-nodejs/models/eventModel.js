const db = require('../database.js');

exports.insertEvent = (data, callback) => {
  const sql = `
    INSERT INTO event (
      nama_event, deskripsi, syarat_ketentuan, tanggal, waktu, lokasi, 
      narasumber, poster, biaya, jumlah_peserta, tanggal_mulai, waktu_mulai, 
      tanggal_akhir, waktu_akhir, has_sessions, pengguna_id
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`;

  const values = [
    data.nama_event,
    data.deskripsi,
    data.syarat_ketentuan,
    data.tanggal,
    data.waktu,
    data.lokasi,
    data.narasumber,
    data.poster,
    data.biaya,
    data.jumlah_peserta,
    data.tanggal_mulai,
    data.waktu_mulai,
    data.tanggal_akhir,
    data.waktu_akhir,
    data.has_sessions || 0,
    data.pengguna_id  
  ];

  db.query(sql, values, callback);
};

exports.insertSession = (eventId, sessionData, callback) => {
  const sql = `
    INSERT INTO event_sesi (
      event_id, nama_sesi, tanggal_sesi, waktu_sesi, 
      narasumber_sesi, lokasi_sesi
    )
    VALUES (?, ?, ?, ?, ?, ?)`;

  const values = [
    eventId,
    sessionData.nama_sesi,
    sessionData.tanggal_sesi,
    sessionData.waktu_sesi,
    sessionData.narasumber_sesi,
    sessionData.lokasi_sesi
  ];

  db.query(sql, values, callback);
};

exports.insertMultipleSessions = (eventId, sessions, callback) => {
  if (!sessions || sessions.length === 0) {
    return callback(null, []);
  }

  const sql = `
    INSERT INTO event_sessions (
      event_id, nama_sesi, tanggal_sesi, waktu_sesi, 
      narasumber_sesi, lokasi_sesi
    )
    VALUES ?`;

  const values = sessions.map(session => [
    eventId,
    session.nama_sesi,
    session.tanggal_sesi,
    session.waktu_sesi,
    session.narasumber_sesi,
    session.lokasi_sesi
  ]);

  db.query(sql, [values], callback);
};

// Tambahkan fungsi untuk mengambil event berdasarkan pengguna
exports.findByUserId = (userId, callback) => {
  const sql = `
    SELECT 
      e.*,
      GROUP_CONCAT(
        CONCAT_WS('|', 
          es.id, 
          es.nama_sesi, 
          es.tanggal_sesi, 
          es.waktu_sesi, 
          es.narasumber_sesi, 
          es.lokasi_sesi
        ) SEPARATOR ';;'
      ) as sessions_data
    FROM event e
    LEFT JOIN event_sessions es ON e.id = es.event_id
    WHERE e.pengguna_id = ?
    GROUP BY e.id
    ORDER BY e.tanggal DESC`;
    
  db.query(sql, [userId], (err, results) => {
    if (err) {
      return callback(err, null);
    }

    // Process sessions data
    const processedResults = results.map(event => {
      if (event.sessions_data) {
        const sessions = event.sessions_data.split(';;').map(sessionStr => {
          const [id, nama_sesi, tanggal_sesi, waktu_sesi, narasumber_sesi, lokasi_sesi] = sessionStr.split('|');
          return {
            id: parseInt(id),
            nama_sesi,
            tanggal_sesi,
            waktu_sesi,
            narasumber_sesi,
            lokasi_sesi
          };
        });
        event.sessions = sessions;
      } else {
        event.sessions = [];
      }
      
      // Remove the concatenated string
      delete event.sessions_data;
      return event;
    });

    callback(null, processedResults);
  });
};

exports.findAll = (callback) => {
  const sql = `
    SELECT 
      e.*,
      p.name as pengguna_name,  -- Ambil nama pengguna dari tabel pengguna
      GROUP_CONCAT(
        CONCAT_WS('|', 
          es.id, 
          es.nama_sesi, 
          es.tanggal_sesi, 
          es.waktu_sesi, 
          es.narasumber_sesi, 
          es.lokasi_sesi
        ) SEPARATOR ';;'
      ) as sessions_data
    FROM event e
    LEFT JOIN pengguna p ON e.pengguna_id = p.id  -- JOIN dengan tabel pengguna
    LEFT JOIN event_sessions es ON e.id = es.event_id
    GROUP BY e.id
    ORDER BY e.tanggal DESC`;
    
  db.query(sql, (err, results) => {
    if (err) {
      return callback(err, null);
    }

    // Process sessions data
    const processedResults = results.map(event => {
      if (event.sessions_data) {
        const sessions = event.sessions_data.split(';;').map(sessionStr => {
          const [id, nama_sesi, tanggal_sesi, waktu_sesi, narasumber_sesi, lokasi_sesi] = sessionStr.split('|');
          return {
            id: parseInt(id),
            nama_sesi,
            tanggal_sesi,
            waktu_sesi,
            narasumber_sesi,
            lokasi_sesi
          };
        });
        event.sessions = sessions;
      } else {
        event.sessions = [];
      }
      
      // Remove the concatenated string
      delete event.sessions_data;
      return event;
    });

    callback(null, processedResults);
  });
};

exports.findById = (id, callback) => {
  const sql = `
    SELECT 
      e.*,
      p.name as pengguna_name,  -- Ambil nama pengguna
      es.id as session_id,
      es.nama_sesi, 
      es.tanggal_sesi, 
      es.waktu_sesi, 
      es.narasumber_sesi, 
      es.lokasi_sesi
    FROM event e
    LEFT JOIN pengguna p ON e.pengguna_id = p.id  -- JOIN dengan tabel pengguna
    LEFT JOIN event_sessions es ON e.id = es.event_id
    WHERE e.id = ?
    ORDER BY es.id`;
    
  db.query(sql, [id], (err, results) => {
    if (err) {
      return callback(err, null);
    }

    if (results.length === 0) {
      return callback(null, null);
    }

    // Process the results to group sessions
    const event = {
      id: results[0].id,
      nama_event: results[0].nama_event,
      deskripsi: results[0].deskripsi,
      syarat_ketentuan: results[0].syarat_ketentuan,
      tanggal: results[0].tanggal,
      waktu: results[0].waktu,
      lokasi: results[0].lokasi,
      narasumber: results[0].narasumber,
      poster: results[0].poster,
      biaya: results[0].biaya,
      jumlah_peserta: results[0].jumlah_peserta,
      tanggal_mulai: results[0].tanggal_mulai,
      waktu_mulai: results[0].waktu_mulai,
      tanggal_akhir: results[0].tanggal_akhir,
      waktu_akhir: results[0].waktu_akhir,
      has_sessions: results[0].has_sessions,
      pengguna_id: results[0].pengguna_id,  // Tambahkan pengguna_id
      pengguna_name: results[0].pengguna_name,  // Tambahkan nama pengguna
      created_at: results[0].created_at,
      updated_at: results[0].updated_at,
      sessions: []
    };

    // Add sessions if they exist
    results.forEach(row => {
      if (row.session_id) {
        event.sessions.push({
          id: row.session_id,
          nama_sesi: row.nama_sesi,
          tanggal_sesi: row.tanggal_sesi,
          waktu_sesi: row.waktu_sesi,
          narasumber_sesi: row.narasumber_sesi,
          lokasi_sesi: row.lokasi_sesi
        });
      }
    });

    callback(null, event);
  });
};