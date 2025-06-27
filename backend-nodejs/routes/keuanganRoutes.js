const express = require("express");
const router = express.Router();
const keuanganController = require("../controllers/keuanganController");

router.get("/registrations", keuanganController.getRegistrations);

router.put(
  "/registrations/:registrationId/status",
  keuanganController.updateRegistrationStatus
);

router.get("/test", keuanganController.testQuery);

router.get("/events", async (req, res) => {
  const connectDB = require("../database");
  const db = await connectDB();
  try {
    const [events] = await db.query(
      "SELECT id_event, nama_event FROM event ORDER BY nama_event"
    );
    res.json({
      success: true,
      data: events,
    });
  } catch (error) {
    console.error("Error fetching events:", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengambil data event",
    });
  }
});

module.exports = router;
