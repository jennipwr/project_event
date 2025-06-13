const express = require('express');
const router = express.Router();
const db = require('../database.js');

router.get('/roles', (req, res) => {
    db.query('SELECT * FROM role', (err, results) => {
        if (err) return res.status(500).json({ error: err });
        res.json(results);
    });
});

module.exports = router;
