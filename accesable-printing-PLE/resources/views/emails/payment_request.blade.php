<!DOCTYPE html>
<html>
<head>
    <title>Betaalverzoek</title>
</head>
<body>
<h1>Beste {{ $order->user->name }},</h1>
<p>Bedankt voor je aanvraag voor het project: <strong>{{ $order->title }}</strong>.</p>
<p>De totale kosten bedragen: <strong>€ {{ number_format($order->total_price, 2, ',', '.') }}</strong>.</p>
<p>Klik op de onderstaande knop om de betaling veilig af te ronden via Stripe:</p>

<a href="{{ $paymentUrl }}" style="background: #b08d57; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
    Nu Betalen
</a>

<p>Na ontvangst van de betaling wordt het printproces direct gestart.</p>
<p>Met vriendelijke groet,<br>Accessible Printing</p>
</body>
</html>
