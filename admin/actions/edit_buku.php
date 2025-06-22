<?php
include '../../shared/koneksi.php';

$id         = (int)$_POST['id'];
$judul      = trim($_POST['judul']);
$kategori   = (int)$_POST['kategori_id'];
$supplier   = (int)$_POST['supplier_id'];
$stok_awal  = (int)$_POST['stok_awal'];

$pdo->prepare("
  UPDATE data_buku 
    SET judul=?, kategori_id=?, supplier_id=?, stok_awal=?, created_at=NOW()
  WHERE id=?
")->execute([$judul,$kategori,$supplier,$stok_awal,$id]);

header("Location: ../data-buku.php");
exit;
