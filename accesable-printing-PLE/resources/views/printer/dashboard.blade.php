<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Dashboard | Accessible Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/STLLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
</head>
<body>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="brand-title">
                <i class="fa-solid fa-cube"></i> Printer Panel
            </div>
            <nav>
                <p class="nav-label">Beheer</p>
                <a href="{{ route('printer.dashboard') }}" class="nav-link active">
                    <i class="fa-solid fa-list-check"></i> Alle Aanvragen
                </a>
                <a href="{{ route('welcome') }}" class="nav-link">
                    <i class="fa-solid fa-house"></i> Terug naar Site
                </a>
            </nav>
        </div>

        <div class="sidebar-bottom">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); this.closest('form').submit();"
                   class="nav-link logout-link">
                    <i class="fa-solid fa-right-from-bracket"></i> Uitloggen
                </a>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="content-header">
            <h1>Print Aanvragen</h1>
            <p>Beheer binnengekomen projecten, betalingen en logistieke gegevens.</p>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <h4>Totaal Aanvragen</h4>
                <p>{{ $allRequests->count() }}</p>
            </div>
            <div class="stat-card pending">
                <h4>Wacht op Betaling</h4>
                <p>{{ $allRequests->where('payment_status', 'pending')->count() }}</p>
            </div>
            <div class="stat-card" style="border-left-color: #166534;">
                <h4>voltooide orders</h4>
                <p>{{ $allRequests->where('payment_status', 'escrow')->count() + $allRequests->where('payment_status', 'paid')->count() }}</p>
            </div>
        </div>

        @if(session('success'))
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0; font-weight: bold;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background: #fde8e8; color: #9b1c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f8b4b4; font-weight: bold;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>Inspectie (Modellen)</th>
                    <th>Klant & Volledig Adres</th>
                    <th>Project & Afmetingen</th>
                    <th>Specificaties & Financiën</th>
                    <th>Productie Status Acties</th>
                </tr>
                </thead>
                <tbody>
                @forelse($allRequests as $request)
                    @php
                        $files = is_string($request->stl_files) ? json_decode($request->stl_files, true) : $request->stl_files;
                        $currentStatus = strtolower($request->status ?? 'pending');
                    @endphp
                    <tr style="@if($request->payment_status === 'disputed') background-color: #fffaf0; @endif">
                        <td style="width: 140px;">
                            @if(is_array($files) && count($files) > 0)
                                <div class="preview-btn-stack" style="display: flex; flex-direction: column; gap: 5px;">
                                    @foreach($files as $index => $file)
                                        @if(isset($file['path']))
                                            <button class="mini-preview-btn" onclick="openStlModal('{{ asset('storage/' . $file['path']) }}', '{{ $file['title'] ?? $file['original_name'] ?? 'Model ' . ($index + 1) }}')">
                                                <i class="fa-solid fa-eye"></i>
                                                <span>{{ \Illuminate\Support\Str::limit($file['title'] ?? $file['original_name'] ?? 'Model ' . ($index + 1), 12) }}</span>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <span class="no-files">Geen modellen</span>
                            @endif

                            @if($request->stl_files)
                                <div class="mp-download-container" style="display: flex; flex-direction: column; gap: 4px; margin-top: 15px;">
                                    <small style="color: #999; font-weight: bold; font-size: 9px; text-transform: uppercase;">Downloads:</small>
                                    @foreach($files as $index => $file)
                                        @php $displayName = $file['original_name'] ?? $file['title'] ?? 'Model ' . ($index + 1); @endphp
                                        <a href="{{ asset('storage/' . $file['path']) }}" class="mp-btn-download-styled" download="{{ $displayName }}">
                                            <span class="icon">↓</span> {{ \Illuminate\Support\Str::limit($displayName, 12) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="user-info">
                                <span class="user-name" style="font-size: 16px; font-weight: 800; color: var(--p-dark);">{{ $request->user->name }}</span>

                                <div class="address-details" style="margin-top: 8px; font-size: 13px; line-height: 1.5; color: #444; background: #fdfdfd; padding: 10px; border-radius: 6px; border: 1px solid #eee; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
                                    <div style="margin-bottom: 4px;">
                                        <i class="fa-solid fa-map-location-dot" style="color: var(--p-gold); margin-right: 6px; width: 15px;"></i>
                                        <strong>{{ $request->street }} {{ $request->streetnumber }}</strong>
                                    </div>
                                    <div style="padding-left: 25px;">
                                        {{ $request->zipcode }}<br>
                                        {{ $request->city }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $request->title }}</strong>
                            <p style="font-size: 12px; color: #666; margin: 4px 0 10px 0;">{{ $request->description }}</p>

                            <div class="model-dimensions-info" style="margin-top: 10px; background: #fcfaf7; padding: 8px; border-radius: 4px; border: 1px solid #f0efeb;">
                                <small style="display:block; color: var(--p-gold); font-weight: bold; font-size: 9px; text-transform: uppercase; margin-bottom: 5px;">Geconfigureerde Formaten & Aantallen:</small>
                                @if(is_array($files))
                                    @foreach($files as $index => $file)
                                        @php
                                            $scale = ($file['scale'] ?? 100) / 100;
                                            $qty = $file['quantity'] ?? 1;

                                            if (isset($file['h'])) {
                                                $h_val = (float)$file['h'];
                                                $b_val = (float)$file['b'];
                                                $d_val = (float)$file['d'];
                                            }
                                            elseif (isset($file['dimensions'])) {
                                                $h_val = (($file['dimensions']['z'] ?? 0) / 10) * $scale;
                                                $b_val = (($file['dimensions']['x'] ?? 0) / 10) * $scale;
                                                $d_val = (($file['dimensions']['y'] ?? 0) / 10) * $scale;
                                            }
                                            elseif (isset($file['x'])) {
                                                $h_val = (($file['z'] ?? 0) / 10) * $scale;
                                                $b_val = (($file['x'] ?? 0) / 10) * $scale;
                                                $d_val = (($file['y'] ?? 0) / 10) * $scale;
                                            }
                                            else {
                                                $h_val = $b_val = $d_val = '?';
                                            }

                                            $h = is_numeric($h_val) ? number_format($h_val, 2, ',', '.') : $h_val;
                                            $b = is_numeric($b_val) ? number_format($b_val, 2, ',', '.') : $b_val;
                                            $d = is_numeric($d_val) ? number_format($d_val, 2, ',', '.') : $d_val;
                                            $displayName = $file['title'] ?? $file['original_name'] ?? 'Model ' . ($index + 1);
                                        @endphp
                                        <div style="font-size: 11px; margin-bottom: 5px; color: #555; border-bottom: 1px solid #f0f0f0; padding-bottom: 3px;">
                                            <div style="font-weight: bold; color: var(--p-dark);">
                                                {{ \Illuminate\Support\Str::limit($displayName, 25) }}
                                                <span style="color: var(--p-gold); font-size: 12px; margin-left: 4px;">({{ $qty }} stuks)</span>
                                            </div>
                                            <span style="color: var(--p-accent);">H:</span> {{ $h }}cm |
                                            <span style="color: var(--p-accent);">B:</span> {{ $b }}cm |
                                            <span style="color: var(--p-accent);">D:</span> {{ $d }}cm
                                            <span style="color: #aaa;">({{ $file['scale'] ?? 100 }}% Schaal)</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="tag {{ strtolower($request->material) == 'resin' ? 'tag-resin' : 'tag-fdm' }}">
                                {{ $request->material }}
                            </span>
                            <span class="tag tag-color">{{ $request->color }}</span>

                            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #ddd;">
                                <small style="color: #999; display: block; font-size: 10px; font-weight: bold; text-transform: uppercase;">Betaalstatus & Prijs:</small>

                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                                    @if($request->payment_status === 'paid')
                                        <span class="tag" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; margin:0;">
                                            <i class="fa-solid fa-circle-check"></i> VOLTOOID
                                        </span>
                                    @elseif($request->payment_status === 'escrow')
                                        <span class="tag" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; margin:0;">
                                            <i class="fa-solid fa-shield-halved"></i> ESCROW VAST
                                        </span>
                                    @elseif($request->payment_status === 'pending')
                                        <span class="tag" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; margin:0;">
                                            <i class="fa-solid fa-clock"></i> AFWACHTING
                                        </span>
                                    @elseif($request->payment_status === 'disputed')
                                        <span class="tag" style="background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; margin:0;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> DISPUTE / CLAIM
                                        </span>
                                    @elseif($request->payment_status === 'cancelled')
                                        <span class="tag" style="background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; margin:0;">
                                            <i class="fa-solid fa-ban"></i> GEANNULEERD
                                        </span>
                                    @endif

                                    <strong style="color: var(--p-accent); font-size: 16px;">
                                        € {{ number_format($request->total_price, 2, ',', '.') }}
                                    </strong>
                                </div>

                                {{-- COMPONENT: BIJ DEFECT/DISPUTE DE REDEN EN FOTO INLINE TONEN --}}
                                @if($request->payment_status === 'disputed')
                                    <div style="margin-top: 10px; background: #fff5f5; border: 1px solid #fecaca; padding: 10px; border-radius: 4px;">
                                        <strong style="font-size: 11px; color: #991b1b; display: block; text-transform: uppercase;">⚠️ Klant meldt defect:</strong>
                                        <p style="font-size: 12px; margin: 5px 0; color: #333; line-height: 1.4;">
                                            "{{ $request->defect_reason ?? 'Geen reden opgegeven' }}"
                                        </p>
                                        @if($request->defect_image_path)
                                            <a href="{{ asset('storage/' . $request->defect_image_path) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; color: var(--p-accent); font-weight: bold; text-decoration: underline; margin-bottom: 10px;">
                                                <i class="fa-solid fa-image"></i> Bekijk schadefoto
                                            </a>
                                        @endif

                                        {{-- ACTIEKNOPPEN VOOR ADMIN --}}
                                        <div style="display: flex; gap: 5px; margin-top: 5px;">
                                            <form action="{{ route('admin.dispute.approve', $request->id) }}" method="POST" onsubmit="return confirm('Weet je het zeker? Het geld wordt nu teruggestort naar de klant.');">
                                                @csrf
                                                <button type="submit" style="background: #ef4444; color: white; border: none; padding: 5px 8px; font-size: 10px; border-radius: 3px; cursor: pointer; font-weight: bold;">
                                                    GELD TERUGSTORTEN
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.dispute.reject', $request->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 5px 8px; font-size: 10px; border-radius: 3px; cursor: pointer; font-weight: bold;">
                                                    CLAIM AFWIJZEN
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            {{-- HUIDIGE STATUS DISPLAY --}}
                            <div style="margin-bottom: 12px;">
                                <small style="color: #999; display: block; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px;">Huidige status:</small>
                                <span class="status-badge status-{{ $currentStatus }}">
                                    {{ strtoupper($request->status ?? 'pending') }}
                                </span>
                            </div>

                            {{-- INTERACTIEVE STATUS KNOPPEN OP BASIS VAN WORKFLOW --}}
                            @if($request->payment_status === 'cancelled')
                                <div style="font-size: 12px; color: #666; font-style: italic; padding: 5px 0;">
                                    <i class="fa-solid fa-ban"></i> Order is geannuleerd
                                </div>
                            @elseif($request->payment_status === 'paid')
                                <div style="font-size: 12px; color: #166534; font-weight: bold;">
                                    <i class="fa-solid fa-circle-check"></i> Volledig afgerond & uitbetaald.
                                </div>
                            @else
                                <div class="status-action-box" style="background: #f9f9f9; padding: 10px; border: 1px solid #e5e5e5; border-radius: 4px; display: flex; flex-direction: column; gap: 8px;">
                                    <form action="{{ route('printer.update-status', $request->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="printing">
                                        <button type="submit" class="admin-btn btn-printing @if($currentStatus === 'printing') active-status @endif"
                                                @if($currentStatus === 'printing' || $currentStatus === 'ready' || $currentStatus === 'shipped') disabled @endif>
                                            <i class="fa-solid fa-hammer"></i> 1. Start Printing
                                        </button>
                                    </form>

                                    <form action="{{ route('printer.update-status', $request->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="shipped">
                                        <button type="submit" class="admin-btn btn-shipped @if($currentStatus === 'shipped') active-status @endif"
                                                @if($currentStatus === 'shipped' || $request->payment_status === 'pending') disabled @endif>
                                            <i class="fa-solid fa-truck-fast"></i> 2. Mark Shipped
                                        </button>
                                    </form>
                                </div>

                                @if($request->payment_status === 'pending')
                                    <small style="color: #b91c1c; font-size: 10px; display: block; margin-top: 5px; line-height: 1.3;">
                                        * Wacht op betaling van de klant voordat je kunt verzenden.
                                    </small>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">Geen aanvragen gevonden.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="stlModal" class="modal-overlay">
    <div class="modal-wrapper">
        <div class="modal-header">
            <div>
                <small style="color: var(--p-gold); font-weight: bold; text-transform: uppercase; font-size: 10px;">Model Inspectie</small>
                <h3 id="modalTitle" style="margin: 0;">Model Preview</h3>
            </div>
            <button class="close-modal-btn" onclick="closeStlModal()">&times;</button>
        </div>
        <div id="modalViewer">
            <div id="loading-spinner" class="spinner">Model wordt geladen...</div>
        </div>
        <div class="modal-footer">
            <p><i class="fa-solid fa-arrows-up-down-left-right"></i> Draaien: Muis | Zoomen: Scroll | Verslepen: Rechtsklik + Sleep</p>
        </div>
    </div>
</div>

<style>
    :root {
        --p-bg: #f4f4f2;
        --p-dark: #2d2a26;
        --p-gold: #b08d57;
        --p-accent: #7c2d2d;
        --p-border: #dcd7cc;
        --p-text: #2d2a26;
    }

    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: var(--p-bg); margin: 0; color: var(--p-text); }
    .admin-layout { display: flex; min-height: 100vh; }

    /* Sidebar */
    .sidebar { width: 260px; background: var(--p-dark); color: white; padding: 25px 20px; position: sticky; top: 0; height: 100vh; display: flex; flex-direction: column; }
    .brand-title { color: var(--p-gold); font-size: 18px; font-weight: 800; text-transform: uppercase; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
    .nav-link { color: #ccc; text-decoration: none; font-size: 14px; padding: 12px 0; display: block; transition: 0.3s; }
    .nav-link:hover, .nav-link.active { color: var(--p-gold); }

    /* Content Area */
    .main-content { flex-grow: 1; padding: 40px; }
    .content-header h1 { margin: 0; font-size: 32px; font-weight: 800; }
    .table-container { background: white; border-radius: 8px; box-shadow: 0 4px 25px rgba(0,0,0,0.1); overflow: hidden; margin-top: 25px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: var(--p-dark); color: white; padding: 18px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1.2px; }
    td { padding: 18px; border-bottom: 1px solid var(--p-border); vertical-align: top; }

    /* Preview Knop Stack */
    .mini-preview-btn {
        background: #fff; border: 1px solid #ddd; color: var(--p-dark);
        padding: 6px 10px; border-radius: 4px; cursor: pointer; display: flex;
        align-items: center; gap: 8px; width: 100%; transition: 0.2s;
    }
    .mini-preview-btn:hover { border-color: var(--p-gold); background: #fdfaf5; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .mini-preview-btn i { font-size: 14px; color: var(--p-gold); }
    .mini-preview-btn span { font-size: 11px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Tags & Badges */
    .tag { padding: 4px 10px; border-radius: 3px; font-size: 10px; font-weight: 800; text-transform: uppercase; margin-right: 5px; }
    .tag-resin { background: #e0f2fe; color: #0369a1; }
    .tag-fdm { background: #fef3c7; color: #92400e; }
    .tag-color { background: #f3f4f6; color: #374151; }
    .mp-btn-download-styled { display: block; background: #f8f7f2; border: 1px solid var(--p-border); padding: 5px 10px; font-size: 10px; color: black; text-decoration: none; font-weight: bold; border-radius: 2px; text-align: center; }
    .mp-btn-download-styled:hover { border-color: var(--p-gold); background: #fff; }

    /* STATUS BADGES */
    .status-badge { display: inline-block; padding: 3px 8px; font-size: 11px; font-weight: bold; border-radius: 4px; border: 1px solid transparent; }
    .status-pending { background: #f3f4f6; color: #4b5563; border-color: #d1d5db; }
    .status-printing { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .status-ready { background: #faf5ff; color: #6b21a8; border-color: #e9d5ff; }
    .status-shipped { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .status-cancelled { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }

    /* DYNAMISCHE ADMIN ACTIEKNOPPEN */
    .admin-btn { width: 100%; border: 1px solid #ddd; padding: 8px 12px; font-size: 12px; font-weight: 700; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: 0.2s; background: white; }
    .btn-printing:hover:not(:disabled) { border-color: #1d4ed8; color: #1d4ed8; background: #f8fafc; }
    .btn-shipped:hover:not(:disabled) { border-color: #166534; color: #166534; background: #f0fdf4; }
    .admin-btn:disabled { background: #f5f5f5; color: #bbb; cursor: not-allowed; border-color: #e5e5e5; }
    .active-status { background: #2d2a26 !important; color: #ffffff !important; border-color: #2d2a26 !important; box-shadow: inset 0 2px 4px rgba(0,0,0,0.15); }

    /* MODAL VOOR 3D */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center; }
    .modal-wrapper { background: white; width: 85%; height: 85vh; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; position: relative; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
    .modal-header { padding: 20px 30px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .close-modal-btn { background: none; border: none; font-size: 35px; cursor: pointer; color: #bbb; line-height: 1; }
    .close-modal-btn:hover { color: var(--p-accent); }
    #modalViewer { flex-grow: 1; background: #ffffff; position: relative; }
    .modal-footer { padding: 15px; text-align: center; background: #f8f8f8; color: #888; font-size: 12px; border-top: 1px solid #eee; }
    .spinner { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: var(--p-gold); font-weight: bold; font-size: 14px; }

    /* Stats Cards */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 8px; border-left: 5px solid var(--p-gold); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .stat-card.pending { border-left-color: var(--p-accent); }
    .stat-card h4 { margin: 0; font-size: 11px; color: #999; text-transform: uppercase; }
    .stat-card p { margin: 10px 0 0; font-size: 28px; font-weight: 900; }
</style>

<script>
    let scene, camera, renderer, controls, mesh;

    function openStlModal(url, title) {
        document.getElementById('stlModal').style.display = 'flex';
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('loading-spinner').style.display = 'block';
        initThreeJs(url);
    }

    function closeStlModal() {
        document.getElementById('stlModal').style.display = 'none';
        if (renderer) {
            renderer.dispose();
            const container = document.getElementById('modalViewer');
            const canvas = container.querySelector('canvas');
            if (canvas) container.removeChild(canvas);
        }
    }

    function initThreeJs(url) {
        const container = document.getElementById('modalViewer');
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0xffffff);

        camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 2500);
        camera.position.set(200, 200, 200);

        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(container.clientWidth, container.clientHeight);
        container.appendChild(renderer.domElement);

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;

        const light1 = new THREE.HemisphereLight(0xffffff, 0xbbbbbb, 1.2);
        scene.add(light1);
        const light2 = new THREE.DirectionalLight(0xffffff, 0.6);
        light2.position.set(100, 100, 100);
        scene.add(light2);

        const loader = new THREE.STLLoader();
        loader.load(url, function (geometry) {
            document.getElementById('loading-spinner').style.display = 'none';

            const material = new THREE.MeshPhongMaterial({
                color: 0x333333,
                specular: 0x111111,
                shininess: 30,
                side: THREE.DoubleSide
            });
            mesh = new THREE.Mesh(geometry, material);

            geometry.computeBoundingBox();
            const center = new THREE.Vector3();
            geometry.boundingBox.getCenter(center);
            mesh.position.sub(center);

            scene.add(mesh);

            const size = geometry.boundingBox.getSize(new THREE.Vector3()).length();
            camera.position.set(size * 1.6, size * 1.6, size * 1.6);
            controls.update();
        });

        function animate() {
            if (document.getElementById('stlModal').style.display === 'flex') {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
        }
        animate();
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('stlModal')) closeStlModal();
    }
</script>

</body>
</html>
