// Step 1: Create a minimal server to test routes one by one
// Create a new file: server-debug.js

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();

// Basic middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Test route
app.get('/api/test', (req, res) => {
    res.json({ success: true, message: 'Server is working' });
});

// Try to load routes one by one to identify the problematic one
console.log('Loading routes...');

try {
    console.log('Loading AuthRoutes...');
    const AuthRoutes = require('./routes/AuthRoutes');
    app.use('/api/auth-test', AuthRoutes);
    console.log('✓ AuthRoutes loaded successfully');
} catch (error) {
    console.error('✗ Error loading AuthRoutes:', error.message);
}

try {
    console.log('Loading RoleRoutes...');
    const RoleRoutes = require('./routes/RoleRoutes');
    app.use('/api/role-test', RoleRoutes);
    console.log('✓ RoleRoutes loaded successfully');
} catch (error) {
    console.error('✗ Error loading RoleRoutes:', error.message);
}

try {
    console.log('Loading eventRoutes...');
    const eventRoutes = require('./routes/eventRoutes');
    app.use('/api/event-test', eventRoutes);
    console.log('✓ eventRoutes loaded successfully');
} catch (error) {
    console.error('✗ Error loading eventRoutes:', error.message);
}

try {
    console.log('Loading registerRoutes...');
    const registerRoutes = require('./routes/registerRoutes');
    app.use('/api/register-test', registerRoutes);
    console.log('✓ registerRoutes loaded successfully');
} catch (error) {
    console.error('✗ Error loading registerRoutes:', error.message);
}

try {
    console.log('Loading guestRoutes...');
    const guestRoutes = require('./routes/guestRoutes');
    app.use('/api/guest-test', guestRoutes);
    console.log('✓ guestRoutes loaded successfully');
} catch (error) {
    console.error('✗ Error loading guestRoutes:', error.message);
}

try {
    console.log('Loading detailRoutes...');
    const detailRoutes = require('./routes/detailRoutes');
    app.use('/api/detail-test', detailRoutes);
    console.log('✓ detailRoutes loaded successfully');
} catch (error) {
    console.error('✗ Error loading detailRoutes:', error.message);
}

try {
    console.log('Loading registrasiRoutes...');
    const registrasiRoutes = require('./routes/registrasiRoutes');
    app.use('/api/registrasi-test', registrasiRoutes);
    console.log('✓ registrasiRoutes loaded successfully');
} catch (error) {
    console.error('✗ Error loading registrasiRoutes:', error.message);
}

const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
    console.log(`Debug server running on http://localhost:${PORT}`);
    console.log('Test with: GET http://localhost:' + PORT + '/api/test');
});