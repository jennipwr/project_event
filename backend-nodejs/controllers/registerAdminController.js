const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const connectDB = require('../database.js');

const registerAdminController = {
    async register(req, res) {
        const { name, email, password, role_id_role } = req.body;

        // Validasi input
        if (!name || !email || !password || !role_id_role) {
            return res.status(400).json({
                success: false,
                message: 'Name, email, password, and role are required'
            });
        }

        // Validasi email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return res.status(400).json({
                success: false,
                message: 'Invalid email format'
            });
        }

        // Password minimal
        if (password.length < 8) {
            return res.status(400).json({
                success: false,
                message: 'Password must be at least 8 characters'
            });
        }

        try {
            const db = await connectDB();

            // Tentukan prefix berdasarkan role
            let prefix;
            switch (parseInt(role_id_role)) {
                case 1: prefix = 'MB-'; break;
                case 2: prefix = 'PN-'; break;
                case 4: prefix = 'KGN-'; break;
                default:
                    return res.status(400).json({ success: false, message: 'Invalid role ID' });
            }

            // Ambil ID terakhir berdasarkan prefix
            const [lastIdResult] = await db.execute(`
                SELECT id FROM pengguna 
                WHERE id LIKE ? 
                ORDER BY CAST(SUBSTRING(id, 4) AS UNSIGNED) DESC 
                LIMIT 1
            `, [`${prefix}%`]);

            let newId;
            if (lastIdResult.length > 0) {
                const lastId = lastIdResult[0].id;
                const nextNumber = parseInt(lastId.split('-')[1]) + 1;
                newId = `${prefix}${nextNumber.toString().padStart(3, '0')}`;
            } else {
                newId = `${prefix}001`;
            }

            // Cek email duplikat
            const [checkEmail] = await db.execute(`SELECT id FROM pengguna WHERE email = ?`, [email]);
            if (checkEmail.length > 0) {
                return res.status(409).json({
                    success: false,
                    message: 'Email already registered'
                });
            }

            // Hash password
            const hashed = await bcrypt.hash(password, 12);

            // Insert data pengguna
            await db.execute(`
                INSERT INTO pengguna (id, name, email, password, role_id_role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'aktif', NOW(), NOW())
            `, [newId, name, email, hashed, role_id_role]);

            const token = jwt.sign({
                id: newId,
                email,
                role_id_role
            }, process.env.JWT_SECRET || 'your-secret-key', { expiresIn: '24h' });

            return res.status(201).json({
                success: true,
                message: 'Admin user registered successfully',
                data: {
                    id: newId,
                    name,
                    email,
                    role_id_role,
                    status: 'aktif',
                    token
                }
            });

        } catch (error) {
            console.error('Admin registration error:', error);
            if (error.code === 'ER_DUP_ENTRY') {
                return res.status(409).json({
                    success: false,
                    message: 'Duplicate entry'
                });
            }

            res.status(500).json({
                success: false,
                message: 'Internal server error'
            });
        }
    }
};

module.exports = registerAdminController;
