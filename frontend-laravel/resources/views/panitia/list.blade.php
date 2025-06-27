@extends('layouts.index')

@section('content')
<div class="container-fluid">
  <!-- Header Section -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">Daftar Event Saya</h4>
      <p class="text-muted mb-0">Kelola event yang telah Anda buat</p>
    </div>
    <a href="{{ route('panitia.addEvent') }}" class="btn btn-primary">
      <i class="fas fa-plus me-2"></i>Tambah Event Baru
    </a>
  </div>

  {{-- Display success/error messages --}}
  @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
  @endif
  
  @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
  @endif

  <!-- Stats Cards -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="card-title mb-0">Total Event</h6>
              <h3 class="mb-0">{{ isset($events) && count($events) > 0 ? count($events) : 0 }}</h3>
            </div>
            <div class="align-self-center">
              <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-success text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="card-title mb-0">Total Sesi</h6>
              <h3 class="mb-0">
                {{ isset($events) ? array_sum(array_column($events, 'total_sesi')) : 0 }}
              </h3>
            </div>
            <div class="align-self-center">
              <i class="fas fa-calendar-check fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-info text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="card-title mb-0">Total Peserta</h6>
              <h3 class="mb-0">
                {{ isset($events) ? array_sum(array_column($events, 'total_peserta')) : 0 }}
              </h3>
            </div>
            <div class="align-self-center">
              <i class="fas fa-users fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Events List -->
  <div class="card">
    <div class="card-body">
      @if(isset($events) && count($events) > 0)
        <div class="row">
          @foreach($events as $event)
            <div class="col-lg-6 col-xl-4 mb-4">
              <div class="card h-100 shadow-sm border-0">
                <!-- Event Image/Poster -->
                <div class="position-relative">
                  @if(!empty($event['poster']))
                    <img src="http://localhost:3000/{{ str_replace('\\', '/', $event['poster']) }}" 
                         class="card-img-top" 
                         style="height: 200px; object-fit: cover;" 
                         alt="{{ $event['nama_event'] }}">
                  @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                      <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                  @endif
                  
                  <!-- Status Badge -->
                  <div class="position-absolute top-0 start-0 m-2">
                    <span class="badge bg-primary">Event</span>
                  </div>
                </div>

                <div class="card-body d-flex flex-column">
                  <!-- Event Title & Description -->
                  <h5 class="card-title mb-2">{{ $event['nama_event'] }}</h5>
                  <p class="card-text text-muted small mb-3">
                    {{ Str::limit($event['deskripsi'], 100) }}
                  </p>
                  
                  <!-- Event Details -->
                  <div class="mb-3">
                    <div class="d-flex align-items-center mb-1">
                      <i class="fas fa-info-circle text-primary me-2"></i>
                      <small>{{ Str::limit($event['deskripsi'], 50) }}</small>
                    </div>
                    @if(!empty($event['syarat_ketentuan']))
                      <div class="d-flex align-items-center mb-1">
                        <i class="fas fa-clipboard-list text-primary me-2"></i>
                        <small>Memiliki syarat & ketentuan</small>
                      </div>
                    @endif
                  </div>

                  <!-- Statistics -->
                  <div class="row text-center mb-3">
                    <div class="col-4">
                      <div class="border-end">
                        <div class="fw-bold text-primary">{{ $event['total_sesi'] ?? 0 }}</div>
                        <small class="text-muted">Total Sesi</small>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="border-end">
                        <div class="fw-bold text-success">{{ $event['total_peserta'] ?? 0 }}</div>
                        <small class="text-muted">Peserta</small>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="fw-bold text-warning">{{ $event['sisa_kapasitas'] ?? 0 }}</div>
                      <small class="text-muted">Sisa Slot</small>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="mt-auto">
                    <div class="d-flex gap-2">
                      <button type="button" 
                              class="btn btn-primary btn-sm flex-fill"
                              onclick="showEventDetail({{ $event['id_event'] }})">
                        <i class="fas fa-eye me-1"></i>Detail
                      </button>
                      <a href="{{ route('panitia.editEvent', $event['id_event']) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i>
                      </a>
                      <button type="button" 
                              class="btn btn-outline-danger btn-sm"
                              onclick="confirmDelete({{ $event['id_event'] }}, '{{ $event['nama_event'] }}')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <!-- Empty State -->
        <div class="text-center py-5">
          <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
          <h5 class="text-muted">Belum Ada Event</h5>
          <p class="text-muted mb-4">Anda belum membuat event apapun. Mulai buat event pertama Anda!</p>
          <a href="{{ route('panitia.addEvent') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Buat Event Pertama
          </a>
        </div>
      @endif
    </div>
  </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailModalLabel">Detail Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="eventDetailContent">
        <div class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat detail event...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus event <strong id="eventName"></strong>?</p>
        <p class="text-danger small">
          <i class="fas fa-exclamation-triangle me-1"></i>
          Tindakan ini tidak dapat dibatalkan.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <form id="deleteForm" method="POST" style="display: inline;">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash me-1"></i>Hapus Event
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('ExtraJS')
<script>
function showEventDetail(eventId) {
  var modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
  modal.show();
  
  document.getElementById('eventDetailContent').innerHTML = `
    <div class="text-center py-4">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Memuat detail event...</p>
    </div>
  `;
  
  fetch(`/api/panitia/events/${eventId}/detail`)
    .then(response => {
      console.log('Response status:', response.status);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        throw new Error('Response is not JSON');
      }
      
      return response.json();
    })
    .then(data => {
      console.log('Received data:', data);
      console.log('Data structure:', {
        total_peserta: data.total_peserta,
        sisa_kapasitas: data.sisa_kapasitas,
        sessions: data.sessions,
        available_keys: Object.keys(data)
      });
      
      if (data.error) {
        throw new Error(data.message || 'Terjadi kesalahan');
      }
      
      // Calculate statistics
      let totalPeserta = data.total_peserta || 0;
      let sisaKapasitas = data.sisa_kapasitas || 0;
      let totalKapasitas = data.total_kapasitas || 0;

      if ((!totalPeserta || !sisaKapasitas) && data.sessions && Array.isArray(data.sessions)) {
        let pesertaTerdaftar = 0;
        let kapasitasTotal = 0;
        
        data.sessions.forEach(session => {
          console.log('Session data:', session);
          pesertaTerdaftar += parseInt(session.peserta_terdaftar || session.total_peserta || 0);
          kapasitasTotal += parseInt(session.jumlah_peserta || session.kapasitas || 0);
        });
        
        totalPeserta = pesertaTerdaftar;
        totalKapasitas = kapasitasTotal;
        sisaKapasitas = totalKapasitas - totalPeserta;
      }

      console.log('Final calculated totals:', { totalPeserta, totalKapasitas, sisaKapasitas });
      
      let eventDetailHtml = `
        <!-- Event Poster -->
        <div class="row mb-4">
          <div class="col-md-4">
            ${data.poster ? 
              `<img src="http://localhost:3000/${data.poster.replace(/\\/g, '/')}" 
                   class="img-fluid rounded" 
                   alt="${data.nama_event}"
                   style="max-height: 250px; width: 100%; object-fit: cover;">` :
              `<div class="bg-light rounded d-flex align-items-center justify-content-center" 
                   style="height: 250px;">
                 <i class="fas fa-image fa-4x text-muted"></i>
               </div>`
            }
          </div>
          <div class="col-md-8">
            <h4 class="fw-bold mb-3">${data.nama_event}</h4>
            
            <!-- Event Info -->
            <div class="mb-3">
              <div class="d-flex align-items-center mb-2">
                <i class="fas fa-tag text-primary me-2"></i>
                <strong>Event ID:</strong>
                <span class="ms-2">${data.id_event}</span>
              </div>
            </div>
            
            <!-- Statistics -->
            <div class="row text-center">
              <div class="col-4">
                <div class="border rounded p-2">
                  <div class="fw-bold text-primary fs-4">${data.sessions ? data.sessions.length : 0}</div>
                  <small class="text-muted">Total Sesi</small>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2">
                  <div class="fw-bold text-success fs-4">${totalPeserta}</div>
                  <small class="text-muted">Peserta</small>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2">
                  <div class="fw-bold text-warning fs-4">${sisaKapasitas}</div>
                  <small class="text-muted">Sisa Slot</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Event Description -->
        <div class="mb-4">
          <h6 class="fw-bold mb-2">
            <i class="fas fa-info-circle text-primary me-2"></i>Deskripsi Event
          </h6>
          <div class="border rounded p-3 bg-light">
            <p class="mb-0">${data.deskripsi || 'Tidak ada deskripsi'}</p>
          </div>
        </div>
        
        <!-- Terms & Conditions -->
        ${data.syarat_ketentuan ? `
        <div class="mb-4">
          <h6 class="fw-bold mb-2">
            <i class="fas fa-clipboard-list text-primary me-2"></i>Syarat & Ketentuan
          </h6>
          <div class="border rounded p-3 bg-light">
            <p class="mb-0">${data.syarat_ketentuan}</p>
          </div>
        </div>
        ` : ''}
        
        <!-- Event Sessions -->
        <div class="mb-4">
          <h6 class="fw-bold mb-3">
            <i class="fas fa-calendar-check text-primary me-2"></i>Sesi Event
          </h6>
          ${data.sessions && data.sessions.length > 0 ? `
            <div class="row">
              ${data.sessions.map((session, index) => `
                <div class="col-md-6 mb-3">
                  <div class="card border-0 bg-light">
                    <div class="card-body">
                      <h6 class="card-title fw-bold">Sesi ${index + 1}: ${session.nama_sesi}</h6>
                      ${session.waktu_sesi ? `
                        <div class="mb-2">
                          <i class="fas fa-clock text-muted me-2"></i>
                          <small>${session.waktu_sesi}</small>
                        </div>
                      ` : ''}
                      ${session.narasumber_sesi ? `
                        <div class="mb-2">
                          <i class="fas fa-user text-muted me-2"></i>
                          <small><strong>Narasumber:</strong> ${session.narasumber_sesi}</small>
                        </div>
                      ` : ''}
                      ${session.lokasi_sesi ? `
                        <div class="mb-2">
                          <i class="fas fa-map-marker-alt text-muted me-2"></i>
                          <small><strong>Lokasi:</strong> ${session.lokasi_sesi}</small>
                        </div>
                      ` : ''}
                      ${session.tanggal_sesi ? `
                        <div class="mb-2">
                          <i class="fas fa-calendar text-muted me-2"></i>
                          <small><strong>Tanggal:</strong> ${new Date(session.tanggal_sesi).toLocaleDateString('id-ID')}</small>
                        </div>
                      ` : ''}
                      ${session.biaya_sesi !== undefined ? `
                        <div class="mb-2">
                          <i class="fas fa-money-bill-wave text-muted me-2"></i>
                          <small><strong>Biaya:</strong> ${session.biaya_sesi == 0 ? 'GRATIS' : 'Rp ' + parseInt(session.biaya_sesi).toLocaleString('id-ID')}</small>
                        </div>
                      ` : ''}
                      ${session.jumlah_peserta ? `
                        <div>
                          <i class="fas fa-users text-muted me-2"></i>
                          <small><strong>Maks Peserta:</strong> ${session.jumlah_peserta}</small>
                        </div>
                      ` : ''}
                    </div>
                  </div>
                </div>
              `).join('')}
            </div>
          ` : `
            <div class="text-center py-3 bg-light rounded">
              <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
              <p class="text-muted mb-0">Belum ada sesi yang ditambahkan</p>
            </div>
          `}
        </div>
        
        <!-- Organizer Info -->
        <div class="border-top pt-3">
          <h6 class="fw-bold mb-2">
            <i class="fas fa-user-tie text-primary me-2"></i>Penyelenggara
          </h6>
          <div class="d-flex align-items-center">
            <div>
              <div><strong>${data.nama_penyelenggara || 'Tidak diketahui'}</strong></div>
              <div class="text-muted small">${data.email_penyelenggara || ''}</div>
            </div>
          </div>
        </div>
      `;
      
      document.getElementById('eventDetailContent').innerHTML = eventDetailHtml;
    })
    .catch(error => {
      console.error('Error fetching event detail:', error);
      document.getElementById('eventDetailContent').innerHTML = `
        <div class="text-center py-4">
          <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
          <h6 class="text-danger">Gagal Memuat Detail Event</h6>
          <p class="text-muted">${error.message}</p>
          <button class="btn btn-primary btn-sm" onclick="showEventDetail(${eventId})">
            <i class="fas fa-redo me-1"></i>Coba Lagi
          </button>
        </div>
      `;
    });
}

function confirmDelete(eventId, eventName) {
  document.getElementById('eventName').textContent = eventName;
  document.getElementById('deleteForm').action = `/panitia/event/${eventId}/delete`;
  var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
  deleteModal.show();
}

function confirmDelete(eventId, eventName) {
  document.getElementById('eventName').textContent = eventName;
  document.getElementById('deleteForm').action = `/panitia/event/${eventId}/delete`;
  var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
  deleteModal.show();
}
</script>
@endsection