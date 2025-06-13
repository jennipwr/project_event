const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const connectDB = require('../database.js');

const registerController = {
    async register(req, res) {
        const { name, email, password, role_id_role = 1 } = req.body;

        // Validasi input
        if (!name || !email || !password) {
            return res.status(400).json({
                success: false,
                message: 'Name, email, and password are required'
            });
        }

        // Validasi format email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return res.status(400).json({
                success: false,
                message: 'Please provide a valid email address'
            });
        }

        // Validasi password minimal 8 karakter
        if (password.length < 8) {
            return res.status(400).json({
                success: false,
                message: 'Password must be at least 8 characters long'
            });
        }

        try {
            // Koneksi ke database
            const db = await connectDB();

            // Generate ID baru
            const getLastIdQuery = `
                SELECT id FROM pengguna 
                WHERE id LIKE 'MB-%'
                ORDER BY CAST(SUBSTRING(id, 4) AS UNSIGNED) DESC 
                LIMIT 1
            `;

            const [lastIdResult] = await db.execute(getLastIdQuery);
            let newId;

            if (lastIdResult.length > 0) {
                const lastId = lastIdResult[0].id;
                const numberPart = parseInt(lastId.split('-')[1]);
                const nextNumber = numberPart + 1;
                newId = `MB-${nextNumber.toString().padStart(3, '0')}`;
            } else {
                newId = 'MB-001';
            }

            // Cek apakah email sudah terdaftar
            const checkEmailQuery = 'SELECT id FROM pengguna WHERE email = ?';
            const [existingUser] = await db.execute(checkEmailQuery, [email]);

            if (existingUser.length > 0) {
                return res.status(409).json({
                    success: false,
                    message: 'Email address is already registered'
                });
            }

            // Hash password
            const saltRounds = 12;
            const hashedPassword = await bcrypt.hash(password, saltRounds);

            // Insert user ke database
            const insertQuery = `
                INSERT INTO pengguna (id, name, email, password, role_id_role, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            `;

            await db.execute(insertQuery, [
                newId,
                name,
                email,
                hashedPassword,
                role_id_role
            ]);

            // Generate JWT token (optional, untuk auto-login setelah register)
            const token = jwt.sign(
                { 
                    id: newId, 
                    email: email,
                    role_id_role: role_id_role 
                },
                process.env.JWT_SECRET || 'your-secret-key',
                { expiresIn: '24h' }
            );

            res.status(201).json({
                success: true,
                message: 'User registered successfully',
                data: {
                    id: newId,
                    name: name,
                    email: email,
                    role_id_role: role_id_role,
                    token: token // Optional: kirim token jika ingin auto-login
                }
            });

        } catch (error) {
            console.error('Registration error:', error);
            
            // Handle duplicate entry error (jika ada unique constraint)
            if (error.code === 'ER_DUP_ENTRY') {
                return res.status(409).json({
                    success: false,
                    message: 'Email address is already registered'
                });
            }

            res.status(500).json({
                success: false,
                message: 'Internal server error during registration'
            });
        }
    }
};

module.exports = registerController;