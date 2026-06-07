<?php
// ==============================================
// FILE: pages/dashboard.php
// FUNGSI: Halaman dashboard utama bendahara
// AUTHOR: Anggota 1 - Front-End Login & Dashboard
// ==============================================

session_start();

// Cek apakah user sudah login
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Koneksi ke database (akan diisi Anggota 3)
require_once '../config/koneksi.php';

// ==============================================
// AMBIL DATA DARI DATABASE
// Query ini akan diintegrasikan oleh Anggota 3 & 4
// ==============================================

// Query total pemasukan (Anggota 3 akan membuat fungsi ini)
$total_pemasukan = 0;
$total_pengeluaran = 0;
$saldo_akhir = 0;
$jumlah_warga = 0;
$transaksi_terbaru = [];

// Jika koneksi database tersedia, ambil data real
if(isset($conn) && $conn) {
    // Total pemasukan
    $query_pemasukan = "SELECT SUM(nominal) as total FROM pemasukan";
    $result_pemasukan = mysqli_query($conn, $query_pemasukan);
    if($result_pemasukan && mysqli_num_rows($result_pemasukan) > 0) {
        $row = mysqli_fetch_assoc($result_pemasukan);
        $total_pemasukan = $row['total'] ?? 0;
    }
    
    // Total pengeluaran
    $query_pengeluaran = "SELECT SUM(nominal) as total FROM pengeluaran";
    $result_pengeluaran = mysqli_query($conn, $query_pengeluaran);
    if($result_pengeluaran && mysqli_num_rows($result_pengeluaran) > 0) {
        $row = mysqli_fetch_assoc($result_pengeluaran);
        $total_pengeluaran = $row['total'] ?? 0;
    }
    
    // Saldo akhir
    $saldo_akhir = $total_pemasukan - $total_pengeluaran;
    
    // Jumlah warga
    $query_warga = "SELECT COUNT(*) as total FROM warga";
    $result_warga = mysqli_query($conn, $query_warga);
    if($result_warga && mysqli_num_rows($result_warga) > 0) {
        $row = mysqli_fetch_assoc($result_warga);
        $jumlah_warga = $row['total'] ?? 0;
    }
    
    // 5 Transaksi pemasukan terbaru
    $query_transaksi = "SELECT p.*, w.nama_warga 
                        FROM pemasukan p 
                        LEFT JOIN warga w ON p.id_warga = w.id_warga 
                        ORDER BY p.tanggal DESC 
                        LIMIT 5";
    $result_transaksi = mysqli_query($conn, $query_transaksi);
    if($result_transaksi) {
        while($row = mysqli_fetch_assoc($result_transaksi)) {
            $transaksi_terbaru[] = $row;
        }
    }
}

// Format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kas RT Digital</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- ============================================== -->
<!-- SIDEBAR / NAVIGASI UTAMA -->
<!-- ============================================== -->
<div class="wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="bi bi-house-door-fill"></i>
                <span>KasRT Digital</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-arrow-left-short"></i>
            </button>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-title">MENU UTAMA</div>
            <a href="dashboard.php" class="menu-item active">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="warga.php" class="menu-item">
                <i class="bi bi-people-fill"></i>
                <span>Data Warga</span>
            </a>
            <a href="pemasukan.php" class="menu-item">
                <i class="bi bi-cash-stack"></i>
                <span>Pemasukan / Jimpitan</span>
            </a>
            <a href="pengeluaran.php" class="menu-item">
                <i class="bi bi-receipt"></i>
                <span>Pengeluaran</span>
            </a>
            <a href="laporan.php" class="menu-item">
                <i class="bi bi-file-text-fill"></i>
                <span>Laporan Kas</span>
            </a>
            
            <div class="menu-title mt-4">PENGATURAN</div>
            <a href="../logout.php" class="menu-item" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo $_SESSION['nama'] ?? 'Bendahara'; ?></div>
                    <div class="user-role">Bendahara RT</div>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="navbar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="page-title">Dashboard</h2>
            </div>
            <div class="navbar-right">
                <div class="date-display">
                    <i class="bi bi-calendar3"></i>
                    <span id="currentDate"></span>
                </div>
                <div class="notification-icon">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
            </div>
        </nav>
        
        <!-- ============================================== -->
        <!-- KONTEN DASHBOARD - CARD RINGKASAN -->
        <!-- ============================================== -->
        <div class="content-wrapper">
            <!-- Row Statistik Cards -->
            <div class="row g-4 mb-4">
                <!-- Card Total Pemasukan -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card-dashboard stat-card-primary">
                        <div class="stat-icon">
                            <i class="bi bi-arrow-down-circle"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Total Pemasukan</span>
                            <h3 class="stat-value text-success"><?php echo formatRupiah($total_pemasukan); ?></h3>
                            <span class="stat-trend positive">
                                <i class="bi bi-graph-up"></i> +12.5%
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Card Total Pengeluaran -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card-dashboard stat-card-danger">
                        <div class="stat-icon">
                            <i class="bi bi-arrow-up-circle"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Total Pengeluaran</span>
                            <h3 class="stat-value text-danger"><?php echo formatRupiah($total_pengeluaran); ?></h3>
                            <span class="stat-trend negative">
                                <i class="bi bi-graph-down"></i> -3.2%
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Card Saldo Akhir -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card-dashboard stat-card-info">
                        <div class="stat-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Saldo Akhir</span>
                            <h3 class="stat-value text-primary"><?php echo formatRupiah($saldo_akhir); ?></h3>
                            <span class="stat-trend neutral">
                                <i class="bi bi-dot"></i> Saat Ini
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Card Jumlah Warga -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card-dashboard stat-card-warning">
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Jumlah Warga</span>
                            <h3 class="stat-value"><?php echo $jumlah_warga; ?></h3>
                            <span class="stat-trend neutral">
                                <i class="bi bi-person-plus"></i> Keluarga
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Row Chart dan Transaksi Terbaru -->
            <div class="row g-4">
                <!-- Chart Statistik -->
                <div class="col-xl-6">
                    <div class="card-glass h-100">
                        <div class="card-header-custom">
                            <h5><i class="bi bi-pie-chart-fill me-2"></i> Statistik Kas</h5>
                            <select class="form-select-sm rounded-pill" id="chartType">
                                <option value="bar">Bar Chart</option>
                                <option value="doughnut">Doughnut</option>
                            </select>
                        </div>
                        <div class="card-body-custom">
                            <canvas id="kasChart" style="max-height: 300px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Transaksi Terbaru -->
                <div class="col-xl-6">
                    <div class="card-glass h-100">
                        <div class="card-header-custom">
                            <h5><i class="bi bi-clock-history me-2"></i> Transaksi Terbaru</h5>
                            <a href="pemasukan.php" class="btn btn-sm btn-link">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="card-body-custom p-0">
                            <div class="transaction-list">
                                <?php if(count($transaksi_terbaru) > 0): ?>
                                    <?php foreach($transaksi_terbaru as $tr): ?>
                                    <div class="transaction-item">
                                        <div class="transaction-icon bg-success-light">
                                            <i class="bi bi-cash-stack text-success"></i>
                                        </div>
                                        <div class="transaction-details">
                                            <div class="transaction-title"><?php echo htmlspecialchars($tr['nama_warga'] ?? 'Warga'); ?></div>
                                            <div class="transaction-subtitle"><?php echo htmlspecialchars($tr['jenis_pemasukan'] ?? 'Pemasukan'); ?></div>
                                        </div>
                                        <div class="transaction-amount text-success">
                                            + <?php echo formatRupiah($tr['nominal'] ?? 0); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p>Belum ada transaksi</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistik Bulanan -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card-glass">
                        <div class="card-header-custom">
                            <h5><i class="bi bi-calendar-week me-2"></i> Statistik Pemasukan per Bulan</h5>
                            <select class="form-select-sm rounded-pill" id="yearSelect">
                                <option value="2024">2024</option>
                                <option value="2025" selected>2025</option>
                            </select>
                        </div>
                        <div class="card-body-custom">
                            <canvas id="monthlyChart" style="max-height: 300px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>

<script>
// ==============================================
// JAVASCRIPT UNTUK DASHBOARD
// Fungsi: Chart, date display, sidebar toggle
// ==============================================

// Set current date
function updateDate() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').innerHTML = now.toLocaleDateString('id-ID', options);
}
updateDate();

// Dashboard Chart
let kasChart = null;

function initChart() {
    const ctx = document.getElementById('kasChart').getContext('2d');
    const chartType = document.getElementById('chartType').value;
    
    if(kasChart) kasChart.destroy();
    
    kasChart = new Chart(ctx, {
        type: chartType === 'bar' ? 'bar' : 'doughnut',
        data: {
            labels: ['Pemasukan', 'Pengeluaran', 'Saldo'],
            datasets: [{
                label: 'Nominal (Rp)',
                data: [
                    <?php echo $total_pemasukan; ?>,
                    <?php echo $total_pengeluaran; ?>,
                    <?php echo $saldo_akhir; ?>
                ],
                backgroundColor: chartType === 'bar' 
                    ? ['#10b981', '#ef4444', '#3b82f6']
                    : ['#10b981', '#ef4444', '#3b82f6'],
                borderColor: '#fff',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return label + ': Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
}

// Change chart type
document.getElementById('chartType').addEventListener('change', function() {
    initChart();
});

// Monthly chart (data akan diisi oleh Anggota 4)
let monthlyChart = null;

function initMonthlyChart() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    if(monthlyChart) monthlyChart.destroy();
    
    // Data sementara (akan diganti dengan data real dari database)
    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Pemasukan',
                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Pengeluaran',
                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initChart();
    initMonthlyChart();
});

// Sidebar toggle for mobile
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');

if(sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });
}

if(mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    if(window.innerWidth <= 768) {
        if(!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>
</body>
</html>