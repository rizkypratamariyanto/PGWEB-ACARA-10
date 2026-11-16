<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Web GIS Database</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet"
        href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css">

    <style>
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        /* Judul Peta */
        .judul {
            position: absolute;
            top: 10px;
            left: 60px;
            z-index: 1000;
            background: white;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px #888;
        }

        /* Tabel Data */
        #tabelData {
            position: absolute;
            top: 120px;
            right: 10px;
            z-index: 1000;
            width: 460px;
            max-width: calc(100% - 30px);
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        #tabelData h3 {
            margin: 0 0 10px;
            font-size: 16px;
        }

        #jumlahData {
            font-size: 12px;
            margin-bottom: 10px;
            color: #555;
        }

        #cariData {
            width: 100%;
            box-sizing: border-box;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .table-container {
            max-height: 300px;
            overflow: auto;
        }

        #tabelData table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        #tabelData th,
        #tabelData td {
            border: 1px solid #ddd;
            padding: 7px;
            text-align: left;
        }

        #tabelData th {
            background: #2c7fb8;
            color: white;
            position: sticky;
            top: 0;
        }

        #tableBody tr {
            cursor: pointer;
        }

        #tableBody tr:hover {
            background: #e6f2ff;
        }

        /* Tombol Lihat Lokasi */
        .tombolLokasi {
            background: #2c7fb8;
            color: white;
            border: none;
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
        }

        .tombolLokasi:hover {
            background: #1d5f8a;
        }

        /* Tombol Sembunyikan Tabel */
        #tombolTabel {
            position: absolute;
            top: 120px;
            right: 10px;
            z-index: 1001;
            background: #2c7fb8;
            color: white;
            border: none;
            padding: 9px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        #tombolTabel:hover {
            background: #1d5f8a;
        }

        /* Menyembunyikan seluruh tabel */
        #tabelData.tersembunyi {
            display: none;
        }

        @media (max-width: 600px) {
            #tabelData {
                width: 85%;
                right: 5px;
                top: 120px;
            }

            .table-container {
                max-height: 200px;
            }
        }
    </style>
</head>

<body>

    <!-- Judul -->
    <div class="judul">
        <b>Web GIS Data Geoportal</b>
    </div>

    <!-- Peta -->
    <div id="map"></div>

    <!-- Tombol Sembunyikan Tabel -->
    <button id="tombolTabel" onclick="toggleTabel()">
        Sembunyikan Tabel
    </button>

    <!-- Tabel Database -->
    <div id="tabelData">

        <h3>Data Lokasi Bank Sampah</h3>

        <div id="jumlahData">
            Jumlah data: 0
        </div>

        <input
            type="text"
            id="cariData"
            placeholder="Cari Kemantren, Kelurahan, atau Alamat..."
        >

        <div class="table-container">

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kemantren</th>
                        <th>Kelurahan</th>
                        <th>Alamat</th>
                        <th>Lokasi</th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    <tr>
                        <td colspan="5">Memuat data...</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    
    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"></script>

    <script>


        var map = L.map('map').setView(
            [-7.677567, 110.406899], 12
        );

        var basemap1 = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                attribution: 'Tiles &copy; Esri',
                maxZoom: 19
            }
        );

        /* Basemap OpenStreetMap */
        var basemap2 = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution: '&copy; OpenStreetMap contributors'
            }
        );

        basemap1.addTo(map);

        /* Layer Marker dari Database */
        var markerLayer = L.featureGroup().addTo(map);

        /* Elemen Tabel */
        var tableBody = document.getElementById('tableBody');
        var jumlahData = document.getElementById('jumlahData');
        var cariData = document.getElementById('cariData');

        /* Menyimpan Data */
        var semuaData = [];

        /* Mengambil Data dari PHP */
        fetch('get_data.php')

            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal mengambil data database');
                }

                return response.json();
            })

            .then(data => {

                console.log(data);

                if (!Array.isArray(data)) {
                    throw new Error(data.message || 'Format data tidak valid');
                }

                semuaData = data;

                /* Menampilkan Marker */
                data.forEach(function(item) {

                    var latitude = Number(item.latitude);
                    var longitude = Number(item.longitude);

                    if (
                        !Number.isFinite(latitude) ||
                        !Number.isFinite(longitude) ||
                        latitude < -90 ||
                        latitude > 90 ||
                        longitude < -180 ||
                        longitude > 180
                    ) {
                        return;
                    }

                    var marker = L.marker([
                        latitude,
                        longitude
                    ]);

                    /* Informasi Popup */
                    var popup = document.createElement('div');

                    var judulPopup = document.createElement('b');
                    judulPopup.textContent = item.kemantren || '-';

                    var kelurahan = document.createElement('p');
                    kelurahan.textContent =
                        'Kelurahan: ' + (item.kelurahan || '-');

                    var alamat = document.createElement('p');
                    alamat.textContent =
                        'Alamat: ' + (item.alamat || '-');

                    var koordinat = document.createElement('p');
                    koordinat.textContent =
                        'Koordinat: ' + latitude + ', ' + longitude;

                    popup.appendChild(judulPopup);
                    popup.appendChild(kelurahan);
                    popup.appendChild(alamat);
                    popup.appendChild(koordinat);

                    marker.bindPopup(popup);

                    /* Tooltip Bank Sampah */
                    marker.bindTooltip(
                        'Bank Sampah',
                        {
                            permanent: false,
                            direction: 'top',
                            offset: [0, -10],
                            opacity: 0.9
                        }
                    );

                    marker.addTo(markerLayer);

                    /* Menyimpan Marker */
                    item._marker = marker;

                });

                /* Menampilkan Tabel */
                tampilkanTabel(semuaData);

                /* Zoom Otomatis */
                if (markerLayer.getLayers().length > 0) {

                    map.fitBounds(markerLayer.getBounds(), {
                        padding: [30, 30],
                        maxZoom: 16
                    });

                }

            })

            .catch(error => {

                console.error('Error:', error);

                tableBody.innerHTML =
                    '<tr><td colspan="5">Gagal memuat data.</td></tr>';

                jumlahData.textContent = 'Data tidak tersedia';

                alert('Gagal memuat data: ' + error.message);

            });

        /* Fungsi Menampilkan Tabel */
        function tampilkanTabel(data) {

            tableBody.innerHTML = '';

            jumlahData.textContent =
                'Jumlah data: ' + data.length;

            if (data.length === 0) {

                tableBody.innerHTML =
                    '<tr><td colspan="5">Data tidak ditemukan.</td></tr>';

                return;
            }

            data.forEach(function(item) {

                var row = document.createElement('tr');

                var cellID = document.createElement('td');
                cellID.textContent = item.ogc_fid;

                var cellKemantren = document.createElement('td');
                cellKemantren.textContent = item.kemantren || '-';

                var cellKelurahan = document.createElement('td');
                cellKelurahan.textContent = item.kelurahan || '-';

                var cellAlamat = document.createElement('td');
                cellAlamat.textContent = item.alamat || '-';

                var cellLokasi = document.createElement('td');

                var tombol = document.createElement('button');
                tombol.textContent = 'Lihat';
                tombol.className = 'tombolLokasi';

                tombol.addEventListener('click', function(event) {
                    event.stopPropagation();
                    arahkanKeLokasi(item);
                });

                cellLokasi.appendChild(tombol);

                row.appendChild(cellID);
                row.appendChild(cellKemantren);
                row.appendChild(cellKelurahan);
                row.appendChild(cellAlamat);
                row.appendChild(cellLokasi);

                row.addEventListener('click', function() {
                    arahkanKeLokasi(item);
                });

                tableBody.appendChild(row);

            });

        }

        /* Fungsi Menuju Lokasi Marker */
        function arahkanKeLokasi(item) {

            if (!item._marker) {
                return;
            }

            var latitude = Number(item.latitude);
            var longitude = Number(item.longitude);

            map.setView(
                [latitude, longitude],
                17
            );

            item._marker.openPopup();

        }

        /* Fungsi Pencarian Data */
        cariData.addEventListener('input', function() {

            var kataKunci = this.value.toLowerCase();

            var hasilPencarian = semuaData.filter(function(item) {

                return (
                    String(item.ogc_fid || '').toLowerCase().includes(kataKunci) ||
                    String(item.kemantren || '').toLowerCase().includes(kataKunci) ||
                    String(item.kelurahan || '').toLowerCase().includes(kataKunci) ||
                    String(item.alamat || '').toLowerCase().includes(kataKunci)
                );

            });

            tampilkanTabel(hasilPencarian);

        });

        /* Fungsi Sembunyikan dan Tampilkan Tabel */
        function toggleTabel() {

            var tabel = document.getElementById('tabelData');
            var tombol = document.getElementById('tombolTabel');

            tabel.classList.toggle('tersembunyi');

            if (tabel.classList.contains('tersembunyi')) {

                tombol.textContent = 'Tampilkan Tabel';

            } else {

                tombol.textContent = 'Sembunyikan Tabel';

            }

        }

        /* Layer Control */
        var baseMaps = {
            "Esri Imagery": basemap1,
            "OpenStreetMap": basemap2
        };

        var overlayMaps = {
            "Lokasi Bank Sampah": markerLayer
        };

        L.control.layers(
            baseMaps,
            overlayMaps,
            {
                collapsed: false
            }
        ).addTo(map);

        /* Scale Bar */
        L.control.scale({
            maxWidth: 150,
            position: 'bottomright',
            metric: true,
            imperial: false
        }).addTo(map);

    </script>

</body>
</html>
