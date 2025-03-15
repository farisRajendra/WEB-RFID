<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Izin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
   
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .header {
            background: #007BFF;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            color: white;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 100px;
        }
        .row .form-group {
            flex: 1;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }
        .submit-btn:hover {
            background: #218838;
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
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Atur Izin</h1>
        <p>Form Pengajuan Izin Kerja</p>
    </div>

    <div class="row">
        <div class="form-group">
            <label for="tanggal">Tanggal:</label>
            <input type="date" id="tanggal">
        </div>

        <div class="form-group">
            <label for="search">Karyawan:</label>
            <input type="text" id="search" placeholder="Masukkan nama...">
        </div>
    </div>


    <div class="form-group">
        <label for="keterangan">Keterangan:</label>
        <select id="keterangan">
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
            <option value="dinas">Dinas Luar</option>
        </select>
    </div>

    <button class="submit-btn" onclick="submitIzin()">Submit</button>

    <table id="izinTable">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let today = new Date().toISOString().split("T")[0];
        document.getElementById("tanggal").value = today;
    });

    let nomorUrut = 1;

    function submitIzin() {
        let tanggal = document.getElementById("tanggal").value;
        let search = document.getElementById("search").value;
        let keterangan = document.getElementById("keterangan").value;

        if (tanggal && search) {
            // Ambil data dari localStorage
            let izinData = JSON.parse(localStorage.getItem("izinData")) || [];

            // Tambahkan data baru
            izinData.push({
                no: nomorUrut++,
                tanggal: tanggal,
                nama: search,
                keterangan: keterangan === "izin" ? "Izin" : keterangan === "sakit" ? "Izin Sakit" : "Dinas Luar"
            });

            // Simpan data kembali ke localStorage
            localStorage.setItem("izinData", JSON.stringify(izinData));

            // Tambahkan ke tabel pada halaman ini
            let table = document.getElementById("izinTable").getElementsByTagName("tbody")[0];
            let newRow = table.insertRow();

            newRow.insertCell(0).innerText = nomorUrut - 1;
            newRow.insertCell(1).innerText = tanggal;
            newRow.insertCell(2).innerText = search;
            newRow.insertCell(3).innerText = keterangan === "izin" ? "Izin" : keterangan === "sakit" ? "Izin Sakit" : "Dinas Luar";

            // Reset form input
            document.getElementById("search").value = "";
            document.getElementById("keterangan").value = "izin";
        } else {
            alert("Harap isi semua kolom!");
        }
    }
</script>

</body>
</html>
