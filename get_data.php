<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "koneksi.php";

try {

    // Mengambil data dari tabel data_geoportal
    $sql = "SELECT
                ogc_fid,
                kemantren,
                kelurahan,
                alamat,
                latitude,
                longitude
            FROM data_geoportal
            WHERE latitude IS NOT NULL
            AND longitude IS NOT NULL";

    // Menjalankan query
    $result = $conn->query($sql);

    $data = [];

    // Membaca setiap baris data
    while ($row = $result->fetch_assoc()) {

        $longitude = (float) $row['longitude'];
        $latitude = (float) $row['latitude'];

        // Memeriksa validitas koordinat
        if (
            $longitude >= -180 &&
            $longitude <= 180 &&
            $latitude >= -90 &&
            $latitude <= 90
        ) {

            $data[] = [
                "ogc_fid" => $row['ogc_fid'],
                "kemantren" => $row['kemantren'],
                "kelurahan" => $row['kelurahan'],
                "alamat" => $row['alamat'],
                "latitude" => $latitude,
                "longitude" => $longitude
            ];

        }
    }

    // Mengirimkan data dalam format JSON
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
    );

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

} finally {

    // Menutup koneksi database
    if (isset($conn)) {
        $conn->close();
    }

}
?>
