<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - Modernize Free</title>
  <link rel="shortcut icon" type="image/png" href="{{asset('assets/images/logos/favicon.png')}}" />
  <link rel="stylesheet" href="{{asset('assets/css/styles.min.css')}}" />
  <style>
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
    }
    
    .card-register {
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
    
    .btn-register {
      background: linear-gradient(135deg, #5d87ff 0%, #764ba2 100%);
      border: none;
      border-radius: 12px;
      padding: 14px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      text-transform: uppercase;
    }
    
    .btn-register:hover {
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
    
    .password-strength {
      height: 4px;
      border-radius: 2px;
      margin-top: 5px;
      transition: all 0.3s ease;
    }
    
    .strength-weak { background: #dc3545; width: 25%; }
    .strength-fair { background: #ffc107; width: 50%; }
    .strength-good { background: #28a745; width: 75%; }
    .strength-strong { background: #28a745; width: 100%; }
    
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
      animation-delay: 0s;
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
            <div class="card card-register mb-0">
              <div class="card-body p-5">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="text-center mb-4">
                  <a href="{{ route('home')}}" class="logo-img d-block mb-3">
                    <img src="{{asset('assets/images/logos/dark-logo.svg')}}" width="180" alt="Logo">
                  </a>
                  <h2 class="welcome-text mb-2">Join Modernize</h2>
                  <p class="subtitle">Create your account and start your journey</p>
                </div>

                <form action="{{ route('admin.register.store') }}" method="POST" id="registerForm">
                  @csrf
                  
                  <div class="mb-3">
                    <label for="name" class="form-label">
                      <i class="ti ti-user me-2"></i>Full Name
                    </label>
                    <input type="text" class="form-control" id="name" name="name" 
                           placeholder="Enter your full name" required autofocus 
                           value="{{ old('name') }}">
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="email" class="form-label">
                      <i class="ti ti-mail me-2"></i>Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Enter your email address" required 
                           value="{{ old('email') }}">
                    @error('email')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="role_id_role" class="form-label">
                        <i class="ti ti-user-cog me-2"></i>Pilih Role
                    </label>
                    <select class="form-select" id="role_id_role" name="role_id_role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="2"  {{ old('role_id_role') == 2 ? 'selected' : '' }}>Panitia</option>
                        <option value="4" {{ old('role_id_role') == 4 ? 'selected' : '' }}>Keuangan</option>
                    </select>
                    @error('role_id_role')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    </div>


                  <div class="mb-3">
                    <label for="password" class="form-label">
                      <i class="ti ti-lock me-2"></i>Password
                    </label>
                    <div class="input-group">
                      <input type="password" class="form-control" id="password" name="password" 
                             placeholder="Create a strong password" required>
                      <span class="input-icon" onclick="togglePassword('password')">
                        <i class="ti ti-eye" id="togglePassword"></i>
                      </span>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                    <small class="text-muted">Use 8+ characters with letters, numbers & symbols</small>
                    @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-4">
                    <label for="password_confirmation" class="form-label">
                      <i class="ti ti-lock-check me-2"></i>Confirm Password
                    </label>
                    <div class="input-group">
                      <input type="password" class="form-control" id="password_confirmation" 
                             name="password_confirmation" placeholder="Confirm your password" required>
                      <span class="input-icon" onclick="togglePassword('password_confirmation')">
                        <i class="ti ti-eye" id="togglePasswordConfirm"></i>
                      </span>
                    </div>
                    <div id="passwordMatch" class="mt-1"></div>
                  </div>

                  <div class="mb-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                      <label class="form-check-label" for="agreeTerms">
                        I agree to the <a href="#" class="text-link">Terms of Service</a> and 
                        <a href="#" class="text-link">Privacy Policy</a>
                      </label>
                    </div>
                  </div>

                  <button type="submit" class="btn btn-primary btn-register w-100 py-3 fs-4 mb-4">
                    Create Account
                  </button>
                </form>

                <div class="text-center">
                  <p class="mb-0">Already have an account? 
                    <a href="{{ route('login.form') }}" class="text-link">Sign in here</a>
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
      const icon = fieldId === 'password' ? document.getElementById('togglePassword') : document.getElementById('togglePasswordConfirm');
      
      if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'ti ti-eye-off';
      } else {
        field.type = 'password';
        icon.className = 'ti ti-eye';
      }
    }

    // Password strength checker
    document.getElementById('password').addEventListener('input', function() {
      const password = this.value;
      const strengthBar = document.getElementById('passwordStrength');
      
      let strength = 0;
      if (password.length >= 8) strength++;
      if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
      if (password.match(/\d/)) strength++;
      if (password.match(/[^a-zA-Z\d]/)) strength++;
      
      strengthBar.className = 'password-strength ';
      if (strength === 1) strengthBar.className += 'strength-weak';
      else if (strength === 2) strengthBar.className += 'strength-fair';
      else if (strength === 3) strengthBar.className += 'strength-good';
      else if (strength === 4) strengthBar.className += 'strength-strong';
    });

    // Password confirmation checker
    document.getElementById('password_confirmation').addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const confirmPassword = this.value;
      const matchDiv = document.getElementById('passwordMatch');
      
      if (confirmPassword === '') {
        matchDiv.innerHTML = '';
      } else if (password === confirmPassword) {
        matchDiv.innerHTML = '<small class="text-success"><i class="ti ti-check me-1"></i>Passwords match</small>';
      } else {
        matchDiv.innerHTML = '<small class="text-danger"><i class="ti ti-x me-1"></i>Passwords do not match</small>';
      }
    });

    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('password_confirmation').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
      }
    });

    // Add focus effects
    document.querySelectorAll('.form-control').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
      });
      
      input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
      });
    });
  </script>
</body>

</html>