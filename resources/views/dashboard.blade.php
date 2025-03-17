<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            width: 250px;
            background-color: #2E75B6;
            color: white;
            height: 100vh;
            padding: 20px;
        }
        .sidebar h2 {
            margin-bottom: 30px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background-color: #1F4E79;
            border-radius: 5px;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-box {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            width: 200px;
            color: white;
        }

        .yellow { background: #fbbc04; }
        .red { background: #ea4335; }
        .blue { background: #4285f4; }

        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: auto;
        }

        .logout-btn {
            display: block;
            padding: 10px;
            background-color: red;
            color: white;
            text-align: center;
            border-radius: 5px;
            margin-top: 20px;
            text-decoration: none;
        }
    </style>
</head>
<body>  

<div class="sidebar">
        <h2>Dashboard Admin</h2>
        <a href="{{ route('pegawai') }}" class="{{ request()->is('pegawai') ? 'active' : '' }}">Pegawai</a>
        <a href="{{ route('set_jam_kerja') }}" class="{{ request()->is('set_jam_kerja') ? 'active' : '' }}">Atur Jam Kerja</a>
        <a href="{{ route('atur_izin') }}" class="{{ request()->is('atur_izin') ? 'active' : '' }}">Izin Pegawai</a>
        <a href="{{ route('laporan_absen') }}" class="{{ request()->is('laporan_absen') ? 'active' : '' }}">Laporan Absen</a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Log Out</button>
        </form>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="stats">
            <div class="stat-box yellow">Pegawai Masuk</div>
            <div class="stat-box red">Pegawai Tidak Masuk</div>
            <div class="stat-box blue">Total Pegawai</div>
        </div>

        <img src="{{ asset('images/dashbord.png') }}" alt="Grafik Dashboard" width="500">
    </div>

</body>
</html>
