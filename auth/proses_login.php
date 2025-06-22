<?php
// proses_login.php
session_start();
include '../shared/koneksi.php';

// Ambil input
$email = trim($_POST['email'] ?? '');
$pass  = trim($_POST['password'] ?? '');
$role  = $_POST['role'] === 'admin' ? 'ADM' : 'STF';  // Role code

if (!$email || !$pass) {
    header('Location: login.php?error=Data tidak lengkap');
    exit;
}

// Cari di database: join roles untuk mendapatkan code
$sql = "
 SELECT u.id, u.user_id, u.fullname, u.email, u.password, u.status, u.image,
       r.code AS role_code
FROM users u
  JOIN roles r ON u.role_id = r.id
  WHERE u.email = ?
";
$stm = $pdo->prepare($sql);
$stm->execute([$email]);
$user = $stm->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: login.php?error=Email tidak ditemukan');
    exit;
}

// Cocokkan password (plain)
if ($user['password'] !== $pass) {
    header('Location: login.php?error=Password salah');
    exit;
}

// Cocokkan role
if ($user['role_code'] !== $role) {
    header('Location: login.php?error=Role tidak sesuai');
    exit;
}

// Cek status aktif
if ($user['status'] !== 'Aktif') {
    header('Location: login.php?error=Akun non-aktif');
    exit;
}

// Login berhasil: simpan session
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_code'] = $user['user_id'];
$_SESSION['fullname']  = $user['fullname'];
$_SESSION['role_code'] = $user['role_code'];
$_SESSION['image']     = $user['image']; 

// Redirect sesuai role
if ($role === 'ADM') {
    header('Location: ../admin/dashboard.php');
} else {
    header('Location: ../staff/dashboard.php');
}
exit;
