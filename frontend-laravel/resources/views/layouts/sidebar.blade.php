<!-- Sidebar Start -->
<aside class="left-sidebar">
  <!-- Sidebar scroll-->
  <div>
    <!-- Logo -->
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="{{ 
        session('user')['role_id_role'] == 3 ? route('admin.dashboard') : (
        session('user')['role_id_role'] == 2 ? route('panitia.dashboard') : (
        session('user')['role_id_role']== 4 ? route('keuangan.dashboard') : 
        route('member.dashboard')
        )) 
      }}" class="text-nowrap logo-img">
        <img src="{{ asset('assets/images/logos/dark-logo.svg') }}" width="180" alt="Logo" />
      </a>
      <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
        <i class="ti ti-x fs-8"></i>
      </div>
    </div>

    <!-- Sidebar navigation-->
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
      <ul id="sidebarnav">

        <!-- Home -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">Home</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ 
            session('user')['role_id_role'] == 3 ? route('admin.dashboard') : (
            session('user')['role_id_role'] == 2 ? route('panitia.dashboard') : (
            session('user')['role_id_role'] == 4 ? route('keuangan.dashboard') : 
            route('member.dashboard')
            )) 
          }}" aria-expanded="false">
            <span><i class="ti ti-layout-dashboard"></i></span>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <!-- PANITIA -->
        @if(session('user')['role_id_role'] == 2)
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">KELOLA EVENT</span>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('panitia.listEvents') }}" aria-expanded="false">
            <span><i class="ti ti-cards"></i></span>
            <span class="hide-menu">Lihat Event</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('panitia.addEvent') }}" aria-expanded="false">
            <span><i class="ti ti-file-description"></i></span>
            <span class="hide-menu">Tambah Event</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('panitia.scan') }}" aria-expanded="false">
            <span><i class="ti ti-qrcode"></i></span>
            <span class="hide-menu">Scan QR-Code</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('panitia.sertifikat') }}" aria-expanded="false">
            <span><i class="ti ti-certificate"></i></span>
            <span class="hide-menu">Upload Sertifikat</span>
          </a>
        </li>
        @endif

        <!-- KEUANGAN -->
        @if(session('user')['role_id_role'] == 4)
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">PEMBAYARAN</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('keuangan.show') }}" aria-expanded="false">
            <span><i class="ti ti-file-description"></i></span>
            <span class="hide-menu">Konfirmasi Pembayaran</span>
          </a>
        </li>
        @endif

        <!-- ADMIN -->
        @if(session('user')['role_id_role'] == 3)
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">MANAJEMEN AKUN</span>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('admin.register') }}" aria-expanded="false">
            <span><i class="ti ti-user-plus"></i></span>
            <span class="hide-menu">Register</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('admin.akuns') }}" aria-expanded="false">
            <span><i class="ti ti-users"></i></span>
            <span class="hide-menu">Daftar Akun</span>
          </a>
        </li>
        @endif

        <!-- AUTH -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">AUTH</span>
        </li>

        <li class="sidebar-item">
          
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
              @csrf
          </form>
          <a class="sidebar-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <span><i class="ti ti-logout"></i></span>
            <span class="hide-menu">Logout</span>
          </a>

        </li>

      </ul>
    </nav>
    <!-- End Sidebar navigation -->
  </div>
  <!-- End Sidebar scroll-->
</aside>
<!-- Sidebar End -->
