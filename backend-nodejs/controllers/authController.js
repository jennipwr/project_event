const connectDB = require('../database.js'); // Jika pakai fungsi connectDB
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');

exports.login = async (req, res) => {
    const { email, password } = req.body;

    try {
        const db = await connectDB(); // koneksi async

        const sql = `
            SELECT pengguna.*, role.nama_role 
            FROM pengguna 
            JOIN role ON pengguna.role_id_role = role.id_role 
            WHERE email = ?
        `;

        const [results] = await db.query(sql, [email]); // pakai await, bukan callback

        if (results.length === 0) {
            return res.status(401).json({ message: 'Email tidak ditemukan' });
        }

        const user = results[0];

        const isMatch = await bcrypt.compare(password, user.password);
        if (!isMatch) {
            return res.status(401).json({ message: 'Password salah' });
        }

        const token = jwt.sign(
            { id: user.id, role: user.nama_role, email: user.email, status: user.status, role_id_role: user.role_id_role },
            process.env.JWT_SECRET,
            { expiresIn: '1h' }
        );

        res.json({
            message: 'Login berhasil',
            token: token,
            id: user.id,
            role: user.nama_role,
            name: user.name,
            email: user.email,
            status: user.status,
            role_id_role: user.role_id_role
        });

    } catch (err) {
        console.error('Login error:', err);
        return res.status(500).json({ message: 'Terjadi kesalahan sistem', error: err.message });
    }
};
