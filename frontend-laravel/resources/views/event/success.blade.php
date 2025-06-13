@extends('layouts.guest')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div class="success-icon mx-auto">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <h1 class="display-6 fw-bold text-success mb-3">Registrasi Berhasil!</h1>
                    <p class="lead text-muted mb-4">
                        {{ session('success', 'Terima kasih telah mendaftar. Registrasi Anda telah berhasil diproses.') }}
                    </p>

                    <!-- Status Information -->
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div class="col text-start">
                                <h6 class="alert-heading mb-1">Informasi Penting</h6>
                                <p class="mb-0">
                                    Silakan cek email Anda untuk konfirmasi lebih lanjut. 
                                    Anda juga dapat melihat status registrasi di halaman riwayat tiket.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('event.history') }}" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-history me-2"></i>Lihat Riwayat Tiket
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="fas fa-home me-2"></i>Kembali ke Beranda
                        </a>
                    </div>

                    <!-- Additional Info -->
                    <div class="mt-5 pt-4 border-top">
                        <h6 class="fw-semibold text-dark mb-3">Langkah Selanjutnya:</h6>
                        <div class="row g-3 text-start">
                            <div class="col-md-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Cek Email</h6>
                                        <small class="text-muted">Konfirmasi akan dikirim via email</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Tunggu Konfirmasi</h6>
                                        <small class="text-muted">Proses verifikasi 1-24 jam</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-qrcode text-primary me-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Terima Tiket</h6>
                                        <small class="text-muted">QR Code akan diberikan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('ExtraCss')
<style>
    .success-icon {
        animation: bounceIn 1s ease-in-out;
    }
    
    @keyframes bounceIn {
        0%, 20%, 40%, 60%, 80% {
            animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
        }
        0% {
            opacity: 0;
            transform: scale3d(.3, .3, .3);
        }
        20% {
            transform: scale3d(1.1, 1.1, 1.1);
        }
        40% {
            transform: scale3d(.9, .9, .9);
        }
        60% {
            opacity: 1;
            transform: scale3d(1.03, 1.03, 1.03);
        }
        80% {
            transform: scale3d(.97, .97, .97);
        }
        100% {
            opacity: 1;
            transform: scale3d(1, 1, 1);
        }
    }
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .card {
        border-radius: 15px;
    }
    
    .alert {
        border-radius: 10px;
    }
</style>
@endsection