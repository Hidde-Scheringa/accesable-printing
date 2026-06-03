<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Betaling Geslaagd</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #f4f4f2; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        i { color: #166534; font-size: 50px; margin-bottom: 20px; }
        h1 { color: #2d2a26; margin: 0 0 10px; }
        p { color: #666; line-height: 1.5; }
        .btn { display: inline-block; margin-top: 25px; padding: 12px 25px; background: #b08d57; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>
<div class="card">
    <i class="fa-solid fa-circle-check"></i>
    <h1>Bedankt!</h1>
    <p>De betaling voor order #{{ $orderId }} is succesvol ontvangen. We gaan direct aan de slag met je printwerk.</p>
    <a href="{{ route('dashboard') }}" class="btn">Terug naar Dashboard</a>
</div>
</body>
</html>
