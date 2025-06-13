const express = require('express');
const router = express.Router();
const registrasiController = require('../controllers/registrasiController');

// Get event details for registration
router.get('/event/:eventId', registrasiController.getEventForRegistration);

// Process ticket registration
router.post('/process', registrasiController.processRegistration);

// Get user's registration history
router.get('/history/:userId', registrasiController.getRegistrationHistory);

// Get registration details by ID
router.get('/details/:registrationId', registrasiController.getRegistrationById);

module.exports = router;