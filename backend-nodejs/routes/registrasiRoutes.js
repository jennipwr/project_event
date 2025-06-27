const express = require("express");
const router = express.Router();
const registrasiController = require("../controllers/registrasiController");
const upload = require("../config/multer");

router.get("/event/:eventId", registrasiController.getEventForRegistration);

router.post(
  "/process",
  upload.single("bukti_transaksi"),
  registrasiController.processRegistration
);

router.get("/history/:userId", registrasiController.getRegistrationHistory);

router.get(
  "/details/:registrationId",
  registrasiController.getRegistrationById
);

router.put(
  "/reupload/:registrationId",
  upload.single("bukti_transaksi"),
  registrasiController.reuploadPaymentProof
);

router.get("/test", (req, res) => {
    res.json({
        success: true,
        message: "Node.js API is working",
        timestamp: new Date().toISOString(),
        environment: process.env.NODE_ENV || 'development'
    });
});

router.get('/certificate/download/:registrationId', registrasiController.getCertificate);

router.get("/debug/:registrationId", async (req, res) => {
    try {
        const { registrationId } = req.params;
        const db = await connectDB();
        
        // Test berbagai query
        const queries = {
            basic: `SELECT * FROM registrasi WHERE id_registrasi = ?`,
            withEvent: `SELECT r.*, e.nama_event FROM registrasi r LEFT JOIN event e ON r.event_id_event = e.id_event WHERE r.id_registrasi = ?`,
            count: `SELECT COUNT(*) as total FROM registrasi WHERE id_registrasi = ?`
        };
        
        const results = {};
        
        for (const [name, query] of Object.entries(queries)) {
            try {
                const [result] = await db.execute(query, [registrationId]);
                results[name] = {
                    success: true,
                    data: result,
                    count: result.length
                };
            } catch (error) {
                results[name] = {
                    success: false,
                    error: error.message
                };
            }
        }
        
        res.json({
            success: true,
            registrationId: registrationId,
            queries: results,
            timestamp: new Date().toISOString()
        });
        
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message,
            registrationId: req.params.registrationId
        });
    }
});

module.exports = router;
