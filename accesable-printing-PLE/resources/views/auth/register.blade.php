<div class="mp-page-wrapper">
    <div class="mp-container-narrow">

        <header class="mp-header-full">
            <div class="mp-header-content">
                <div class="mp-header-main-section">
                    <div class="mp-header-brand-line"></div>
                    <div class="mp-header-info">
                        <h1 class="mp-main-title">Smidse Registratie</h1>
                        <span class="mp-sub-text">Account aanmaken bij Accessible Printing</span>
                    </div>
                </div>
                <nav>
                    <a href="/" class="mp-btn-back" title="Terug naar Home">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </nav>
            </div>
        </header>

        <form method="POST" action="{{ route('register') }}" class="mp-form-stack" id="registerForm">
            @csrf

            <div class="mp-card">
                <h3 class="mp-section-header">Persoonlijke Gegevens</h3>
                <div class="mp-card-body">
                    <div class="mp-form-group">
                        <label class="mp-label" for="name">Naam</label>
                        <input id="name" class="mp-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Hoe mogen we je noemen?">
                        @error('name')
                        <small class="mp-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mp-form-group">
                        <label class="mp-label" for="email">Email Adres</label>
                        <input id="email" class="mp-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="je@email.nl">
                        @error('email')
                        <small class="mp-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mp-card">
                <h3 class="mp-section-header">Beveiliging</h3>
                <div class="mp-card-body">
                    <div class="mp-grid-2">
                        <div class="mp-form-group">
                            <label class="mp-label" for="password">Wachtwoord</label>
                            <input id="password" class="mp-input" type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                            @error('password')
                            <small class="mp-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mp-form-group">
                            <label class="mp-label" for="password_confirmation">Bevestig Wachtwoord</label>
                            <input id="password_confirmation" class="mp-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mp-card">
                <div class="mp-card-body">
                    <div class="mp-form-group" style="margin-bottom: 0; display: flex; align-items: flex-start; gap: 12px;">
                        <input type="checkbox" id="terms" name="terms" required class="mp-checkbox">
                        <label for="terms" class="mp-terms-label">
                            Ik ga akkoord met de <a href="#" id="openTerms" class="mp-gold-link">Algemene Voorwaarden</a>
                            en verklaar dat ik enkel modellen upload waarvoor ik de juiste licenties bezit.
                        </label>
                    </div>
                </div>
            </div>

            <div class="mp-form-actions">
                <a class="mp-link-back" href="{{ route('login') }}">
                    Al een account? <span class="mp-gold-text">Log in</span>
                </a>

                <button type="submit" class="mp-btn-submit" id="submitBtn">
                    Registreren
                </button>
            </div>
        </form>
    </div>
</div>

<div id="termsModal" class="mp-modal">
    <div class="mp-modal-content">
        <div class="mp-modal-header">
            <h2 class="mp-section-header" style="border: none; margin: 0; padding: 0;">Voorwaarden & Licenties</h2>
            <span class="mp-modal-close" id="closeTerms">&times;</span>
        </div>
        <div class="mp-modal-body">
            <div class="mp-terms-section">
                <h3>1. Verantwoordelijkheid Licenties</h3>
                <p>Door een model te uploaden, garandeert u dat u de rechtmatige eigenaar bent of over een geldige commerciële licentie beschikt. "Personal Use Only" bestanden mogen niet commercieel vervaardigd worden.</p>
            </div>

            <div class="mp-terms-section">
                <h3>2. Vrijwaring</h3>
                <p>Accessible Printing fungeert als uitvoerder. De gebruiker vrijwaart het platform van juridische claims voortvloeiend uit inbreuk op het auteursrecht door de gebruiker geüploade bestanden.</p>
            </div>

            <div class="mp-terms-section">
                <h3>3. Gegevensbescherming</h3>
                <p>Wij verplichten ons om digitale bestanden na verzending van de fysieke print direct en permanent te verwijderen. Uw data wordt nooit gedeeld met derden.</p>
            </div>

            <div class="mp-terms-section">
                <h3>4. levering en fdm toleranties</h3>
                <p>Omdat we on demand werken kan het een paar weken duren voordat de miniature verzonden en ontvagen is. dit hangt af van de order en drukte.
                Omdat wij met FDM (fillament) werken betekent dit dat de miniatures soms printrandjes hebben, dit is gebruikelijk bij fdm en kan geschuurd worden. Wij zien dit niet als gebreken.</p>
            </div>
        </div>
        <div class="mp-modal-footer">
            <button type="button" class="mp-btn-submit" id="closeTermsBtn" style="padding: 10px 25px; font-size: 13px;">Ik begrijp het</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById("termsModal");
        const openBtn = document.getElementById("openTerms");
        const closeX = document.getElementById("closeTerms");
        const closeBtn = document.getElementById("closeTermsBtn");
        const checkbox = document.getElementById("terms");
        const submitBtn = document.getElementById("submitBtn");

        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = "flex";
            document.body.style.overflow = "hidden";
        });

        const closeModal = () => {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        };

        closeX.addEventListener('click', closeModal);
        closeBtn.addEventListener('click', closeModal);

        window.onclick = function(event) {
            if (event.target == modal) { closeModal(); }
        }

        checkbox.addEventListener('change', function() {
            submitBtn.disabled = !this.checked;
        });

        submitBtn.disabled = !checkbox.checked;
    });
</script>

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

    body { margin: 0; padding: 0; background: var(--mp-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: var(--mp-text); }

    .mp-page-wrapper { min-height: 100vh; padding: 40px 20px; box-sizing: border-box; }
    .mp-container-narrow { max-width: 650px; margin: 0 auto; }

    /* Header (Consistent met Welcome & Showcase) */
    .mp-header-full {
        background: var(--mp-header-bg);
        border-bottom: 4px solid var(--mp-gold);
        padding: 15px 25px;
        border-radius: 4px 4px 0 0;
        box-shadow: var(--mp-shadow);
    }
    .mp-header-content { display: flex; justify-content: space-between; align-items: center; }
    .mp-header-main-section { display: flex; align-items: center; }
    .mp-header-brand-line { width: 3px; height: 35px; background: var(--mp-gold); margin-right: 15px; }
    .mp-main-title { font-size: 18px; font-weight: 800; color: #f1ede4; margin: 0; letter-spacing: 0.5px; text-transform: uppercase; }
    .mp-sub-text { font-size: 10px; color: var(--mp-gold); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }

    .mp-btn-back { color: #f1ede4; font-size: 18px; transition: 0.3s; }
    .mp-btn-back:hover { color: var(--mp-gold); }

    /* Card Styling */
    .mp-card { background: var(--mp-card-bg); border-radius: 0; border: 1px solid var(--mp-border); border-top: none; margin-bottom: 0; }
    .mp-card:last-of-type { border-radius: 0 0 4px 4px; box-shadow: var(--mp-shadow); }
    .mp-card-body { padding: 25px; }

    .mp-section-header {
        font-size: 14px;
        color: var(--mp-accent);
        text-transform: uppercase;
        font-weight: 800;
        margin: 0;
        padding: 15px 25px;
        background: #f9f9f9;
        border-bottom: 1px solid var(--mp-border);
        letter-spacing: 1px;
    }

    /* Form Elements */
    .mp-form-group { margin-bottom: 20px; }
    .mp-label { display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px; color: var(--mp-text); text-transform: uppercase; }
    .mp-input {
        width: 100%; padding: 12px; border: 1px solid var(--mp-border);
        border-radius: 2px; font-size: 14px; box-sizing: border-box;
        transition: 0.2s; background: #fafafa;
    }
    .mp-input:focus { outline: none; border-color: var(--mp-gold); background: white; }

    .mp-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    .mp-error { color: var(--mp-accent); margin-top: 5px; display: block; font-size: 11px; font-weight: 700; }

    .mp-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: var(--mp-accent); }
    .mp-terms-label { font-size: 13px; color: var(--mp-text-muted); line-height: 1.4; cursor: pointer; }
    .mp-gold-link { color: var(--mp-gold); font-weight: 700; text-decoration: underline; }

    /* Actions */
    .mp-form-actions { display: flex; justify-content: space-between; align-items: center; padding: 25px 5px; }
    .mp-link-back { font-size: 12px; color: var(--mp-text-muted); text-decoration: none; font-weight: 600; text-transform: uppercase; }
    .mp-gold-text { color: var(--mp-gold); }

    .mp-btn-submit {
        background: var(--mp-accent); color: white; border: none; padding: 12px 35px;
        border-radius: 2px; font-size: 13px; font-weight: 800; cursor: pointer;
        transition: 0.3s; text-transform: uppercase; letter-spacing: 1px;
    }
    .mp-btn-submit:hover { background: #5a2020; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
    .mp-btn-submit:disabled { background: #ccc; cursor: not-allowed; transform: none; box-shadow: none; }

    /* Modal */
    .mp-modal {
        display: none; position: fixed; z-index: 9999; left: 0; top: 0;
        width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);
        backdrop-filter: blur(5px); align-items: center; justify-content: center;
    }
    .mp-modal-content {
        background-color: white; border-radius: 4px; width: 90%; max-width: 500px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5); border-top: 4px solid var(--mp-gold);
    }
    .mp-modal-header { padding: 20px 25px; border-bottom: 1px solid var(--p-border); display: flex; justify-content: space-between; align-items: center; background: #f9f9f9; }
    .mp-modal-close { font-size: 24px; color: var(--mp-text-muted); cursor: pointer; }
    .mp-modal-body { padding: 25px; max-height: 60vh; overflow-y: auto; font-size: 14px; line-height: 1.6; }
    .mp-terms-section h3 { font-size: 14px; font-weight: 800; color: var(--mp-accent); margin-bottom: 8px; text-transform: uppercase; }
    .mp-modal-footer { padding: 15px 25px; border-top: 1px solid var(--mp-border); text-align: right; }

    @media (max-width: 600px) {
        .mp-grid-2 { grid-template-columns: 1fr; }
        .mp-form-actions { flex-direction: column; gap: 20px; text-align: center; }
    }
</style>
