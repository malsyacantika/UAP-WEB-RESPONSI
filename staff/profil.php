<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'STF') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $image_link = trim($_POST['image']); // AMBIL LINK GAMBAR
    $password_baru = $_POST['password_baru'];
    
    try {
        // --- DIUBAH --- Query disesuaikan untuk menyertakan 'image' dan kolom 'fullname' yang benar
        if (!empty($password_baru)) {
            // Update dengan password baru
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, image = ?, password = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $image_link, $password_hash, $_SESSION['user_id']]);
        } else {
            // Update tanpa password
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, image = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $image_link, $_SESSION['user_id']]);
        }
        
        // Log aktivitas
        $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aktivitas) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], "Memperbarui profil"]);
        
        $success = "Profil berhasil diperbarui!";
        
    } catch (Exception $e) {
        $error = "Gagal memperbarui profil: " . $e->getMessage();
    }
}

// Ambil data user terbaru untuk ditampilkan
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil statistik aktivitas user
$aktivitasHariIni = $pdo->prepare("SELECT COUNT(*) FROM log_aktivitas WHERE user_id = ? AND DATE(waktu) = CURDATE()");
$aktivitasHariIni->execute([$_SESSION['user_id']]);
$aktivitasHariIni = $aktivitasHariIni->fetchColumn();

$aktivitasMingguIni = $pdo->prepare("SELECT COUNT(*) FROM log_aktivitas WHERE user_id = ? AND WEEK(waktu) = WEEK(CURDATE())");
$aktivitasMingguIni->execute([$_SESSION['user_id']]);
$aktivitasMingguIni = $aktivitasMingguIni->fetchColumn();

// Ambil log aktivitas terbaru
$logAktivitas = $pdo->prepare("SELECT aktivitas, waktu FROM log_aktivitas WHERE user_id = ? ORDER BY waktu DESC LIMIT 10");
$logAktivitas->execute([$_SESSION['user_id']]);
$logAktivitas = $logAktivitas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B-LOG - Profil Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <?php include '../shared/components/navbar-staff.php'; ?>

    <div class="page-header">
        <div class="container-compact">
            <h1 class="page-title">Profil Saya</h1>
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

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Informasi Profil</h6>
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
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-compact">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control-compact" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-compact">Email</label>
                                    <input type="email" name="email" class="form-control-compact" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label-compact">Link Foto Profil</label>
                                    <input type="text" name="image" class="form-control-compact" placeholder="https://example.com/gambar.jpg" value="<?= htmlspecialchars($user['image'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-compact">Username</label>
                                    <input type="text" class="form-control-compact" value="<?= htmlspecialchars($user['user_id']) ?>" disabled>
                                    <small class="text-muted">Username tidak dapat diubah</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-compact">Role</label>
                                    <input type="text" class="form-control-compact" value="Staff" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-compact">Password Baru</label>
                                    <input type="password" name="password_baru" class="form-control-compact" placeholder="Kosongkan jika tidak ingin mengubah">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-compact">Tanggal Bergabung</label>
                                    <input type="text" class="form-control-compact" value="<?= date('d/m/Y', strtotime($user['created_at'])) ?>" disabled>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary-compact">
                                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-compact mb-3">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Statistik Aktivitas</h6>
                    </div>
                    <div class="p-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="text-primary fw-medium" style="font-size: 24px;"><?= $aktivitasHariIni ?></div>
                                <div style="font-size: 12px; color: #5f6368;">Hari Ini</div>
                            </div>
                            <div class="col-6">
                                <div class="text-success fw-medium" style="font-size: 24px;"><?= $aktivitasMingguIni ?></div>
                                <div style="font-size: 12px; color: #5f6368;">Minggu Ini</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-compact">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0" style="font-size: 14px; font-weight: 500;">Log Aktivitas Terbaru</h6>
                    </div>
                    <div class="p-3" style="max-height: 255px; overflow-y: auto;">
                        <?php foreach($logAktivitas as $log): ?>
                            <div class="activity-item">
                                <div style="font-size: 13px;"><?= htmlspecialchars($log['aktivitas']) ?></div>
                                <div style="font-size: 11px; color: #5f6368;">
                                    <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($log['waktu'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($logAktivitas)): ?>
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