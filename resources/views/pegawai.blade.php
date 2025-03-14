<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pegawai</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: white;
            color: black;
            text-align: center;
            padding: 20px;
        }
        .container {
            background: white;
            color: black;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            margin: auto;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            
        }
        .add-btn {
    background: white;
    color: #0077b6; /* Warna biru */
    padding: 10px;
    cursor: pointer;
    border: 2px solid #0077b6; /* Garis biru */
    border-radius: 5px;
    font-size: 18px;
    font-weight: bold;
}
.add-btn:hover {
    background: #f0f0f0; /* Warna abu-abu terang saat hover */
}
        input[type="text"] {
            padding: 8px;
            width: 200px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4285f4;
            color: white;
        }
        .form-container {
            display: none;
            background: white;
            color: black;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            margin: auto;
            position: fixed;
            top: 20%;
            left: 25%;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
        }
        .form-container input, .form-container button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
        }
        .close-btn {
            background: red;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        .icon-btn {
    background: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
}
.icon-btn:hover {
    background: #f0f0f0; /* Warna abu-abu saat hover */
}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <button class="add-btn" onclick="openForm()">➕</button>
            <input type="text" id="search" placeholder="Cari Nama Pegawai" onkeyup="searchTable()">
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pegawai</th>
                    <th>Jabatan</th>
                    <th>Tanggal Lahir</th>
                    <th>RFID Id</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="pegawai-list">
            </tbody>
        </table>
    </div>
    <div class="form-container" id="pegawaiForm">
        <h2>Tambah/Edit Pegawai</h2>
        <input type="hidden" id="editIndex">
        <input type="text" id="nama" placeholder="Nama Pegawai">
        <input type="text" id="jabatan" placeholder="Jabatan">
        <input type="date" id="tgl_lahir">
        <input type="text" id="rfid" placeholder="RFID Id">
        <button onclick="savePegawai()">Simpan</button>
        <button class="close-btn" onclick="closeForm()">Tutup</button>
    </div>
    <script>
        let pegawai = [];
        function openForm(index = null) {
            document.getElementById("pegawaiForm").style.display = "block";
            if (index !== null) {
                document.getElementById("editIndex").value = index;
                document.getElementById("nama").value = pegawai[index].nama;
                document.getElementById("jabatan").value = pegawai[index].jabatan;
                document.getElementById("tgl_lahir").value = pegawai[index].tgl_lahir;
                document.getElementById("rfid").value = pegawai[index].rfid;
            } else {
                document.getElementById("editIndex").value = "";
                document.getElementById("nama").value = "";
                document.getElementById("jabatan").value = "";
                document.getElementById("tgl_lahir").value = "";
                document.getElementById("rfid").value = "";
            }
        }
        function closeForm() {
            document.getElementById("pegawaiForm").style.display = "none";
        }
        function savePegawai() {
            let index = document.getElementById("editIndex").value;
            let data = {
                nama: document.getElementById("nama").value,
                jabatan: document.getElementById("jabatan").value,
                tgl_lahir: document.getElementById("tgl_lahir").value,
                rfid: document.getElementById("rfid").value
            };
            if (index === "") {
                pegawai.push(data);
            } else {
                pegawai[index] = data;
            }
            renderTable();
            closeForm();
        }
        function renderTable() {
            let list = document.getElementById("pegawai-list");
            list.innerHTML = "";
            pegawai.forEach((p, index) => {
                let row = `<tr>
                    <td>${index + 1}</td>
                    <td>${p.nama}</td>
                    <td>${p.jabatan}</td>
                    <td>${p.tgl_lahir}</td>
                    <td>${p.rfid}</td>
                    <td>
                        <button class="icon-btn" onclick="openForm(${index})">✏️</button>
                        <button class="icon-btn" onclick="deletePegawai(${index})">🗑️</button>
                    </td>
                </tr>`;
                list.innerHTML += row;
            });
        }
        function deletePegawai(index) {
            pegawai.splice(index, 1);
            renderTable();
        }
        function searchTable() {
            let input = document.getElementById("search").value.toLowerCase();
            let rows = document.querySelectorAll("#pegawai-list tr");
            rows.forEach(row => {
                let nama = row.cells[1].textContent.toLowerCase();
                row.style.display = nama.includes(input) ? "" : "none";
            });
        }
    </script>
</body>
</html>
