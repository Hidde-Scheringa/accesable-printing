<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/STLLoader.js"></script>

<header class="mp-header-full">
    <div class="mp-header-inner">
        <div class="mp-header-main-section">
            <div class="mp-header-brand-line"></div>
            <div class="mp-header-info">
                <h1 class="mp-main-title">Nieuw Printverzoek</h1>
                <span class="mp-sub-text">Configureer uw project</span>
            </div>
        </div>
        <div class="mp-header-actions">
            <a href="{{ route('dashboard') }}" class="mp-btn-back">
                <span class="icon">←</span> Dashboard
            </a>
        </div>
    </div>
</header>

<div class="mp-page-wrapper">
    <div class="mp-container-centered">
        <form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
            @csrf
            <input type="hidden" name="total_price_hidden" id="total_price_input" value="0">

            <div class="mp-filter-box">
                <h3 class="mp-filter-header">1. Project Gegevens</h3>
                <div class="mp-form-padding">
                    <div class="mp-form-group">
                        <label class="mp-label-styled">Titel van het project</label>
                        <input type="text" name="title" class="mp-input-styled" placeholder="Bijv. Prototype X" required>
                    </div>

                    <div class="mp-form-group" style="margin-top:20px;">
                        <label class="mp-label-styled">Materiaal</label>
                        <div class="mp-tag-static">FDM (Filament)</div>
                        <input type="hidden" name="material" value="FDM">
                    </div>

                    <div class="mp-form-group" style="margin-top:20px;">
                        <label class="mp-label-styled">Kleur</label>
                        <select name="color" class="mp-select-styled" required>
                            <option value="Grijs">Grijs</option>
                            <option value="Zwart">Zwart</option>
                            <option value="Wit">Wit</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mp-filter-box" style="margin-top: 24px;">
                <h3 class="mp-filter-header">2. STL Bestanden & Configuratie (max 150mb)</h3>
                <div class="mp-upload-zone" onclick="document.getElementById('stl_input').click();">
                    <input type="file" name="stl_files[]" id="stl_input" class="mp-file-hidden" accept=".stl" multiple required>
                    <div class="upload-ui">
                        <span class="upload-icon">+</span>
                        <span class="upload-text">Klik hier om bestanden te selecteren</span>
                    </div>
                </div>
                <div style="padding: 0 20px 20px 20px;">
                    <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:5px; font-weight:bold; color:var(--mp-text-muted);">
                        <span id="mb-text">0 MB / 150 MB gebruikt</span>
                    </div>
                    <div class="progress-bar-bg" style="height: 6px;">
                        <div id="mb-progress-bar" class="progress-bar-fill" style="width: 0%; background: var(--mp-accent);"></div>
                    </div>
                </div>
                <div id="stl-analysis-list" class="mp-analysis-stack"></div>
            </div>

            <div id="section-summary" style="display: none; margin-top: 24px;">
                <div class="mp-filter-box">
                    <h3 class="mp-filter-header">3. Adresgegevens & Bevestiging</h3>
                    <div class="mp-form-padding">
                        <div class="mp-form-group">
                            <label class="mp-label-styled">Straatnaam</label>
                            <input type="text" name="street" class="mp-input-styled" placeholder="Straat" required>
                        </div>
                        <div class="mp-form-group" style="margin-top:15px;">
                            <label class="mp-label-styled">Huisnummer</label>
                            <input type="text" name="streetnumber" class="mp-input-styled" placeholder="Nr" required>
                        </div>
                        <div class="mp-form-group" style="margin-top:15px;">
                            <label class="mp-label-styled">Postcode</label>
                            <input type="text" name="zipcode" class="mp-input-styled" placeholder="1234 AB" required>
                        </div>
                        <div class="mp-form-group" style="margin-top:15px;">
                            <label class="mp-label-styled">Woonplaats</label>
                            <input type="text" name="city" class="mp-input-styled" placeholder="Stad" required>
                        </div>

                        <div id="free-shipping-container" style="margin-top: 25px; padding: 0 10px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">
                                <span id="free-shipping-text" style="color: var(--mp-text-muted);">Nog € 24,00 tot gratis verzending</span>
                                <span style="color: var(--mp-gold);">Gratis vanaf € 24,-</span>
                            </div>
                            <div class="progress-bar-bg" style="height: 8px;">
                                <div id="free-shipping-progress" class="progress-bar-fill" style="width: 0%; background: var(--mp-gold);"></div>
                            </div>
                        </div>

                        <div class="mp-summary-shipping" style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 25px; padding: 0 10px; color: var(--mp-text-muted);">
                            <span>Verzend- & Verpakkingskosten:</span>
                            <strong id="shipping-costs-display">€ 8,50</strong>
                        </div>

                        <div id="stimulus-discount-note" style="text-align: right; font-size: 12px; color: #16a34a; font-weight: 700; margin-top: 5px; padding: 0 10px; display: none;">
                            Stapelkorting toegepast!
                        </div>

                        <div class="mp-disclaimer-box" style="margin-top: 25px; background: #fffcf5; border: 1px solid #f5ebd0; border-left: 4px solid var(--mp-gold); padding: 15px; border-radius: 2px;">
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <span style="color: var(--mp-gold); font-weight: bold; font-size: 16px; line-height: 1;">ℹ</span>
                                <p style="margin: 0; font-size: 12px; color: #615c53; font-weight: 500; line-height: 1.5;">
                                    <strong>Belangrijke informatie:</strong> Omdat elk model volledig op maat wordt geproduceerd, kan het <strong>tot 2 weken duren</strong> voordat uw bestelling verzonden en bezorgd wordt.
                                </p>
                            </div>
                        </div>

                        <div class="mp-price-card" style="margin-top: 20px;">
                            <div class="mp-price-display">
                                <div class="price-value" id="total-price-display">€ 0,00</div>
                                <div class="price-label">Totaalprijs</div>
                            </div>

                            <div id="progress-container" style="display:none; margin-top: 20px;">
                                <div class="progress-info" id="progress-text" style="font-weight: bold; margin-bottom: 8px; color: var(--mp-gold);">Uploaden...</div>
                                <div class="progress-bar-bg"><div id="progress-bar" class="progress-bar-fill"></div></div>
                            </div>

                            <button type="submit" class="mp-btn-action-full" id="submit-btn" style="margin-top: 20px;">
                                Veilig afrekenen via Stripe
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    :root {
        --mp-bg: #e7e7e7; --mp-card-bg: #ffffff; --mp-header-bg: #2d2a26;
        --mp-accent: #7c2d2d; --mp-gold: #b08d57; --mp-border: #dcd7cc;
        --mp-text: #2d2a26; --mp-text-muted: #706a64; --mp-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    body { margin: 0; background: var(--mp-bg); font-family: 'Segoe UI', sans-serif; color: var(--mp-text); }
    .mp-header-full { background: var(--mp-header-bg); border-bottom: 4px solid var(--mp-gold); box-shadow: var(--mp-shadow); }
    .mp-header-inner { max-width: 800px; margin: 0 auto; padding: 25px 20px; display: flex; justify-content: space-between; align-items: center; }
    .mp-header-main-section { display: flex; align-items: center; gap: 15px; }
    .mp-header-brand-line { width: 4px; height: 35px; background: var(--mp-gold); border-radius: 2px; }
    .mp-main-title { font-size: 22px; font-weight: 800; color: #f1ede4; margin: 0; }
    .mp-sub-text { font-size: 11px; color: var(--mp-gold); font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }
    .mp-btn-back { background: transparent; border: 1px solid rgba(176, 141, 87, 0.4); padding: 10px 18px; border-radius: 2px; cursor: pointer; color: #f1ede4; font-size: 11px; font-weight: 700; text-transform: uppercase; text-decoration: none; display: flex; align-items: center; gap: 8px; }
    .mp-page-wrapper { padding: 40px 20px; display: flex; justify-content: center; }
    .mp-container-centered { width: 100%; max-width: 800px; }
    .mp-filter-box { background: var(--mp-card-bg); border-radius: 4px; overflow: hidden; box-shadow: var(--mp-shadow); border-top: 4px solid var(--mp-gold); }
    .mp-filter-header { font-size: 13px; font-weight: 700; padding: 15px 20px; border-bottom: 1px solid #f0efeb; text-transform: uppercase; background: #faf9f6; }
    .mp-form-padding { padding: 25px; }
    .mp-label-styled { font-size: 11px; font-weight: 700; color: var(--mp-text-muted); text-transform: uppercase; display: block; margin-bottom: 6px; }
    .mp-input-styled, .mp-select-styled { width: 100%; background: #f8f7f2; border: 1px solid var(--mp-border); padding: 14px; border-radius: 2px; box-sizing: border-box; font-size: 14px; }
    .mp-tag-static { background: #f1ede4; padding: 10px 15px; font-size: 13px; font-weight: 700; border: 1px solid var(--mp-border); border-radius: 2px; color: var(--mp-text-muted); }
    .mp-upload-zone { border: 2px dashed var(--mp-border); margin: 20px; padding: 40px; text-align: center; cursor: pointer; background: #f8f7f2; transition: 0.2s; }
    .upload-icon { font-size: 30px; color: var(--mp-gold); display: block; margin-bottom: 10px; }
    .upload-text { font-size: 13px; font-weight: 700; color: var(--mp-text-muted); }
    .mp-file-hidden { display: none; }
    .file-analysis-card { margin: 0 20px 15px 20px; padding: 18px; background: #fff; border: 1px solid var(--mp-border); border-left: 4px solid var(--mp-accent); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .mp-price-card { background: #f8f7f2; border: 1px solid var(--mp-border); padding: 25px; text-align: center; border-radius: 2px; }
    .price-value { font-size: 42px; font-weight: 900; color: var(--mp-accent); }
    .price-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--mp-text-muted); margin-top: 5px; }
    .mp-btn-action-full { width: 100%; background: var(--mp-accent); color: white; border: none; padding: 18px; font-weight: 700; text-transform: uppercase; cursor: pointer; border-radius: 2px; transition: 0.2s; }
    .progress-bar-bg { width: 100%; background: #edeae4; height: 12px; border-radius: 6px; overflow: hidden; border: 1px solid var(--mp-border); }
    .progress-bar-fill { height: 100%; background: var(--mp-gold); width: 0%; transition: 0.1s; }
</style>

<script>
    let filesData = [];
    const GRAM_PRICE = 0.01;
    const PLA_DENSITY = 0.00124;
    const INFILL_FACTOR = 0.30;
    const PROFIT_MULT = 1.60;
    const SHIPPING_THRESHOLD = 24.00;

    document.getElementById('stl_input').addEventListener('change', async function(e) {
        const files = e.target.files;
        if (!files.length) return;

        // 1. Bereken totaal MB
        let totalBytes = 0;
        for (let i = 0; i < files.length; i++) {
            totalBytes += files[i].size;
        }
        const totalMB = totalBytes / (1024 * 1024);

        // 2. Controleer limiet (150MB)
        if (totalMB > 150) {
            // Reset de input zodat de bestanden niet worden meegestuurd
            e.target.value = '';

            // Update de balk naar rood en toon foutstatus in de UI
            document.getElementById('mb-text').innerHTML = `<span style="color:#991b1b;">Fout: Totale grootte (${totalMB.toFixed(1)} MB) overschrijdt limiet van 150 MB. Selecteer kleinere bestanden.</span>`;
            document.getElementById('mb-progress-bar').style.width = '100%';
            document.getElementById('mb-progress-bar').style.backgroundColor = '#991b1b';

            // Verberg de rest van de interface
            document.getElementById('stl-analysis-list').innerHTML = '';
            document.getElementById('section-summary').style.display = 'none';
            return; // STOP HIER
        }

        // 3. Als we hier komen, is het onder de limiet: update balk
        const percent = (totalMB / 150) * 100;
        document.getElementById('mb-text').innerText = `${totalMB.toFixed(1)} MB / 150 MB gebruikt`;
        document.getElementById('mb-progress-bar').style.width = percent + '%';
        document.getElementById('mb-progress-bar').style.backgroundColor = 'var(--mp-accent)';

        // 4. Analyseer de bestanden (Three.js)
        const listContainer = document.getElementById('stl-analysis-list');
        listContainer.innerHTML = '<div style="padding:20px; text-align:center; font-size:12px; color:var(--mp-gold);">BESTANDEN ANALYSEREN...</div>';

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

                filesData.push({
                    id: i,
                    baseVolume: volume,
                    currentScale: 100,
                    currentQuantity: 1,
                    baseSize: { x: size.x, y: size.y, z: size.z }
                });

                const card = document.createElement('div');
                card.className = 'file-analysis-card';
                card.innerHTML = `
                <div style="flex-grow: 1;">
                    <span style="font-weight:700; color:var(--mp-text);">${file.name}</span>
                    <span id="size-display-${i}" style="display:block; font-size:12px; color:var(--mp-text-muted);">Formaat: ...</span>

                    <input type="hidden" name="heights[]" id="h-input-${i}">
                    <input type="hidden" name="widths[]" id="w-input-${i}">
                    <input type="hidden" name="depths[]" id="d-input-${i}">
                    <input type="hidden" name="prices[]" id="p-input-${i}">

                    <div style="display: flex; gap: 15px; margin-top:10px;">
                        <div style="flex: 1;">
                            <label class="mp-label-styled">Schaal</label>
                            <select name="scales[]" class="scale-selector mp-select-styled" data-id="${i}" style="padding:5px; font-size:12px; height:35px;">
                                <option value="100" selected>1.0x (Origineel)</option>
                                <option value="125">1.25x</option>
                                <option value="150">1.50x</option>
                                <option value="200">2.0x</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label class="mp-label-styled">Aantal</label>
                            <input type="number" name="quantities[]" class="quantity-selector mp-input-styled" data-id="${i}" value="1" min="1" step="1" style="padding:5px; font-size:12px; height:35px;">
                        </div>
                    </div>
                </div>
                <div id="price-file-${i}" style="font-weight:900; color:var(--mp-accent); font-size:20px; min-width: 100px; text-align: right;">€ 0,00</div>
            `;
                if(i === 0) listContainer.innerHTML = '';
                listContainer.appendChild(card);
                updateFileDetails(i);
            } catch (err) { console.error(err); }
        }
        updateTotalDisplay();
        document.getElementById('section-summary').style.display = 'block';
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('scale-selector') || e.target.classList.contains('quantity-selector')) {
            const id = parseInt(e.target.dataset.id);
            if (e.target.classList.contains('scale-selector')) {
                filesData[id].currentScale = parseInt(e.target.value);
            } else {
                filesData[id].currentQuantity = parseInt(e.target.value) || 1;
            }
            updateFileDetails(id);
            updateTotalDisplay();
        }
    });

    function updateFileDetails(id) {
        const file = filesData[id];
        const scaleFactor = file.currentScale / 100;
        const quantity = file.currentQuantity;

        const w = (file.baseSize.x * scaleFactor) / 10;
        const d = (file.baseSize.y * scaleFactor) / 10;
        const h = (file.baseSize.z * scaleFactor) / 10;

        document.getElementById(`size-display-${id}`).innerHTML = `Formaat: ${h.toFixed(1)}h x ${w.toFixed(1)}b x ${d.toFixed(1)}d cm`;

        document.getElementById(`h-input-${id}`).value = h.toFixed(2);
        document.getElementById(`w-input-${id}`).value = w.toFixed(2);
        document.getElementById(`d-input-${id}`).value = d.toFixed(2);

        const scaledVolumeMm3 = file.baseVolume * Math.pow(scaleFactor, 3);
        const weightInGram = scaledVolumeMm3 * INFILL_FACTOR * PLA_DENSITY;
        const materialCost = weightInGram * GRAM_PRICE;

        let baseProfit = 4.00;
        if (weightInGram < 50) baseProfit = 3.50;

        let pricePerUnit = Math.max(4.00, (materialCost * PROFIT_MULT) + baseProfit);
        let finalPrice = pricePerUnit * quantity;

        document.getElementById(`price-file-${id}`).innerText = "€ " + finalPrice.toFixed(2).replace('.', ',');
        document.getElementById(`p-input-${id}`).value = finalPrice.toFixed(2);
        file.lastCalculatedPrice = finalPrice;
    }

    function updateTotalDisplay() {
        let itemsSubTotal = 0;
        let totalQtyItems = 0;

        filesData.forEach(f => {
            itemsSubTotal += f.lastCalculatedPrice || 0;
            totalQtyItems += f.currentQuantity || 0;
        });

        const freeShippingText = document.getElementById('free-shipping-text');
        const freeShippingProgress = document.getElementById('free-shipping-progress');
        const discountNote = document.getElementById('stimulus-discount-note');

        let shippingCosts = 8.50;
        let appliedDiscount = 0;

        if (totalQtyItems === 2) {
            shippingCosts = 7.50;
            appliedDiscount = 1;
        } else if (totalQtyItems >= 3) {
            shippingCosts = 6.50;
            appliedDiscount = 2;
        }

        if (itemsSubTotal >= SHIPPING_THRESHOLD || totalQtyItems === 0) {
            if (freeShippingText) freeShippingText.innerHTML = '<span style="color: #16a34a;">Gefeliciteerd! Je hebt GRATIS verzending</span>';
            if (freeShippingProgress) freeShippingProgress.style.width = '100%';
            shippingCosts = 0.00;
            if (discountNote) discountNote.style.display = 'none';
        } else {
            let remainder = SHIPPING_THRESHOLD - itemsSubTotal;
            let percentage = (itemsSubTotal / SHIPPING_THRESHOLD) * 100;

            if (freeShippingText) {
                freeShippingText.innerText = `Nog € ${remainder.toFixed(2).replace('.', ',')} tot gratis verzending`;
            }
            if (freeShippingProgress) {
                freeShippingProgress.style.width = percentage + '%';
            }

            if (appliedDiscount > 0 && totalQtyItems > 0) {
                if (discountNote) {
                    discountNote.innerText = `€ ${appliedDiscount},00 Verzend korting toegepast!`;
                    discountNote.style.display = 'block';
                }
            } else {
                if (discountNote) discountNote.style.display = 'none';
            }
        }

        const shippingDisplay = document.getElementById('shipping-costs-display');
        if (shippingDisplay) {
            if (shippingCosts === 0 && totalQtyItems > 0) {
                shippingDisplay.innerHTML = '<span style="color: #16a34a; font-weight: 800;">GRATIS</span>';
            } else {
                shippingDisplay.textContent = "€ " + shippingCosts.toLocaleString('nl-NL', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }

        let grandTotal = itemsSubTotal + shippingCosts;

        document.getElementById('total-price-display').innerText = "€ " + grandTotal.toFixed(2).replace('.', ',');
        document.getElementById('total_price_input').value = grandTotal.toFixed(2);
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

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                document.getElementById('progress-bar').style.width = percent + '%';
                document.getElementById('progress-text').innerText = `Uploaden: ${percent}%`;
            }
        });

        xhr.onload = () => {
            try {
                const res = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && res.success && res.stripe_url) {
                    // VERANDERING: Als het opslaan gelukt is, sturen we de gebruiker direct naar Stripe!
                    window.location.href = res.stripe_url;
                } else {
                    alert("Fout: " + (res.message || "Onbekende fout bij opslaan."));
                    document.getElementById('submit-btn').disabled = false;
                }
            } catch (err) {
                alert("Server Error. Controleer je logs.");
                document.getElementById('submit-btn').disabled = false;
            }
        };

        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });
</script>
