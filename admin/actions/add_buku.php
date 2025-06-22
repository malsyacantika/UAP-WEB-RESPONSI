<?php
include '../../shared/koneksi.php';

$judul      = trim($_POST['judul']);
$kategori   = (int)$_POST['kategori_id'];
$supplier   = (int)$_POST['supplier_id'];
$stok_awal  = (int)$_POST['stok_awal'];

$pdo->prepare("
  INSERT INTO data_buku (judul,kategori_id,supplier_id,stok_awal,created_at)
  VALUES (?,?,?,?,NOW())
")->execute([$judul,$kategori,$supplier,$stok_awal]);

header("Location: ../data-buku.php");
exit;
