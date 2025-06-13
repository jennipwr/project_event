@extends('layouts.index')

@section('content')
<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold mb-4">Form Tambah Event</h5>
    
    {{-- Display success/error messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <form action="{{ route('panitia.submitEvent') }}" method="POST" enctype="multipart/form-data">
      @csrf
      
      {{-- Organizer Information --}}
      <div class="card mb-4 border-primary">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="card-title mb-0 text-primary">
            <i class="fas fa-user-tie me-2"></i>Informasi Penyelenggara
          </h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-8">
              <div class="mb-3">
                <label class="form-label fw-semibold">Diselenggarakan oleh:</label>
                <div class="form-control-plaintext bg-light rounded p-2">
                  <strong class="text-dark">{{ $user['name']}}</strong>
                  <br>
                  <small class="text-muted">{{ $user['email'] }}</small>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label fw-semibold">Status:</label>
                <div class="form-control-plaintext">
                  <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="fas fa-crown me-1"></i>Panitia
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {{-- Event Basic Information --}}
      <div class="card mb-4 border-success">
        <div class="card-header bg-success bg-opacity-10">
          <h6 class="card-title mb-0 text-success">
            <i class="fas fa-calendar-alt me-2"></i>Informasi Dasar Event
          </h6>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <label for="nama_event" class="form-label fw-bold">
              <i class="fas fa-star me-1 text-warning"></i>Nama Event
            </label>
            <input type="text" class="form-control form-control-lg border-2 @error('nama_event') is-invalid @enderror" 
                   id="nama_event" name="nama_event" value="{{ old('nama_event') }}" 
                   placeholder="Masukkan nama event yang menarik..." required>
            @error('nama_event')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="deskripsi" class="form-label fw-bold">
              <i class="fas fa-align-left me-1 text-info"></i>Deskripsi Event
            </label>
            <textarea class="form-control border-2 @error('deskripsi') is-invalid @enderror" 
                      id="deskripsi" name="deskripsi" rows="5" 
                      placeholder="Jelaskan event Anda secara detail..." required>{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="syarat_ketentuan" class="form-label fw-bold">
              <i class="fas fa-clipboard-list me-1 text-warning"></i>Syarat & Ketentuan
            </label>
            <textarea class="form-control border-2 @error('syarat_ketentuan') is-invalid @enderror" 
                      id="syarat_ketentuan" name="syarat_ketentuan" rows="4" 
                      placeholder="Tuliskan syarat dan ketentuan yang harus dipenuhi peserta..." required>{{ old('syarat_ketentuan') }}</textarea>
            @error('syarat_ketentuan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="poster" class="form-label fw-bold">
              <i class="fas fa-image me-1 text-primary"></i>Poster Kegiatan
            </label>
            <div class="border-2 border-dashed border-primary rounded p-4 text-center bg-light">
              <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
              <input type="file" class="form-control @error('poster') is-invalid @enderror" 
                     id="poster" name="poster" accept="image/*">
              <div class="form-text mt-2">
                <i class="fas fa-info-circle me-1"></i>
                Format yang diizinkan: JPG, JPEG, PNG, GIF. Maksimal 2MB.
              </div>
            </div>
            @error('poster')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      {{-- Sessions --}}
      <div class="card mb-4 border-info">
        <div class="card-header bg-info bg-opacity-10">
          <h6 class="card-title mb-0 text-info">
            <i class="fas fa-layer-group me-2"></i>Sesi Acara
          </h6>
        </div>
        <div class="card-body">
          <div class="alert alert-info border-0 bg-info bg-opacity-10" role="alert">
            <i class="fas fa-lightbulb me-2"></i>
            <strong>Tips:</strong> Setiap sesi dapat memiliki tanggal, biaya, dan kuota peserta yang berbeda untuk fleksibilitas maksimal!
          </div>
          
          <div id="sessions-list">
            @php
              $sessionCount = old('sessions') ? count(old('sessions')) : 1;
            @endphp
            
            @for($i = 0; $i < $sessionCount; $i++)
              <div class="session-item border-2 border-info rounded-3 p-4 mb-4 bg-light position-relative" data-session="{{ $i }}">
                <div class="position-absolute top-0 start-0 bg-info text-white px-3 py-1 rounded-bottom-3">
                  <i class="fas fa-calendar-day me-1"></i>
                  <strong>Sesi {{ $i + 1 }}</strong>
                </div>
                
                <div class="d-flex justify-content-end mb-3">
                  <button type="button" class="btn btn-sm btn-outline-danger remove-session rounded-pill" {{ $i === 0 ? 'style=display:none;' : '' }}>
                    <i class="fas fa-trash me-1"></i>Hapus Sesi
                  </button>
                </div>
                
                {{-- Session Basic Info --}}
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-bold">
                      <i class="fas fa-tag me-1 text-success"></i>Nama Sesi
                    </label>
                    <input type="text" class="form-control border-2 @error('sessions.'.$i.'.nama_sesi') is-invalid @enderror" 
                           name="sessions[{{ $i }}][nama_sesi]" value="{{ old('sessions.'.$i.'.nama_sesi') }}" 
                           placeholder="Contoh: Opening Ceremony, Workshop A, dll..." required>
                    @error('sessions.'.$i.'.nama_sesi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold">
                      <i class="fas fa-user-graduate me-1 text-primary"></i>Nama Pembicara
                    </label>
                    <input type="text" class="form-control border-2 @error('sessions.'.$i.'.narasumber_sesi') is-invalid @enderror" 
                           name="sessions[{{ $i }}][narasumber_sesi]" value="{{ old('sessions.'.$i.'.narasumber_sesi') }}" 
                           placeholder="Nama lengkap pembicara/narasumber..." required>
                    @error('sessions.'.$i.'.narasumber_sesi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                {{-- Session Details --}}
                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-bold">
                      <i class="fas fa-calendar me-1 text-danger"></i>Tanggal Pelaksanaan
                    </label>
                    <input type="date" class="form-control border-2 @error('sessions.'.$i.'.tanggal_sesi') is-invalid @enderror" 
                           name="sessions[{{ $i }}][tanggal_sesi]" value="{{ old('sessions.'.$i.'.tanggal_sesi') }}" required>
                    @error('sessions.'.$i.'.tanggal_sesi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">
                      <i class="fas fa-clock me-1 text-info"></i>Waktu Sesi
                    </label>
                    <input type="time" class="form-control border-2 @error('sessions.'.$i.'.waktu_sesi') is-invalid @enderror" 
                           name="sessions[{{ $i }}][waktu_sesi]" value="{{ old('sessions.'.$i.'.waktu_sesi') }}" required>
                    @error('sessions.'.$i.'.waktu_sesi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">
                      <i class="fas fa-users me-1 text-warning"></i>Maks. Peserta
                    </label>
                    <input type="number" class="form-control border-2 @error('sessions.'.$i.'.jumlah_peserta') is-invalid @enderror" 
                           name="sessions[{{ $i }}][jumlah_peserta]" min="1" value="{{ old('sessions.'.$i.'.jumlah_peserta') }}" 
                           placeholder="50" required>
                    @error('sessions.'.$i.'.jumlah_peserta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                {{-- Location --}}
                <div class="row mb-3">
                  <div class="col-md-12">
                    <label class="form-label fw-bold">
                      <i class="fas fa-map-marker-alt me-1 text-danger"></i>Lokasi Sesi
                    </label>
                    <input type="text" class="form-control border-2 @error('sessions.'.$i.'.lokasi_sesi') is-invalid @enderror" 
                          name="sessions[{{ $i }}][lokasi_sesi]" value="{{ old('sessions.'.$i.'.lokasi_sesi') }}" 
                          placeholder="Alamat lengkap tempat pelaksanaan sesi..." required>
                    @error('sessions.'.$i.'.lokasi_sesi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                {{-- Registration Fee --}}
                <div class="row">
                  <div class="col-md-12">
                    <label class="form-label fw-bold">
                      <i class="fas fa-money-bill-wave me-1 text-success"></i>Biaya Registrasi (Rp)
                    </label>
                    <div class="input-group">
                      <span class="input-group-text bg-success text-white border-2 border-success">
                        <i class="fas fa-rupiah-sign"></i>
                      </span>
                      <input type="number" class="form-control border-2 border-success @error('sessions.'.$i.'.biaya_sesi') is-invalid @enderror" 
                             name="sessions[{{ $i }}][biaya_sesi]" min="0" value="{{ old('sessions.'.$i.'.biaya_sesi') }}" 
                             placeholder="100000" required>
                    </div>
                    <div class="form-text">
                      <i class="fas fa-info-circle me-1"></i>
                      Masukkan 0 jika gratis
                    </div>
                    @error('sessions.'.$i.'.biaya_sesi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            @endfor
          </div>
          
          <div class="text-center">
            <button type="button" id="add-session" class="btn btn-outline-info btn-lg rounded-pill px-4">
              <i class="fas fa-plus-circle me-2"></i>Tambah Sesi Baru
            </button>
          </div>
        </div>
      </div>

      <div class="d-flex gap-3 justify-content-center">
        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5">
          <i class="fas fa-save me-2"></i>Simpan Event
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg rounded-pill px-5">
          <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@section('ExtraCss')
<style>
  .session-item {
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }
  
  .session-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  }
  
  .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  }
  
  .card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
  }
  
  .card:hover {
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
  }
</style>
@endsection

@section('ExtraJS')
<script>
  document.addEventListener('DOMContentLoaded', function() {
      const sessionsList = document.getElementById('sessions-list');
      const addSessionBtn = document.getElementById('add-session');
      let sessionIndex = {{ $sessionCount }};

      // Add new session
      addSessionBtn.addEventListener('click', function() {
          const sessionHtml = `
              <div class="session-item border-2 border-info rounded-3 p-4 mb-4 bg-light position-relative" data-session="${sessionIndex}">
                  <div class="position-absolute top-0 start-0 bg-info text-white px-3 py-1 rounded-bottom-3">
                      <i class="fas fa-calendar-day me-1"></i>
                      <strong>Sesi ${sessionIndex + 1}</strong>
                  </div>
                  
                  <div class="d-flex justify-content-end mb-3">
                      <button type="button" class="btn btn-sm btn-outline-danger remove-session rounded-pill">
                          <i class="fas fa-trash me-1"></i>Hapus Sesi
                      </button>
                  </div>
                  
                  <div class="row mb-3">
                      <div class="col-md-6">
                          <label class="form-label fw-bold">
                              <i class="fas fa-tag me-1 text-success"></i>Nama Sesi
                          </label>
                          <input type="text" class="form-control border-2" name="sessions[${sessionIndex}][nama_sesi]" 
                                 placeholder="Contoh: Opening Ceremony, Workshop A, dll..." required>
                      </div>
                      <div class="col-md-6">
                          <label class="form-label fw-bold">
                              <i class="fas fa-user-graduate me-1 text-primary"></i>Nama Pembicara
                          </label>
                          <input type="text" class="form-control border-2" name="sessions[${sessionIndex}][narasumber_sesi]" 
                                 placeholder="Nama lengkap pembicara/narasumber..." required>
                      </div>
                  </div>

                  <div class="row mb-3">
                      <div class="col-md-4">
                          <label class="form-label fw-bold">
                              <i class="fas fa-calendar me-1 text-danger"></i>Tanggal Pelaksanaan
                          </label>
                          <input type="date" class="form-control border-2" name="sessions[${sessionIndex}][tanggal_sesi]" required>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label fw-bold">
                              <i class="fas fa-clock me-1 text-info"></i>Waktu Sesi
                          </label>
                          <input type="time" class="form-control border-2" name="sessions[${sessionIndex}][waktu_sesi]" required>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label fw-bold">
                              <i class="fas fa-users me-1 text-warning"></i>Maks. Peserta
                          </label>
                          <input type="number" class="form-control border-2" name="sessions[${sessionIndex}][jumlah_peserta]" 
                                 min="1" placeholder="50" required>
                      </div>
                  </div>

                  <div class="row mb-3">
                      <div class="col-md-12">
                          <label class="form-label fw-bold">
                              <i class="fas fa-map-marker-alt me-1 text-danger"></i>Lokasi Sesi
                          </label>
                          <input type="text" class="form-control border-2" name="sessions[${sessionIndex}][lokasi_sesi]" 
                                 placeholder="Alamat lengkap tempat pelaksanaan sesi..." required>
                      </div>
                  </div>

                  <div class="row">
                      <div class="col-md-12">
                          <label class="form-label fw-bold">
                              <i class="fas fa-money-bill-wave me-1 text-success"></i>Biaya Registrasi (Rp)
                          </label>
                          <div class="input-group">
                              <span class="input-group-text bg-success text-white border-2 border-success">
                                  <i class="fas fa-rupiah-sign"></i>
                              </span>
                              <input type="number" class="form-control border-2 border-success" name="sessions[${sessionIndex}][biaya_sesi]" 
                                     min="0" placeholder="100000" required>
                          </div>
                          <div class="form-text">
                              <i class="fas fa-info-circle me-1"></i>
                              Masukkan 0 jika gratis
                          </div>
                      </div>
                  </div>
              </div>
          `;
          
          sessionsList.insertAdjacentHTML('beforeend', sessionHtml);
          sessionIndex++;
          updateSessionNumbers();
          
          // Add animation to new session
          const newSession = sessionsList.lastElementChild;
          newSession.style.opacity = '0';
          newSession.style.transform = 'translateY(20px)';
          
          setTimeout(() => {
              newSession.style.transition = 'all 0.5s ease';
              newSession.style.opacity = '1';
              newSession.style.transform = 'translateY(0)';
          }, 10);
      });

      // Remove session
      sessionsList.addEventListener('click', function(e) {
          if (e.target.classList.contains('remove-session') || e.target.closest('.remove-session')) {
              const sessionItem = e.target.closest('.session-item');
              
              // Add fade out animation
              sessionItem.style.transition = 'all 0.3s ease';
              sessionItem.style.opacity = '0';
              sessionItem.style.transform = 'translateY(-20px)';
              
              setTimeout(() => {
                  sessionItem.remove();
                  updateSessionNumbers();
              }, 300);
          }
      });

      // Update session numbers
      function updateSessionNumbers() {
          const sessions = sessionsList.querySelectorAll('.session-item');
          sessions.forEach((session, index) => {
              const title = session.querySelector('.position-absolute strong');
              title.innerHTML = `Sesi ${index + 1}`;
              
              // Show/hide remove button for first session
              const removeBtn = session.querySelector('.remove-session');
              if (index === 0 && sessions.length === 1) {
                  removeBtn.style.display = 'none';
              } else {
                  removeBtn.style.display = 'inline-block';
              }
          });
      }
  });
</script>
@endsection