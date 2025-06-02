<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Jam Kerja</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #FFC107;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: white;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            color: white;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .output {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .box {
            padding: 10px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }
        .submit-btn {
            width: 100%;
            padding: 10px;
            background: blue;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 15px;
            cursor: pointer;
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
            content: '✗';
            color: white;
            font-size: 40px;
            font-weight: bold;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .modal-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .modal-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .modal-close-btn {
            background: #4285f4;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-close-btn:hover {
            background: #3367d6;
            transform: translateY(-2px);
        }

        .error-modal .modal-close-btn {
            background: #f44336;
        }

        .error-modal .modal-close-btn:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Pengaturan Jam Kerja</h1>
        <p>Atur Jadwal Jam Masuk & Jam Keluar</p>
    </div>
    
    <label for="jamMasuk">Jam Masuk:</label>
    <input type="time" id="jamMasuk" value="{{ substr($jam_kerjas->jam_masuk, 0, 5) }}">
        
    <label for="jamKeluar">Jam Keluar:</label>
    <input type="time" id="jamKeluar" value="{{ substr($jam_kerjas->jam_keluar, 0, 5) }}">
    
    <div class="output">
        <div class="box" id="outputMasuk">{{ $jam_kerjas->jam_masuk }}</div>
        <div class="box" id="outputKeluar">{{ $jam_kerjas->jam_keluar }}</div>
    </div>
    
    <button class="submit-btn" onclick="submitData()">Submit</button>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="success-modal">
        <div class="success-icon"></div>
        <h2 class="modal-title">Berhasil!</h2>
        <p class="modal-message">Data jam kerja berhasil disimpan.</p>
        <button class="modal-close-btn" onclick="closeModal('successModal')">OK</button>
    </div>
</div>

<!-- Error Modal -->
<div class="modal-overlay" id="errorModal">
    <div class="error-modal">
        <div class="error-icon"></div>
        <h2 class="modal-title">Gagal!</h2>
        <p class="modal-message" id="errorMessage">Terjadi kesalahan saat menyimpan data.</p>
        <button class="modal-close-btn" onclick="closeModal('errorModal')">OK</button>
    </div>
</div>

<script>
    function showModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    function submitData() {
        let jamMasuk = document.getElementById("jamMasuk").value;
        let jamKeluar = document.getElementById("jamKeluar").value;
        
        // Update tampilan
        document.getElementById("outputMasuk").textContent = jamMasuk + ":00";
        document.getElementById("outputKeluar").textContent = jamKeluar + ":00";
        
        fetch('/save-jam-kerja', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                jam_masuk: jamMasuk, 
                jam_keluar: jamKeluar 
            })
        })
        .then(response => response.json())
         .then(data => {
            if (data.success || data.message.includes('berhasil')) {
                showModal('successModal');
            } else {
                document.getElementById('errorMessage').textContent = data.message;
                showModal('errorModal');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorMessage').textContent = 'Terjadi kesalahan jaringan!';
            showModal('errorModal');
        });
    }
</script>

</body>
</html>