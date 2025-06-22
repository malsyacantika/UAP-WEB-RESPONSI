<?php 
// koneksi.php
date_default_timezone_set('Asia/Jakarta');
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'b_log';

try {
    // Buat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Tampilkan exception jika ada kesalahan
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Kalau gagal koneksi, hentikan aplikasi dan tampilkan pesan
    die("Koneksi gagal: " . $e->getMessage());
}
