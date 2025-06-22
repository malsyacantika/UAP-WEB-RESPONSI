<?php
include 'koneksi.php';

$query = $pdo->query("SELECT id, judul FROM data_buku ORDER BY judul");
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['judul']) . '</option>';
}
