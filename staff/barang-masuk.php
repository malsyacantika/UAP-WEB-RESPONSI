<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'STF') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Ambil data buku untuk dropdown
$buku = $pdo->query("SELECT id, judul FROM data_buku ORDER BY judul")->fetchAll(PDO::FETCH_ASSOC);

// Ambil data barang masuk
$barangMasuk = $pdo->query("
    SELECT bm.*, b.judul 
    FROM barang_masuk bm 
    JOIN data_buku b ON bm.buku_id = b.id 
    ORDER BY bm.tanggal DESC, bm.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Proses form jika ada submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buku_id = $_POST['buku_id'];
    $jumlah = $_POST['jumlah'];
    $pemasok = $_POST['pemasok'];
    $tanggal = $_POST['tanggal'];
    
    try {
        $pdo->beginTransaction();
        
        // Insert barang masuk
        $stmt = $pdo->prepare("INSERT INTO barang_masuk (buku_id, jumlah, pemasok, tanggal) VALUES (?, ?, ?, ?)");
        $stmt->execute([$buku_id, $jumlah, $pemasok, $tanggal]);
        
        // Update stok buku
        $stmt = $pdo->prepare("UPDATE data_buku SET stok_awal = stok_awal + ? WHERE id = ?");
        $stmt->execute([$jumlah, $buku_id]);
        
        // Log aktivitas
        $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aktivitas) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], "Menambah barang masuk: $jumlah unit"]);
        
        $pdo->commit();
        $success = "Data barang masuk berhasil ditambahkan!";
        
        // Refresh data
        $barangMasuk = $pdo->query("
            SELECT bm.*, b.judul 
            FROM barang_masuk bm 
            JOIN data_buku b ON bm.buku_id = b.id 
            ORDER BY bm.tanggal DESC, bm.id DESC
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
    <title>B-LOG - Barang Masuk</title>
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
            <h1 class="page-title">Barang Masuk</h1>
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
                <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Input Barang Masuk</h6>
            </div>
            <div class="p-3">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-compact">Buku</label>
                            <select name="buku_id" class="form-control-compact form-select" required>
                                <option value="">Pilih Buku</option>
                                <?php foreach ($buku as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['judul']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-compact">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control-compact" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-compact">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control-compact" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-compact">Pemasok</label>
                            <input type="text" name="pemasok" class="form-control-compact" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary-compact">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Barang Masuk
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card-compact">
            <div class="p-3 border-bottom">
                <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Data Barang Masuk</h6>
            </div>
            <div class="p-3">
                <div class="table-responsive">
                    <table class="table table-compact">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Judul Buku</th>
                                <th style="width: 100px;">Jumlah</th>
                                <th style="width: 150px;">Pemasok</th>
                                <th style="width: 120px;">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barangMasuk as $i => $bm): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-medium"><?= htmlspecialchars($bm['judul']) ?></td>
                                    <td>
                                        <span class="badge bg-success badge-compact">+<?= $bm['jumlah'] ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($bm['pemasok']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($bm['tanggal'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($barangMasuk)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data barang masuk</td>
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
</body>
</html>
