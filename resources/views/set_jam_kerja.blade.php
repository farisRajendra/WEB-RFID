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

<script>
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
            alert(data.message);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan!');
        });
    }
</script>

</body>
</html>