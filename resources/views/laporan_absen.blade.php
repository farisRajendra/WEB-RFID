<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Laporan Absensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 8px rgba(0, 0, 0, 0.2);
        }
        .header {
            background: #007BFF;
            padding: 20px;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .filter-container {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        .filter-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .filter-container button {
            padding: 10px 20px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #007BFF;
            color: white;
            cursor: pointer;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Laporan Absensi</h1>
        <p>Data Kehadiran Karyawan</p>
    </div>

    <div class="filter-container">
        <input type="date" id="startDate" />
        <input type="date" id="endDate" />
        <input type="text" id="searchInput" placeholder="Nama atau ID RFID" />
        <button onclick="tampilkanLaporan()">Tampilkan</button>
        <button onclick="exportToExcel('absensi', 'Laporan_Absensi.xlsx')">Ekspor ke Excel</button>
    </div>

    <table id="absensi">
        <thead>
            <tr>
                <th onclick="sortTable(0)">No</th>
                <th onclick="sortTable(1)">Nama</th>
                <th onclick="sortTable(2)">ID RFID</th>
                <th onclick="sortTable(3)">Tanggal</th>
                <th onclick="sortTable(4)">Jam Masuk</th>
                <th onclick="sortTable(5)">Jam Pulang</th>
                <th onclick="sortTable(6)">Status</th>
                <th onclick="sortTable(7)">Keterangan</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <!-- Data akan diisi oleh JavaScript -->
        </tbody>
    </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    // Data dari Laravel dikirim sebagai JSON
    const data = @json($absensiData ?? []);

    // Set default tanggal filter ke hari ini
    document.getElementById('startDate').valueAsDate = new Date();
    document.getElementById('endDate').valueAsDate = new Date();

    // Fungsi untuk ambil jam dari string datetime "YYYY-MM-DD HH:mm:ss"
    function ambilJam(datetimeStr) {
        if (!datetimeStr) return '-';
        if (datetimeStr.includes(' ')) {
            return datetimeStr.split(' ')[1];
        }
        return datetimeStr; // jika sudah jam saja
    }

    function tampilkanLaporan() {
        const tableBody = document.getElementById("tableBody");
        tableBody.innerHTML = "";

        const searchQuery = document.getElementById("searchInput").value.toLowerCase();
        const startDate = new Date(document.getElementById("startDate").value);
        const endDate = new Date(document.getElementById("endDate").value);

        // Filter data berdasarkan tanggal dan pencarian nama/id
        const filteredData = data.filter(item => {
            const itemDate = new Date(item.tanggal);
            return (
                (item.nama.toLowerCase().includes(searchQuery) || item.id.toLowerCase().includes(searchQuery)) &&
                itemDate >= startDate && itemDate <= endDate
            );
        });

        if (filteredData.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8">Data tidak ditemukan</td></tr>';
            return;
        }

        filteredData.forEach((item, index) => {
            const jamMasuk = ambilJam(item.jamMasuk);
            const jamPulang = ambilJam(item.jamPulang);

            const row = `<tr>
                <td>${index + 1}</td>
                <td>${item.nama}</td>
                <td>${item.id}</td>
                <td>${item.tanggal}</td>
                <td>${jamMasuk}</td>
                <td>${jamPulang}</td>
                <td>${item.status}</td>
                <td>${item.keterangan || '-'}</td>
            </tr>`;
            tableBody.innerHTML += row;
        });
    }

    document.addEventListener("DOMContentLoaded", tampilkanLaporan);

    let sortOrder = true;

    function sortTable(columnIndex) {
        const table = document.getElementById("absensi");
        const rows = Array.from(table.rows).slice(1);
        const sortedRows = rows.sort((a, b) => {
            const cellA = a.cells[columnIndex].innerText.trim();
            const cellB = b.cells[columnIndex].innerText.trim();

            if (columnIndex === 0) {
                return sortOrder ? cellA - cellB : cellB - cellA;
            }
            if (columnIndex === 3) {
                return sortOrder
                    ? new Date(cellA) - new Date(cellB)
                    : new Date(cellB) - new Date(cellA);
            }

            return sortOrder ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        });
        table.tBodies[0].append(...sortedRows);
        sortOrder = !sortOrder;
    }

    function exportToExcel(tableID, fileName) {
        const table = document.getElementById(tableID);
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
        XLSX.writeFile(workbook, fileName);
    }
</script>

</body>
</html>