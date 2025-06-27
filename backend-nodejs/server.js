require("dotenv").config();
const express = require("express");
const cors = require("cors");
const path = require("path");
const app = express();

app.use(cors({
  origin: process.env.FRONTEND_URL || "http://localhost:8000",
  credentials: true,
}));
app.use(express.json());

app.use("/uploads", express.static(path.join(__dirname, "uploads")));

// Import dan mount routes satu per satu
const routes = [
  { name: "AuthRoutes", path: "./routes/AuthRoutes", mount: "/api" },
  { name: "RoleRoutes", path: "./routes/RoleRoutes", mount: "/api" },
  { name: "registerRoutes", path: "./routes/registerRoutes", mount: "/api" },
  { name: "guestRoutes", path: "./routes/guestRoutes", mount: "/api" },
  { name: "eventRoutes", path: "./routes/eventRoutes", mount: "/api" },
  { name: "registrasiRoutes", path: "./routes/registrasiRoutes", mount: "/api/registrasi" },
  { name: "detailRoutes", path: "./routes/detailRoutes", mount: "/api/detail" },
  { name: "keuanganRoutes", path: "./routes/keuanganRoutes", mount: "/api/keuangan" },
  { name: "sertifikatRoutes", path: "./routes/sertifikatRoutes", mount: "/api/sertifikat" },
  { name: "scanRoutes", path: "./routes/scanRoutes", mount: "/api/panitia/scan" },
  { name: "registerAdminRoutes", path: "./routes/registerAdminRoutes", mount: "/api" },
  { name: "penggunaRoutes", path: "./routes/penggunaRoutes", mount: "/api" },
];

routes.forEach(({ name, path, mount }) => {
  try {
    console.log(`Loading and mounting ${name}...`);
    const route = require(path);
    app.use(mount, route);
    console.log(`✓ ${name} mounted successfully at ${mount}`);
  } catch (error) {
    console.error(`✗ Failed to mount ${name}:`, error.message);
    process.exit(1);
  }
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log(`🎉 Server running successfully on http://localhost:${PORT}`);
});

app.get("/api/test", (req, res) => {
  res.json({
    success: true,
    message: "Server is working!",
    timestamp: new Date().toISOString()
  });
});