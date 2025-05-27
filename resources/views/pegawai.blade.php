<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            color: #0077b6;
            padding: 10px;
            cursor: pointer;
            border: 2px solid #0077b6;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
        }
        .add-btn:hover {
            background: #f0f0f0;
        }
        input[type="text"], input[type="date"] {
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
            z-index: 1000;
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
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .edit-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px;
            cursor: pointer;
            border-radius: 5px;
        }
        .delete-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 8px;
            cursor: pointer;
            border-radius: 5px;
        }
        
        /* Minimal RFID auto-fill styles */
        .rfid-status {
            font-size: 12px;
            color: #28a745;
            margin-top: 5px;
            min-height: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistem Manajemen Pegawai</h1>
        <div class="header">
            <button class="add-btn" onclick="openForm()">➕ Tambah Pegawai</button>
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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="pegawai-list">
                <!-- Data akan dimasukkan di sini -->
            </tbody>
        </table>
    </div>

    <div class="form-container" id="pegawaiForm">
        <h2 id="formTitle">Tambah Pegawai</h2>
        <input type="hidden" id="pegawaiId">
        <input type="text" id="nama" placeholder="Nama Pegawai">
        <input type="text" id="jabatan" placeholder="Jabatan">
        <input type="date" id="tanggal_lahir" placeholder="Tanggal Lahir">
        <input type="text" id="rfid" placeholder="RFID Id">
        <div class="rfid-status" id="rfidStatus"></div>
        <button onclick="savePegawai()">Simpan</button>
        <button class="close-btn" onclick="closeForm()">Tutup</button>
    </div>

    <script>
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // RFID Auto-fill variables - HANYA TAMBAHAN INI
        let rfidCheckInterval = null;
        let isFormOpen = false;
        
        function openForm(id = null) {
            document.getElementById("pegawaiForm").style.display = "block";
            isFormOpen = true; // Tambahan untuk auto-fill
            
            if (id) {
                // Mode edit: Ambil data dari server
                document.getElementById("formTitle").innerText = "Edit Pegawai";
                fetch(`/pegawai/${id}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById("pegawaiId").value = data.id;
                    document.getElementById("nama").value = data.nama;
                    document.getElementById("jabatan").value = data.jabatan;
                    document.getElementById("tanggal_lahir").value = data.tanggal_lahir.split('T')[0];
                    document.getElementById("rfid").value = data.rfid ? data.rfid.rfid : '';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Terjadi kesalahan saat memuat data pegawai!");
                });
            } else {
                // Mode tambah: Reset form
                document.getElementById("formTitle").innerText = "Tambah Pegawai";
                document.getElementById("pegawaiId").value = "";
                document.getElementById("nama").value = "";
                document.getElementById("jabatan").value = "";
                document.getElementById("tanggal_lahir").value = "";
                document.getElementById("rfid").value = "";
                
                // TAMBAHAN: Start auto-checking untuk RFID
                startRfidAutoCheck();
            }
        }

        function closeForm() {
            document.getElementById("pegawaiForm").style.display = "none";
            isFormOpen = false; // Tambahan untuk auto-fill
            stopRfidAutoCheck(); // Tambahan: Stop auto-checking
            document.getElementById("rfidStatus").textContent = ""; // Clear status
        }

        // FUNGSI BARU UNTUK AUTO-FILL RFID
        function startRfidAutoCheck() {
            // Check setiap 2 detik
            rfidCheckInterval = setInterval(() => {
                if (isFormOpen) {
                    checkLastRfidScan();
                } else {
                    stopRfidAutoCheck();
                }
            }, 2000);
        }

        function stopRfidAutoCheck() {
            if (rfidCheckInterval) {
                clearInterval(rfidCheckInterval);
                rfidCheckInterval = null;
            }
        }

        function checkLastRfidScan() {
            fetch('/last-rfid-scan', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.rfid_id) {
                    // Auto-fill RFID input
                    document.getElementById("rfid").value = data.rfid_id;
                    document.getElementById("rfidStatus").textContent = "✓ RFID ter-scan: " + data.rfid_id;
                }
            })
            .catch(error => {
                console.error('Error checking RFID:', error);
            });
        }
        // AKHIR FUNGSI BARU

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }

        function savePegawai() {
            const id = document.getElementById("pegawaiId").value;
            const nama = document.getElementById("nama").value;
            const jabatan = document.getElementById("jabatan").value;
            const tanggal_lahir = document.getElementById("tanggal_lahir").value;
            const rfid = document.getElementById("rfid").value;

            // Basic validation
            if (!nama || !jabatan || !tanggal_lahir) {
                alert("Mohon isi semua field yang diperlukan!");
                return;
            }

            const data = { nama, jabatan, tanggal_lahir, rfid };
            const url = id ? `/pegawai/${id}` : '/pegawai/store';
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error: ${response.status} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                alert(data.message || "Data berhasil disimpan!");
                closeForm();
                loadPegawai();
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi kesalahan saat menyimpan data: " + error.message);
            });
        }

        function loadPegawai() {
            fetch('/pegawai', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const list = document.getElementById("pegawai-list");
                list.innerHTML = "";
                data.forEach((p, index) => {
                    const formattedDate = formatDate(p.tanggal_lahir);
                    const rfidValue = p.rfid ? p.rfid.rfid : '-';
                    const row = `<tr>
                        <td>${index + 1}</td>
                        <td>${p.nama}</td>
                        <td>${p.jabatan}</td>
                        <td>${formattedDate}</td>
                        <td>${rfidValue}</td>
                        <td class="action-buttons">
                            <button class="edit-btn" onclick="openForm(${p.id})">✏️ Edit</button>
                            <button class="delete-btn" onclick="deletePegawai(${p.id})">🗑️ Hapus</button>
                        </td>
                    </tr>`;
                    list.innerHTML += row;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi kesalahan saat memuat data!");
            });
        }

        function deletePegawai(id) {
            if (confirm("Apakah Anda yakin ingin menghapus pegawai ini?")) {
                fetch(`/pegawai/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message || "Pegawai berhasil dihapus!");
                    loadPegawai();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Terjadi kesalahan saat menghapus data!");
                });
            }
        }

        function searchTable() {
            const input = document.getElementById("search").value.toLowerCase();
            const rows = document.querySelectorAll("#pegawai-list tr");
            rows.forEach(row => {
                const nama = row.cells[1].textContent.toLowerCase();
                row.style.display = nama.includes(input) ? "" : "none";
            });
        }

        // Load data saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            loadPegawai();
        });

        // TAMBAHAN: Cleanup saat halaman ditutup
        window.addEventListener('beforeunload', function() {
            stopRfidAutoCheck();
        });
    </script>
</body>
</html>