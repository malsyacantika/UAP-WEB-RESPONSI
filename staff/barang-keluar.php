<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'STF') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Ambil data buku untuk dropdown
$buku = $pdo->query("SELECT id, judul, stok_awal FROM data_buku WHERE stok_awal > 0 ORDER BY judul")->fetchAll(PDO::FETCH_ASSOC);

// Ambil data barang keluar
$barangKeluar = $pdo->query("
    SELECT bk.*, b.judul 
    FROM barang_keluar bk 
    JOIN data_buku b ON bk.buku_id = b.id 
    ORDER BY bk.tanggal DESC, bk.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Proses form jika ada submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buku_id = $_POST['buku_id'];
    $jumlah = $_POST['jumlah'];
    $tujuan = $_POST['tujuan'];
    $tanggal = $_POST['tanggal'];
    
    try {
        // Cek stok tersedia
        $stmt = $pdo->prepare("SELECT stok_awal FROM data_buku WHERE id = ?");
        $stmt->execute([$buku_id]);
        $stok = $stmt->fetchColumn();
        
        if ($stok < $jumlah) {
            throw new Exception("Stok tidak mencukupi. Stok tersedia: $stok");
        }
        
        $pdo->beginTransaction();
        
        // Insert barang keluar
        $stmt = $pdo->prepare("INSERT INTO barang_keluar (buku_id, jumlah, tujuan, tanggal) VALUES (?, ?, ?, ?)");
        $stmt->execute([$buku_id, $jumlah, $tujuan, $tanggal]);
        
        // Update stok buku
        $stmt = $pdo->prepare("UPDATE data_buku SET stok_awal = stok_awal - ? WHERE id = ?");
        $stmt->execute([$jumlah, $buku_id]);
        
        // Log aktivitas
        $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aktivitas) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], "Mengeluarkan barang: $jumlah unit"]);
        
        $pdo->commit();
        $success = "Data barang keluar berhasil ditambahkan!";
        
        // Refresh data
        $buku = $pdo->query("SELECT id, judul, stok_awal FROM data_buku WHERE stok_awal > 0 ORDER BY judul")->fetchAll(PDO::FETCH_ASSOC);
        $barangKeluar = $pdo->query("
            SELECT bk.*, b.judul 
            FROM barang_keluar bk 
            JOIN data_buku b ON bk.buku_id = b.id 
            ORDER BY bk.tanggal DESC, bk.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Gagal menambahkan data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B-LOG - Barang Keluar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include '../shared/components/navbar-staff.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-compact">
            <h1 class="page-title">Barang Keluar</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-compact">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form Input -->
        <div class="card-compact mb-4">
            <div class="p-3 border-bottom">
                <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Input Barang Keluar</h6>
            </div>
            <div class="p-3">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-compact">Buku</label>
                            <select name="buku_id" class="form-control-compact form-select" required id="bukuSelect">
                                <option value="">Pilih Buku</option>
                                <?php foreach ($buku as $b): ?>
                                    <option value="<?= $b['id'] ?>" data-stok="<?= $b['stok_awal'] ?>">
                                        <?= htmlspecialchars($b['judul']) ?> (Stok: <?= $b['stok_awal'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-compact">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control-compact" min="1" required id="jumlahInput">
                            <small class="text-muted" id="stokInfo"></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-compact">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control-compact" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-compact">Tujuan</label>
                            <input type="text" name="tujuan" class="form-control-compact" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary-compact">
                                <i class="bi bi-box-arrow-up me-1"></i>Keluarkan Barang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card-compact">
            <div class="p-3 border-bottom">
                <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Data Barang Keluar</h6>
            </div>
            <div class="p-3">
                <div class="table-responsive">
                    <table class="table table-compact">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Judul Buku</th>
                                <th style="width: 100px;">Jumlah</th>
                                <th style="width: 150px;">Tujuan</th>
                                <th style="width: 120px;">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barangKeluar as $i => $bk): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-medium"><?= htmlspecialchars($bk['judul']) ?></td>
                                    <td>
                                        <span class="badge bg-danger badge-compact">-<?= $bk['jumlah'] ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($bk['tujuan']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($bk['tanggal'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($barangKeluar)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data barang keluar</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 40px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update stok info saat buku dipilih
        document.getElementById('bukuSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const stok = selectedOption.getAttribute('data-stok');
            const stokInfo = document.getElementById('stokInfo');
            const jumlahInput = document.getElementById('jumlahInput');
            
            if (stok) {
                stokInfo.textContent = `Stok tersedia: ${stok}`;
                jumlahInput.max = stok;
            } else {
                stokInfo.textContent = '';
                jumlahInput.removeAttribute('max');
            }
        });
    </script>
</body>
</html>
