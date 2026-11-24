<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode     = trim($_POST['kode_buku']);
    $judul    = trim($_POST['judul_buku']);
    $peminjam = trim($_POST['nama_peminjam']);
    $pinjam   = $_POST['tgl_pinjam'];
    $kembali  = $_POST['tgl_kembali'];

    $stmt = mysqli_prepare($conn, "INSERT INTO peminjaman (kode_buku, judul_buku, nama_peminjam, tgl_pinjam, tgl_kembali, status) VALUES (?, ?, ?, ?, ?, 'Dipinjam')");
    mysqli_stmt_bind_param($stmt, "sssss", $kode, $judul, $peminjam, $pinjam, $kembali);
    mysqli_stmt_execute($stmt);

    $_SESSION['notif'] = "Data peminjaman baru berhasil disimpan!";
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Peminjaman</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card form-card">
            <h2>Tambah Peminjaman Buku</h2>
            <div id="domAlert"></div>
            <form method="POST" id="formPinjam">
                <div class="form-group">
                    <label>Kode Buku</label>
                    <input type="text" name="kode_buku" placeholder="Contoh: BK-004" required>
                </div>
                <div class="form-group">
                    <label>Judul Buku</label>
                    <input type="text" name="judul_buku" required>
                </div>
                <div class="form-group">
                    <label>Nama Peminjam</label>
                    <input type="text" name="nama_peminjam" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" id="tgl_pinjam" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Jatuh Tempo Kembali</label>
                    <input type="date" name="tgl_kembali" id="tgl_kembali" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>