<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 900px;
            margin: 29px auto;
            background: white;
            padding: 29px;
            border-radius: 10px;
            box-shadow: 0 8px 8px rgba(0, 0, 0, 0.2);
        }
        .header {
            background: #007BFF;
            padding: 20px;
            margin-bottom: 20px; 
            text-align: center;
            border-radius: 10px 10px 0 0;
            color: white;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
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
            background: #f9f9f9;
        }
        .filter-container {
            display: flex;
            gap: 10px;
            margin-bottom: 29px;
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
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Laporan Absensi</h1>
            <p>Data Pengajuan Izin dan Kehadiran Karyawan</p>
        </div>
    
        <!-- Filter untuk laporan absensi -->
        <div class="filter-container">
            <input type="date" id="startDate">
            <input type="date" id="endDate">
            <input type="text" id="searchInput" placeholder="Nama atau ID RFID">
            <button onclick="tampilkanLaporan()">Tampilkan Laporan</button>
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
                <!-- Data absensi dan izin akan dimasukkan melalui JavaScript -->
            </tbody>
        </table>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        // Set nilai default tanggal awal dan akhir
        document.getElementById('startDate').valueAsDate = new Date();
        document.getElementById('endDate').valueAsDate = new Date();
    
        // Data absensi (contoh)
        const data = [
            { no: 1, nama: "Petelot", id: "2746950979", tanggal: "2025-02-08", jamMasuk: "20:57:59", jamPulang: "21:01:52", status: "Hadir", keterangan: "-" },
            { no: 2, nama: "Nur Jannah", id: "3013136640", tanggal: "2025-02-08", jamMasuk: "21:02:12", jamPulang: "21:04:37", status: "Hadir", keterangan: "-" },
            { no: 3, nama: "Andi Usman", id: "2747552611", tanggal: "2025-02-08", jamMasuk: "21:12:03", jamPulang: "21:12:30", status: "Hadir", keterangan: "-" },
            { no: 4, nama: "Ali Akbar", id: "2747552622", tanggal: "2025-02-09", jamMasuk: "08:00:00", jamPulang: "16:00:00", status: "Hadir", keterangan: "-" }
        ];
    
        function tampilkanLaporan() {
            const tableBody = document.getElementById("tableBody");
            tableBody.innerHTML = ""; // Kosongkan tabel sebelum menampilkan data

            const searchQuery = document.getElementById("searchInput").value.toLowerCase();
            const startDate = new Date(document.getElementById("startDate").value);
            const endDate = new Date(document.getElementById("endDate").value);

            const izinData = JSON.parse(localStorage.getItem("izinData")) || [];
            const allData = [...data, ...izinData];

            const filteredData = allData.filter(item => {
                const itemDate = new Date(item.tanggal);
                return (
                    (item.nama.toLowerCase().includes(searchQuery) || (item.id && item.id.includes(searchQuery))) &&
                    itemDate >= startDate && itemDate <= endDate
                );
            });

            if (filteredData.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8">Data tidak ditemukan</td></tr>';
                return;
            }

            filteredData.forEach((item, index) => {
                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${item.nama}</td>
                    <td>${item.id || "-"}</td>
                    <td>${item.tanggal}</td>
                    <td>${item.jamMasuk || "-"}</td>
                    <td>${item.jamPulang || "-"}</td>
                    <td>${item.status}</td>
                    <td>${item.keterangan || "-"}</td>
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
