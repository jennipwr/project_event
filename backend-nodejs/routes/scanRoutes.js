const express = require('express');
const router = express.Router();
const scanController = require('../controllers/scanController');

router.post('/qrcode', scanController.scanQrCode);

router.get('/events/:userId', scanController.getEventsByUser);

router.get('/sessions/:eventId', scanController.getEventSessions);

module.exports = router;