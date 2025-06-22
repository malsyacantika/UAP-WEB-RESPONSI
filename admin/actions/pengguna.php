<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'ADM') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Proses Delete
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    // Ambil user_id dari session untuk perbandingan
    $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUserId = $stmt->fetchColumn();

    if ($deleteId !== $currentUserId) { // Tidak bisa hapus diri sendiri
        $pdo->prepare("DELETE FROM users WHERE id = ?")
            ->execute([$deleteId]);
        $success = "Pengguna berhasil dihapus!";
    } else {
        $error = "Tidak dapat menghapus akun sendiri!";
    }
}

// Proses Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- DISESUAIKAN --- Menggunakan kolom dari database
    $fullname = trim($_POST['fullname']);
    $user_id_post = trim($_POST['user_id']);
    $email = trim($_POST['email']);
    $role_id_post = $_POST['role_id'];
    $password = $_POST['password']; // Mengambil password sebagai plain text
    
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $id_to_update = (int)$_POST['id'];
        if ($password) {
            // --- DIUBAH --- Password disimpan sebagai plain text
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, user_id = ?, email = ?, role_id = ?, password = ? WHERE id = ?");
            $stmt->execute([$fullname, $user_id_post, $email, $role_id_post, $password, $id_to_update]);
        } else {
            // --- DISESUAIKAN --- Query UPDATE tanpa mengubah password
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, user_id = ?, email = ?, role_id = ? WHERE id = ?");
            $stmt->execute([$fullname, $user_id_post, $email, $role_id_post, $id_to_update]);
        }
        $success = "Pengguna berhasil diupdate!";
    } else {
        // Insert
        // --- DIUBAH --- Password disimpan sebagai plain text, bukan hash
        $stmt = $pdo->prepare("INSERT INTO users (fullname, user_id, email, role_id, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $user_id_post, $email, $role_id_post, $password]);
        $success = "Pengguna berhasil ditambahkan!";
    }
}

// Ambil data untuk Edit
$editUser = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Load data users
$stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_db_id = $stmt->fetchColumn();

$users = $pdo->query("SELECT * FROM users ORDER BY fullname")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>B-LOG - Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <?php include '../shared/components/navbar-admin.php'; ?>

    <div class="page-header">
        <div class="container-compact">
            <h1 class="page-title">Kelola Pengguna</h1>
        </div>
    </div>

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

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-medium">
                            <?= $editUser ? 'Edit Pengguna' : 'Tambah Pengguna' ?>
                        </h6>
                    </div>
                    <div class="p-3">
                        <form method="POST">
                            <?php if ($editUser): ?>
                                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label form-label-compact">Nama Lengkap</label>
                                <input type="text" name="fullname" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($editUser['fullname'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">User ID (Username)</label>
                                <input type="text" name="user_id" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($editUser['user_id'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Email</label>
                                <input type="email" name="email" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Role</label>
                                <select name="role_id" class="form-select form-control-compact" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="1" <?= ($editUser['role_id'] ?? '') == 1 ? 'selected' : '' ?>>Admin</option>
                                    <option value="2" <?= ($editUser['role_id'] ?? '') == 2 ? 'selected' : '' ?>>Staff</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">
                                    Password <?= $editUser ? '(kosongkan jika tidak diubah)' : '' ?>
                                </label>
                                <input type="password" name="password" class="form-control form-control-compact" 
                                       <?= !$editUser ? 'required' : '' ?>>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-compact flex-fill">
                                    <i class="bi bi-<?= $editUser ? 'pencil' : 'plus' ?> me-1"></i>
                                    <?= $editUser ? 'Update' : 'Tambah' ?>
                                </button>
                                <?php if ($editUser): ?>
                                    <a href="pengguna.php" class="btn btn-light btn-compact">
                                        <i class="bi bi-x"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-medium">Daftar Pengguna</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-compact align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Nama Lengkap</th>
                                    <th>User ID</th>
                                    <th>Email</th>
                                    <th style="width: 100px;">Role</th>
                                    <th style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $i => $u): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td class="fw-medium">
                                        <?= htmlspecialchars($u['fullname']) ?>
                                        <?php if ($u['id'] == $current_db_id): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary badge-compact ms-1">Anda</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['user_id']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php if ($u['role_id'] == 1): ?>
                                            <span class="badge bg-danger badge-compact">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-info badge-compact">Staff</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="?edit_id=<?= $u['id'] ?>" class="btn btn-warning btn-sm btn-compact" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($u['id'] !== $current_db_id): ?>
                                                <a href="?delete_id=<?= $u['id'] ?>" class="btn btn-danger btn-sm btn-compact" 
                                                   onclick="return confirm('Hapus pengguna ini?')" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm btn-compact" disabled title="Tidak dapat menghapus diri sendiri">
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