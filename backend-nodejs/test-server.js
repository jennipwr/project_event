require("dotenv").config();
const express = require("express");
const cors = require("cors");

const app = express();

// Minimal middleware
app.use(cors({
  origin: "http://localhost:8000",
  credentials: true,
}));
app.use(express.json());

// Import dan mount routes satu per satu dengan try-catch
const routes = [
  { name: "AuthRoutes", path: "./routes/AuthRoutes", mount: "/api" },
  { name: "RoleRoutes", path: "./routes/RoleRoutes", mount: "/api" },
  { name: "registerRoutes", path: "./routes/registerRoutes", mount: "/api" },
  { name: "guestRoutes", path: "./routes/guestRoutes", mount: "/api" },
  { name: "eventRoutes", path: "./routes/eventRoutes", mount: "/api" },
  { name: "registrasiRoutes", path: "./routes/registrasiRoutes", mount: "/api/registrasi" },
  { name: "detailRoutes", path: "./routes/detailRoutes", mount: "/api/detail" },
  { name: "keuanganRoutes", path: "./routes/keuanganRoutes", mount: "/api/keuangan" },
  { name: "scanRoutes", path: "./routes/scanRoutes", mount: "/api/panitia/scan" }
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

// Tambahan: Test endpoint
app.get("/api/test", (req, res) => {
  res.json({ 
    success: true, 
    message: "Server is working!", 
    timestamp: new Date().toISOString() 
  });
});