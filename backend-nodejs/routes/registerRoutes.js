const express = require('express');
const router = express.Router();
const registerController = require('../controllers/registerController');

// Route untuk registrasi
router.post('/register', registerController.register);

module.exports = router;