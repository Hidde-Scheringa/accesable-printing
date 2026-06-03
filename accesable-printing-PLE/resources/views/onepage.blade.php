<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessible Printing - Bring Your Campaign to Life</title>
    <!-- FontAwesome voor de RPG/Tabletop & Tech iconen -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --op-bg: #1a1816; /* Donkere, sfeervolle RPG achtergrond */
            --op-card-bg: #282522; /* Warme, donkere kaarten */
            --op-brand-dark: #11100e;
            --op-accent: #9e2a2b; /* RPG rood / bloedrood */
            --op-gold: #d4af37; /* Echt goud/messing accent voor fantasy sfeer */
            --op-border: #3e3a35;
            --op-text: #eae5dad8; /* Zacht wit/perkament kleur voor tekst */
            --op-text-muted: #a8a095;
            --op-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--op-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--op-text);
            line-height: 1.6;
            padding: 60px 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .op-standalone-wrapper {
            max-width: 850px; /* Iets smaller voor een perfecte leesbare single-column scroll */
            width: 100%;
            margin: 0 auto;
            animation: opPageFadeIn 0.8s ease-out;
        }

        @keyframes opPageFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Branding Header */
        .op-brand-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .op-brand-badge {
            display: inline-block;
            background: var(--op-brand-dark);
            color: var(--op-gold);
            padding: 8px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            border: 1px solid var(--op-border);
            border-bottom: 3px solid var(--op-gold);
            border-radius: 4px;
        }

        /* Hero Banner */
        .op-hero-banner {
            background: linear-gradient(135deg, #0f0e0d 0%, #3a322a 100%);
            padding: 60px 40px;
            border-radius: 8px;
            border: 1px solid var(--op-border);
            border-bottom: 5px solid var(--op-gold);
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--op-shadow);
            text-align: center;
        }

        .op-hero-title {
            color: #fff;
            font-size: 40px;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.6);
        }

        .op-hero-subtitle {
            color: var(--op-gold);
            font-size: 16px;
            margin-top: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Single Column Scroll Cards */
        .op-card {
            background: var(--op-card-bg);
            padding: 40px;
            border-radius: 8px;
            box-shadow: var(--op-shadow);
            border: 1px solid var(--op-border);
            margin-bottom: 40px; /* Ruimte tussen het scrollen */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .op-card:hover {
            box-shadow: 0 15px 35px rgba(0,0,0,0.7);
            border-color: var(--op-gold);
        }

        .op-card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--op-border);
            padding-bottom: 15px;
        }

        .op-card-header i {
            font-size: 26px;
            color: var(--op-gold);
        }

        .op-card-header h3 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
            font-weight: 700;
        }

        .op-card p {
            font-size: 15px;
            line-height: 1.8;
            color: var(--op-text-muted);
            margin-bottom: 20px;
        }

        .op-card p:last-of-type {
            margin-bottom: 0;
        }

        /* Feature List & Blockquote */
        .op-feature-list {
            list-style: none;
            padding: 0;
            margin-top: 25px;
        }

        .op-feature-list li {
            font-size: 14px;
            padding: 12px 0;
            border-bottom: 1px solid var(--op-border);
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--op-text);
        }

        .op-feature-list li:last-child {
            border-bottom: none;
        }

        .op-feature-list li::before {
            content: '🎲';
            font-size: 12px;
        }

        .op-quote {
            border-left: 4px solid var(--op-accent);
            margin: 25px 0;
            font-style: italic;
            color: #fff;
            font-size: 15px;
            background: rgba(158, 42, 43, 0.15);
            padding: 20px;
            border-radius: 0 6px 6px 0;
            line-height: 1.7;
        }

        /* Media Elements */
        .op-video-wrapper {
            position: relative;
            width: 100%;
            height: 450px; /* Hoger en indrukwekkender nu het de volledige breedte pakt */
            border-radius: 6px;
            overflow: hidden;
            background: #000;
            border: 1px solid var(--op-border);
            margin-top: 15px;
        }

        .op-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Image Gallery (Nu overzichtelijk 4-op-een-rij of 2x2 op mobiel) */
        .op-gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .op-gallery-item {
            position: relative;
            border-radius: 6px;
            overflow: hidden;
            height: 150px;
            border: 1px solid var(--op-border);
            background: #111;
        }

        .op-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .op-gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            padding: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: flex-end;
        }

        .op-gallery-overlay span {
            color: var(--op-gold);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .op-gallery-item:hover img {
            transform: scale(1.1);
        }

        .op-gallery-item:hover .op-gallery-overlay {
            opacity: 1;
        }

        .op-caption {
            font-size: 13px;
            color: var(--op-text-muted);
            margin-top: 15px;
            font-style: italic;
            border-top: 1px dashed var(--op-border);
            padding-top: 12px;
        }

        /* Bottom USP Bar */
        .op-usp-bar {
            display: flex;
            justify-content: space-around;
            background: var(--op-brand-dark);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--op-shadow);
            border: 1px solid var(--op-border);
            border-bottom: 4px solid var(--op-accent);
        }

        .op-usp-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .op-usp-item i {
            color: var(--op-gold);
            font-size: 26px;
            animation: opFloat 4s ease-in-out infinite;
        }

        .op-usp-item:nth-child(2) i { animation-delay: 1s; }
        .op-usp-item:nth-child(3) i { animation-delay: 2s; }

        @keyframes opFloat {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0px); }
        }

        .op-usp-item span {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--op-text);
            letter-spacing: 1px;
        }

        /* Responsive Breakpoints */
        @media (max-width: 850px) {
            body { padding: 30px 15px; }
            .op-hero-banner { padding: 40px 20px; }
            .op-hero-title { font-size: 30px; }
            .op-card { padding: 25px; }
            .op-video-wrapper { height: 280px; }
            .op-gallery-grid { grid-template-columns: 1fr 1fr; }
            .op-usp-bar { flex-direction: column; gap: 30px; }
        }
    </style>
</head>
<body>

<div class="op-standalone-wrapper">
    <!-- Subtiele branding bovenaan -->
    <div class="op-brand-header">
        <span class="op-brand-badge">Accessible Printing Hub</span>
    </div>

    <!-- Hero Banner -->
    <div class="op-hero-banner">
        <h2 class="op-hero-title">Van idee naar de Speeltafel door 3D printer</h2>
        <p class="op-hero-subtitle">Jouw tabletop miniatures, professioneel geprint en direct aan huis geleverd.</p>
    </div>

    <!-- CARD 1: Het Probleem -->
    <div class="op-card">
        <div class="op-card-header">
            <i class="fa-solid fa-dragon"></i>
            <h3>Maximale Immersie, Minimale Hinder</h3>
        </div>
        <p>Dungeon Masters stoppen talloze uren in hun verhalen. Maar om die werelden écht tot leven te wekken, zijn miniatures en terreinstukken cruciaal. Het probleem? Een eigen, kwalitatieve 3D-printer aanschaffen is duur, complex in onderhoud en vereist gigantisch veel testtijd.</p>
        <p>Niet iedereen kan of wil deze investering doen, maar je wilt je spelers in je campagne natuurlijk wel die epische boss-fights en gedetailleerde weergaves bieden. Mini's maken het verhaal simpelweg compleet.</p>

        <ul class="op-feature-list">
            <li><strong>Kiezen uit een catalog:</strong>Kies je models uit een database vol files met allerlei figuren van draken tot de machtige Badgertaur</li>
            <li><strong>Geen technische kennis nodig:</strong>Laat je print wensen werkelijk worden zonder technische kennis hiervoor te hebben</li>
            <li><strong>Snel & Betrouwbaar:</strong> Upload je wensen, wij regelen de rest tot aan de voordeur.</li>
        </ul>
    </div>

    <!-- CARD 2: De Oplossing -->
    <div class="op-card">
        <div class="op-card-header">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <h3>On-Demand In Opdracht Geprint</h3>
        </div>
        <p>Dit probleem vormt de absolute kern van onze service. Onze webapp functioneert als jouw directe ingang naar een professionele productielijn. Geen onvoorspelbare marktplaatsen of wisselende hobbyisten, maar één vaste, dedicated print-expert die jouw opdrachten met uiterste precisie verwerkt.</p>

        <blockquote class="op-quote">
            "Vul je digitale shopping list met goblins, draken, muren of desnoods een heel dorp. Wij printen ze strak uit en sturen ze direct naar je op."
        </blockquote>

        <p>Of het nu gaat om robuuste FDM-scenery voor modulaire kerkers of ultra-gedetailleerde resin miniatures voor unieke hero-characters: jouw visie wordt door ons vakkundig omgezet in tastbaar plastic, klaar voor de speeltafel.</p>
    </div>

    <!-- CARD 3: Video Showcase -->
    <div class="op-card">
        <div class="op-card-header">
            <i class="fa-solid fa-fire-burner" style="color: var(--op-gold);"></i>
            <h3>Let it be printed</h3>
        </div>
        <div class="op-video-wrapper">
            <video autoplay muted loop playsinline class="op-video">
                <source src="{{ asset('videos/3d-dragonprint.mp4') }}" type="video/mp4">
                Je browser ondersteunt geen HTML5 video.
            </video>
        </div>
        <p class="op-caption">De opdrachten worden geprint in fdm fillament het materiaal om het goed en toch nog goedkoop te kunnen houden.
        Fillament is prima te gebruiken om te kunnen primen en verven</p>
    </div>

    <!-- CARD 4: Portfolio Galerij (Met jouw lokale public/portfolio bestanden) -->
    <div class="op-card">
        <div class="op-card-header">
            <i class="fa-solid fa-shield-cat" style="color: var(--op-accent);"></i>
            <h3>Geprinte Campagne Elementen</h3>
        </div>

        <div class="op-gallery-grid">
            <div class="op-gallery-item">
                <img src="{{ asset('portfolio/image1.jpg') }}" alt="Goblins & Monsters">
                <div class="op-gallery-overlay"><span>Space marines</span></div>
            </div>
            <div class="op-gallery-item">
                <img src="{{ asset('portfolio/image2.jpg') }}" alt="Modulaire Kerkermuren">
                <div class="op-gallery-overlay"><span>Barbarian</span></div>
            </div>
            <div class="op-gallery-item">
                <img src="{{ asset('portfolio/image3.jpg') }}" alt="Gedetailleerde Heroes">
                <div class="op-gallery-overlay"><span>Hero in church</span></div>
            </div>
            <div class="op-gallery-item">
                <img src="{{ asset('portfolio/image4.jpg') }}" alt="Volledige Dorpen">
                <div class="op-gallery-overlay"><span>Goblin leader</span></div>
            </div>
        </div>
        <p class="op-caption">Kwaliteitsvoorbeelden van fysieke miniatures geproduceerd met onze printer</p>
    </div>

    <!-- Onderste USP Bar -->
    <div class="op-usp-bar">
        <div class="op-usp-item">
            <i class="fa-solid fa-dice-d20"></i>
            <span>Gebouwd voor DM's</span>
        </div>
        <div class="op-usp-item">
            <i class="fa-solid fa-print"></i>
            <span>Door ervaren printers</span>
        </div>
        <div class="op-usp-item">
            <i class="fa-solid fa-box-open"></i>
            <span>Zorgvuldig Verpakt</span>
        </div>
    </div>
</div>

</body>
</html>
