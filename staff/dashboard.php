<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'STF') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Statistik dasar
$jumlahJudul = $pdo->query("SELECT COUNT(*) FROM data_buku")->fetchColumn();
$totalStok = $pdo->query("SELECT COALESCE(SUM(stok_awal),0) FROM data_buku")->fetchColumn();

// Statistik hari ini
$masukHariIni = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE DATE(tanggal) = CURDATE()");
$masukHariIni->execute();
$masukHariIni = $masukHariIni->fetchColumn();

$keluarHariIni = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE DATE(tanggal) = CURDATE()");
$keluarHariIni->execute();
$keluarHariIni = $keluarHariIni->fetchColumn();

// Statistik minggu ini
$masukMingguIni = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE WEEK(tanggal) = WEEK(CURDATE())")->fetchColumn();
$keluarMingguIni = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE WEEK(tanggal) = WEEK(CURDATE())")->fetchColumn();

// Daftar stok buku dengan kategori
$stokBuku = $pdo->query("
  SELECT b.judul, b.stok_awal, k.nama as kategori 
  FROM data_buku b 
  LEFT JOIN kategori k ON b.kategori_id = k.id 
  ORDER BY b.stok_awal ASC, b.judul
")->fetchAll(PDO::FETCH_ASSOC);

// Aktivitas saya hari ini
$aktivitasSaya = $pdo->prepare("
  SELECT aktivitas, waktu 
  FROM log_aktivitas 
  WHERE user_id = ? AND DATE(waktu) = CURDATE() 
  ORDER BY waktu DESC 
  LIMIT 10
");
$aktivitasSaya->execute([$_SESSION['user_id']]);
$aktivitasSaya = $aktivitasSaya->fetchAll(PDO::FETCH_ASSOC);

// Transaksi terbaru
$transaksiTerbaru = $pdo->query("
  (SELECT b.judul, bm.jumlah, bm.tanggal, 'Masuk' as tipe, bm.pemasok as keterangan
   FROM barang_masuk bm 
   JOIN data_buku b ON bm.buku_id = b.id 
   ORDER BY bm.tanggal DESC, bm.id DESC LIMIT 5)
  UNION ALL
  (SELECT b.judul, bk.jumlah, bk.tanggal, 'Keluar' as tipe, bk.tujuan as keterangan
   FROM barang_keluar bk 
   JOIN data_buku b ON bk.buku_id = b.id 
   ORDER BY bk.tanggal DESC, bk.id DESC LIMIT 5)
  ORDER BY tanggal DESC
  LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// Data untuk chart
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $masuk = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE DATE(tanggal) = ?");
    $masuk->execute([$date]);
    $keluar = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE DATE(tanggal) = ?");
    $keluar->execute([$date]);
    
    $chartData[] = [
        'date' => date('M j', strtotime($date)),
        'masuk' => $masuk->fetchColumn(),
        'keluar' => $keluar->fetchColumn()
    ];
}

$chartLabels = json_encode(array_column($chartData, 'date'));
$chartMasuk = json_encode(array_column($chartData, 'masuk'));
$chartKeluar = json_encode(array_column($chartData, 'keluar'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>B-LOG - Dashboard Staff</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="../assets/css/admin-styles.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- Navbar -->
  <?php include '../shared/components/navbar-staff.php'; ?>

  <!-- Page Header -->
  <div class="page-header">
    <div class="container-compact">
      <h1 class="page-title">Dashboard Staff</h1>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container-compact">
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon bg-primary bg-opacity-10 text-primary">
            <i class="bi bi-book"></i>
          </div>
          <div class="stat-number"><?= $jumlahJudul ?></div>
          <div class="stat-label">Total Buku</div>
        </div>
      </div>
      
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon bg-success bg-opacity-10 text-success">
            <i class="bi bi-stack"></i>
          </div>
          <div class="stat-number"><?= $totalStok ?></div>
          <div class="stat-label">Total Stok</div>
        </div>
      </div>
      
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon bg-success bg-opacity-10 text-success">
            <i class="bi bi-download"></i>
          </div>
          <div class="stat-number"><?= $masukHariIni ?></div>
          <div class="stat-label">Masuk Hari Ini</div>
        </div>
      </div>
      
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon bg-danger bg-opacity-10 text-danger">
            <i class="bi bi-upload"></i>
          </div>
          <div class="stat-number"><?= $keluarHariIni ?></div>
          <div class="stat-label">Keluar Hari Ini</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions & Weekly Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-8">
        <div class="card-compact p-3">
          <h6 class="mb-3" style="font-size: 14px; font-weight: 500;">Quick Actions</h6>
          <div class="d-flex flex-wrap">
            <a href="barang-masuk.php" class="quick-action">
              <i class="bi bi-download me-1"></i>Input Barang Masuk
            </a>
            <a href="barang-keluar.php" class="quick-action">
              <i class="bi bi-upload me-1"></i>Input Barang Keluar
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card-compact p-3">
          <h6 class="mb-3" style="font-size: 14px; font-weight: 500;">Minggu Ini</h6>
          <div class="row text-center">
            <div class="col-6">
              <div class="text-success fw-medium" style="font-size: 18px;"><?= $masukMingguIni ?></div>
              <div style="font-size: 12px; color: #5f6368;">Masuk</div>
            </div>
            <div class="col-6">
              <div class="text-danger fw-medium" style="font-size: 18px;"><?= $keluarMingguIni ?></div>
              <div style="font-size: 12px; color: #5f6368;">Keluar</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Chart & Activities -->
    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="chart-container">
          <h6 class="mb-3" style="font-size: 14px; font-weight: 500;">Tren Aktivitas (7 Hari)</h6>
          <div style="height: 250px;">
            <canvas id="trendChart"></canvas>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="card-compact">
          <div class="p-3 border-bottom">
            <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Aktivitas Saya Hari Ini</h6>
          </div>
          <div class="p-3" style="max-height: 250px; overflow-y: auto;">
            <?php foreach($aktivitasSaya as $activity): ?>
            <div class="activity-item">
              <div style="font-size: 13px;"><?= htmlspecialchars($activity['aktivitas']) ?></div>
              <div style="font-size: 11px; color: #5f6368;">
                <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($activity['waktu'])) ?>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($aktivitasSaya)): ?>
              <div class="text-center" style="color: #5f6368; font-size: 13px; padding: 20px;">
                Belum ada aktivitas hari ini
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Table & Recent Transactions -->
    <div class="row g-3">
      <div class="col-lg-7">
        <div class="card-compact">
          <div class="p-3 border-bottom">
            <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Daftar Stok Buku</h6>
          </div>
          <div class="p-3">
            <div class="search-box mb-3">
              <i class="bi bi-search"></i>
              <input type="text" id="searchInput" class="form-control" placeholder="Cari judul buku...">
            </div>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
              <table class="table table-compact">
                <thead class="sticky-top bg-white">
                  <tr>
                    <th style="width: 50px;">#</th>
                    <th>Judul Buku</th>
                    <th style="width: 120px;">Kategori</th>
                    <th style="width: 60px;">Stok</th>
                  </tr>
                </thead>
                <tbody id="bookStockTable">
                  <?php foreach ($stokBuku as $i => $b): 
                    $stokLevel = $b['stok_awal'] < 5 ? 'danger' : ($b['stok_awal'] < 10 ? 'warning' : 'success');
                  ?>
                    <tr>
                      <td><?= $i + 1 ?></td>
                      <td class="fw-medium"><?= htmlspecialchars($b['judul']) ?></td>
                      <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary badge-compact">
                          <?= htmlspecialchars($b['kategori'] ?? 'Tanpa Kategori') ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge bg-<?= $stokLevel ?> badge-compact">
                          <?= $b['stok_awal'] ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-5">
        <div class="card-compact">
          <div class="p-3 border-bottom">
            <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Transaksi Terbaru</h6>
          </div>
          <div class="p-3" style="max-height: 300px; overflow-y: auto;">
            <?php foreach($transaksiTerbaru as $transaksi): ?>
            <div class="transaction-item">
              <div class="d-flex justify-content-between align-items-start">
                <div style="min-width: 0;">
                  <div style="font-size: 13px; font-weight: 500;" class="text-truncate"><?= htmlspecialchars($transaksi['judul']) ?></div>
                  <div style="font-size: 11px; color: #5f6368;"><?= htmlspecialchars($transaksi['keterangan']) ?></div>
                </div>
                <div class="text-end ms-2">
                  <span class="badge bg-<?= $transaksi['tipe'] === 'Masuk' ? 'success' : 'danger' ?> badge-compact">
                    <?= $transaksi['tipe'] === 'Masuk' ? '+' : '-' ?><?= $transaksi['jumlah'] ?>
                  </span>
                  <div style="font-size: 10px; color: #5f6368;"><?= date('d/m', strtotime($transaksi['tanggal'])) ?></div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($transaksiTerbaru)): ?>
              <div class="text-center" style="color: #5f6368; font-size: 13px; padding: 20px;">
                Belum ada transaksi
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div style="height: 40px;"></div>

  <!-- Logout Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0">
        <div class="modal-body text-center p-4">
          <i class="bi bi-question-circle text-warning" style="font-size: 48px;"></i>
          <h6 class="mt-3 mb-2">Konfirmasi Logout</h6>
          <p class="text-muted" style="font-size: 13px;">Apakah Anda yakin ingin keluar?</p>
          <div class="d-flex gap-2 justify-content-center mt-3">
            <button type="button" class="btn btn-light" style="padding: 6px 12px; font-size: 12px; border-radius: 6px;" data-bs-dismiss="modal">Batal</button>
            <a href="../auth/logout.php" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px; border-radius: 6px;">Keluar</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Chart
    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?= $chartLabels ?>,
        datasets: [
          {
            label: 'Masuk',
            data: <?= $chartMasuk ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.3,
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: '#10b981'
          },
          {
            label: 'Keluar',
            data: <?= $chartKeluar ?>,
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            fill: true,
            tension: 0.3,
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: '#ef4444'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 15,
              font: {
                size: 11
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              }
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              color: '#f0f0f0'
            },
            ticks: {
              font: {
                size: 10
              }
            }
          }
        }
      }
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const tableRows = document.querySelectorAll('#bookStockTable tr');
      
      tableRows.forEach(row => {
        const bookTitle = row.cells[1]?.textContent.toLowerCase() || '';
        if (bookTitle.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  </script>
</body>
</html>
