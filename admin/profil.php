<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'ADM') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $image_link = trim($_POST['image']); // AMBIL LINK GAMBAR
    $password = $_POST['password'];
    
    // --- DIUBAH --- Query disesuaikan untuk menyertakan kolom 'image'
    if ($password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, username = ?, image = ?, password = ? WHERE id = ?");
        $stmt->execute([$fullname, $username, $image_link, $hashedPassword, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, username = ?, image = ? WHERE id = ?");
        $stmt->execute([$fullname, $username, $image_link, $_SESSION['user_id']]);
    }
    
    $_SESSION['fullname'] = $fullname;
    $_SESSION['username'] = $username;
    // Anda bisa juga menyimpan link gambar ke session jika diperlukan di bagian lain
    // $_SESSION['image'] = $image_link; 
    
    $success = "Profil berhasil diupdate!";
}

// Ambil data user terbaru untuk ditampilkan
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil aktivitas terbaru user
$aktivitas = $pdo->prepare("
    SELECT aktivitas, waktu 
    FROM log_aktivitas 
    WHERE user_id = ? 
    ORDER BY waktu DESC 
    LIMIT 10
");
$aktivitas->execute([$_SESSION['user_id']]);
$logAktivitas = $aktivitas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>B-LOG - Profil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <?php include '../shared/components/navbar-admin.php'; ?>

    <div class="page-header">
        <div class="container-compact">
            <h1 class="page-title">Profil Admin</h1>
        </div>
    </div>

    <div class="container-compact">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-medium">Edit Profil</h6>
                    </div>
                    <div class="p-3">
                        <div class="text-center mb-4">
                            <img src="<?= htmlspecialchars(!empty($user['image']) ? $user['image'] : '../assets/images/default-avatar.png') ?>" 
                                 alt="Foto Profil" 
                                 class="rounded-circle" 
                                 width="120" height="120" style="object-fit: cover;"
                                 onerror="this.onerror=null;this.src='../assets/images/default-avatar.png';">
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label form-label-compact">Nama Lengkap</label>
                                <input type="text" name="fullname" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($user['fullname']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label form-label-compact">Link Foto Profil</label>
                                <input type="text" name="image" class="form-control form-control-compact" 
                                       placeholder="https://example.com/gambar.jpg"
                                       value="<?= htmlspecialchars($user['image'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-compact">Username</label>
                                <input type="text" name="username" class="form-control form-control-compact" 
                                       value="<?= htmlspecialchars($user['user_id']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Role</label>
                                <input type="text" class="form-control form-control-compact" value="Administrator" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label form-label-compact">Password Baru (kosongkan jika tidak diubah)</label>
                                <input type="password" name="password" class="form-control form-control-compact">
                            </div>

                            <button type="submit" class="btn btn-primary-compact">
                                <i class="bi bi-check me-1"></i>Update Profil
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0 fw-medium">Aktivitas Terbaru</h6>
                    </div>
                    <div class="p-3" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach($logAktivitas as $log): ?>
                        <div class="activity-item">
                            <div style="font-size: 13px;"><?= htmlspecialchars($log['aktivitas']) ?></div>
                            <div style="font-size: 11px; color: #5f6368;">
                                <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($log['waktu'])) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($logAktivitas)): ?>
                            <div class="text-center" style="color: #5f6368; font-size: 13px; padding: 20px;">
                                Belum ada aktivitas
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 40px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>