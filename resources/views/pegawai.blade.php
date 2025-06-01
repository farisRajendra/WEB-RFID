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

        /* MODERN POPUP STYLES */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Success Modal */
        .success-modal {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            transform: scale(0.7) translateY(50px);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }

        .success-modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: #4285f4;
        }

        .modal-overlay.active .success-modal {
            transform: scale(1) translateY(0);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #4285f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        .success-icon::after {
            content: '✓';
            color: white;
            font-size: 40px;
            font-weight: bold;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(66, 133, 244, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(66, 133, 244, 0); }
            100% { box-shadow: 0 0 0 0 rgba(66, 133, 244, 0); }
        }

        /* Error Modal */
        .error-modal {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            transform: scale(0.7) translateY(50px);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }

        .error-modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: #f44336;
        }

        .modal-overlay.active .error-modal {
            transform: scale(1) translateY(0);
        }

        .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #f44336;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: shake 0.5s ease-in-out;
        }

        .error-icon::after {
            content: '✕';
            color: white;
            font-size: 40px;
            font-weight: bold;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Confirm Modal */
        .confirm-modal {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            text-align: center;
            transform: scale(0.7) translateY(50px);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }

        .confirm-modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: #0077b6;
        }

        .modal-overlay.active .confirm-modal {
            transform: scale(1) translateY(0);
        }

        .confirm-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #0077b6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirm-icon::after {
            content: '?';
            color: white;
            font-size: 40px;
            font-weight: bold;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .modal-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
            font-family: 'Poppins', sans-serif;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .popup-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 100px;
            font-family: 'Poppins', sans-serif;
        }

        .popup-btn-primary {
            background: #4285f4;
            color: white;
        }

        .popup-btn-success {
            background: #4CAF50;
            color: white;
        }

        .popup-btn-danger {
            background: #f44336;
            color: white;
        }

        .popup-btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #e9ecef;
        }

        .popup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .popup-close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .popup-close-btn:hover {
            color: #333;
        }

        /* Loading Modal */
        .loading-modal {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 300px;
            width: 90%;
            text-align: center;
            transform: scale(0.7) translateY(50px);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-overlay.active .loading-modal {
            transform: scale(1) translateY(0);
        }

        .spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4285f4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

    <!-- POPUP MODALS -->
    <!-- Success Modal -->
    <div id="successModal" class="modal-overlay">
        <div class="success-modal">
            <button class="popup-close-btn" onclick="closePopup('successModal')">&times;</button>
            <div class="success-icon"></div>
            <h2 class="modal-title">Berhasil!</h2>
            <p class="modal-message" id="successMessage">Data Anda telah berhasil disimpan.</p>
            <div class="modal-buttons">
                <button class="popup-btn popup-btn-success" onclick="closePopup('successModal')">OK</button>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal-overlay">
        <div class="error-modal">
            <button class="popup-close-btn" onclick="closePopup('errorModal')">&times;</button>
            <div class="error-icon"></div>
            <h2 class="modal-title">Terjadi Kesalahan!</h2>
            <p class="modal-message" id="errorMessage">Maaf, terjadi kesalahan saat memproses data Anda.</p>
            <div class="modal-buttons">
                <button class="popup-btn popup-btn-danger" onclick="closePopup('errorModal')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="confirm-modal">
            <button class="popup-close-btn" onclick="closePopup('confirmModal')">&times;</button>
            <div class="confirm-icon"></div>
            <h2 class="modal-title">Konfirmasi</h2>
            <p class="modal-message" id="confirmMessage">Apakah Anda yakin ingin menghapus data ini?</p>
            <div class="modal-buttons">
                <button class="popup-btn popup-btn-danger" onclick="confirmDelete()">Ya, Hapus</button>
                <button class="popup-btn popup-btn-secondary" onclick="closePopup('confirmModal')">Batal</button>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="modal-overlay">
        <div class="loading-modal">
            <div class="spinner"></div>
            <h2 class="modal-title">Memproses...</h2>
            <p class="modal-message">Mohon tunggu sebentar</p>
        </div>
    </div>

    <script>
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // RFID Auto-fill variables
        let rfidCheckInterval = null;
        let isFormOpen = false;
        let deleteId = null; // For confirmation modal
        
        // POPUP FUNCTIONS
        function showSuccessPopup(message = "Data berhasil disimpan!") {
            document.getElementById('successMessage').textContent = message;
            document.getElementById('successModal').classList.add('active');
        }

        function showErrorPopup(message = "Terjadi kesalahan saat memproses data!") {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorModal').classList.add('active');
        }

        function showConfirmPopup(message = "Apakah Anda yakin ingin menghapus data ini?") {
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmModal').classList.add('active');
        }

        function showLoadingPopup() {
            document.getElementById('loadingModal').classList.add('active');
        }

        function closePopup(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function confirmDelete() {
            closePopup('confirmModal');
            showLoadingPopup();
            
            fetch(`/pegawai/${deleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                closePopup('loadingModal');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                showSuccessPopup(data.message || "Pegawai berhasil dihapus!");
                loadPegawai();
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorPopup("Terjadi kesalahan saat menghapus data!");
            });
        }
        
        function openForm(id = null) {
            document.getElementById("pegawaiForm").style.display = "block";
            isFormOpen = true;
            
            if (id) {
                document.getElementById("formTitle").innerText = "Edit Pegawai";
                showLoadingPopup();
                
                fetch(`/pegawai/${id}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    closePopup('loadingModal');
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
                    showErrorPopup("Terjadi kesalahan saat memuat data pegawai!");
                });
            } else {
                document.getElementById("formTitle").innerText = "Tambah Pegawai";
                document.getElementById("pegawaiId").value = "";
                document.getElementById("nama").value = "";
                document.getElementById("jabatan").value = "";
                document.getElementById("tanggal_lahir").value = "";
                document.getElementById("rfid").value = "";
                
                startRfidAutoCheck();
            }
        }

        function closeForm() {
            document.getElementById("pegawaiForm").style.display = "none";
            isFormOpen = false;
            stopRfidAutoCheck();
            document.getElementById("rfidStatus").textContent = "";
        }

        function startRfidAutoCheck() {
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
                    document.getElementById("rfid").value = data.rfid_id;
                    document.getElementById("rfidStatus").textContent = "✓ RFID ter-scan: " + data.rfid_id;
                }
            })
            .catch(error => {
                console.error('Error checking RFID:', error);
            });
        }

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

            if (!nama || !jabatan || !tanggal_lahir) {
                showErrorPopup("Mohon isi semua field yang diperlukan!");
                return;
            }

            showLoadingPopup();
            closeForm();

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
                closePopup('loadingModal');
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error: ${response.status} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                showSuccessPopup(data.message || "Data berhasil disimpan!");
                loadPegawai();
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorPopup("Terjadi kesalahan saat menyimpan data: " + error.message);
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
                showErrorPopup("Terjadi kesalahan saat memuat data!");
            });
        }

        function deletePegawai(id) {
            deleteId = id;
            showConfirmPopup("Apakah Anda yakin ingin menghapus pegawai ini? Tindakan ini tidak dapat dibatalkan.");
        }

        function searchTable() {
            const input = document.getElementById("search").value.toLowerCase();
            const rows = document.querySelectorAll("#pegawai-list tr");
            rows.forEach(row => {
                const nama = row.cells[1].textContent.toLowerCase();
                row.style.display = nama.includes(input) ? "" : "none";
            });
        }

        // Close popup when clicking overlay
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                    modal.classList.remove('active');
                });
            }
        });

        // Load data saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            loadPegawai();
        });

        // Cleanup saat halaman ditutup
        window.addEventListener('beforeunload', function() {
            stopRfidAutoCheck();
        });
    </script>
</body>
</html>