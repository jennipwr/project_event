const express = require('express');
const router = express.Router();
const guestController = require('../controllers/guestController');

// Route untuk test koneksi database (debugging)
router.get('/test/connection', guestController.testConnection);

// Route untuk mendapatkan semua event (untuk tampilan home)
router.get('/events/all', guestController.getAllEvents);

// Route untuk pencarian event (harus di atas :eventId)
router.get('/events/search', guestController.searchEvents);

// Route untuk mendapatkan detail event
router.get('/events/:eventId', guestController.getEventDetail);

router.get('/debug', guestController.debugEvents);

module.exports = router;