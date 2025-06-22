<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'ADM') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buku_id = (int)$_POST['buku_id'];
    $jumlah = (int)$_POST['jumlah'];
    $keterangan = trim($_POST['keterangan']);
    
    // Insert barang masuk
    $stmt = $pdo->prepare("INSERT INTO barang_masuk (buku_id, jumlah, tanggal, keterangan) VALUES (?, ?, NOW(), ?)");
    $stmt->execute([$buku_id, $jumlah, $keterangan]);
    
    // Update stok
    $updateStok = $pdo->prepare("UPDATE data_buku SET stok_awal = stok_awal + ? WHERE id = ?");
    $updateStok->execute([$jumlah, $buku_id]);
    
    // Log aktivitas
    $logStmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aktivitas, waktu) VALUES (?, ?, NOW())");
    $logStmt->execute([$_SESSION['user_id'], "Barang masuk: $jumlah unit buku ID $buku_id"]);
    
    $success = "Barang masuk berhasil dicatat!";
}

// Ambil data buku untuk dropdown
$bukus = $pdo->query("SELECT id, judul, stok_awal FROM data_buku ORDER BY judul")->fetchAll(PDO::FETCH_ASSOC);

// Ambil riwayat barang masuk
$riwayat = $pdo->query("
    SELECT bm.*, db.judul 
    FROM barang_masuk bm 
    JOIN data_buku db ON bm.buku_id = db.id 
    ORDER BY bm.created_at DESC 
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>B-LOG - Barang Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include '../shared/components/navbar-admin.php'; ?>

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

        <div class="row g-4">
            <!-- Form Input -->
            <div class="col-lg-5">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-medium">Input Barang Masuk</h6>
                    </div>
                    <div class="p-3">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label form-label-compact">Pilih Buku</label>
                                <select name="buku_id" class="form-select form-control-compact" required>
                                    <option value="">-- Pilih Buku --</option>
                                    <?php foreach($bukus as $buku): ?>
                                        <option value="<?= $buku['id'] ?>">
                                            <?= htmlspecialchars($buku['judul']) ?> (Stok: <?= $buku['stok_awal'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Jumlah Masuk</label>
                                <input type="number" name="jumlah" class="form-control form-control-compact" min="1" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Keterangan</label>
                                <textarea name="keterangan" class="form-control form-control-compact" rows="3" placeholder="Keterangan tambahan..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary-compact w-100">
                                <i class="bi bi-download me-1"></i>Catat Barang Masuk
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Riwayat -->
            <div class="col-lg-7">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-medium">Riwayat Barang Masuk</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-compact align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Buku</th>
                                    <th style="width: 80px;">Jumlah</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($riwayat as $r): ?>
                                <tr>
                                    <td style="font-size: 12px;">
                                        <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                                    </td>
                                    <td class="fw-medium"><?= htmlspecialchars($r['judul']) ?></td>
                                    <td>
                                        <span class="badge bg-success badge-compact"><?= $r['jumlah'] ?></span>
                                    </td>
                                    <td style="font-size: 12px;">
                                        <?= htmlspecialchars($r['keterangan'] ?: '-') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 40px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
