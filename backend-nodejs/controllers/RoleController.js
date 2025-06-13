const db = require('../database.js');

exports.getAllRole = (req, res) => {
    db.query('SELECT * FROM role', (err, result) => {
        if (err) return res.status(500).json({ error: err });
        res.json(result);
    });
};
