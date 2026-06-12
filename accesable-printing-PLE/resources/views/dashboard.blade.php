<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/STLLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<div class="mp-page-wrapper">
    <header class="mp-header-card">
        <div class="mp-header-inner-wrap">
            <div class="mp-header-main-section">
                <div class="mp-header-brand-line"></div>
                <div class="mp-header-info">
                    <h1 class="mp-main-title">Accessible Printing</h1>
                    <span class="mp-sub-text">Uw specialist in hoogwaardige 3D-prints</span>
                </div>
            </div>

            <div class="mp-header-actions">
                <div class="mp-action-group-secondary">
                    <form method="POST" action="{{ route('logout') }}" class="mp-inline-form">
                        @csrf
                        <button type="submit" class="mp-btn-secondary" style="border-color: rgba(255,255,255,0.2); color: #b5b0a5;">Uitloggen</button>
                    </form>
                </div>

                <div class="mp-action-group-primary">
                    <a href="{{ route('requests.create') }}" class="mp-btn-action">
                        <span>+</span> Nieuw printverzoek
                    </a>
                    <a href="{{ route('catalog.create') }}" class="mp-btn-action" style="background: var(--mp-gold); color: #2d2a26;">
                        <span>+</span> Nieuw Catalogus Item
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="mp-container">
        <div class="mp-layout-grid">
            <aside class="mp-sidebar">
                <div class="mp-filter-box">
                    <h3 class="mp-filter-header">Navigatie</h3>
                    <div class="mp-filter-section">
                        <ul class="mp-link-list">
                            <li class="mp-link-item active">🏠 Dashboard</li>
                            <li class="mp-link-item" onclick="window.location='{{ route('catalog.index') }}'">📖 Model Catalogus</li>
                            <li class="mp-link-item" onclick="window.location='{{ route('catalog.selection') }}'">🛒 Mijn Selectie ({{ count(session('print_selection', [])) }})</li>
                        </ul>
                    </div>
                </div>

                <div class="mp-filter-box" style="margin-top: 20px;">
                    <h3 class="mp-filter-header">Status Filter</h3>
                    <div class="mp-filter-section">
                        <ul class="mp-link-list">
                            <li class="mp-link-item active">Alle aanvragen ({{ Auth::user()->requests->count() }})</li>
                            <li class="mp-link-item text-muted">Wacht op betaling ({{ Auth::user()->requests->where('payment_status', 'pending')->count() }})</li>
                            <li class="mp-link-item text-muted">In productie ({{ Auth::user()->requests->where('payment_status', 'escrow')->count() }})</li>
                        </ul>
                    </div>
                </div>
            </aside>

            <main class="mp-content">
                @if(session('success'))
                    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #bbf7d0; font-size: 14px; font-weight: bold;">
                        <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="background: #fde8e8; color: #9b1c1c; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f8b4b4; font-size: 14px; font-weight: bold;">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                    </div>
                @endif

                <div class="mp-list-stack">
                    @forelse(Auth::user()->requests()->orderBy('created_at', 'desc')->get() as $request)
                        @php
                            $files = is_array($request->stl_files) ? $request->stl_files : json_decode($request->stl_files, true);
                            $currentStatus = strtolower($request->status ?? 'pending');
                        @endphp
                        <article class="mp-item-card">
                            <div class="mp-item-visual-container">
                                <div class="mp-item-visual">
                                    @if(count($files) > 0)
                                        <div class="stl-viewer"
                                             id="viewer-{{ $request->id }}"
                                             data-url="{{ isset($files[0]['path']) ? asset('storage/' . $files[0]['path']) : '' }}">
                                            @if(!isset($files[0]['path']))
                                                <div class="stl-loader-spinner">GEEN PREVIEW</div>
                                            @else
                                                <div class="stl-loader-spinner">Laden...</div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mp-visual-inner">
                                            <span style="font-size: 24px; opacity: 0.3;">NO PREVIEW</span>
                                        </div>
                                    @endif
                                </div>

                                @if(count($files) > 1)
                                    <div class="mp-file-switcher">
                                        @foreach($files as $index => $file)
                                            <button type="button"
                                                    class="mp-switch-btn {{ $index === 0 ? 'active' : '' }}"
                                                    onclick="switchSTL('{{ $request->id }}', '{{ isset($file['path']) ? asset('storage/' . $file['path']) : '' }}', this)">
                                                {{ \Illuminate\Support\Str::limit($file['title'] ?? 'Model ' . ($index + 1), 12) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="mp-item-details">
                                <div class="mp-item-top">
                                    <div class="mp-title-group">
                                        <h2 class="mp-item-name">{{ $request->title }}</h2>
                                        <p class="mp-item-desc">{{ $request->description }}</p>
                                    </div>
                                    <div class="mp-item-price-tag">
                                        <small>TOTAALPRIJS</small>
                                        <span class="mp-price-value">€ {{ number_format($request->total_price ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="mp-dimensions-grid">
                                    <span class="mp-dim-header">Geconfigureerde Formaten:</span>
                                    @foreach($files as $index => $file)
                                        @php
                                            $scale = ($file['scale'] ?? 100) / 100;
                                            $qty = $file['quantity'] ?? 1;

                                            if (isset($file['from_catalog']) && $file['from_catalog']) {
                                                // Gebruik de _cm waarden direct als ze bestaan (want die zijn al geschaald in de controller)
                                                // Als ze niet bestaan, pak de basis (x/10) en pas dan pas de schaal toe
                                                $b_val = $file['x_cm'] ?? (($file['x'] ?? 0) / 10 * ($file['scale'] / 100));
                                                $h_val = $file['y_cm'] ?? (($file['y'] ?? 0) / 10 * ($file['scale'] / 100));
                                                $d_val = $file['z_cm'] ?? (($file['z'] ?? 0) / 10 * ($file['scale'] / 100));
                                            } else {
                                                $h_val = (float)($file['h'] ?? 0);
                                                $b_val = (float)($file['b'] ?? 0);
                                                $d_val = (float)($file['d'] ?? 0);
                                            }

                                            $displayName = $file['title'] ?? $file['original_name'] ?? ('Model ' . ($index + 1));
                                        @endphp

                                        <div class="dimension-row">
                                            <span class="dim-name">
                                                {{ \Illuminate\Support\Str::limit($displayName, 30) }}
                                                <span style="color: var(--mp-gold); margin-left: 5px;">({{ $qty }}x)</span>:
                                            </span>
                                                                                <span class="dim-values">
                                                <b>H:</b> {{ number_format($h_val, 2, ',', '.') }}cm |
                                                <b>B:</b> {{ number_format($b_val, 2, ',', '.') }}cm |
                                                <b>D:</b> {{ number_format($d_val, 2, ',', '.') }}cm
                                                <small>({{ $file['scale'] ?? 100 }}%)</small>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mp-item-specs">
                                    <span class="mp-tag">📍 {{ $request->city }}</span>
                                    <span class="mp-tag">Kleur: {{ $request->color }}</span>
                                    <span class="mp-tag">Materiaal: {{ $request->material }}</span>

                                    {{-- DYNAMISCHE BETALING STATUS TAGS --}}
                                    @if($request->payment_status === 'paid')
                                        <span class="mp-tag" style="background: #dcfce7; color: #166534; border-color: #bbf7d0;">
                                            <i class="fa-solid fa-circle-check"></i> GEVERIFIEERD & AFGEROND
                                        </span>
                                    @elseif($request->payment_status === 'escrow')
                                        <span class="mp-tag" style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
                                            <i class="fa-solid fa-shield-halved"></i> PLATFORM BEHEER (VEILIG)
                                        </span>
                                    @elseif($request->payment_status === 'pending')
                                        <span class="mp-tag" style="background: #fee2e2; color: #991b1b; border-color: #fca5a5;">
                                            <i class="fa-solid fa-clock"></i> WACHT OP BETALING
                                        </span>
                                    @elseif($request->payment_status === 'disputed')
                                        <span class="mp-tag" style="background: #fff7ed; color: #c2410c; border-color: #fed7aa;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> CLAIM INGEDIEND
                                        </span>
                                    @elseif($request->payment_status === 'cancelled')
                                        <span class="mp-tag" style="background: #f3f4f6; color: #374151; border-color: #e5e7eb;">
                                            <i class="fa-solid fa-ban"></i> GEANNULEERD
                                        </span>
                                    @elseif($request->payment_status === 'refunded')
                                        <span class="mp-tag" style="background: #dcfce7; color: #166534; border-color: #bbf7d0;">
                                            <i class="fa-solid fa-money-bill-transfer"></i>  Claim Terugestort
                                        </span>
                                    @endif

                                    <span class="mp-tag mp-tag-status">Productie: {{ strtoupper($request->status ?? 'In behandeling') }}</span>
                                </div>

                                <div class="mp-item-footer" style="display: flex; justify-content: space-between; align-items: flex-end;">
                                    <div class="mp-download-container">
                                        @foreach($files as $index => $file)
                                            @if(isset($file['path']))
                                                @if(isset($file['from_catalog']) && $file['from_catalog'])
                                                    <span class="mp-btn-download-styled mp-btn-disabled" title="Bestanden uit de catalogus zijn beveiligd">
                                                        <span class="icon">🔒</span> {{ \Illuminate\Support\Str::limit($file['title'] ?? 'Model ' . ($index + 1), 15) }}
                                                    </span>
                                                @else
                                                    <a href="{{ asset('storage/' . $file['path']) }}" class="mp-btn-download-styled" download>
                                                        <span class="icon">↓</span> {{ \Illuminate\Support\Str::limit($file['title'] ?? $file['original_name'] ?? 'Model ' . ($index + 1), 15) }}
                                                    </a>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>

                                    {{-- DYNAMISCHE ACTIEKNOPPEN OP BASIS VAN STATUS --}}
                                    <div class="mp-payment-action" style="margin-bottom: -5px;">
                                        @if($request->payment_status === 'cancelled')
                                            <span style="font-size: 12px; color: #64748b; font-weight: bold; font-style: italic;">
                                                <i class="fa-solid fa-ban"></i> Deze order is geannuleerd
                                            </span>
                                        @elseif($request->payment_status === 'disputed')
                                            <div style="background: #fff7ed; border-left: 3px solid #f97316; padding: 8px 12px; border-radius: 0 4px 4px 0; max-width: 320px;">
                                                <p style="font-size: 11px; color: #c2410c; text-align: left; margin: 0; line-height: 1.4;">
                                                    <i class="fa-solid fa-circle-info"></i> Je schadeclaim is ingediend. De admin beoordeelt de foto en verwerkt de terugbetaling.
                                                </p>
                                            </div>
                                        @elseif($request->payment_status === 'pending')
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <form action="{{ route('order.cancel', $request->id) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je dit printverzoek wilt annuleren?');">
                                                    @csrf
                                                    <button type="submit" class="mp-btn-secondary" style="border-color: #ef4444; color: #ef4444; padding: 11px 15px; font-size: 12px;">
                                                        Annuleren
                                                    </button>
                                                </form>
                                                <a href="{{ route('payment.resume', $request->id) }}" class="mp-btn-action" style="background: var(--mp-gold); color: #2d2a26; padding: 10px 15px;">
                                                    <i class="fa-solid fa-credit-card"></i> Nu Betalen
                                                </a>
                                            </div>
                                        @elseif($request->payment_status === 'escrow')
                                            @if($currentStatus === 'pending' || $currentStatus === 'in_production')
                                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                                                    <div style="background: #fdfbf7; border-left: 3px solid var(--mp-gold); padding: 8px 12px; border-radius: 0 4px 4px 0; max-width: 320px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 5px;">
                                                        <p style="font-size: 11px; color: var(--mp-text-muted); text-align: left; margin: 0; line-height: 1.4;">
                                                            <i class="fa-solid fa-info-circle" style="color: var(--mp-gold); margin-right: 4px;"></i>
                                                            Je betaling staat veilig geparkeerd. De expert bereidt de productie voor.
                                                        </p>
                                                    </div>
                                                    <form action="{{ route('order.cancel', $request->id) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je dit printverzoek wilt annuleren? Je geld in escrow wordt teruggestort.');">
                                                        @csrf
                                                        <button type="submit" class="mp-btn-secondary" style="border-color: #ef4444; color: #ef4444; padding: 10px 15px; font-size: 12px;">
                                                            <i class="fa-solid fa-ban"></i> Order Annuleren
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($currentStatus === 'printing')
                                                <div style="background: #eff6ff; border-left: 3px solid #1d4ed8; padding: 8px 12px; border-radius: 0 4px 4px 0; max-width: 320px;">
                                                    <p style="font-size: 11px; color: #1e40af; text-align: left; margin: 0; line-height: 1.4;">
                                                        <i class="fa-solid fa-gears fa-spin"></i> De printers draaien op dit moment! Je kunt deze order niet meer annuleren.
                                                    </p>
                                                </div>
                                            @elseif($currentStatus === 'shipped')
                                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                                                    <div style="background: #fdfbf7; border-left: 3px solid var(--mp-gold); padding: 8px 12px; border-radius: 0 4px 4px 0; max-width: 320px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                        <p style="font-size: 11px; color: var(--mp-text-muted); text-align: left; margin: 0; line-height: 1.4;">
                                                            <i class="fa-solid fa-info-circle" style="color: var(--mp-gold); margin-right: 4px;"></i>
                                                            Het pakket is verzonden! Bevestig de ontvangst en geef het geld vrij of dien een schadeclaim in als er onderdelen defect zijn geraakt.
                                                        </p>
                                                    </div>

                                                    <div style="display: flex; gap: 8px; margin-top: 10px;">
                                                        <button type="button" onclick="document.getElementById('dispute-form-{{ $request->id }}').style.display='block';"
                                                                class="mp-btn-secondary" style="border-color: #f97316; color: #f97316;">
                                                            <i class="fa-solid fa-triangle-exclamation"></i> Defect melden
                                                        </button>

                                                        <form action="{{ route('order.approve', $request->id) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je de miniatures in goede orde hebt ontvangen?');">
                                                            @csrf
                                                            <button type="submit" class="mp-btn-action" style="background: #2e7d32; border: none; cursor: pointer;">
                                                                <i class="fa-solid fa-check-double"></i> Order goedkeuren
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <div id="dispute-form-{{ $request->id }}" class="mp-dispute-modal-wrap" style="display: none; margin-top: 15px; border: 1px solid var(--mp-border); padding: 15px; border-radius: 4px; background: #fffdf5;">
                                                        <h3 style="margin-top:0; font-size: 14px; color: #c2410c;">Schadeclaim indienen</h3>
                                                        <p style="font-size: 12px; color: #666;">Selecteer de defecte items en upload een bewijsfoto.</p>

                                                        <form action="{{ route('order.dispute', $request->id) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            @foreach($files as $file)
                                                                <div class="mp-dispute-item-row">
                                                                    <span>{{ \Illuminate\Support\Str::limit($file['title'], 20) }}</span>
                                                                    <input type="hidden" name="items[]" value="{{ $file['title'] }}">
                                                                    <input type="number" name="qtys[]" min="0" max="{{ $file['quantity'] }}" value="0" class="mp-dispute-input">
                                                                </div>
                                                            @endforeach

                                                            <textarea name="defect_reason" required class="mp-dispute-textarea" placeholder="Omschrijf het defect..."></textarea>
                                                            <label>Upload schadefoto's (meerdere selecteren mogelijk):</label>
                                                            <input type="file" name="defect_images[]" accept="image/*" multiple required>

                                                            <div style="display: flex; gap: 10px; margin-top: 10px;">
                                                                <button type="submit" class="mp-btn-action" style="background: #c2410c;">Claim verzenden</button>
                                                                <button type="button" class="mp-btn-secondary" onclick="document.getElementById('dispute-form-{{ $request->id }}').style.display='none';">Annuleren</button>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    {{-- INLINE DISPUTE FORMULIER --}}
                                                    <div id="dispute-form-{{ $request->id }}" style="display: none; margin-top: 10px; width: 100%; max-width: 350px; background: #fff; border: 1px solid var(--mp-border); padding: 15px; border-radius: 4px; text-align: left; box-shadow: var(--mp-shadow);">
                                                        <form action="{{ route('order.dispute', $request->id) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <div style="margin-bottom: 10px;">
                                                                <label style="display: block; font-size: 11px; font-weight: bold; margin-bottom: 4px;">Reden van defect / claim:</label>
                                                                <textarea name="defect_reason" required style="width: 100%; min-height: 60px; padding: 6px; font-size: 12px; border: 1px solid var(--mp-border); border-radius: 2px;" placeholder="Bijv: Afgebroken arm tijdens transport..."></textarea>
                                                            </div>
                                                            <div style="margin-bottom: 12px;">
                                                                <label style="display: block; font-size: 11px; font-weight: bold; margin-bottom: 4px;">Foto van de schade:</label>
                                                                <input type="file" name="defect_image" accept="image/*" required style="font-size: 11px;">
                                                            </div>
                                                            <div style="display: flex; gap: 5px;">
                                                                <button type="submit" class="mp-btn-action" style="background: var(--mp-accent); padding: 8px 12px; font-size: 11px;">Claim Indienen</button>
                                                                <button type="button" class="mp-btn-secondary" style="padding: 8px 12px; font-size: 11px;" onclick="this.parentElement.parentElement.parentElement.style.display='none';">Annuleren</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        @elseif($request->payment_status === 'paid')
                                            <span style="font-size: 12px; color: var(--mp-text-muted); font-weight: bold; font-style: italic;">
                                                <i class="fa-solid fa-circle-check" style="color: #2e7d32;"></i> Succesvol ontvangen & afgerond
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="mp-empty-card">
                            <p>U heeft momenteel geen actieve printverzoeken.</p>
                            <a href="{{ route('requests.create') }}" style="color: var(--mp-accent); font-weight: bold; text-decoration: none;">Klik hier om een nieuw project te starten.</a>
                        </div>
                    @endforelse
                </div>
            </main>
        </div>
    </div>
</div>

<style>
    :root {
        --mp-bg: #e7e7e7;
        --mp-card-bg: #ffffff;
        --mp-header-bg: #2d2a26;
        --mp-accent: #7c2d2d;
        --mp-accent-hover: #5a2020;
        --mp-gold: #b08d57;
        --mp-border: #dcd7cc;
        --mp-text: #2d2a26;
        --mp-text-muted: #706a64;
        --mp-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .mp-page-wrapper { background: var(--mp-bg); min-height: 100vh; padding-bottom: 40px; font-family: 'Segoe UI', sans-serif; color: var(--mp-text); }
    .mp-header-card { background: var(--mp-header-bg); padding: 30px 0; border-bottom: 4px solid var(--mp-gold); margin-bottom: 32px; box-shadow: var(--mp-shadow); width: 100%; }
    .mp-header-inner-wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
    .mp-container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
    .mp-header-main-section { display: flex; align-items: center; gap: 20px; }
    .mp-header-brand-line { width: 4px; height: 45px; background: var(--mp-gold); border-radius: 2px; }
    .mp-main-title { font-size: 26px; font-weight: 800; color: #f1ede4; margin: 0; letter-spacing: -0.5px; }
    .mp-sub-text { font-size: 13px; color: var(--mp-gold); font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }
    .mp-header-actions { display: flex; align-items: center; gap: 25px; }
    .mp-action-group-secondary { display: flex; align-items: center; gap: 10px; border-right: 1px solid rgba(255,255,255,0.1); padding-right: 25px; }
    .mp-action-group-primary { display: flex; align-items: center; gap: 12px; }
    .mp-btn-action { background: var(--mp-accent); color: #f1ede4; text-decoration: none; padding: 12px 20px; border-radius: 2px; font-weight: 700; font-size: 13px; transition: 0.2s; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
    .mp-btn-action:hover { background: var(--mp-accent-hover); transform: translateY(-1px); }
    .mp-btn-secondary { background: transparent; border: 1px solid var(--mp-gold); margin-top: 15px; padding: 10px 18px; border-radius: 2px; cursor: pointer; color: var(--mp-gold); font-size: 11px; font-weight: 700; text-transform: uppercase; text-decoration: none; transition: 0.2s; }
    .mp-layout-grid { display: grid; grid-template-columns: 240px 1fr; gap: 24px; }
    .mp-filter-box { background: var(--mp-card-bg); border-radius: 4px; padding: 20px; box-shadow: var(--mp-shadow); border-top: 4px solid var(--mp-gold); }
    .mp-filter-header { font-size: 15px; font-weight: 700; color: var(--mp-text); margin-bottom: 15px; text-transform: uppercase; }
    .mp-link-list { list-style: none; padding: 0; margin: 0; }
    .mp-link-item { font-size: 14px; padding: 8px 0; color: var(--mp-text-muted); cursor: pointer; border-bottom: 1px solid #f0efeb; transition: 0.2s; }
    .mp-link-item:hover { color: var(--mp-gold); padding-left: 5px; }
    .mp-link-item.active { color: var(--mp-accent); font-weight: 700; }
    .mp-item-card { background: var(--mp-card-bg); border-radius: 4px; display: flex; overflow: hidden; margin-bottom: 24px; box-shadow: var(--mp-shadow); }
    .mp-item-visual-container { width: 260px; background: #f7f6f2; border-right: 1px solid var(--mp-border); flex-shrink: 0; display: flex; flex-direction: column; }
    .mp-item-visual { width: 100%; height: 240px; position: relative; }
    .stl-viewer { width: 100%; height: 100%; }
    .mp-item-details { padding: 24px; flex-grow: 1; display: flex; flex-direction: column; }
    .mp-item-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
    .mp-item-name { font-size: 19px; font-weight: 700; border-left: 4px solid var(--mp-accent); padding-left: 12px; margin: 0; }
    .mp-item-desc { font-size: 13px; color: var(--mp-text-muted); margin: 10px 0; line-height: 1.5; }
    .mp-item-price-tag { text-align: right; }
    .mp-item-price-tag small { font-size: 9px; font-weight: 800; color: var(--mp-gold); letter-spacing: 1px; display: block; }
    .mp-price-value { font-size: 24px; font-weight: 900; color: var(--mp-accent); }
    .mp-dimensions-grid { background: #f8f7f2; border: 1px solid var(--mp-border); padding: 12px; border-radius: 2px; margin-bottom: 15px; }
    .mp-dim-header { font-size: 10px; font-weight: 800; color: var(--mp-text-muted); text-transform: uppercase; display: block; margin-bottom: 8px; }
    .dimension-row { font-size: 12px; margin-bottom: 4px; border-bottom: 1px solid rgba(0,0,0,0.03); padding-bottom: 4px; }
    .dimension-row:last-child { border-bottom: none; }
    .dim-name { font-weight: 700; color: var(--mp-text); margin-right: 8px; }
    .dim-values { color: var(--mp-text-muted); }
    .dim-values b { color: var(--mp-accent); }
    .dim-values small { color: var(--mp-gold); font-size: 10px; margin-left: 5px; }
    .mp-item-specs { display: flex; gap: 10px; padding-top: 10px; flex-wrap: wrap; }
    .mp-tag { background: #f1ede4; padding: 4px 10px; font-size: 11px; font-weight: 700; border: 1px solid var(--mp-border); border-radius: 2px; }
    .mp-tag-status { background: var(--mp-accent); color: white; border: none; }
    .mp-download-container { display: flex; gap: 8px; flex-wrap: wrap; padding-top: 15px; margin-top: auto; }
    .mp-btn-download-styled { display: inline-flex; align-items: center; background: #fff; border: 1px solid var(--mp-border); color: var(--mp-text); padding: 6px 12px; border-radius: 2px; font-size: 11px; font-weight: 700; text-decoration: none; text-transform: uppercase; transition: 0.2s ease; }
    .mp-btn-download-styled:not(.mp-btn-disabled):hover { border-color: var(--mp-gold); background: #f8f7f2; transform: translateY(-1px); }
    .mp-btn-disabled { background: #f0efeb; color: #b5b0a5; cursor: not-allowed; opacity: 0.7; border-style: dashed; }
    .mp-file-switcher { display: flex; gap: 0; padding: 0; background: #edeae4; justify-content: center; margin-top: auto; }
    .mp-switch-btn { flex: 1; background: white; border: 1px solid var(--mp-border); font-size: 10px; padding: 8px 5px; cursor: pointer; border-bottom: none; }
    .mp-switch-btn.active { background: var(--mp-gold); color: white; }
    .stl-loader-spinner { height: 100%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--mp-text-muted); }

    /* Dispute Form Styling */
    .mp-dispute-modal-wrap {
        background: #fffdf5;
        border: 1px solid #fde68a;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .mp-dispute-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        padding: 6px 10px;
        margin-bottom: 5px;
        border: 1px solid #eee;
        font-size: 12px;
    }

    .mp-dispute-input {
        width: 50px;
        padding: 2px 5px;
        border: 1px solid #ccc;
        text-align: center;
    }

    .mp-dispute-textarea {
        width: 100%;
        height: 70px;
        padding: 8px;
        margin-top: 10px;
        border: 1px solid #ccc;
        font-size: 12px;
        resize: vertical;
    }

    @media (max-width: 850px) {
        .mp-layout-grid { grid-template-columns: 1fr; }
        .mp-item-card { flex-direction: column; }
        .mp-item-visual-container { width: 100%; }
        .mp-header-inner-wrap { flex-direction: column; gap: 20px; text-align: center; }
        .mp-header-actions { flex-direction: column; gap: 15px; }
        .mp-action-group-secondary { border-right: none; padding-right: 0; }
    }
</style>

<script>
    const viewerInstances = {};

    function initSTLViewer(container) {
        const id = container.id;
        const url = container.getAttribute('data-url');
        if(!url || url === '') return;

        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf7f6f2);

        const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(container.clientWidth, container.clientHeight);

        container.innerHTML = '';
        container.appendChild(renderer.domElement);

        scene.add(new THREE.HemisphereLight(0xffffff, 0x444444, 1.2));
        const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
        dirLight.position.set(5, 10, 7.5);
        scene.add(dirLight);

        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.autoRotate = true;

        const loader = new THREE.STLLoader();
        viewerInstances[id] = { scene, camera, renderer, controls, loader, currentMesh: null };

        loadModel(id, url);

        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }
        animate();
    }

    function loadModel(viewerId, url) {
        const inst = viewerInstances[viewerId];
        if(!inst || !url) return;

        inst.loader.load(url, function(geometry) {
            if(inst.currentMesh) inst.scene.remove(inst.currentMesh);

            geometry.computeBoundingBox();
            const center = new THREE.Vector3();
            geometry.boundingBox.getCenter(center);
            geometry.translate(-center.x, -center.y, -center.z);

            const material = new THREE.MeshPhongMaterial({ color: 0x5a554f, specular: 0x222222, shininess: 30 });
            const mesh = new THREE.Mesh(geometry, material);
            mesh.rotation.x = -Math.PI / 2;

            inst.scene.add(mesh);
            inst.currentMesh = mesh;

            const box = new THREE.Box3().setFromObject(mesh);
            const size = box.getSize(new THREE.Vector3()).length();
            inst.camera.position.set(size * 1.2, size * 1.2, size * 1.2);
            inst.controls.update();
        });
    }

    function switchSTL(requestId, url, btn) {
        const viewerId = 'viewer-' + requestId;
        btn.parentElement.querySelectorAll('.mp-switch-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        loadModel(viewerId, url);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.stl-viewer').forEach(div => initSTLViewer(div));
    });
</script>
