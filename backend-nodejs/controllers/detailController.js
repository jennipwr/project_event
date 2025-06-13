const connectDB = require('../database.js');

const getEventDetail = async (req, res) => {
    try {
        const eventId = req.params.id;
        console.log('=== DEBUG: getEventDetail called ===');
        console.log('Event ID:', eventId);
        
        const connection = await connectDB();

        // Get event details with organizer info
        const [eventRows] = await connection.execute(`
            SELECT 
                e.id_event,
                e.nama_event,
                e.poster,
                e.deskripsi,
                e.syarat_ketentuan,
                e.pengguna_id,
                p.name as organizer_name,
                p.email as organizer_email
            FROM event e
            LEFT JOIN pengguna p ON e.pengguna_id = p.id
            WHERE e.id_event = ?
        `, [eventId]);

        console.log('Event detail result:', eventRows);

        if (eventRows.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Event tidak ditemukan'
            });
        }

        const event = eventRows[0];

        // Get event sessions
        const [sessionRows] = await connection.execute(`
            SELECT 
                id_sesi,
                nama_sesi,
                tanggal_sesi,
                waktu_sesi,
                narasumber_sesi,
                lokasi_sesi,
                jumlah_peserta,
                biaya_sesi,
                event_id_event
            FROM event_sesi
            WHERE event_id_event = ?
            ORDER BY tanggal_sesi ASC, waktu_sesi ASC
        `, [eventId]);

        console.log('Event sessions result:', sessionRows);

        // Format date range and price range
        const dateRange = formatDateRange(sessionRows);
        const priceRange = formatPriceRange(sessionRows);

        // Build response object
        const response = {
            success: true,
            event: {
                id_event: event.id_event,
                nama_event: event.nama_event,
                deskripsi: event.deskripsi,
                syarat_ketentuan: event.syarat_ketentuan,
                organizer_name: event.organizer_name || 'Tidak diketahui',
                organizer_email: event.organizer_email,
                poster_url: event.poster ? `http://localhost:3000/uploads/${event.poster.replace(/uploads\\?/g, '').replace(/\\/g, '')}` : null,
                sessions: sessionRows,
                date_range: dateRange,
                price_range: priceRange
            }
        };

        console.log('Final response:', JSON.stringify(response, null, 2));

        return res.json(response);

    } catch (error) {
        console.error('Error getting event detail:', error);
        return res.status(500).json({
            success: false,
            message: 'Terjadi kesalahan server',
            error: error.message
        });
    }
};

const formatDateRange = (sessions) => {
    if (sessions.length === 0) return 'Tanggal belum ditentukan';

    const dates = sessions.map(session => new Date(session.tanggal_sesi));
    const minDate = new Date(Math.min(...dates));
    const maxDate = new Date(Math.max(...dates));

    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const formatDate = (date) => {
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    };

    if (minDate.getTime() === maxDate.getTime()) {
        // Same date
        return formatDate(minDate);
    } else if (minDate.getMonth() === maxDate.getMonth() && minDate.getFullYear() === maxDate.getFullYear()) {
        // Same month and year
        return `${minDate.getDate()}-${maxDate.getDate()} ${months[minDate.getMonth()]} ${minDate.getFullYear()}`;
    } else {
        // Different months or years
        return `${formatDate(minDate)} - ${formatDate(maxDate)}`;
    }
};

const formatPriceRange = (sessions) => {
    if (sessions.length === 0) return 'Harga belum ditentukan';

    const prices = sessions.map(session => parseInt(session.biaya_sesi) || 0);
    const minPrice = Math.min(...prices);
    const maxPrice = Math.max(...prices);

    const formatPrice = (price) => {
        if (price === 0) return 'Gratis';
        return 'Rp ' + price.toLocaleString('id-ID');
    };

    if (minPrice === 0 && maxPrice === 0) {
        return 'Gratis';
    } else if (minPrice === 0) {
        return `Gratis - ${formatPrice(maxPrice)}`;
    } else if (minPrice === maxPrice) {
        return formatPrice(minPrice);
    } else {
        return `${formatPrice(minPrice)} - ${formatPrice(maxPrice)}`;
    }
};

module.exports = {
    getEventDetail
};