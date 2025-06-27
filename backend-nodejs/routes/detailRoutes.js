const express = require('express');
const router = express.Router();
const { getEventDetail } = require('../controllers/detailController');

router.get('/:id', getEventDetail);

module.exports = router;
