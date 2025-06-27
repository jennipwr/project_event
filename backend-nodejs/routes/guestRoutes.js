const express = require('express');
const router = express.Router();
const guestController = require('../controllers/guestController');

router.get('/test/connection', guestController.testConnection);

router.get('/events/all', guestController.getAllEvents);

router.get('/events/search', guestController.searchEvents);

router.get('/events/:eventId', guestController.getEventDetail);

router.get('/debug', guestController.debugEvents);

module.exports = router;