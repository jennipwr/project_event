const connectDB = require('../database');

class PenggunaController {
    // Ambil semua akun panitia dan keuangan
    async getPanitiaDanKeuangan(req, res) {
        try {
            console.log('Fetching panitia dan keuangan data...');
            const db = await connectDB();
            
            const [rows] = await db.execute(`
                SELECT id, name, email, role_id_role, status, created_at, updated_at
                FROM pengguna 
                WHERE role_id_role IN (2, 4)
                ORDER BY created_at DESC
            `);
            
            console.log(`Found ${rows.length} users`);
            
            res.json({ 
                success: true, 
                data: rows,
                count: rows.length 
            });
        } catch (err) {
            console.error('Error in getPanitiaDanKeuangan:', err);
            res.status(500).json({ 
                success: false, 
                message: 'Gagal mengambil data akun',
                error: process.env.NODE_ENV === 'development' ? err.message : undefined
            });
        }
    }

    // Update nama dan email
    async updatePengguna(req, res) {
        const { id } = req.params;
        const { name, email } = req.body;

        // Validasi input
        if (!name || !email) {
            return res.status(400).json({
                success: false,
                message: 'Nama dan email wajib diisi'
            });
        }

        // Validasi email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return res.status(400).json({
                success: false,
                message: 'Format email tidak valid'
            });
        }

        try {
            console.log(`Updating user ${id}:`, { name, email });
            const db = await connectDB();
            
            // Cek apakah user ada
            const [existingUser] = await db.execute(
                `SELECT id FROM pengguna WHERE id = ?`,
                [id]
            );

            if (existingUser.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'Pengguna tidak ditemukan'
                });
            }

            // Cek apakah email sudah digunakan user lain
            const [emailCheck] = await db.execute(
                `SELECT id FROM pengguna WHERE email = ? AND id != ?`,
                [email, id]
            );

            if (emailCheck.length > 0) {
                return res.status(400).json({
                    success: false,
                    message: 'Email sudah digunakan oleh pengguna lain'
                });
            }

            // Update data
            const [result] = await db.execute(
                `UPDATE pengguna SET name = ?, email = ?, updated_at = NOW() WHERE id = ?`,
                [name, email, id]
            );

            if (result.affectedRows === 0) {
                return res.status(400).json({
                    success: false,
                    message: 'Gagal memperbarui data pengguna'
                });
            }

            console.log(`User ${id} updated successfully`);
            res.json({ 
                success: true, 
                message: 'Data pengguna berhasil diperbarui' 
            });
        } catch (err) {
            console.error('Error in updatePengguna:', err);
            res.status(500).json({ 
                success: false, 
                message: 'Gagal memperbarui data pengguna',
                error: process.env.NODE_ENV === 'development' ? err.message : undefined
            });
        }
    }

    // Ubah status aktif/nonaktif
    async toggleStatus(req, res) {
        const { id } = req.params;
        const { status } = req.body;

        // Validasi status
        if (!status || !['aktif', 'nonaktif'].includes(status)) {
            return res.status(400).json({
                success: false,
                message: 'Status harus berupa "aktif" atau "nonaktif"'
            });
        }

        try {
            console.log(`Toggling status for user ${id} to ${status}`);
            const db = await connectDB();
            
            // Cek apakah user ada
            const [existingUser] = await db.execute(
                `SELECT id, status FROM pengguna WHERE id = ?`,
                [id]
            );

            if (existingUser.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'Pengguna tidak ditemukan'
                });
            }

            // Update status
            const [result] = await db.execute(
                `UPDATE pengguna SET status = ?, updated_at = NOW() WHERE id = ?`, 
                [status, id]
            );

            if (result.affectedRows === 0) {
                return res.status(400).json({
                    success: false,
                    message: 'Gagal mengubah status pengguna'
                });
            }

            console.log(`User ${id} status changed to ${status}`);
            res.json({ 
                success: true, 
                message: `Status pengguna berhasil diubah menjadi ${status}` 
            });
        } catch (err) {
            console.error('Error in toggleStatus:', err);
            res.status(500).json({ 
                success: false, 
                message: 'Gagal mengubah status pengguna',
                error: process.env.NODE_ENV === 'development' ? err.message : undefined
            });
        }
    }

    // Hapus pengguna
    async deletePengguna(req, res) {
        const { id } = req.params;

        try {
            console.log(`Deleting user ${id}`);
            const db = await connectDB();
            
            // Cek apakah user ada
            const [existingUser] = await db.execute(
                `SELECT id, name FROM pengguna WHERE id = ?`,
                [id]
            );

            if (existingUser.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'Pengguna tidak ditemukan'
                });
            }

            // Hapus pengguna
            const [result] = await db.execute(`DELETE FROM pengguna WHERE id = ?`, [id]);

            if (result.affectedRows === 0) {
                return res.status(400).json({
                    success: false,
                    message: 'Gagal menghapus pengguna'
                });
            }

            console.log(`User ${id} (${existingUser[0].name}) deleted successfully`);
            res.json({ 
                success: true, 
                message: `Pengguna ${existingUser[0].name} berhasil dihapus` 
            });
        } catch (err) {
            console.error('Error in deletePengguna:', err);
            
            // Handle foreign key constraint error
            if (err.code === 'ER_ROW_IS_REFERENCED_2') {
                return res.status(400).json({
                    success: false,
                    message: 'Tidak dapat menghapus pengguna karena masih memiliki data terkait'
                });
            }

            res.status(500).json({ 
                success: false, 
                message: 'Gagal menghapus pengguna',
                error: process.env.NODE_ENV === 'development' ? err.message : undefined
            });
        }
    }
}

module.exports = new PenggunaController();