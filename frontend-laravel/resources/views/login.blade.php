<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Modernize Free</title>
  <link rel="shortcut icon" type="image/png" href="{{asset('assets/images/logos/favicon.png')}}" />
  <link rel="stylesheet" href="{{asset('assets/css/styles.min.css')}}" />
  <style>
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
    }
    
    .card-login {
      border: none;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
    }
    
    .form-control {
      border-radius: 12px;
      border: 2px solid #e9ecef;
      padding: 12px 16px;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.9);
    }
    
    .form-control:focus {
      border-color: #5d87ff;
      box-shadow: 0 0 0 0.2rem rgba(93, 135, 255, 0.25);
      transform: translateY(-2px);
    }
    
    .btn-login {
      background: linear-gradient(135deg, #5d87ff 0%, #764ba2 100%);
      border: none;
      border-radius: 12px;
      padding: 14px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      text-transform: uppercase;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(93, 135, 255, 0.4);
    }
    
    .welcome-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 700;
      font-size: 1.8rem;
    }
    
    .subtitle {
      color: #6c757d;
      font-weight: 500;
    }
    
    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 8px;
    }
    
    .input-group {
      position: relative;
    }
    
    .input-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      z-index: 10;
      cursor: pointer;
    }
    
    .floating-shapes {
      position: absolute;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 1;
    }
    
    .shape {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      animation: float 6s ease-in-out infinite;
    }
    
    .shape:nth-child(1) {
      width: 80px;
      height: 80px;
      top: 10%;
      left: 10%;
      animation-delay: 0s;
    }
    
    .shape:nth-child(2) {
      width: 120px;
      height: 120px;
      top: 20%;
      right: 10%;
      animation-delay: 2s;
    }
    
    .shape:nth-child(3) {
      width: 60px;
      height: 60px;
      bottom: 20%;
      left: 20%;
      animation-delay: 4s;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    .alert {
      border-radius: 12px;
      border: none;
    }
    
    .text-link {
      color: #5d87ff;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .text-link:hover {
      color: #764ba2;
      text-decoration: underline;
    }
    
    .form-check-input:checked {
      background-color: #5d87ff;
      border-color: #5d87ff;
    }
    
    .form-check-input:focus {
      border-color: #5d87ff;
      box-shadow: 0 0 0 0.25rem rgba(93, 135, 255, 0.25);
    }
    
    .form-check-label {
      color: #495057;
      font-weight: 500;
    }
  </style>
</head>

<body>
  <div class="page-wrapper gradient-bg" id="main-wrapper">
    <div class="floating-shapes">
      <div class="shape"></div>
      <div class="shape"></div>
      <div class="shape"></div>
    </div>
    
    <div class="position-relative min-vh-100 d-flex align-items-center justify-content-center" style="z-index: 2;">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-4">
            <div class="card card-login mb-0">
              <div class="card-body p-5">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="text-center mb-4">
                  <a href="{{ route('home') }}" class="logo-img d-block mb-3">
                    <img src="{{asset('assets/images/logos/dark-logo.svg')}}" width="180" alt="Logo">
                  </a>
                  <h2 class="welcome-text mb-2">Welcome Back</h2>
                  <p class="subtitle">Sign in to your account to continue</p>
                </div>

                <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                  @csrf
                  
                  <div class="mb-3">
                    <label for="email" class="form-label">
                      <i class="ti ti-mail me-2"></i>Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Enter your email address" required autofocus 
                           value="{{ old('email') }}">
                    @error('email')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-4">
                    <label for="password" class="form-label">
                      <i class="ti ti-lock me-2"></i>Password
                    </label>
                    <div class="input-group">
                      <input type="password" class="form-control" id="password" name="password" 
                             placeholder="Enter your password" required>
                      <span class="input-icon" onclick="togglePassword('password')">
                        <i class="ti ti-eye" id="togglePassword"></i>
                      </span>
                    </div>
                    @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="flexCheckChecked" name="remember">
                      <label class="form-check-label" for="flexCheckChecked">
                        Remember this device
                      </label>
                    </div>
                    <a href="#" class="text-link">Forgot Password?</a>
                  </div>

                  <button type="submit" class="btn btn-primary btn-login w-100 py-3 fs-4 mb-4">
                    Sign In
                  </button>
                </form>

                <div class="text-center">
                  <p class="mb-0">New to Modernize? 
                    <a href="{{ route('register') }}" class="text-link">Create an account</a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{asset('assets/libs/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
  
  <script>
    // Toggle password visibility
    function togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const icon = document.getElementById('togglePassword');
      
      if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'ti ti-eye-off';
      } else {
        field.type = 'password';
        icon.className = 'ti ti-eye';
      }
    }

    // Add focus effects
    document.querySelectorAll('.form-control').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
      });
      
      input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
      });
    });

    // Form validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      
      if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all required fields!');
      }
    });
  </script>
</body>

</html>