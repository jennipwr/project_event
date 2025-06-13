const mysql = require('mysql2/promise');

let connection;

async function connectDB() {
    if (!connection) {
        connection = await mysql.createConnection({
            host: 'localhost',
            user: 'root',
            password: '',
            database: 'pwl_event'
        });
        console.log('Koneksi ke MySQL berhasil!');
    }
    return connection;
}

module.exports = connectDB;
