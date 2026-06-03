<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio | Accessible Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --mp-bg: #e7e7e7;
            --mp-card-bg: #ffffff;
            --mp-header-bg: #2d2a26;
            --mp-accent: #7c2d2d;
            --mp-gold: #b08d57;
            --mp-border: #dcd7cc;
            --mp-text: #2d2a26;
            --mp-text-muted: #706a64;
            --mp-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0;
            background: var(--mp-bg);
            font-family: 'Segoe UI', sans-serif;
            color: var(--mp-text);
        }

        .mp-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* HEADER */
        .mp-header-full {
            background: var(--mp-header-bg);
            border-bottom: 4px solid var(--mp-gold);
            padding: 15px 0;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .mp-nav-group { display: flex; align-items: center; gap: 25px; }

        .mp-header-content { display: flex; justify-content: space-between; align-items: center; }
        .mp-header-brand-line { width: 3px; height: 40px; background: var(--mp-gold); margin-right: 15px; }
        .mp-main-title { font-size: 22px; font-weight: 800; color: #f1ede4; margin: 0; }
        .mp-sub-text { font-size: 11px; color: var(--mp-gold); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
        .mp-btn-back { color: #f1ede4; text-decoration: none; font-weight: 700; font-size: 13px; text-transform: uppercase; transition: 0.3s; }
        .mp-btn-back:hover { color: var(--mp-gold); }

        .mp-link-nav { color: #f1ede4; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; opacity: 0.85; }
        .mp-link-nav:hover { opacity: 1; color: var(--mp-gold); }
        .mp-link-login { color: #f1ede4; text-decoration: none; font-weight: 700; font-size: 13px; transition: 0.3s; padding: 10px 15px; border-radius: 3px; border: 1px solid transparent; text-transform: uppercase; }
        .mp-link-login:hover { color: var(--mp-gold); border-color: rgba(176, 141, 87, 0.3); background: rgba(255, 255, 255, 0.05); }

        .mp-btn-action { background: var(--mp-accent); color: white; padding: 10px 22px; text-decoration: none; font-weight: 700; border-radius: 3px; text-transform: uppercase; font-size: 11px; transition: 0.3s; border: none; cursor: pointer; display: inline-block; }
        .mp-btn-action:hover { background: #943636; box-shadow: 0 4px 12px rgba(124, 45, 45, 0.3); }

        /* INTRO */
        .portfolio-intro { padding: 60px 0 40px; text-align: center; background: white; border-bottom: 1px solid var(--mp-border); margin-bottom: 40px; }
        .mp-section-header { font-size: 32px; color: var(--mp-accent); text-transform: uppercase; font-weight: 800; margin-bottom: 10px; }
        .intro-divider { width: 80px; height: 4px; background: var(--mp-gold); margin: 0 auto 25px; }

        /* GRID */
        .portfolio-grid { column-count: 3; column-gap: 25px; }
        .portfolio-item {
            display: inline-block; width: 100%; margin-bottom: 25px;
            background: var(--mp-card-bg); border-radius: 4px; overflow: hidden;
            box-shadow: var(--mp-shadow); border: 1px solid var(--mp-border);
            transition: 0.3s;
        }
        .portfolio-item:hover { transform: translateY(-8px); border-color: var(--mp-gold); }
        .portfolio-item img { width: 100%; display: block; height: auto; }
        .portfolio-info { padding: 15px; background: white; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-between; }
        .portfolio-info span { font-size: 11px; font-weight: 700; color: var(--mp-text-muted); text-transform: uppercase; }

        @media (max-width: 900px) { .portfolio-grid { column-count: 2; } }
        @media (max-width: 600px) { .portfolio-grid { column-count: 1; } }
    </style>
</head>
<body>

<header class="mp-header-full">
    <div class="mp-container">
        <div class="mp-header-content">
            <div style="display: flex; align-items: center;">
                <div class="mp-header-brand-line"></div>
                <div>
                    <h1 class="mp-main-title">Accessible Printing</h1>
                    <span class="mp-sub-text">Kwaliteit in elk detail</span>
                </div>
            </div>
            <nav class="mp-nav-group">
                <a href="{{ route('welcome') }}" class="mp-btn-back"><i class="fa-solid fa-arrow-left"></i> Terug</a>
                <a href="{{ route('showcase.index') }}" class="mp-link-nav">Showcase</a>
                <a href="{{ route('catalog.index') }}" class="mp-link-nav">Catalogus</a>

                @auth
                    <a href="{{ url('/dashboard') }}" class="mp-btn-action">Naar Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="mp-link-login">Inloggen</a>
                    <a href="{{ route('register') }}" class="mp-btn-action">Registreren</a>
                @endauth
            </nav>
        </div>
    </div>
</header>

<section class="portfolio-intro">
    <div class="mp-container">
        <h2 class="mp-section-header">Eindresultaten van klanten</h2>
        <div class="intro-divider"></div>
        <p style="color: var(--mp-text-muted);">Een showcase van de afgemaakte projecten van onze prints</p>
    </div>
</section>

<div class="mp-container">
    <div class="portfolio-grid">
        @forelse($images as $image)
            <div class="portfolio-item">
                <img src="{{ asset('portfolio/' . $image) }}" alt="Portfolio item">
                <div class="portfolio-info">
                    <span><i class="fa-solid fa-cube" style="color: var(--mp-gold);"></i> Gerealiseerd</span>
                    <i class="fa-solid fa-check-circle" style="color: var(--mp-gold);"></i>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                <p>Geen afbeeldingen gevonden</p>
            </div>
        @endforelse
    </div>
</div>

<footer style="text-align: center; padding: 60px; color: var(--mp-text-muted); font-size: 12px;">
    &copy; {{ date('Y') }} Accessible Printing.
</footer>

</body>
</html>
