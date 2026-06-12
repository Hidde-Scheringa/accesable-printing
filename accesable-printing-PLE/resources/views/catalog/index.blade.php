<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogus | Accessible Printing</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/STLLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="mp-page-wrapper">
    <header class="mp-full-header">
        <div class="mp-header-content">
            <div class="header-left">
                <div class="mp-header-brand-line"></div>
                <div class="mp-header-info">
                    <h1 class="mp-main-title">Model Catalogus</h1>
                    <p class="mp-sub-text">Premium 3D-ontwerpen voor uw projecten</p>
                </div>
            </div>

            <div class="mp-header-actions">
                <a href="{{ route('home') }}" class="btn-nav-outline">
                    <i class="fa-solid fa-house"></i> <span>{{ Auth::check() ? 'Dashboard' : 'Home' }}</span>
                </a>

                <a href="{{ route('catalog.selection') }}" class="btn-cart-gold">
                    <div class="cart-icon-wrapper">
                        <i class="fa-solid fa-shopping-basket"></i>
                        <span class="cart-badge">{{ count(session('print_selection', [])) }}</span>
                    </div>
                    <span class="cart-label">Selectie</span>
                </a>
            </div>
        </div>
    </header>

    <div class="mp-container">
        <div class="mp-filter-section">
            <div class="mp-filter-bar">
                <a href="{{ route('catalog.index') }}" class="filter-btn {{ !request('category') ? 'active' : '' }}">
                    <i class="fa-solid fa-border-all"></i> Alle
                </a>
                <a href="{{ route('catalog.index', ['category' => 'Animals']) }}" class="filter-btn {{ request('category') == 'Animals' ? 'active' : '' }}">
                    <i class="fa-solid fa-paw"></i> Animals
                </a>
                <a href="{{ route('catalog.index', ['category' => 'Monsters']) }}" class="filter-btn {{ request('category') == 'Monsters' ? 'active' : '' }}">
                    <i class="fa-solid fa-dragon"></i> Monsters
                </a>
                <a href="{{ route('catalog.index', ['category' => 'Warriors']) }}" class="filter-btn {{ request('category') == 'Warriors' ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved"></i> Warriors
                </a>
            </div>
        </div>

        <div class="mp-catalog-grid">
            @forelse($items as $item)
                <div class="mp-item-card">
                    {{-- FIXED: Check of er bestanden zijn om 'Undefined array key 0' te voorkomen --}}
                    @php $firstFile = $item->stl_files[0] ?? null; @endphp

                    <div class="mp-item-visual-container" onclick='openMultiPreviewModal(@json($item->stl_files ?? []), "{{ $item->title }}")'>
                        @if($firstFile && isset($firstFile['path']))
                            <div class="stl-viewer"
                                 id="viewer-{{ $item->id }}"
                                 data-url="{{ asset('storage/' . $firstFile['path']) }}">
                                <div class="stl-loader-spinner"><i class="fa-solid fa-circle-notch fa-spin"></i></div>
                            </div>
                        @else
                            <div style="height: 100%; display: flex; align-items: center; justify-content: center; background: #eee; color: #bbb;">
                                <i class="fa-solid fa-box-open" style="font-size: 30px;"></i>
                            </div>
                        @endif

                        <div class="mp-zoom-overlay">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                            <span>Inspecteer Model</span>
                        </div>

                        @if($item->stl_files && count($item->stl_files) > 1)
                            <div class="mp-file-count-badge">+{{ count($item->stl_files) - 1 }} onderdelen</div>
                        @endif
                    </div>

                    <div class="mp-item-details">
                        <div class="mp-item-top">
                            <div class="mp-title-group">
                                <span class="mp-tag-category">{{ $item->category ?? 'Algemeen' }}</span>
                                <h2 class="mp-item-name">{{ $item->title }}</h2>
                            </div>
                            @if($item->price)
                                <div class="mp-item-price">€{{ number_format($item->price, 2) }}</div>
                            @endif
                        </div>

                        @if(!empty($item->stl_files))
                            <div class="mp-dimensions" style="margin: 10px 0; font-size: 11px; color: #888;">
                                @php
                                    // We nemen het eerste bestand voor de weergave
                                    $f = $item->stl_files[0];
                                    $x = number_format(($f['x'] ?? 0) / 10, 1);
                                    $y = number_format(($f['y'] ?? 0) / 10, 1);
                                    $z = number_format(($f['z'] ?? 0) / 10, 1);
                                @endphp
                                Formaat: b{{ $x }} x h{{ $y }} x d{{ $z }} cm
                            </div>
                        @endif

                        <p class="mp-item-desc">{{ Str::limit($item->description, 85) }}</p>

                        <div class="mp-item-footer">
                            <form action="{{ route('catalog.add', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="mp-btn-action-add">
                                    <i class="fa-solid fa-plus-circle"></i> Toevoegen aan aanvraag
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="mp-empty-card" style="grid-column: 1 / -1;">
                    <i class="fa-solid fa-box-open"></i>
                    <p>Geen modellen gevonden in deze categorie.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-12 flex justify-center mp-pagination-wrapper">
            {{ $items->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<div id="multiPreviewModal" class="mp-modal">
    <div class="mp-modal-content multi-layout">
        <span class="mp-modal-close" onclick="closeMultiModal()">&times;</span>
        <div class="modal-viewer-section">
            <div class="mp-modal-header">
                <h2 id="multiModalTitle">Project Inspectie</h2>
            </div>
            <div id="multiModalViewer" class="mp-modal-body"></div>
        </div>
        <div class="modal-files-sidebar">
            <h3 class="sidebar-title">Onderdelen (<span id="fileCountDisplay">0</span>)</h3>
            <div id="fileListContainer" class="file-list"></div>
        </div>
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

    body { margin: 0; padding: 0; background: var(--mp-bg); font-family: 'Segoe UI', sans-serif; color: var(--mp-text); }

    /* HEADER */
    .mp-full-header {
        background: var(--mp-header-bg);
        padding: 15px 0;
        border-bottom: 4px solid var(--mp-gold);
        position: sticky; top: 0; z-index: 1000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    .mp-header-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
    .mp-header-brand-line { width: 3px; height: 40px; background: var(--mp-gold); margin-right: 15px; }
    .header-left { display: flex; align-items: center; }
    .mp-main-title { font-size: 22px; font-weight: 800; color: #f1ede4; margin: 0; }
    .mp-sub-text { font-size: 11px; color: var(--mp-gold); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }

    .mp-header-actions { display: flex; align-items: center; gap: 1rem; }
    .btn-nav-outline { color: #f1ede4; text-decoration: none; font-weight: 600; font-size: 13px; padding: 10px 15px; border: 1px solid rgba(255,255,255,0.1); border-radius: 3px; transition: 0.3s; }
    .btn-nav-outline:hover { color: var(--mp-gold); border-color: var(--mp-gold); }
    .btn-cart-gold { background: var(--mp-gold); color: white; padding: 10px 22px; text-decoration: none; font-weight: 700; border-radius: 3px; font-size: 11px; text-transform: uppercase; display: flex; align-items: center; gap: 10px; }

    /* FILTERS */
    .mp-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
    .mp-filter-bar { display: flex; gap: 10px; margin-bottom: 40px; }
    .filter-btn { background: white; padding: 10px 20px; border-radius: 3px; text-decoration: none; color: var(--mp-text); font-weight: 700; border: 1px solid var(--mp-border); font-size: 13px; transition: 0.3s; box-shadow: var(--mp-shadow); }
    .filter-btn.active { background: var(--mp-header-bg); color: var(--mp-gold); border-color: var(--mp-gold); }

    /* GRID & CARDS */
    .mp-catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
    .mp-item-card { background: white; border-radius: 4px; overflow: hidden; box-shadow: var(--mp-shadow); transition: 0.3s; }
    .mp-item-card:hover { transform: translateY(-5px); }

    .mp-item-visual-container { height: 250px; background: #f7f6f2; position: relative; cursor: pointer; border-bottom: 1px solid var(--mp-border); }
    .stl-viewer { height: 100%; width: 100%; }
    .mp-zoom-overlay { position: absolute; inset: 0; background: rgba(45, 42, 38, 0.4); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; opacity: 0; transition: 0.3s; backdrop-filter: blur(2px); z-index: 10; }
    .mp-item-visual-container:hover .mp-zoom-overlay { opacity: 1; }
    .mp-file-count-badge { position: absolute; top: 15px; right: 15px; background: var(--mp-accent); color: white; padding: 4px 8px; font-size: 10px; font-weight: 800; border-radius: 2px; z-index: 5; }

    .mp-item-details { padding: 25px; }
    .mp-tag-category { font-size: 10px; color: var(--mp-gold); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
    .mp-item-name { font-size: 18px; font-weight: 700; margin: 0 0 10px 0; border-left: 4px solid var(--mp-accent); padding-left: 12px; }
    .mp-item-price { font-size: 18px; font-weight: 800; color: var(--mp-accent); background: #fdf2f2; padding: 4px 12px; border-radius: 2px; }
    .mp-item-desc { font-size: 14px; color: var(--mp-text-muted); line-height: 1.6; height: 45px; overflow: hidden; }

    .mp-btn-action-add { width: 100%; background: var(--mp-accent); color: white; border: none; padding: 12px; border-radius: 3px; font-weight: 700; cursor: pointer; text-transform: uppercase; font-size: 11px; margin-top: 20px; transition: 0.3s; }
    .mp-btn-action-add:hover { background: #943636; }

    /* PAGINATION OVERRIDES */
    .mp-pagination-wrapper nav div:first-child { display: none; }
    .mp-pagination-wrapper nav { display: flex; flex-direction: row; }
    .mp-pagination-wrapper p { display: none; }

    /* MODAL STYLING */
    .mp-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px); }
    .mp-modal-content.multi-layout { display: flex; background: #f7f6f2; width: 95%; max-width: 1200px; height: 85vh; border-radius: 8px; overflow: hidden; position: relative; border-top: 4px solid var(--mp-gold); }
    .modal-viewer-section { flex: 1; display: flex; flex-direction: column; }
    .mp-modal-header { padding: 25px; background: white; border-bottom: 1px solid var(--mp-border); }
    .mp-modal-body { flex: 1; width: 100%; }
    .modal-files-sidebar { width: 320px; background: white; border-left: 1px solid var(--mp-border); padding: 25px; display: flex; flex-direction: column; }
    .file-item-btn { text-align: left; padding: 14px; border: 1px solid var(--mp-border); background: #f9f9f9; cursor: pointer; border-radius: 4px; font-size: 13px; margin-bottom: 8px; }
    .file-item-btn.active { background: var(--mp-header-bg); color: var(--mp-gold); border-color: var(--mp-gold); }
    .mp-modal-close { position: absolute; top: 15px; right: 20px; font-size: 35px; color: var(--mp-text-muted); cursor: pointer; z-index: 100; }
</style>

<script>
    function initSTLViewer(container, url, autoRotate = true) {
        if (!url) return;
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf7f6f2);

        const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 2000);
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.setSize(container.clientWidth, container.clientHeight);

        container.innerHTML = '';
        container.appendChild(renderer.domElement);

        scene.add(new THREE.HemisphereLight(0xffffff, 0x444444, 1.2));
        const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
        dirLight.position.set(5, 10, 7.5);
        scene.add(dirLight);

        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.autoRotate = autoRotate;

        const loader = new THREE.STLLoader();
        loader.load(url, function (geometry) {
            geometry.computeBoundingBox();
            const center = new THREE.Vector3();
            geometry.boundingBox.getCenter(center);
            geometry.translate(-center.x, -center.y, -center.z);

            const material = new THREE.MeshPhongMaterial({ color: 0x5a554f, specular: 0x222222, shininess: 30 });
            const mesh = new THREE.Mesh(geometry, material);
            mesh.rotation.x = -Math.PI / 2;
            scene.add(mesh);

            const box = new THREE.Box3().setFromObject(mesh);
            const size = box.getSize(new THREE.Vector3()).length();
            camera.position.set(size * 1.2, size * 1.2, size * 1.2);
            controls.update();
        });

        function animate() {
            if (container.offsetParent !== null) {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
        }
        animate();
    }

    function openMultiPreviewModal(files, projectTitle) {
        event.stopPropagation();
        const modal = document.getElementById('multiPreviewModal');
        const listContainer = document.getElementById('fileListContainer');
        const titleEl = document.getElementById('multiModalTitle');
        const countEl = document.getElementById('fileCountDisplay');

        titleEl.innerText = projectTitle;
        countEl.innerText = files.length;
        listContainer.innerHTML = '';
        modal.style.display = "flex";

        files.forEach((file, index) => {
            const btn = document.createElement('button');
            const fileName = file.path.split('/').pop();
            btn.className = `file-item-btn ${index === 0 ? 'active' : ''}`;
            btn.innerHTML = `<i class="fa-solid fa-file-code"></i> Miniature ${index + 1}`;
            btn.onclick = (e) => {
                e.stopPropagation();
                document.querySelectorAll('.file-item-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            };
            listContainer.appendChild(btn);
        });

        if(files.length > 0) {
            loadModelInModal(files[0].path, files[0].path.split('/').pop());
        }
    }

    function loadModelInModal(path, name) {
        const container = document.getElementById('multiModalViewer');

        initSTLViewer(container, `/storage/${path}`, false);
    }

    function closeMultiModal() {
        document.getElementById('multiPreviewModal').style.display = "none";
        document.getElementById('multiModalViewer').innerHTML = '';
    }

    window.addEventListener('load', () => {
        document.querySelectorAll('.stl-viewer').forEach(container => {
            initSTLViewer(container, container.getAttribute('data-url'), true);
        });
    });

    window.onclick = (e) => {
        if(e.target == document.getElementById('multiPreviewModal')) closeMultiModal();
    };
</script>

</body>
</html>
