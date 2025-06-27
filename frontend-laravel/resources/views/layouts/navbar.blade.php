<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <!-- Brand/Logo -->
        <a href="{{ route('home')}}" class="navbar-brand">
            <img
                src="{{ asset ('assets/images/logos/dark-logo.svg') }}"
                width="180"
                alt="Logo"
            />
        </a>

        <!-- Mobile Toggle Button -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Search Form -->
            <div class="search-container mx-auto">
                <form class="d-flex" role="search">
                    <input
                        class="form-control search-input flex-grow-1"
                        type="search"
                        placeholder="Cari event yang menarik di sini"
                        aria-label="Search"
                    />
                    <button class="btn btn-primary search-btn" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Right Side Navigation -->
            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Authentication Buttons - Show when NOT logged in -->
                <div class="auth-buttons d-flex gap-2 me-3" id="authButtons">
                    <a href="{{ route('register') }}" class="btn btn-outline-primary">
                        Daftar
                    </a>
                    <a href="{{ route('login.form') }}" class="btn btn-primary">
                        Masuk
                    </a>
                </div>

                <!-- User Section - Show when logged in -->
                <div class="user-section d-none align-items-center" id="userSection">
                    <!-- Ticket Button -->
                    <a href="{{ route('event.history') }}" class="btn btn-outline-primary ticket-btn me-3">
                        <i class="fas fa-ticket-alt me-2"></i>
                        Lihat Tiket
                    </a>
                    
                    <!-- User Greeting -->
                    <span class="user-greeting me-3" id="userGreeting">
                        Hai, Loading...
                    </span>
                    
                    <!-- Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link nav-icon-hover"
                            href="javascript:void(0)"
                            id="drop2"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <img
                                src="{{ asset ('assets/images/profile/user-1.jpg') }}"
                                alt="Profile"
                                width="35"
                                height="35"
                                class="rounded-circle profile-img"
                                id="profileImage"
                            />
                        </a>
                        <div
                            class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up"
                            aria-labelledby="drop2"
                        >
                            <div class="message-body">
                                <!-- User Info Header -->
                                <div class="user-info-header px-3 py-2 border-bottom">
                                    <h6 class="mb-0" id="dropdownUserName">Loading...</h6>
                                    <small class="text-muted" id="dropdownUserEmail">Loading...</small>
                                </div>
                                
                                <a
                                    href="javascript:void(0)"
                                    class="d-flex align-items-center gap-2 dropdown-item"
                                >
                                    <i class="ti ti-user fs-6"></i>
                                    <p class="mb-0 fs-3">My Profile</p>
                                </a>
                                <a
                                    href="javascript:void(0)"
                                    class="d-flex align-items-center gap-2 dropdown-item"
                                >
                                    <i class="ti ti-mail fs-6"></i>
                                    <p class="mb-0 fs-3">My Account</p>
                                </a>
                                <a
                                    href="javascript:void(0)"
                                    class="d-flex align-items-center gap-2 dropdown-item"
                                >
                                    <i class="ti ti-list-check fs-6"></i>
                                    <p class="mb-0 fs-3">My Events</p>
                                </a>
                                <a
                                    href="javascript:void(0)"
                                    class="d-flex align-items-center gap-2 dropdown-item"
                                >
                                    <i class="ti ti-settings fs-6"></i>
                                    <p class="mb-0 fs-3">Settings</p>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('logout')}}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</button>
                                </form>
                                
                            </div>
                        </div>
                    </li>
                </div>
            </ul>
        </div>
    </div>
</nav>
<!-- Navbar End -->

<script>
// Fungsi untuk mendapatkan data user dari session Laravel
function getUserFromSession() {
    // Mengambil data user dari Laravel session yang di-pass ke JavaScript
    @if(session('user'))
        return {
            isLoggedIn: true,
            user: {
                id: "{{ session('user.id') }}",
                name: "{{ session('user.name') }}",
                email: "{{ session('user.email') }}",
                role: "{{ session('user.role') }}"
            }
        };
    @else
        return {
            isLoggedIn: false,
            user: null
        };
    @endif
}

// Function untuk toggle visibility berdasarkan status login
function toggleAuthElements() {
    const sessionData = getUserFromSession();
    const authButtons = document.getElementById('authButtons');
    const userSection = document.getElementById('userSection');
    const userGreeting = document.getElementById('userGreeting');
    const dropdownUserName = document.getElementById('dropdownUserName');
    const dropdownUserEmail = document.getElementById('dropdownUserEmail');
    
    if (sessionData.isLoggedIn && sessionData.user) {
        // Hide auth buttons dengan d-none
        authButtons.classList.add('d-none');
        
        // Show user section
        userSection.classList.remove('d-none');
        userSection.classList.add('d-flex');
        
        // Update user greeting
        userGreeting.textContent = `Hai, ${sessionData.user.name}`;
        
        // Update dropdown user info
        dropdownUserName.textContent = sessionData.user.name;
        dropdownUserEmail.textContent = sessionData.user.email;
    } else {
        // Show auth buttons dengan menghapus d-none
        authButtons.classList.remove('d-none');
        
        // Hide user section
        userSection.classList.add('d-none');
        userSection.classList.remove('d-flex');
    }
}

// Jalankan function saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    toggleAuthElements();
    
    // Optional: Update user info setiap 30 detik untuk memastikan session masih aktif
    setInterval(function() {
        // Bisa ditambahkan AJAX call untuk check session status
        toggleAuthElements();
    }, 30000);
});

// Optional: Function untuk handle profile image error
document.getElementById('profileImage').addEventListener('error', function() {
    this.src = "{{ asset ('assets/images/profile/default-avatar.png') }}"; // fallback image
});
</script>

<style>
/* Custom styles untuk auth buttons */
.auth-buttons .btn {
    padding: 8px 16px;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.auth-buttons .btn-outline-primary:hover {
    background-color: var(--bs-primary);
    color: white;
}

.auth-buttons .btn-primary:hover {
    background-color: var(--bs-primary);
    opacity: 0.9;
}

/* Ticket Button Styles */
.ticket-btn {
    padding: 8px 16px;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid #5d87ff;
    color: #5d87ff;
    background: rgba(93, 135, 255, 0.05);
    white-space: nowrap;
}

.ticket-btn:hover {
    background: linear-gradient(135deg, #5d87ff 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(93, 135, 255, 0.3);
    border-color: transparent;
}

.ticket-btn i {
    font-size: 0.9rem;
}

/* User greeting styles */
.user-greeting {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 600;
    font-size: 1.1rem;
    white-space: nowrap;
}

/* Profile image styles */
.profile-img {
    border: 2px solid #5d87ff;
    transition: all 0.3s ease;
    cursor: pointer;
}

.profile-img:hover {
    transform: scale(1.05);
    border-color: #764ba2;
}

/* Dropdown enhancements */
.dropdown-menu {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-radius: 12px;
    padding: 15px 0;
    min-width: 250px;
}

.user-info-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    margin-bottom: 10px;
}

.dropdown-item {
    padding: 10px 20px;
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 2px 10px;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #5d87ff 0%, #764ba2 100%);
    color: white;
    transform: translateX(5px);
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

.dropdown-divider {
    margin: 10px 0;
    border-color: #e9ecef;
}

/* User section alignment */
.user-section {
    gap: 15px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .user-section {
        flex-direction: column;
        align-items: flex-start !important;
        width: 100%;
        margin-top: 15px;
        gap: 10px;
    }
    
    .ticket-btn {
        width: 100%;
        text-align: center;
        margin-bottom: 10px;
    }
    
    .user-greeting {
        font-size: 1rem;
        margin-bottom: 10px;
    }
}

@media (max-width: 768px) {
    .auth-buttons {
        flex-direction: column;
        width: 100%;
        margin-top: 10px;
    }
    
    .auth-buttons .btn {
        width: 100%;
        margin-bottom: 5px;
    }
    
    .dropdown-menu {
        position: static !important;
        transform: none !important;
        width: 100%;
        margin-top: 10px;
        box-shadow: none;
        border: 1px solid #dee2e6;
    }
}

@media (max-width: 576px) {
    .user-greeting {
        font-size: 0.9rem;
    }
    
    .profile-img {
        width: 30px;
        height: 30px;
    }
    
    .ticket-btn {
        font-size: 0.9rem;
        padding: 6px 12px;
    }
}

/* Animation untuk smooth transitions */
.user-section {
    animation: fadeInRight 0.5s ease-in-out;
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Loading state */
.user-greeting:has-text("Loading") {
    background: #dee2e6;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Hover effects untuk better UX */
.navbar-nav .nav-item {
    position: relative;
}

.ticket-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #5d87ff 0%, #764ba2 100%);
    border-radius: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
}

.ticket-btn:hover::before {
    opacity: 1;
}
</style>