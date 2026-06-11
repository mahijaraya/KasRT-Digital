<?php
// ==============================================
// FILE: pages/pemasukan.php
// FUNGSI: Halaman untuk mengelola data pemasukan/jimpitan
// AUTHOR: Anggota 2 - Front-End Form dan Halaman Data
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
// AMBIL DATA PEMASUKAN DAN WARGA DARI DATABASE
// ==============================================
$data_pemasukan = [];
$data_warga = [];

if(isset($conn) && $conn) {
    // Ambil data pemasukan dengan join ke warga
    $query = "SELECT p.*, w.nama_warga, w.no_rumah 
              FROM pemasukan p 
              LEFT JOIN warga w ON p.id_warga = w.id_warga 
              ORDER BY p.tanggal DESC, p.id_pemasukan DESC";
    $result = mysqli_query($conn, $query);
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $data_pemasukan[] = $row;
        }
    }
    
    // Ambil data warga untuk dropdown
    $queryWarga = "SELECT id_warga, nama_warga, no_rumah FROM warga WHERE status = 'Aktif' ORDER BY nama_warga";
    $resultWarga = mysqli_query($conn, $queryWarga);
    if($resultWarga) {
        while($row = mysqli_fetch_assoc($resultWarga)) {
            $data_warga[] = $row;
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
    <title>Pemasukan - Kas RT Digital</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            <a href="dashboard.php" class="menu-item">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="warga.php" class="menu-item">
                <i class="bi bi-people-fill"></i>
                <span>Data Warga</span>
            </a>
            <a href="pemasukan.php" class="menu-item active">
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
                <h2 class="page-title">Pemasukan / Jimpitan</h2>
            </div>
            <div class="navbar-right">
                <div class="date-display">
                    <i class="bi bi-calendar3"></i>
                    <span id="currentDate"></span>
                </div>
                <button id="darkModeToggle" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="bi bi-moon-fill"></i>
                </button>
            </div>
        </nav>
        
        <!-- ============================================== -->
        <!-- KONTEN HALAMAN PEMASUKAN -->
        <!-- ============================================== -->
        <div class="content-wrapper">
            
            <!-- Tombol Tambah Data -->
            <div class="mb-4">
                <button type="button" class="btn btn-primary-gradient btn-modern" data-bs-toggle="modal" data-bs-target="#modalTambahPemasukan">
                    <i class="bi bi-cash-plus me-2"></i> Tambah Pemasukan
                </button>
            </div>
            
            <!-- Form Pencarian dan Filter -->
            <div class="row mb-4 g-3">
                <div class="col-md-5">
                    <div class="input-group rounded-pill shadow-sm bg-white border">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-0 bg-transparent rounded-pill" 
                               id="searchInput" placeholder="Cari nama warga atau jenis pemasukan...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select rounded-pill" id="filterJenis">
                        <option value="semua">📊 Semua Jenis</option>
                        <option value="Kas Bulanan">💰 Kas Bulanan</option>
                        <option value="Jimpitan">🏺 Jimpitan</option>
                        <option value="Iuran Kebersihan">🧹 Iuran Kebersihan</option>
                        <option value="Iuran Keamanan">🛡️ Iuran Keamanan</option>
                        <option value="Lainnya">📝 Lainnya</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="month" class="form-control rounded-pill" id="filterBulan" placeholder="Filter bulan">
                </div>
            </div>
            
            <!-- Ringkasan Pemasukan -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card-dashboard stat-card-primary">
                        <div class="stat-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Total Pemasukan</span>
                            <h4 class="stat-value text-success" id="totalPemasukan">Rp 0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card-dashboard stat-card-info">
                        <div class="stat-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Bulan Ini</span>
                            <h4 class="stat-value text-primary" id="totalBulanIni">Rp 0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card-dashboard stat-card-warning">
                        <div class="stat-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Jumlah Transaksi</span>
                            <h4 class="stat-value" id="jumlahTransaksi">0</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabel Data Pemasukan -->
            <div class="card-glass p-0 overflow-hidden">
                <div class="px-4 py-3 border-bottom" style="background: #f8fafc;">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-table me-2" style="color:#2563eb;"></i>
                        Daftar Pemasukan Kas
                    </h5>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-custom w-100" id="tabelPemasukan">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Tanggal</th>
                                <th>Nama Warga</th>
                                <th>Jenis Pemasukan</th>
                                <th class="text-end">Nominal</th>
                                <th>Keterangan</th>
                                <th width="130" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if(count($data_pemasukan) > 0): ?>
                                <?php $no = 1; foreach($data_pemasukan as $pemasukan): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pemasukan['tanggal'])); ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($pemasukan['nama_warga'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge-status">
                                            <?php
                                            $icon = match($pemasukan['jenis_pemasukan']) {
                                                'Kas Bulanan' => '💰',
                                                'Jimpitan' => '🏺',
                                                'Iuran Kebersihan' => '🧹',
                                                'Iuran Keamanan' => '🛡️',
                                                default => '📝'
                                            };
                                            echo $icon . ' ' . htmlspecialchars($pemasukan['jenis_pemasukan']);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        + <?php echo formatRupiah($pemasukan['nominal']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($pemasukan['keterangan'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill action-btn me-1" 
                                                onclick="editPemasukan(<?php echo $pemasukan['id_pemasukan']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill action-btn" 
                                                onclick="hapusPemasukan(<?php echo $pemasukan['id_pemasukan']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block opacity-50"></i>
                                        Belum ada data pemasukan. Silakan tambah pemasukan baru.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ============================================== -->
<!-- MODAL TAMBAH PEMASUKAN -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahPemasukan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header" style="background: #10b981; color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cash-plus me-2"></i>Tambah Pemasukan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../proses/proses_pemasukan.php" method="POST" id="formTambahPemasukan">
                <div class="modal-body">
                    <input type="hidden" name="action" value="tambah">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-person-fill me-1 text-success"></i> Nama Warga *
                        </label>
                        <select class="form-select rounded-pill" name="id_warga" required>
                            <option value="">Pilih Warga</option>
                            <?php foreach($data_warga as $warga): ?>
                            <option value="<?php echo $warga['id_warga']; ?>">
                                <?php echo htmlspecialchars($warga['nama_warga'] . ' (RT ' . $warga['no_rumah'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-tag-fill me-1 text-success"></i> Jenis Pemasukan *
                        </label>
                        <select class="form-select rounded-pill" name="jenis_pemasukan" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Kas Bulanan">💰 Kas Bulanan</option>
                            <option value="Jimpitan">🏺 Jimpitan</option>
                            <option value="Iuran Kebersihan">🧹 Iuran Kebersihan</option>
                            <option value="Iuran Keamanan">🛡️ Iuran Keamanan</option>
                            <option value="Lainnya">📝 Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-cash me-1 text-success"></i> Nominal *
                        </label>
                        <input type="number" class="form-control rounded-pill" name="nominal" required 
                               placeholder="0" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar3 me-1 text-success"></i> Tanggal *
                        </label>
                        <input type="date" class="form-control rounded-pill" name="tanggal" required 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-chat-text me-1 text-success"></i> Keterangan
                        </label>
                        <textarea class="form-control rounded-4" name="keterangan" rows="2" 
                                  placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-save-fill me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT PEMASUKAN -->
<!-- ============================================== -->
<div class="modal fade" id="modalEditPemasukan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header" style="background: #f59e0b; color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Edit Pemasukan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../proses/proses_pemasukan.php" method="POST" id="formEditPemasukan">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_pemasukan" id="edit_id_pemasukan">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Warga *</label>
                        <select class="form-select rounded-pill" name="id_warga" id="edit_id_warga" required>
                            <option value="">Pilih Warga</option>
                            <?php foreach($data_warga as $warga): ?>
                            <option value="<?php echo $warga['id_warga']; ?>">
                                <?php echo htmlspecialchars($warga['nama_warga'] . ' (RT ' . $warga['no_rumah'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis Pemasukan *</label>
                        <select class="form-select rounded-pill" name="jenis_pemasukan" id="edit_jenis_pemasukan" required>
                            <option value="Kas Bulanan">💰 Kas Bulanan</option>
                            <option value="Jimpitan">🏺 Jimpitan</option>
                            <option value="Iuran Kebersihan">🧹 Iuran Kebersihan</option>
                            <option value="Iuran Keamanan">🛡️ Iuran Keamanan</option>
                            <option value="Lainnya">📝 Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nominal *</label>
                        <input type="number" class="form-control rounded-pill" name="nominal" id="edit_nominal" required min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal *</label>
                        <input type="date" class="form-control rounded-pill" name="tanggal" id="edit_tanggal" required>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea class="form-control rounded-4" name="keterangan" id="edit_keterangan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 text-white">
                        <i class="bi bi-check-lg me-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>

<script>
// ==============================================
// JAVASCRIPT UNTUK HALAMAN PEMASUKAN
// Fungsi: Filter, search, edit, hapus, summary
// ==============================================

// Set current date
function updateDate() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateElement = document.getElementById('currentDate');
    if(dateElement) {
        dateElement.innerHTML = now.toLocaleDateString('id-ID', options);
    }
}
updateDate();

// ===== UPDATE RINGKASAN =====
function updateSummary() {
    const rows = document.querySelectorAll('#tableBody tr');
    let total = 0;
    let transaksiCount = 0;
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();
    let totalBulanIni = 0;
    
    rows.forEach(row => {
        if(row.querySelector('td[colspan]')) return;
        
        const nominalText = row.cells[4]?.innerText || '0';
        const nominal = parseInt(nominalText.replace(/[^0-9]/g, '')) || 0;
        total += nominal;
        transaksiCount++;
        
        // Cek apakah transaksi bulan ini
        const tanggalText = row.cells[1]?.innerText || '';
        const [day, month, year] = tanggalText.split('/');
        if(month && year) {
            const transMonth = parseInt(month) - 1;
            const transYear = parseInt(year);
            if(transMonth === currentMonth && transYear === currentYear) {
                totalBulanIni += nominal;
            }
        }
    });
    
    document.getElementById('totalPemasukan').innerText = formatRupiah(total);
    document.getElementById('totalBulanIni').innerText = formatRupiah(totalBulanIni);
    document.getElementById('jumlahTransaksi').innerText = transaksiCount;
}

function formatRupiah(angka) {
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// ===== FUNGSI SEARCH DAN FILTER =====
function filterTable() {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const filterJenis = document.getElementById('filterJenis')?.value || 'semua';
    const filterBulan = document.getElementById('filterBulan')?.value || '';
    const rows = document.querySelectorAll('#tableBody tr');
    
    rows.forEach(row => {
        if(row.querySelector('td[colspan]')) return;
        
        const namaWarga = row.cells[2]?.innerText.toLowerCase() || '';
        const jenisPemasukan = row.cells[3]?.innerText || '';
        const tanggalText = row.cells[1]?.innerText || '';
        
        // Parse tanggal (format: DD/MM/YYYY)
        const [day, month, year] = tanggalText.split('/');
        const rowDate = year && month ? `${year}-${month.padStart(2, '0')}` : '';
        
        const matchSearch = namaWarga.includes(searchValue) || jenisPemasukan.toLowerCase().includes(searchValue);
        const matchJenis = filterJenis === 'semua' || jenisPemasukan.includes(filterJenis);
        const matchBulan = !filterBulan || rowDate === filterBulan;
        
        row.style.display = matchSearch && matchJenis && matchBulan ? '' : 'none';
    });
    
    updateSummary();
}

// Event listeners
document.getElementById('searchInput')?.addEventListener('keyup', filterTable);
document.getElementById('filterJenis')?.addEventListener('change', filterTable);
document.getElementById('filterBulan')?.addEventListener('change', filterTable);

// ===== FUNGSI EDIT PEMASUKAN =====
function editPemasukan(id) {
    const row = event?.target?.closest('tr');
    if(row) {
        document.getElementById('edit_id_pemasukan').value = id;
        
        // Set nilai dari baris tabel
        const tanggal = row.cells[1]?.innerText || '';
        const [day, month, year] = tanggal.split('/');
        if(year && month && day) {
            document.getElementById('edit_tanggal').value = `${year}-${month}-${day}`;
        }
        
        // Untuk id_warga dan jenis, kita perlu data lebih detail
        // Sementara ini akan diisi oleh Anggota 3 dengan AJAX atau data dari database
        
        const nominalText = row.cells[4]?.innerText || '0';
        const nominal = parseInt(nominalText.replace(/[^0-9]/g, '')) || 0;
        document.getElementById('edit_nominal').value = nominal;
        
        const modal = new bootstrap.Modal(document.getElementById('modalEditPemasukan'));
        modal.show();
    }
}

// ===== FUNGSI HAPUS PEMASUKAN =====
function hapusPemasukan(id) {
    confirmDelete('Apakah Anda yakin ingin menghapus data pemasukan ini?', function() {
        window.location.href = `../proses/proses_pemasukan.php?action=hapus&id_pemasukan=${id}`;
    });
}

// ===== VALIDASI FORM TAMBAH =====
document.getElementById('formTambahPemasukan')?.addEventListener('submit', function(e) {
    const idWarga = this.querySelector('[name="id_warga"]').value;
    const jenis = this.querySelector('[name="jenis_pemasukan"]').value;
    const nominal = this.querySelector('[name="nominal"]').value;
    
    if(idWarga === '') {
        e.preventDefault();
        showToast('Pilih warga terlebih dahulu!', 'error');
        return false;
    }
    
    if(jenis === '') {
        e.preventDefault();
        showToast('Pilih jenis pemasukan!', 'error');
        return false;
    }
    
    if(nominal <= 0) {
        e.preventDefault();
        showToast('Nominal harus lebih dari 0!', 'error');
        return false;
    }
});

// ===== INITIAL SUMMARY =====
setTimeout(() => {
    updateSummary();
}, 100);

// ===== SIDEBAR TOGGLE =====
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

// Dark mode toggle
const darkModeToggle = document.getElementById('darkModeToggle');
if(darkModeToggle) {
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('kasRT_darkMode', isDark);
        this.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
    });
    
    if(localStorage.getItem('kasRT_darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
    }
}
</script>
</body>
</html>