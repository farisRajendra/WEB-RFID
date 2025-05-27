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
    <form method="POST" action="{{ route('izin.store') }}">
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

        <button type="submit" class="submit-btn">Submit</button>
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
</body>
</html>
