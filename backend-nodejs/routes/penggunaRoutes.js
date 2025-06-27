const express = require('express');
const router = express.Router();
const penggunaController = require('../controllers/penggunaController');

router.get('/pengguna/panitia-dan-keuangan', penggunaController.getPanitiaDanKeuangan);
router.put('/pengguna/:id', penggunaController.updatePengguna);
router.patch('/pengguna/:id/status', penggunaController.toggleStatus);
router.delete('/pengguna/:id', penggunaController.deletePengguna);

module.exports = router;
