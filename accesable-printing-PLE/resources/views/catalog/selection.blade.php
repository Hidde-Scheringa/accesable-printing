<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/STLLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<div class="mp-page-wrapper">
    <header class="mp-full-header">
        <div class="mp-header-content">
            <div class="mp-header-info">
                <h1 class="mp-main-title">Geselecteerde Modellen</h1>
                <span class="mp-sub-text">Controleer uw selectie voordat u het verzoek indient</span>
            </div>
            <div class="mp-header-actions">
                <a href="{{ route('catalog.index') }}" class="mp-btn-secondary">
                    ← Verder winkelen
                </a>
            </div>
        </div>
    </header>

    <div class="mp-container">
        <form action="{{ route('catalog.checkout') }}" method="GET" id="checkout-form">
            <div class="mp-layout-grid">
                <main class="mp-content">
                    <div class="mp-list-stack">
                        @forelse($items as $item)
                            <article class="mp-item-card" data-item-id="{{ $item->id }}" style="min-height: auto; align-items: center;">
                                <div class="mp-item-visual-container" style="width: 100px; height: 100px; flex-shrink: 0; background: #f7f6f2; border-right: 1px solid var(--mp-border);">
                                    <div class="stl-viewer"
                                         id="viewer-{{ $item->id }}"
                                         style="width: 100%; height: 100%;"
                                         data-url="{{ asset('storage/' . ($item->stl_files[0]['path'] ?? '')) }}">
                                    </div>
                                </div>

                                <div class="mp-item-details" style="padding: 15px 25px; flex-direction: row; align-items: center; justify-content: space-between; flex-grow: 1; display: flex;">
                                    <div>
                                        <h3 class="mp-item-name" style="font-size: 16px; border-left: 3px solid var(--mp-gold);">{{ $item->title }}</h3>
                                        <span class="mp-label" style="margin-top: 4px; padding-left: 13px;">{{ $item->category ?? 'Algemeen' }}</span>
                                    </div>

                                    <div class="mp-item-controls">
                                        <div class="mp-quantity-selector">
                                            <label for="qty-{{ $item->id }}">Aantal:</label>
                                            <input type="number"
                                                   id="qty-{{ $item->id }}"
                                                   name="quantities[{{ $item->id }}]"
                                                   value="{{ session('print_selection.' . $item->id . '.qty', session('print_selection.' . $item->id . '.quantity', 1)) }}"
                                                   min="1" max="99"
                                                   class="mp-input-qty qty-input">
                                        </div>

                                        @if($item->price)
                                            <span class="mp-scale-value" style="font-size: 16px;">
                                                €<span class="item-total-price" data-unit-price="{{ $item->price }}">{{ number_format($item->price, 2, ',', '.') }}</span>
                                            </span>
                                        @endif

                                        <button type="button" class="mp-remove-btn" title="Verwijder uit selectie" onclick="removeItem({{ $item->id }})">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="mp-empty-card">
                                <p style="margin-bottom: 20px;">Je hebt nog geen modellen geselecteerd uit de catalogus.</p>
                                <a href="{{ route('catalog.index') }}" class="mp-btn-action">
                                    Bekijk de Catalogus
                                </a>
                            </div>
                        @endforelse
                    </div>
                </main>

                <aside class="mp-sidebar">
                    <div class="mp-filter-box" style="border-top: 4px solid var(--mp-gold); position: sticky; top: 20px;">
                        <h3 class="mp-filter-header">Samenvatting</h3>

                        <div class="mp-summary-row">
                            <span class="mp-text-muted">Totaal artikelen:</span>
                            <span id="summary-total-qty" style="font-weight: 700;">{{ $items->count() }}</span>
                        </div>

                        {{-- DOCUMENTATIE: HTML component voor de gratis verzending balk, aangepast naar € 24,- --}}
                        <div id="free-shipping-container" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--mp-border);">
                            <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">
                                <span id="free-shipping-text" style="color: var(--mp-text-muted);">Nog € 24,00 tot gratis verzending</span>
                                <span style="color: var(--mp-gold);">Gratis vanaf € 24,-</span>
                            </div>
                            <div class="progress-bar-bg" style="height: 6px; width: 100%; background: #edeae4; border-radius: 3px; overflow: hidden;">
                                <div id="free-shipping-progress" class="progress-bar-fill" style="width: 0%; height: 100%; background: var(--mp-gold); transition: width 0.2s;"></div>
                            </div>
                        </div>

                        <div class="mp-summary-row" style="margin-top: 15px;">
                            <span class="mp-text-muted">Verzend- & Verpakkingskosten:</span>
                            <span id="shipping-costs-display" style="font-weight: 700; color: var(--mp-text);">€ 8,50</span>
                        </div>

                        {{-- Melding over de stapelkorting --}}
                        <div id="stimulus-discount-note" style="text-align: right; font-size: 12px; color: #16a34a; font-weight: 700; margin-top: 5px; display: none;">
                            Stapelkorting toegepast!
                        </div>

                        <div class="mp-summary-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--mp-border);">
                            <span style="font-weight: 700;">Totaal indicatie:</span>
                            <span class="mp-scale-value" style="font-size: 20px;">
                                €<span id="summary-grand-total">0,00</span>
                            </span>
                        </div>

                        <div style="margin-top: 30px;">
                            @if($items->count() > 0)
                                @auth
                                    <button type="submit" class="mp-btn-action mp-btn-full">
                                        Verzoek Indienen
                                    </button>
                                @else
                                    <div class="mp-auth-prompt">
                                        <p style="font-size: 13px; color: var(--mp-text); margin-bottom: 15px; font-weight: 500;">
                                            U moet ingelogd zijn om een printverzoek in te dienen.
                                        </p>
                                        <a href="{{ route('login') }}" class="mp-btn-action mp-btn-full" style="background: var(--mp-header-bg); color: white; margin-bottom: 10px;">
                                            Inloggen
                                        </a>
                                    </div>
                                @endauth

                                <a href="{{ route('catalog.clear') }}"
                                   style="display: block; text-align: center; margin-top: 20px; font-size: 11px; color: #a0a0a0; text-decoration: none; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
                                    Winkelwagen legen
                                </a>
                            @endif
                        </div>
                    </div>
                </aside>
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
        --mp-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .mp-page-wrapper { background: var(--mp-bg); min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
    .mp-full-header { background: var(--mp-header-bg); padding: 30px 0; border-bottom: 4px solid var(--mp-gold); margin-bottom: 40px; }
    .mp-header-content { max-width: 1100px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
    .mp-container { max-width: 1100px; margin: 0 auto; padding: 0 20px 40px 20px; }
    .mp-main-title { font-size: 24px; font-weight: 700; color: #f1ede4; margin: 0; }
    .mp-sub-text { font-size: 13px; color: var(--mp-gold); text-transform: uppercase; letter-spacing: 1px; }
    .mp-layout-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; }
    .mp-item-card { background: var(--mp-card-bg); border-radius: 4px; display: flex; overflow: hidden; margin-bottom: 15px; box-shadow: var(--mp-shadow); }
    .mp-item-name { font-weight: 700; color: var(--mp-text); margin: 0; padding-left: 10px; }
    .mp-label { font-size: 10px; font-weight: 700; color: var(--mp-text-muted); text-transform: uppercase; display: block; }
    .mp-scale-value { font-weight: 700; color: var(--mp-accent); }
    .mp-item-controls { display: flex; align-items: center; gap: 25px; }
    .mp-quantity-selector { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; }
    .mp-input-qty { width: 50px; padding: 5px; border: 1px solid var(--mp-border); border-radius: 4px; text-align: center; }
    .mp-filter-box { background: var(--mp-card-bg); border-radius: 4px; padding: 24px; box-shadow: var(--mp-shadow); }
    .mp-filter-header { font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 20px; border-bottom: 1px solid var(--mp-border); padding-bottom: 10px; }
    .mp-summary-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
    .mp-btn-action { background: var(--mp-accent); color: #f1ede4; text-decoration: none; padding: 14px 20px; border-radius: 2px; font-weight: 600; text-transform: uppercase; border: none; cursor: pointer; text-align: center; }
    .mp-btn-full { width: 100%; display: block; background: var(--mp-gold); color: #2d2a26; }
    .mp-btn-secondary { border: 1px solid rgba(255,255,255,0.2); padding: 10px 18px; color: #f1ede4; text-decoration: none; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .mp-remove-btn { background: transparent; border: 1px solid #eee; color: #ccc; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .mp-remove-btn:hover { border-color: var(--mp-accent); color: var(--mp-accent); }
    .mp-empty-card { background: var(--mp-card-bg); border: 2px dashed var(--mp-gold); padding: 60px; text-align: center; border-radius: 4px; }

    .mp-auth-prompt {
        width: 100%;
        box-sizing: border-box;
        padding: 15px;
        background: #fdfdfd;
        border: 1px solid var(--mp-border);
        border-radius: 4px;
    }

    /* Forceer de knop om altijd 100% breedte te zijn zonder overflow door padding */
    .mp-btn-full {
        width: 100%;
        display: block;
        box-sizing: border-box; /* Cruciaal: zorgt dat padding binnen de breedte valt */
        margin: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qtyInputs = document.querySelectorAll('.qty-input');
        const SHIPPING_THRESHOLD = 24.00;

        function updatePrices() {
            let itemsSubTotal = 0;
            let totalQtyItems = 0;

            document.querySelectorAll('.mp-item-card').forEach(card => {
                const qtyInput = card.querySelector('.qty-input');
                const qty = parseInt(qtyInput.value) || 0;
                const priceLabel = card.querySelector('.item-total-price');

                if (priceLabel) {
                    const unitPrice = parseFloat(priceLabel.getAttribute('data-unit-price'));
                    const itemTotal = qty * unitPrice;
                    priceLabel.textContent = itemTotal.toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    itemsSubTotal += itemTotal;
                }
                totalQtyItems += qty;
            });

            const freeShippingText = document.getElementById('free-shipping-text');
            const freeShippingProgress = document.getElementById('free-shipping-progress');
            const discountNote = document.getElementById('stimulus-discount-note');

            // 1. Bereken de dynamische verzendkosten o.b.v. het aantal items (Stapelkorting)
            let shippingCosts = 8.50;
            let appliedDiscount = 0;

            if (totalQtyItems === 2) {
                shippingCosts = 7.50;
                appliedDiscount = 1;
            } else if (totalQtyItems >= 3) {
                shippingCosts = 6.50;
                appliedDiscount = 2;
            }

            // 2. Controleer of gratis verzending drempel is bereikt
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

                // Toon de stapelkorting notificatie indien van toepassing
                if (appliedDiscount > 0 && totalQtyItems > 0) {
                    if (discountNote) {
                        discountNote.innerText = `€ ${appliedDiscount},00 verzend korting toegepast!`;
                        discountNote.style.display = 'block';
                    }
                } else {
                    if (discountNote) discountNote.style.display = 'none';
                }
            }

            // 3. Update verzendkosten display
            const shippingDisplay = document.getElementById('shipping-costs-display');
            if (shippingDisplay) {
                if (shippingCosts === 0 && totalQtyItems > 0) {
                    shippingDisplay.innerHTML = '<span style="color: #16a34a; font-weight: 800;">GRATIS</span>';
                } else {
                    shippingDisplay.textContent = "€ " + shippingCosts.toLocaleString('nl-NL', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            }

            // 4. Berekening van het definitieve eindtotaal
            let grandTotal = itemsSubTotal + shippingCosts;

            const summaryGrandTotal = document.getElementById('summary-grand-total');
            const summaryTotalQty = document.getElementById('summary-total-qty');

            if (summaryGrandTotal) summaryGrandTotal.textContent = grandTotal.toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (summaryTotalQty) summaryTotalQty.textContent = totalQtyItems;
        }

        qtyInputs.forEach(input => {
            input.addEventListener('input', updatePrices);
        });

        updatePrices();

        document.querySelectorAll('.stl-viewer').forEach(container => {
            initSTLViewer(container);
        });
    });

    function removeItem(itemId) {
        window.location.href = "{{ url('/catalogus/remove') }}/" + itemId;
    }

    function initSTLViewer(container) {
        const url = container.getAttribute('data-url');
        if(!url) return;

        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf7f6f2);
        const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(container.clientWidth, container.clientHeight);
        container.appendChild(renderer.domElement);

        scene.add(new THREE.HemisphereLight(0xffffff, 0x444444, 1.2));
        const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
        dirLight.position.set(5, 10, 7.5);
        scene.add(dirLight);

        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        const loader = new THREE.STLLoader();

        loader.load(url, function (geometry) {
            geometry.computeBoundingBox();
            const center = new THREE.Vector3();
            geometry.boundingBox.getCenter(center);
            geometry.translate(-center.x, -center.y, -center.z);

            const mesh = new THREE.Mesh(geometry, new THREE.MeshPhongMaterial({ color: 0x5a554f }));
            mesh.rotation.x = -Math.PI / 2;
            scene.add(mesh);

            const size = geometry.boundingBox.getSize(new THREE.Vector3()).length();
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
</script>
