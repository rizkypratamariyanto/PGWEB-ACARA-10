
<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "studi_kasus";

// Membuat koneksi ke database
$conn = new mysqli(
    $servername,
    $username,
    $password,
    $database
);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Mengatur karakter database
$conn->set_charset("utf8mb4");
?>
