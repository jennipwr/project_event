const express = require('express');
const router = express.Router();
const registerAdminController = require('../controllers/registerAdminController');

router.post('/register-admin', registerAdminController.register);

module.exports = router;
