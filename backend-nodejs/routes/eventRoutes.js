const express = require('express');
const router = express.Router();
const eventController = require('../controllers/eventController');
const multer = require('multer');
const path = require('path');

// Setup penyimpanan file poster
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'uploads/');
  },
  filename: (req, file, cb) => {
    const uniqueName = Date.now() + '-' + file.originalname;
    cb(null, uniqueName);
  }
});
const fileFilter = (req, file, cb) => {
  // Hanya accept file gambar
  if (file.mimetype.startsWith('image/')) {
    cb(null, true);
  } else {
    cb(new Error('Only image files are allowed!'), false);
  }
};

const upload = multer({ 
  storage: storage,
  fileFilter: fileFilter,
  limits: {
    fileSize: 2 * 1024 * 1024 // 2MB limit
  }
});

// Routes
// POST - Create new event
router.post('/event', upload.single('poster'), eventController.createEvent);
// PUT - Update event by ID
router.put('/event/:eventId', upload.single('poster'), eventController.updateEvent);

// GET - Get events by user ID
router.get('/events/user/:userId', eventController.getEventsByUser);

// GET - Get event detail by ID
router.get('/event/:eventId', eventController.getEventDetail);
router.get('/panitia/events/:eventId/detail', eventController.getEventDetail);

// DELETE - Delete event by ID
router.delete('/event/:eventId', eventController.deleteEvent);

// Error handling middleware untuk multer
router.use((error, req, res, next) => {
  if (error instanceof multer.MulterError) {
    if (error.code === 'LIMIT_FILE_SIZE') {
      return res.status(400).json({ message: 'File terlalu besar. Maksimal 2MB.' });
    }
  }
  
  if (error.message === 'Only image files are allowed!') {
    return res.status(400).json({ message: 'Hanya file gambar yang diizinkan!' });
  }
  
  res.status(500).json({ message: 'Terjadi kesalahan server', error: error.message });
});

module.exports = router;
