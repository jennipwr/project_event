const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const sertifikatController = require('../controllers/sertifikatController');

const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, path.join(__dirname, '../uploads/sertifikat'));
    },
    filename: function (req, file, cb) {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        const ext = path.extname(file.originalname);
        cb(null, 'SERTIF-' + uniqueSuffix + ext);
    }
});
const upload = multer({ storage });

router.get('/peserta/:eventId/:sesiId', sertifikatController.getPesertaHadir);
router.post('/upload', upload.single('file'), sertifikatController.uploadSertifikat);
router.get('/view/:filename', sertifikatController.viewSertifikat);
router.get('/download/:filename', sertifikatController.downloadSertifikat);

module.exports = router;
