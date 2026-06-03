<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Betaling Geannuleerd</title>
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #f4f4f2; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        h1 { color: #7c2d2d; }
        .btn { display: inline-block; margin-top: 25px; padding: 12px 25px; background: #2d2a26; color: white; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Betaling afgebroken</h1>
    <p>De betaling is niet afgerond. Geen zorgen, je kunt het later opnieuw proberen via je dashboard.</p>
    <a href="{{ route('dashboard') }}" class="btn">Terug naar Dashboard</a>
</div>
</body>
</html>
