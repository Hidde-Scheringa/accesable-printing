<div class="mp-page-wrapper">
    <div class="mp-container-narrow">

        <header class="mp-header-full">
            <div class="mp-header-content">
                <div class="mp-header-main-section">
                    <div class="mp-header-brand-line"></div>
                    <div class="mp-header-info">
                        <h1 class="mp-main-title">Smidse Login</h1>
                        <span class="mp-sub-text">Toegang tot Accessible Printing</span>
                    </div>
                </div>
                <nav>
                    <a href="/" class="mp-btn-back" title="Terug naar Home">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </nav>
            </div>
        </header>

        @if (session('status'))
            <div class="mp-status-alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mp-form-stack">
            @csrf

            <div class="mp-card">
                <h3 class="mp-section-header">Inloggen</h3>
                <div class="mp-card-body">
                    <div class="mp-form-group">
                        <label class="mp-label" for="email">Email Adres</label>
                        <input id="email" class="mp-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="je@email.nl">
                        @error('email')
                        <small class="mp-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mp-form-group">
                        <label class="mp-label" for="password">Wachtwoord</label>
                        <input id="password" class="mp-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                        @error('password')
                        <small class="mp-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mp-form-group-checkbox">
                        <input id="remember_me" type="checkbox" name="remember" class="mp-checkbox">
                        <label for="remember_me" class="mp-terms-label">Onthoud mij</label>
                    </div>
                </div>
            </div>

            <div class="mp-form-actions">
                <div class="mp-links-column">
                    <a class="mp-link-nav" href="{{ route('register') }}">
                        Geen account? <span class="mp-gold-text">Registreren</span>
                    </a>
                    @if (Route::has('password.request'))
                        <a class="mp-link-nav" href="{{ route('password.request') }}" style="margin-top: 5px; opacity: 0.8;">
                            Wachtwoord vergeten?
                        </a>
                    @endif
                </div>

                <button type="submit" class="mp-btn-submit">
                    Inloggen
                </button>
            </div>
        </form>
    </div>
</div>

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

    .mp-page-wrapper { min-height: 100vh; padding: 60px 20px; box-sizing: border-box; }
    .mp-container-narrow { max-width: 500px; margin: 0 auto; }

    /* Header */
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

    /* Status Alert */
    .mp-status-alert {
        background: #f1ede4;
        border-left: 4px solid var(--mp-gold);
        padding: 15px;
        margin-bottom: 20px;
        font-size: 13px;
        font-weight: 600;
        color: var(--mp-text);
        box-shadow: var(--mp-shadow);
    }

    /* Card */
    .mp-card { background: var(--mp-card-bg); border-radius: 0 0 4px 4px; border: 1px solid var(--mp-border); border-top: none; box-shadow: var(--mp-shadow); }
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
    .mp-form-group-checkbox { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
    .mp-label { display: block; font-size: 11px; font-weight: 800; margin-bottom: 8px; color: var(--mp-text); text-transform: uppercase; }
    .mp-input {
        width: 100%; padding: 12px; border: 1px solid var(--mp-border);
        border-radius: 2px; font-size: 14px; box-sizing: border-box;
        transition: 0.2s; background: #fafafa;
    }
    .mp-input:focus { outline: none; border-color: var(--mp-gold); background: white; }

    .mp-error { color: var(--mp-accent); margin-top: 5px; display: block; font-size: 11px; font-weight: 700; }

    .mp-checkbox { width: 16px; height: 16px; cursor: pointer; accent-color: var(--mp-accent); }
    .mp-terms-label { font-size: 13px; color: var(--mp-text-muted); font-weight: 600; cursor: pointer; }

    /* Actions */
    .mp-form-actions { display: flex; justify-content: space-between; align-items: center; padding: 25px 5px; }
    .mp-links-column { display: flex; flex-direction: column; }
    .mp-link-nav { font-size: 11px; color: var(--mp-text-muted); text-decoration: none; font-weight: 600; text-transform: uppercase; transition: 0.2s; }
    .mp-link-nav:hover { color: var(--mp-text); }
    .mp-gold-text { color: var(--mp-gold); }

    .mp-btn-submit {
        background: var(--mp-accent); color: white; border: none; padding: 12px 35px;
        border-radius: 2px; font-size: 13px; font-weight: 800; cursor: pointer;
        transition: 0.3s; text-transform: uppercase; letter-spacing: 1px;
    }
    .mp-btn-submit:hover { background: #5a2020; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }

    @media (max-width: 500px) {
        .mp-form-actions { flex-direction: column-reverse; gap: 20px; text-align: center; }
        .mp-links-column { align-items: center; }
    }
</style>
