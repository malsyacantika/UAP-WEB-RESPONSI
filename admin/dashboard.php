<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'ADM') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Statistik dasar
$jumlahJudul = $pdo->query("SELECT COUNT(*) FROM data_buku")->fetchColumn();
$totalStok = $pdo->query("SELECT COALESCE(SUM(stok_awal),0) FROM data_buku")->fetchColumn();
$jumlahKategori = $pdo->query("SELECT COUNT(*) FROM kategori")->fetchColumn();
$jumlahSupplier = $pdo->query("SELECT COUNT(*) FROM supplier")->fetchColumn();
// --- DIUBAH SESUAI GAMBAR (1) ---
// Mengganti nama tabel dari 'pengguna' menjadi 'users'
$jumlahPengguna = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Statistik hari ini
$masukHariIni = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE DATE(tanggal) = CURDATE()")->fetchColumn();
$keluarHariIni = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE DATE(tanggal) = CURDATE()")->fetchColumn();

// Statistik minggu ini
$masukMingguIni = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE WEEK(tanggal) = WEEK(CURDATE())")->fetchColumn();
$keluarMingguIni = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE WEEK(tanggal) = WEEK(CURDATE())")->fetchColumn();

// Buku dengan stok rendah
$stokRendah = $pdo->query("SELECT judul, stok_awal FROM data_buku WHERE stok_awal < 10 ORDER BY stok_awal ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Aktivitas terbaru
// --- DIUBAH SESUAI GAMBAR (2) ---
// Mengganti nama tabel menjadi 'users' dan kolom 'nama' menjadi 'fullname'
$aktivitasTerbaru = $pdo->query("
    SELECT la.aktivitas, la.waktu, p.fullname 
    FROM log_aktivitas la 
    JOIN users p ON la.user_id = p.id 
    ORDER BY la.waktu DESC 
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
  <title>B-LOG - Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="../assets/css/admin-styles.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include '../shared/components/navbar-admin.php'; ?>

  <div class="page-header">
    <div class="container-compact">
      <h1 class="page-title">Dashboard Admin</h1>
    </div>
  </div>

  <div class="container-compact">
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
          <div class="stat-icon bg-primary bg-opacity-10 text-primary">
            <i class="bi bi-book"></i>
          </div>
          <div class="stat-number"><?= $jumlahJudul ?></div>
          <div class="stat-label">Total Buku</div>
        </div>
      </div>
      
      <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
          <div class="stat-icon bg-success bg-opacity-10 text-success">
            <i class="bi bi-stack"></i>
          </div>
          <div class="stat-number"><?= $totalStok ?></div>
          <div class="stat-label">Total Stok</div>
        </div>
      </div>
      
      <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
          <div class="stat-icon bg-info bg-opacity-10 text-info">
            <i class="bi bi-tags"></i>
          </div>
          <div class="stat-number"><?= $jumlahKategori ?></div>
          <div class="stat-label">Kategori</div>
        </div>
      </div>
      
      <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
          <div class="stat-icon bg-warning bg-opacity-10 text-warning">
            <i class="bi bi-truck"></i>
          </div>
          <div class="stat-number"><?= $jumlahSupplier ?></div>
          <div class="stat-label">Supplier</div>
        </div>
      </div>
      
      <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
          <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
            <i class="bi bi-people"></i>
          </div>
          <div class="stat-number"><?= $jumlahPengguna ?></div>
          <div class="stat-label">Pengguna</div>
        </div>
      </div>
      
      <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
          <div class="stat-icon bg-danger bg-opacity-10 text-danger">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <div class="stat-number"><?= count($stokRendah) ?></div>
          <div class="stat-label">Stok Rendah</div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-8">
        <div class="card-compact p-3">
          <h6 class="mb-3" style="font-size: 14px; font-weight: 500;">Quick Actions</h6>
          <div class="d-flex flex-wrap">
            <a href="actions/add_buku.php" class="quick-action">
              <i class="bi bi-plus-circle me-1"></i>Tambah Buku
            </a>
            <a href="data-buku.php" class="quick-action">
              <i class="bi bi-list me-1"></i>Kelola Buku
            </a>
            <a href="kategori.php" class="quick-action">
              <i class="bi bi-tags me-1"></i>Kelola Kategori
            </a>
            <a href="supplier.php" class="quick-action">
              <i class="bi bi-truck me-1"></i>Kelola Supplier
            </a>
            <a href="pengguna.php" class="quick-action">
              <i class="bi bi-people me-1"></i>Kelola Pengguna
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card-compact p-3">
          <h6 class="mb-3" style="font-size: 14px; font-weight: 500;">Hari Ini</h6>
          <div class="row text-center">
            <div class="col-6">
              <div class="text-success fw-medium" style="font-size: 18px;"><?= $masukHariIni ?></div>
              <div style="font-size: 12px; color: #5f6368;">Masuk</div>
            </div>
            <div class="col-6">
              <div class="text-danger fw-medium" style="font-size: 18px;"><?= $keluarHariIni ?></div>
              <div style="font-size: 12px; color: #5f6368;">Keluar</div>
            </div>
          </div>
        </div>
      </div>
    </div>

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
            <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Aktivitas Terbaru</h6>
          </div>
          <div class="p-3" style="max-height: 250px; overflow-y: auto;">
            <?php foreach($aktivitasTerbaru as $activity): ?>
            <div class="activity-item">
              <div style="font-size: 13px;"><?= htmlspecialchars($activity['aktivitas']) ?></div>
              <div style="font-size: 11px; color: #5f6368;">
                <i class="bi bi-person me-1"></i><?= htmlspecialchars($activity['fullname']) ?>
                <i class="bi bi-clock ms-2 me-1"></i><?= date('d/m H:i', strtotime($activity['waktu'])) ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-7">
        <div class="card-compact">
          <div class="p-3 border-bottom">
            <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Stok Rendah (< 10)</h6>
          </div>
          <div class="p-3">
            <?php if (!empty($stokRendah)): ?>
              <div class="table-responsive">
                <table class="table table-compact">
                  <thead>
                    <tr>
                      <th>Judul Buku</th>
                      <th style="width: 80px;">Stok</th>
                      <th style="width: 100px;">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stokRendah as $buku): 
                      $level = $buku['stok_awal'] < 5 ? 'danger' : 'warning';
                      $status = $buku['stok_awal'] < 5 ? 'Kritis' : 'Rendah';
                    ?>
                      <tr>
                        <td class="fw-medium"><?= htmlspecialchars($buku['judul']) ?></td>
                        <td>
                          <span class="badge bg-<?= $level ?> badge-compact"><?= $buku['stok_awal'] ?></span>
                        </td>
                        <td>
                          <span class="badge bg-<?= $level ?> bg-opacity-10 text-<?= $level ?> badge-compact">
                            <?= $status ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center" style="color: #5f6368; font-size: 13px; padding: 20px;">
                <i class="bi bi-check-circle text-success" style="font-size: 24px;"></i>
                <div class="mt-2">Semua stok dalam kondisi baik</div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div class="col-lg-5">
        <div class="card-compact">
          <div class="p-3 border-bottom">
            <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Statistik Minggu Ini</h6>
          </div>
          <div class="p-3">
            <div class="row text-center mb-3">
              <div class="col-6">
                <div class="text-success fw-medium" style="font-size: 24px;"><?= $masukMingguIni ?></div>
                <div style="font-size: 12px; color: #5f6368;">Total Masuk</div>
              </div>
              <div class="col-6">
                <div class="text-danger fw-medium" style="font-size: 24px;"><?= $keluarMingguIni ?></div>
                <div style="font-size: 12px; color: #5f6368;">Total Keluar</div>
              </div>
            </div>
            
            <div class="progress-compact mb-2">
              <?php 
              $totalTransaksi = $masukMingguIni + $keluarMingguIni;
              $persenMasuk = $totalTransaksi > 0 ? ($masukMingguIni / $totalTransaksi) * 100 : 0;
              ?>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-success" style="width: <?= $persenMasuk ?>%"></div>
                <div class="progress-bar bg-danger" style="width: <?= 100 - $persenMasuk ?>%"></div>
              </div>
            </div>
            
            <div class="d-flex justify-content-between" style="font-size: 11px; color: #5f6368;">
              <span><i class="bi bi-circle-fill text-success me-1"></i>Masuk <?= number_format($persenMasuk, 1) ?>%</span>
              <span><i class="bi bi-circle-fill text-danger me-1"></i>Keluar <?= number_format(100 - $persenMasuk, 1) ?>%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div style="height: 40px;"></div>

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
  </script>
</body>
</html>