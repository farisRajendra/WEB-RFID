<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Atur Izin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0; padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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
        h2 {
            margin-top: 40px;
            color: #333;
        }
        .alert-success {
            color: green;
            font-weight: bold;
        }
        .alert-error {
            color: red;
            font-weight: bold;
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
            background: #28a745;
        }

        .modal-overlay.active .success-modal {
            transform: scale(1) translateY(0);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #28a745;
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
            0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
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
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-close-btn:hover {
            background: #218838;
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
        <h1>Atur Izin</h1>
        <p>Form Pengajuan Izin Kerja</p>
    </div>

    @if(session('success'))
        <p class="alert-success">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Pilih tanggal (reload halaman) --}}
    <form method="GET" action="{{ route('izin.index') }}">
        <div class="form-group">
            <label for="tanggal">Pilih Tanggal:</label>
            <input 
                type="date" 
                id="tanggal" 
                name="tanggal" 
                value="{{ $tanggal ?? date('Y-m-d') }}" 
                onchange="this.form.submit()" 
            />
        </div>
    </form>

    {{-- Form input izin --}}
    @if(isset($pegawai_tidak_absen) && $pegawai_tidak_absen->count() > 0)
    <form method="POST" action="{{ route('izin.store') }}" id="izinForm">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal ?? date('Y-m-d') }}">

        <div class="form-group">
            <label for="pegawai_id">Karyawan (Belum Absen):</label>
            <select id="pegawai_id" name="pegawai_id" required>
                <option value="">Pilih Pegawai</option>
                @foreach($pegawai_tidak_absen as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan:</label>
            <select id="keterangan" name="keterangan" required>
                <option value="izin">Izin</option>
                <option value="sakit">Sakit</option>
                <option value="dinas">Dinas Luar</option>
            </select>
        </div>

        <button type="button" class="submit-btn" onclick="submitForm()">Submit</button>
    </form>
    @else
        <p style="margin-top: 40px; color: green;">
            <strong>Semua pegawai sudah memiliki data absensi pada tanggal {{ $tanggal ?? date('Y-m-d') }}.</strong>
        </p>
    @endif

    {{-- Tabel daftar izin --}}
    <h2>Daftar Izin Tanggal {{ $tanggal ?? date('Y-m-d') }}</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($izin) && $izin->count() > 0)
                @foreach($izin as $index => $data)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('Y-m-d') }}</td>
                        <td>{{ $data->pegawai->nama ?? '-' }}</td>
                        <td>
                            @if($data->keterangan == 'izin')
                                Izin
                            @elseif($data->keterangan == 'sakit')
                                Izin Sakit
                            @else
                                Dinas Luar
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">Belum ada data izin.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="success-modal">
        <div class="success-icon"></div>
        <h2 class="modal-title">Berhasil!</h2>
        <p class="modal-message">Data izin berhasil disimpan.</p>
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

    function submitForm() {
        const pegawaiId = document.getElementById('pegawai_id').value;
        const keterangan = document.getElementById('keterangan').value;
        
        if (!pegawaiId) {
            document.getElementById('errorMessage').textContent = 'Silakan pilih pegawai terlebih dahulu!';
            showModal('errorModal');
            return;
        }

        if (!keterangan) {
            document.getElementById('errorMessage').textContent = 'Silakan pilih keterangan terlebih dahulu!';
            showModal('errorModal');
            return;
        }

        // Jika validasi berhasil, langsung submit form
        // Popup akan ditampilkan oleh Laravel session setelah redirect
        document.getElementById('izinForm').submit();
    }

    // Jika ada session success dari Laravel, tampilkan popup
    @if(session('success'))
        window.addEventListener('load', function() {
            showModal('successModal');
        });
    @endif

    // Jika ada error dari Laravel, tampilkan popup
    @if($errors->any())
        window.addEventListener('load', function() {
            let errorMessages = [];
            @foreach($errors->all() as $error)
                errorMessages.push('{{ $error }}');
            @endforeach
            document.getElementById('errorMessage').innerHTML = errorMessages.join('<br>');
            showModal('errorModal');
        });
    @endif
</script>
</body>
</html>