<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'ADM') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Proses Delete
if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM supplier WHERE id = ?")
        ->execute([(int)$_GET['delete_id']]);
    header("Location: supplier.php");
    exit;
}

// Proses Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);
    $email = trim($_POST['email']);
    
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $stmt = $pdo->prepare("UPDATE supplier SET nama = ?, alamat = ?, telepon = ?, email = ? WHERE id = ?");
        $stmt->execute([$nama, $alamat, $telepon, $email, (int)$_POST['id']]);
        $success = "Supplier berhasil diupdate!";
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO supplier (nama, alamat, telepon, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $alamat, $telepon, $email]);
        $success = "Supplier berhasil ditambahkan!";
    }
}

// Ambil data untuk Edit
$editSupplier = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM supplier WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editSupplier = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Load data supplier
$suppliers = $pdo->query("
    SELECT s.*, COUNT(db.id) as jumlah_buku 
    FROM supplier s 
    LEFT JOIN data_buku db ON s.id = db.supplier_id 
    GROUP BY s.id 
    ORDER BY s.nama
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>B-LOG - Supplier</title>
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
            <h1 class="page-title">Kelola Supplier</h1>
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
            <!-- Form -->
            <div class="col-lg-4">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-medium">
                            <?= $editSupplier ? 'Edit Supplier' : 'Tambah Supplier' ?>
                        </h6>
                    </div>
                    <div class="p-3">
                        <form method="POST">
                            <?php if ($editSupplier): ?>
                                <input type="hidden" name="id" value="<?= $editSupplier['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label form-label-compact">Nama Supplier</label>
                                <input type="text" name="nama" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($editSupplier['nama'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Alamat</label>
                                <textarea name="alamat" class="form-control form-control-compact" rows="2"
                                          placeholder="Alamat lengkap..."><?= htmlspecialchars($editSupplier['alamat'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Telepon</label>
                                <input type="text" name="telepon" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($editSupplier['telepon'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Email</label>
                                <input type="email" name="email" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($editSupplier['email'] ?? '') ?>" placeholder="email@domain.com">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-compact flex-fill">
                                    <i class="bi bi-<?= $editSupplier ? 'pencil' : 'plus' ?> me-1"></i>
                                    <?= $editSupplier ? 'Update' : 'Tambah' ?>
                                </button>
                                <?php if ($editSupplier): ?>
                                    <a href="supplier.php" class="btn btn-light btn-compact">
                                        <i class="bi bi-x"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel -->
            <div class="col-lg-8">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-medium">Daftar Supplier</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-compact align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Nama Supplier</th>
                                    <th>Kontak</th>
                                    <th style="width: 100px;">Jumlah Buku</th>
                                    <th style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($suppliers as $i => $s): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($s['nama']) ?></div>
                                        <div style="font-size: 11px; color: #5f6368;">
                                            <?= htmlspecialchars($s['alamat'] ?: '-') ?>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px;">
                                        <?php if ($s['telepon']): ?>
                                            <div><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($s['telepon']) ?></div>
                                        <?php endif; ?>
                                        <?php if ($s['email']): ?>
                                            <div><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($s['email']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!$s['telepon'] && !$s['email']): ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary badge-compact"><?= $s['jumlah_buku'] ?></span>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="?edit_id=<?= $s['id'] ?>" class="btn btn-warning btn-sm btn-compact" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($s['jumlah_buku'] == 0): ?>
                                                <a href="?delete_id=<?= $s['id'] ?>" class="btn btn-danger btn-sm btn-compact" 
                                                   onclick="return confirm('Hapus supplier ini?')" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm btn-compact" disabled title="Tidak dapat dihapus (ada buku)">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
