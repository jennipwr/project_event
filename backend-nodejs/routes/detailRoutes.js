const express = require('express');
const router = express.Router();
const { getEventDetail } = require('../controllers/detailController');

// Get event detail
router.get('/:id', getEventDetail);

module.exports = router;
