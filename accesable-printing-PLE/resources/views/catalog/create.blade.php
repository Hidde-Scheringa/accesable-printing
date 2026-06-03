<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/STLLoader.js"></script>

<header class="mp-header-full">
    <div class="mp-header-inner">
        <div class="mp-header-main-section">
            <div class="mp-header-brand-line"></div>
            <div class="mp-header-info">
                <h1 class="mp-main-title">Nieuw Catalogus Item</h1>
                <span class="mp-sub-text">Admin Paneel</span>
            </div>
        </div>
        <div class="mp-header-actions">
            <a href="{{ route('catalog.index') }}" class="mp-btn-back">
                <span class="icon">←</span> Terug naar overzicht
            </a>
        </div>
    </div>
</header>

<div class="mp-page-wrapper">
    <div class="mp-container-centered">
        <form action="{{ route('catalog.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
            @csrf
            <input type="hidden" name="price" id="total_price_input" value="0">

            <div class="mp-filter-box">
                <h3 class="mp-filter-header">1. Model Informatie</h3>
                <div class="mp-form-padding">
                    <div class="mp-grid-2">
                        <div class="mp-form-group">
                            <label class="mp-label-styled">Titel van het model</label>
                            <input type="text" name="title" class="mp-input-styled" placeholder="Bijv. Ancient Dragon" required>
                        </div>
                        <div class="mp-form-group">
                            <label class="mp-label-styled">Creator (Credits)</label>
                            <input type="text" name="description" class="mp-input-styled" placeholder="Bijv. Lord of the Print">
                        </div>
                    </div>

                    <div class="mp-form-group" style="margin-top:20px;">
                        <label class="mp-label-styled">Categorie</label>
                        <div class="mp-tag-selector">
                            <input type="hidden" name="category" id="selected-category" value="Monsters">
                            <div class="mp-tag-opt active" data-value="Monsters">Monsters</div>
                            <div class="mp-tag-opt" data-value="Animals">Animals</div>
                            <div class="mp-tag-opt" data-value="Warriors">Warriors</div>
                            <div class="mp-tag-opt" data-value="Terrain">Terrain</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mp-filter-box" style="margin-top: 24px;">
                <h3 class="mp-filter-header">2. STL Bestanden & Analyse</h3>
                <div class="mp-upload-zone" id="drop-zone" onclick="document.getElementById('stl_input').click();">
                    <input type="file" name="files[]" id="stl_input" class="mp-file-hidden" accept=".stl" multiple required>
                    <div class="upload-ui">
                        <span class="upload-icon">+</span>
                        <span class="upload-text">Selecteer STL bestanden</span>
                    </div>
                </div>
                <div id="stl-analysis-list" class="mp-analysis-stack"></div>
            </div>

            <div id="section-summary" style="display: none; margin-top: 24px;">
                <div class="mp-filter-box">
                    <h3 class="mp-filter-header">3. Bevestigen & Opslaan</h3>
                    <div class="mp-form-padding">
                        <div class="mp-price-card">
                            <div class="mp-price-display">
                                <div class="price-value" id="total-price-display">€ 0,00</div>
                                <div class="price-label">Gecalculeerde verkoopprijs</div>
                            </div>

                            <div id="progress-container" style="display:none; margin-top: 25px;">
                                <div id="progress-text" style="font-weight: 800; margin-bottom: 10px; color: var(--mp-gold); text-transform: uppercase; font-size: 11px;">Bestanden verwerken...</div>
                                <div class="progress-bar-bg"><div id="progress-bar" class="progress-bar-fill"></div></div>
                            </div>

                            <button type="submit" class="mp-btn-action-full" id="submit-btn" style="margin-top: 20px;">
                                Toevoegen aan Catalogus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* ... (Style is identiek gebleven aan je snippet) ... */
    :root { --mp-bg: #e7e7e7; --mp-card-bg: #ffffff; --mp-header-bg: #2d2a26; --mp-accent: #7c2d2d; --mp-gold: #b08d57; --mp-border: #dcd7cc; --mp-text: #2d2a26; --mp-text-muted: #706a64; --mp-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    body { margin: 0; background: var(--mp-bg); font-family: 'Segoe UI', sans-serif; color: var(--mp-text); }
    .mp-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .mp-header-full { background: var(--mp-header-bg); border-bottom: 4px solid var(--mp-gold); }
    .mp-header-inner { max-width: 800px; margin: 0 auto; padding: 25px 20px; display: flex; justify-content: space-between; align-items: center; }
    .mp-header-brand-line { width: 4px; height: 35px; background: var(--mp-gold); }
    .mp-main-title { font-size: 22px; font-weight: 800; color: #f1ede4; margin: 0; }
    .mp-sub-text { font-size: 11px; color: var(--mp-gold); text-transform: uppercase; }
    .mp-btn-back { border: 1px solid var(--mp-gold); padding: 8px 15px; color: #f1ede4; text-decoration: none; font-size: 11px; font-weight: 700; text-transform: uppercase; border-radius: 2px; }
    .mp-page-wrapper { padding: 40px 20px; display: flex; justify-content: center; }
    .mp-container-centered { width: 100%; max-width: 800px; }
    .mp-filter-box { background: var(--mp-card-bg); border-radius: 4px; overflow: hidden; box-shadow: var(--mp-shadow); border-top: 4px solid var(--mp-gold); }
    .mp-filter-header { font-size: 13px; font-weight: 700; padding: 15px 20px; border-bottom: 1px solid #f0efeb; text-transform: uppercase; background: #faf9f6; }
    .mp-form-padding { padding: 25px; }
    .mp-label-styled { font-size: 10px; font-weight: 800; color: var(--mp-text-muted); text-transform: uppercase; display: block; margin-bottom: 8px; }
    .mp-input-styled { width: 100%; background: #f8f7f2; border: 1px solid var(--mp-border); padding: 14px; box-sizing: border-box; }
    .mp-tag-selector { display: flex; gap: 10px; flex-wrap: wrap; }
    .mp-tag-opt { padding: 8px 16px; background: #f8f7f2; border: 1px solid var(--mp-border); border-radius: 20px; font-size: 12px; cursor: pointer; }
    .mp-tag-opt.active { background: var(--mp-gold); color: white; }
    .mp-upload-zone { border: 2px dashed var(--mp-border); margin: 20px; padding: 40px; text-align: center; cursor: pointer; background: #f8f7f2; }
    .mp-file-hidden { display: none; }
    .file-analysis-card { margin: 0 20px 15px 20px; padding: 18px; background: #fff; border: 1px solid var(--mp-border); border-left: 4px solid var(--mp-gold); display: flex; justify-content: space-between; align-items: center; }
    .mp-price-card { background: #f8f7f2; padding: 25px; text-align: center; }
    .price-value { font-size: 42px; font-weight: 900; color: var(--mp-accent); }
    .mp-btn-action-full { width: 100%; background: var(--mp-accent); color: white; border: none; padding: 18px; font-weight: 700; text-transform: uppercase; cursor: pointer; }
    .progress-bar-bg { width: 100%; background: #edeae4; height: 12px; border-radius: 6px; overflow: hidden; }
    .progress-bar-fill { height: 100%; background: var(--mp-gold); width: 0%; transition: 0.2s; }
</style>

<script>
    // Categorie selectie
    document.querySelectorAll('.mp-tag-opt').forEach(tag => {
        tag.addEventListener('click', function() {
            document.querySelectorAll('.mp-tag-opt').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('selected-category').value = this.dataset.value;
        });
    });

    let filesData = [];
    const GRAM_PRICE = 0.01;
    const PLA_DENSITY = 0.00124;
    const INFILL_FACTOR = 0.30;
    const PROFIT_MULT = 1.60;

    document.getElementById('stl_input').addEventListener('change', async function(e) {
        const files = e.target.files;
        if (!files.length) return;

        const listContainer = document.getElementById('stl-analysis-list');
        listContainer.innerHTML = '<div style="padding:20px; text-align:center; color:var(--mp-gold); font-weight:bold;">BESTANDEN ANALYSEREN...</div>';

        filesData = [];
        const loader = new THREE.STLLoader();

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            try {
                const buffer = await file.arrayBuffer();
                const geometry = loader.parse(buffer);
                const volume = calculateVolume(geometry);
                const mesh = new THREE.Mesh(geometry);
                const box = new THREE.Box3().setFromObject(mesh);
                const size = box.getSize(new THREE.Vector3());

                // Omzetten naar millimeters (indien Three.js units mm zijn) naar cm voor weergave
                const w = size.x;
                const d = size.y;
                const h = size.z;

                const weightInGram = volume * INFILL_FACTOR * PLA_DENSITY;
                const materialCost = weightInGram * GRAM_PRICE;

                let baseProfit;
                if (weightInGram < 10) baseProfit = 2.00;
                else if (weightInGram < 20) baseProfit = 2.50;
                else if (weightInGram < 50) baseProfit = 3.50;
                else if (weightInGram < 100) baseProfit = 5.00;
                else if (weightInGram < 250) baseProfit = 6.00;
                else baseProfit = 7.50;

                const itemPrice = Math.max(4.00, (materialCost * PROFIT_MULT) + baseProfit);

                filesData.push({ id: i, price: itemPrice });

                const card = document.createElement('div');
                card.className = 'file-analysis-card';
                card.innerHTML = `
                    <div>
                        <span style="font-weight:800; display:block; color:var(--mp-text);">${file.name}</span>
                        <span style="font-size:12px; color:var(--mp-text-muted);">
                            Formaat: ${(h/10).toFixed(1)}h x ${(w/10).toFixed(1)}b x ${(d/10).toFixed(1)}d cm
                        </span>

                        <input type="hidden" name="x[]" value="${w}">
                        <input type="hidden" name="y[]" value="${h}">
                        <input type="hidden" name="z[]" value="${d}">
                        <input type="hidden" name="volumes[]" value="${volume}">
                        <input type="hidden" name="prices[]" value="${itemPrice.toFixed(2)}">
                    </div>
                    <div style="text-align:right;">
                        <span style="font-weight:900; color:var(--mp-accent); font-size:18px;">
                            € ${itemPrice.toFixed(2).replace('.', ',')}
                        </span>
                    </div>
                `;
                if(i === 0) listContainer.innerHTML = '';
                listContainer.appendChild(card);
            } catch (err) { console.error(err); }
        }

        updateTotal();
        document.getElementById('section-summary').style.display = 'block';
    });

    function updateTotal() {
        let total = 0;
        filesData.forEach(f => total += f.price);
        document.getElementById('total-price-display').innerText = "€ " + total.toFixed(2).replace('.', ',');
        document.getElementById('total_price_input').value = total.toFixed(2);
    }

    function calculateVolume(geometry) {
        let vol = 0;
        const pos = geometry.attributes.position;
        for (let i = 0; i < pos.count; i += 3) {
            const v1 = new THREE.Vector3(pos.getX(i), pos.getY(i), pos.getZ(i));
            const v2 = new THREE.Vector3(pos.getX(i+1), pos.getY(i+1), pos.getZ(i+1));
            const v3 = new THREE.Vector3(pos.getX(i+2), pos.getY(i+2), pos.getZ(i+2));
            vol += v1.dot(v2.cross(v3)) / 6.0;
        }
        return Math.abs(vol);
    }

    document.getElementById('upload-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();

        document.getElementById('progress-container').style.display = 'block';
        document.getElementById('submit-btn').disabled = true;

        xhr.onload = () => {
            try {
                const response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    window.location.href = response.redirect;
                } else {
                    alert("Fout: " + (response.error || "Server fout"));
                    document.getElementById('submit-btn').disabled = false;
                }
            } catch(e) {
                console.error(xhr.responseText);
                alert("Er is een kritieke fout opgetreden.");
                document.getElementById('submit-btn').disabled = false;
            }
        };

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                document.getElementById('progress-bar').style.width = percent + '%';
            }
        });

        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });
</script>
