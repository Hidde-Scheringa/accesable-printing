<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessible Printing | Professionele 3D Printservice</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/STLLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="mp-page-wrapper">

    <header class="mp-header-full">
        <div class="mp-container">
            <div class="mp-header-content">
                <div class="mp-header-main-section">
                    <div class="mp-header-brand-line"></div>
                    <div class="mp-header-info">
                        <h1 class="mp-main-title">Accessible Printing</h1>
                        <span class="mp-sub-text">Uw specialist in hoogwaardige 3D-prints</span>
                    </div>
                </div>

                <div class="mp-header-actions">
                    <nav class="mp-nav-group">
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
        </div>
    </header>

    <section class="mp-intro-section">
        <div class="mp-container">
            <div class="mp-intro-layout">
                <div class="mp-intro-content">
                    <h3 class="mp-section-header">De plek voor al uw DnD miniatures</h3>
                    <p class="mp-intro-text">
                        Laat je <strong>Dungeons and Dragons</strong> miniatures wensen werkelijkheid worden.
                        <strong>Accessible Printing</strong> brengt jouw campaigns naar het volgende niveau door miniatures en gebouwen tot leven te wekken.
                        We printen op jouw aanvraag! Of het nou een catalog mini is of je eigen creatie we printen het met plezier voor je uit.
                    </p>
                    <p class="mp-intro-text">
                        Lever je eigen models aan of kies een prachtig ontwerp uit onze groeiende <strong>catalogus</strong>.
                    </p>
                    <p class="mp-intro-text">
                        Graag even zien wat klanten hebben gemaakt van onze geprinte mini's neem een kijkje in de <strong>Showcase</strong>.
                    </p>
                    <div class="intro-actions" style="display: flex; gap: 15px;">
                        <a href="{{ route('catalog.index') }}" class="mp-btn-action">Bekijk de Catalogus</a>
                        <a href="{{ route('showcase.index') }}" class="mp-btn-action" style="background: var(--mp-gold); color: var(--mp-header-bg);">
                            Showcase bekijken
                        </a>
                    </div>
                </div>

                <div class="mp-intro-video-container">
                    <div class="mp-video-frame">
                        <div class="mp-video-wrapper">
                            <video autoplay muted loop playsinline class="mp-intro-video">
                                <source src="{{ asset('videos/3d-dragonprint.mp4') }}" type="video/mp4">
                            </video>
                            <div class="mp-video-overlay"></div>
                        </div>
                        <div class="mp-video-accent"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mp-container">
        <section class="mp-main-grid-section">

            <div class="mp-orders-column">
                <h3 class="mp-section-header">Recent Geprinte Projecten</h3>
                <div class="mp-list-stack">
                    @forelse($recentRequests as $request)
                        <article class="mp-item-card">
                            <div class="mp-item-visual-container" onclick='openMultiPreviewModal(@json($request->stl_files), "{{ $request->title }}")'>
                                @if($request->stl_files && count($request->stl_files) > 0)
                                    <div class="stl-viewer" id="viewer-{{ $request->id }}" data-url="{{ asset('storage/' . $request->stl_files[0]['path']) }}"></div>

                                    @if(count($request->stl_files) > 1)
                                        <div class="mp-file-count-badge">+{{ count($request->stl_files) - 1 }} models</div>
                                    @endif

                                    <div class="mp-zoom-overlay">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                                        <span>Bekijk {{ count($request->stl_files) }} models</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mp-item-details">
                                <div class="mp-item-top">
                                    <h2 class="mp-item-name">{{ $request->title }}</h2>
                                    <p class="mp-item-desc">{{ Str::limit(str_replace('[CATALOGUS_ORDER] ', '', $request->description), 140) }}</p>
                                </div>
                                <div class="mp-item-specs">
                                    <span class="mp-tag">Kleur: @if($request->color == 1) Grijs @elseif($request->color == 2) Zwart @else Wit @endif</span>
                                    <span class="mp-tag">Materiaal: @if($request->material == 1) FDM @else Resin @endif</span>
                                    <span class="mp-tag">Schaal: x{{ number_format($request->scale / 100, 1) }}</span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="mp-empty-state"><p>Er zijn momenteel geen openbare projecten.</p></div>
                    @endforelse
                </div>
            </div>

            <aside class="mp-process-sidebar">
                <div class="mp-process-card">
                    <h3 class="mp-section-header" style="color: var(--mp-gold); border-left-color: var(--mp-accent);">Hoe het werkt</h3>
                    <div class="mp-process-step">
                        <div class="step-icon">1</div>
                        <div class="step-text"><p>Lever eigen models of kies uit de <strong>catalogus</strong>, wij printen het met liefde!</p></div>
                    </div>
                    <div class="mp-process-step">
                        <div class="step-icon">2</div>
                        <div class="step-text"><p>Bij het bestellen geef je de <strong>schaal, materiaal en kleur</strong> aan.</p></div>
                    </div>
                    <div class="mp-process-step">
                        <div class="step-icon">3</div>
                        <div class="step-text"><p>Na acceptatie ontvang je een <strong>factuur</strong> met de vooropgestelde prijs.</p></div>
                    </div>
                    <div class="mp-process-step">
                        <div class="step-icon">4</div>
                        <div class="step-text"><p>Het printen duurt <strong>2 à 3 werkdagen</strong>. Daarna wordt alles zorgvuldig verstuurd.</p></div>
                    </div>
                    <div class="mp-process-footer">En dan kan de campaign beginnen!!</div>
                </div>
            </aside>
        </section>
    </div>

    <section class="mp-review-section-full">
        <div class="mp-container">
            <h3 class="mp-section-header" style="text-align: center; margin-bottom: 40px; border-left: none; padding-left: 0;">
                Ervaringen van onze klanten
            </h3>
        </div>
        <div class="mp-review-viewport-full">
            <div class="mp-review-track" id="reviewTrack">
                <div class="mp-review-card">
                    <p class="mp-review-text">"De details op mijn Dragon-miniature zijn bizar goed. Eindelijk een printer die begrijpt dat schaal belangrijk is voor D&D!"</p>
                    <span class="mp-review-author">- Thomas G. (Dungeon Master)</span>
                </div>
                <div class="mp-review-card">
                    <p class="mp-review-text">"Snelle levering en erg fijn contact over de bestanden. Mijn hele party is jaloers op de nieuwe terrain pieces."</p>
                    <span class="mp-review-author">- Sarah de V.</span>
                </div>
                <div class="mp-review-card">
                    <p class="mp-review-text">"Topkwaliteit resin prints. Geen printlijnen te zien en de kleur grijs is perfect om direct op te primen."</p>
                    <span class="mp-review-author">- Rick's Tabletop Gaming</span>
                </div>
            </div>
        </div>
    </section>

    <div id="multiPreviewModal" class="mp-modal">
        <div class="mp-modal-content multi-layout">
            <span class="mp-modal-close" onclick="closeMultiModal()">&times;</span>

            <div class="modal-viewer-section">
                <div class="mp-modal-header">
                    <h2 id="multiModalTitle">Project Inspectie</h2>
                </div>
                <div id="multiModalViewer" class="mp-modal-body"></div>
                <div class="mp-modal-footer">
                    <p><i class="fa-solid fa-cube"></i> <span id="currentFileName">Selecteer een model</span></p>
                </div>
            </div>

            <div class="modal-files-sidebar">
                <h3 class="sidebar-title">Bestanden (<span id="fileCountDisplay">0</span>)</h3>
                <div id="fileListContainer" class="file-list"></div>
            </div>
        </div>
    </div>

    <footer style="text-align: center; padding: 60px 40px; color: var(--mp-text-muted); font-size: 12px;">
        &copy; {{ date('Y') }} Accessible Printing.
    </footer>
</div>

<script>
    // STL Viewer Logica
    function initSTLViewer(container, url, autoRotate = true) {
        if (!url) return;
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf7f6f2);
        const width = container.clientWidth;
        const height = container.clientHeight;

        const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 2000);
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.setSize(width, height);

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
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }
        animate();
    }

    // Modal Functies
    function openMultiPreviewModal(files, projectTitle) {
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
            btn.innerHTML = `<i class="fa-solid fa-file-code"></i> ${fileName}`;
            btn.onclick = () => {
                document.querySelectorAll('.file-item-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                loadModelInModal(file.path, fileName);
            };
            listContainer.appendChild(btn);
        });

        if(files.length > 0) {
            loadModelInModal(files[0].path, files[0].path.split('/').pop());
        }
    }

    function loadModelInModal(path, name) {
        const container = document.getElementById('multiModalViewer');
        document.getElementById('currentFileName').innerText = name;
        initSTLViewer(container, `/storage/${path}`, false);
    }

    function closeMultiModal() {
        document.getElementById('multiPreviewModal').style.display = "none";
        document.getElementById('multiModalViewer').innerHTML = '';
    }

    // Review Slider Logica
    const track = document.getElementById('reviewTrack');
    let reviewIndex = 0;
    const totalReviews = 3;

    if (track) {
        setInterval(() => {
            reviewIndex = (reviewIndex + 1) % totalReviews;
            track.style.transform = `translateX(-${reviewIndex * 100}%)`;
        }, 6000);
    }

    // Initialiseer kleine viewers
    window.addEventListener('load', () => {
        document.querySelectorAll('.stl-viewer').forEach(c => {
            initSTLViewer(c, c.getAttribute('data-url'), true);
        });
    });

    window.onclick = (e) => {
        if(e.target == document.getElementById('multiPreviewModal')) closeMultiModal();
    };
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

    * { box-sizing: border-box; }
    body { margin: 0; padding: 0; background: var(--mp-bg); font-family: 'Segoe UI', sans-serif; overflow-x: hidden; color: var(--mp-text); }
    .mp-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

    /* HEADER */
    .mp-header-full { background: var(--mp-header-bg); border-bottom: 4px solid var(--mp-gold); padding: 15px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 5px 20px rgba(0,0,0,0.2); }
    .mp-header-content { display: flex; justify-content: space-between; align-items: center; }
    .mp-header-brand-line { width: 3px; height: 40px; background: var(--mp-gold); margin-right: 15px; }
    .mp-header-main-section { display: flex; align-items: center; }
    .mp-main-title { font-size: 22px; font-weight: 800; color: #f1ede4; margin: 0; letter-spacing: 0.5px; }
    .mp-sub-text { font-size: 11px; color: var(--mp-gold); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
    .mp-nav-group { display: flex; align-items: center; gap: 25px; }
    .mp-link-nav { color: #f1ede4; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; opacity: 0.85; }
    .mp-link-nav:hover { opacity: 1; color: var(--mp-gold); }

    /* LOGIN LINK STYLING */
    .mp-link-login { color: #f1ede4; text-decoration: none; font-weight: 700; font-size: 13px; transition: 0.3s; padding: 10px 15px; border-radius: 3px; border: 1px solid transparent; text-transform: uppercase; }
    .mp-link-login:hover { color: var(--mp-gold); border-color: rgba(176, 141, 87, 0.3); background: rgba(255, 255, 255, 0.05); }

    .mp-btn-action { background: var(--mp-accent); color: white; padding: 10px 22px; text-decoration: none; font-weight: 700; border-radius: 3px; text-transform: uppercase; font-size: 11px; transition: 0.3s; border: none; cursor: pointer; display: inline-block; }
    .mp-btn-action:hover { background: #943636; box-shadow: 0 4px 12px rgba(124, 45, 45, 0.3); }

    /* INTRO - VIDEO FIXES */
    .mp-intro-section { padding: 80px 0; background: white; border-bottom: 1px solid var(--mp-border); }
    .mp-intro-layout { display: flex; align-items: center; gap: 70px; }
    .mp-section-header { font-size: 26px; line-height: 1.2; color: var(--mp-accent); border-left: 5px solid var(--mp-gold); padding-left: 20px; margin-bottom: 25px; text-transform: uppercase; font-weight: 800; }
    .mp-intro-text { font-size: 17px; line-height: 1.7; margin-bottom: 15px; }

    .mp-intro-video-container { flex: 1; display: flex; justify-content: flex-end; min-width: 450px; }
    .mp-video-frame { position: relative; width: 100%; max-width: 650px; }
    .mp-video-accent { position: absolute; top: -15px; right: -15px; width: 150px; height: 150px; border-top: 6px solid var(--mp-gold); border-right: 6px solid var(--mp-gold); z-index: 1; pointer-events: none; }

    .mp-video-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        background: #000;
        z-index: 2;
    }

    .mp-intro-video {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    /* GRID & CARDS */
    .mp-main-grid-section { display: grid; grid-template-columns: 1.8fr 1fr; gap: 40px; margin-top: 80px; align-items: start; }
    .mp-list-stack { display: flex; flex-direction: column; gap: 24px; }
    .mp-item-card { background: white; display: flex; border-radius: 4px; overflow: hidden; box-shadow: var(--mp-shadow); min-height: 220px; transition: 0.3s; }
    .mp-item-card:hover { transform: translateX(5px); }

    .mp-item-visual-container { width: 240px; background: #f7f6f2; border-right: 1px solid var(--mp-border); flex-shrink: 0; position: relative; cursor: pointer; }
    .stl-viewer { height: 100%; width: 100%; min-height: 220px; }

    .mp-file-count-badge { position: absolute; top: 10px; right: 10px; background: var(--mp-accent); color: white; padding: 4px 8px; font-size: 10px; font-weight: 800; border-radius: 4px; z-index: 5; }
    .mp-zoom-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(124, 45, 45, 0.2); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; opacity: 0; transition: 0.3s; }
    .mp-item-visual-container:hover .mp-zoom-overlay { opacity: 1; backdrop-filter: blur(2px); }

    .mp-item-details { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .mp-item-name { font-size: 20px; font-weight: 700; margin: 0 0 10px 0; border-left: 4px solid var(--mp-accent); padding-left: 12px; }
    .mp-item-desc { font-size: 14px; color: var(--mp-text-muted); line-height: 1.5; }
    .mp-tag { background: #f1ede4; padding: 5px 12px; font-size: 11px; font-weight: 700; border: 1px solid var(--mp-border); border-radius: 2px; }

    /* SIDEBAR */
    .mp-process-card { background: var(--mp-header-bg); padding: 40px; border-radius: 4px; color: #f1ede4; border-top: 4px solid var(--mp-gold); position: sticky; top: 20px; }
    .mp-process-step { display: flex; gap: 20px; margin-bottom: 25px; }
    .step-icon { background: var(--mp-gold); color: var(--mp-header-bg); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
    .mp-process-footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); text-align: center; font-style: italic; color: var(--mp-gold); font-weight: 700; }

    /* REVIEWS STYLING */
    .mp-review-section-full { padding: 80px 0; background: white; margin-top: 80px; border-top: 1px solid var(--mp-border); }
    .mp-review-viewport-full { width: 100%; overflow: hidden; position: relative; }
    .mp-review-track { display: flex; transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
    .mp-review-card { min-width: 100%; text-align: center; padding: 0 15%; }
    .mp-review-text { font-size: 22px; font-style: italic; margin-bottom: 20px; line-height: 1.6; color: var(--mp-text); font-weight: 500; }
    .mp-review-author { font-weight: 800; color: var(--mp-gold); text-transform: uppercase; font-size: 13px; letter-spacing: 2px; }

    /* MULTI-MODAL */
    .mp-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(8px); }
    .mp-modal-content.multi-layout { display: flex; background: #f7f6f2; width: 95%; max-width: 1200px; height: 85vh; border-radius: 8px; overflow: hidden; position: relative; border-top: 4px solid var(--mp-gold); }
    .modal-viewer-section { flex: 1; display: flex; flex-direction: column; }
    .mp-modal-header { padding: 25px; background: white; border-bottom: 1px solid var(--mp-border); }
    .mp-modal-body { flex: 1; width: 100%; }
    .mp-modal-footer { padding: 15px; background: white; border-top: 1px solid var(--mp-border); text-align: center; font-size: 13px; font-weight: 600; }
    .modal-files-sidebar { width: 320px; background: white; border-left: 1px solid var(--mp-border); padding: 25px; display: flex; flex-direction: column; }
    .sidebar-title { font-size: 16px; margin-bottom: 20px; color: var(--mp-accent); text-transform: uppercase; letter-spacing: 1px; }
    .file-list { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
    .file-item-btn { text-align: left; padding: 14px; border: 1px solid var(--mp-border); background: #f9f9f9; cursor: pointer; border-radius: 4px; font-size: 13px; transition: 0.2s; overflow: hidden; text-overflow: ellipsis; }
    .file-item-btn:hover { border-color: var(--mp-gold); background: #f1ede4; }
    .file-item-btn.active { background: var(--mp-header-bg); color: var(--mp-gold); border-color: var(--mp-gold); }
    .mp-modal-close { position: absolute; top: 15px; right: 20px; font-size: 35px; color: var(--mp-text-muted); cursor: pointer; z-index: 100; }

    @media (max-width: 1000px) {
        .mp-modal-content.multi-layout { flex-direction: column; height: 95vh; }
        .modal-files-sidebar { width: 100%; height: 250px; border-left: none; border-top: 1px solid var(--mp-border); }
    }
</style>
</body>
</html>
