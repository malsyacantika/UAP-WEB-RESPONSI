<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'ADM') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Proses Delete
if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM kategori WHERE id = ?")
        ->execute([(int)$_GET['delete_id']]);
    header("Location: kategori.php");
    exit;
}

// Proses Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $stmt = $pdo->prepare("UPDATE kategori SET nama = ?, deskripsi = ? WHERE id = ?");
        $stmt->execute([$nama, $deskripsi, (int)$_POST['id']]);
        $success = "Kategori berhasil diupdate!";
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO kategori (nama, deskripsi) VALUES (?, ?)");
        $stmt->execute([$nama, $deskripsi]);
        $success = "Kategori berhasil ditambahkan!";
    }
}

// Ambil data untuk Edit
$editKategori = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editKategori = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Load data kategori
$kategoris = $pdo->query("
    SELECT k.*, COUNT(db.id) as jumlah_buku 
    FROM kategori k 
    LEFT JOIN data_buku db ON k.id = db.kategori_id 
    GROUP BY k.id 
    ORDER BY k.nama
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>B-LOG - Kategori</title>
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
            <h1 class="page-title">Kelola Kategori</h1>
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
                            <?= $editKategori ? 'Edit Kategori' : 'Tambah Kategori' ?>
                        </h6>
                    </div>
                    <div class="p-3">
                        <form method="POST">
                            <?php if ($editKategori): ?>
                                <input type="hidden" name="id" value="<?= $editKategori['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label form-label-compact">Nama Kategori</label>
                                <input type="text" name="nama" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($editKategori['nama'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control form-control-compact" rows="3"
                                          placeholder="Deskripsi kategori..."><?= htmlspecialchars($editKategori['deskripsi'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-compact flex-fill">
                                    <i class="bi bi-<?= $editKategori ? 'pencil' : 'plus' ?> me-1"></i>
                                    <?= $editKategori ? 'Update' : 'Tambah' ?>
                                </button>
                                <?php if ($editKategori): ?>
                                    <a href="kategori.php" class="btn btn-light btn-compact">
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
                        <h6 class="mb-0 fw-medium">Daftar Kategori</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-compact align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th style="width: 100px;">Jumlah Buku</th>
                                    <th style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($kategoris as $i => $k): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td class="fw-medium"><?= htmlspecialchars($k['nama']) ?></td>
                                    <td style="font-size: 12px;">
                                        <?= htmlspecialchars($k['deskripsi'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary badge-compact"><?= $k['jumlah_buku'] ?></span>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="?edit_id=<?= $k['id'] ?>" class="btn btn-warning btn-sm btn-compact" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($k['jumlah_buku'] == 0): ?>
                                                <a href="?delete_id=<?= $k['id'] ?>" class="btn btn-danger btn-sm btn-compact" 
                                                   onclick="return confirm('Hapus kategori ini?')" title="Hapus">
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
