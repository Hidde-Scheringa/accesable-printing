<div class="mp-page-wrapper">
    <div class="mp-container">

        <header class="mp-header-card">
            <div class="mp-header-info">
                <h1 class="mp-main-title">Aanvraag Afronden</h1>
                <span class="mp-sub-text">Controleer de specificaties en verzendgegevens</span>
            </div>
            <div class="mp-header-actions">
                <a href="{{ route('catalog.selection') }}" class="mp-btn-secondary">← Terug naar selectie</a>
            </div>
        </header>

        <div id="form-error-alert" class="mp-error-alert" style="display: none;">
            <strong>Oeps! Er ging iets mis:</strong>
            <ul id="error-list"></ul>
        </div>

        <div class="mp-checkout-layout">
            <main class="mp-checkout-main">
                <div class="mp-form-card">
                    <form id="catalog-checkout-form" method="POST">
                        @csrf
                        <input type="hidden" name="total_price_hidden" id="total_price_hidden" value="0.00">

                        <div class="mp-form-section">
                            <h3 class="mp-section-title">1. Project Details</h3>
                            <div class="mp-form-group">
                                <label class="mp-input-label">Titel van je aanvraag</label>
                                <input type="text" name="title" value="{{ old('title', 'Mijn Print Aanvraag') }}" class="mp-input-field" required>
                            </div>
                            <div class="mp-form-group" style="margin-top: 15px;">
                                <label class="mp-input-label">Aanvullende omschrijving (optioneel)</label>
                                <textarea name="description" class="mp-input-field mp-textarea" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="mp-form-section" style="margin-top: 30px;">
                            <h3 class="mp-section-title">2. Verzendadres</h3>
                            <div class="mp-form-row" style="grid-template-columns: 2fr 1fr;">
                                <div class="mp-form-group"><label class="mp-input-label">Straatnaam</label><input type="text" name="street" class="mp-input-field" required></div>
                                <div class="mp-form-group"><label class="mp-input-label">Huisnummer</label><input type="text" name="streetnumber" class="mp-input-field" required></div>
                            </div>
                            <div class="mp-form-row">
                                <div class="mp-form-group"><label class="mp-input-label">Postcode</label><input type="text" name="zipcode" class="mp-input-field" required></div>
                                <div class="mp-form-group"><label class="mp-input-label">Stad</label><input type="text" name="city" class="mp-input-field" required></div>
                            </div>
                        </div>

                        <div class="mp-form-section" style="margin-top: 30px;">
                            <h3 class="mp-section-title">3. Product Specificaties</h3>
                            @foreach($items as $item)
                                @php $qty = $selection[$item->id]['quantity'] ?? 1; @endphp
                                <div class="mp-item-config-block">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                        <h4 class="mp-item-title" style="margin:0;">{{ $item->title }}</h4>
                                        <span style="background: var(--mp-gold); color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 800;">{{ $qty }}x</span>
                                    </div>
                                    <div class="mp-form-row" style="grid-template-columns: 1fr 1fr 1fr;">
                                        <div class="mp-form-group"><label class="mp-input-label">Kleur</label>
                                            <select name="colors[{{ $item->id }}]" class="mp-select"><option>Grijs</option><option>Zwart</option><option>Wit</option></select>
                                        </div>
                                        <div class="mp-form-group"><label class="mp-input-label">Materiaal</label>
                                            <select name="materials[{{ $item->id }}]" class="mp-select"><option>FDM</option><option>Resin</option></select>
                                        </div>
                                        <div class="mp-form-group"><label class="mp-input-label">Schaal</label>
                                            <select name="scales[{{ $item->id }}]" class="mp-select mp-scale-item-selector" data-id="{{ $item->id }}">
                                                <option value="100">1.0x (100%)</option><option value="125">1.25x (125%)</option><option value="150">1.50x (150%)</option><option value="200">2.0x (200%)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" name="calculated_prices[{{ $item->id }}]" id="calculated-price-hidden-{{ $item->id }}" value="0.00">
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" id="submit-btn" class="mp-btn-submit">Aanvraag Bevestigen & Betalen</button>
                    </form>
                </div>
            </main>

            <aside class="mp-checkout-sidebar">
                <div class="mp-summary-card">
                    <h3 class="mp-summary-title">Overzicht</h3>
                    <div class="mp-summary-list">
                        @foreach($items as $item)
                            <div class="mp-summary-item-wrapper"
                                 id="summary-item-{{ $item->id }}"
                                 data-base-price="{{ $item->price }}"
                                 data-base-volume="{{ $item->total_volume_mm3 ?? 0 }}"
                                 data-qty="{{ $selection[$item->id]['quantity'] ?? 1 }}">
                                <div class="mp-item-header">
                                    <span class="item-name"><strong>{{ $selection[$item->id]['quantity'] ?? 1 }}x</strong> {{ $item->title }}</span>
                                    <span class="item-price" id="price-display-{{ $item->id }}">€ 0,00</span>
                                </div>
                            </div>
                            <hr class="mp-divider">
                        @endforeach
                    </div>
                    <div id="free-shipping-container" style="margin-top: 10px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">
                            <span id="free-shipping-text" style="color: #706a64;">Verzendkosten</span>
                        </div>
                        <div style="background: #e5e7eb; height: 6px; border-radius: 3px; overflow: hidden;">
                            <div id="free-shipping-progress" style="background: var(--mp-gold); height: 100%; width: 0%; transition: width 0.3s;"></div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; margin-bottom: 5px;">
                        <span style="color: #706a64;">Verzend- & Verpakkingskosten:</span>
                        <span id="shipping-costs-display" style="font-weight: 700;">€ 8,50</span>
                    </div>
                    <div class="mp-summary-total">
                        <span>Totaal:</span>
                        <span id="checkout-total-display">€ 0,00</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<style>
    :root { --mp-bg: #f4f4f2; --mp-gold: #b08d57; --mp-accent: #7c2d2d; --mp-text: #2d2a26; --mp-border: #dcd7cc; }
    .mp-page-wrapper { background: var(--mp-bg); min-height: 100vh; padding: 40px 20px; font-family: 'Segoe UI', sans-serif; color: var(--mp-text); }
    .mp-container { max-width: 1100px; margin: 0 auto; }
    .mp-header-card { background: #2d2a26; padding: 25px; border-radius: 4px; border-bottom: 4px solid var(--mp-gold); display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; color: #fff; }
    .mp-main-title { font-size: 22px; margin: 0; }
    .mp-sub-text { color: var(--mp-gold); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
    .mp-checkout-layout { display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
    .mp-form-card { background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .mp-section-title { font-size: 14px; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid var(--mp-gold); margin-bottom: 20px; display: inline-block; padding-bottom: 5px; }
    .mp-form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
    .mp-input-label { font-size: 11px; font-weight: 700; color: #706a64; text-transform: uppercase; margin-bottom: 5px; }
    .mp-input-field { padding: 12px; border: 1px solid var(--mp-border); border-radius: 3px; font-size: 14px; }
    .mp-textarea { resize: vertical; min-height: 80px; }
    .mp-form-row { display: grid; gap: 15px; margin-bottom: 10px; }
    .mp-select { padding: 10px; border: 1px solid var(--mp-border); border-radius: 3px; background: #fff; }
    .mp-item-config-block { margin-bottom: 20px; padding: 15px; background: #fafafa; border: 1px solid #eee; border-radius: 4px; }
    .mp-item-title { font-size: 15px; color: var(--mp-accent); font-weight: 700; }
    .mp-summary-card { background: #fff; padding: 25px; border-radius: 4px; border-top: 5px solid var(--mp-gold); position: sticky; top: 20px; }
    .mp-summary-title { font-size: 16px; text-transform: uppercase; font-weight: 800; margin-bottom: 20px; border-bottom: 1px solid var(--mp-border); padding-bottom: 10px; }
    .mp-item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; font-size: 14px; }
    .mp-divider { border: 0; border-top: 1px solid #eee; margin: 15px 0; }
    .mp-summary-total { display: flex; justify-content: space-between; font-weight: 800; font-size: 22px; color: var(--mp-accent); margin-top: 20px; padding-top: 15px; border-top: 2px solid var(--mp-border); }
    .mp-btn-submit { background: var(--mp-accent); color: #fff; border: none; padding: 18px; width: 100%; font-weight: 800; text-transform: uppercase; cursor: pointer; border-radius: 3px; margin-top: 20px; }
    .mp-btn-secondary { color: var(--mp-gold); text-decoration: none; font-size: 11px; font-weight: 700; border: 1px solid var(--mp-gold); padding: 8px 15px; border-radius: 2px; }
    .mp-error-alert { background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 4px; margin-bottom: 25px; font-size: 14px; }
    @media (max-width: 850px) { .mp-checkout-layout { grid-template-columns: 1fr; } }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const GRAM_PRICE = 0.01;
        const PLA_DENSITY = 0.00124;
        const INFILL_FACTOR = 0.30;
        const PROFIT_MULT = 1.60;
        const SHIPPING_THRESHOLD = 24.00;

        const scaleSelectors = document.querySelectorAll('.mp-scale-item-selector');
        const checkoutForm = document.getElementById('catalog-checkout-form');
        const submitBtn = document.getElementById('submit-btn');

        function updateSummary() {
            let itemsSubTotal = 0;
            let totalQty = 0;

            scaleSelectors.forEach(selector => {
                const itemId = selector.getAttribute('data-id');
                const scaleFactor = parseInt(selector.value) / 100;
                const itemWrapper = document.getElementById('summary-item-' + itemId);

                if (!itemWrapper) return;

                const basePrice = parseFloat(itemWrapper.dataset.basePrice);
                const baseVolume = parseFloat(itemWrapper.dataset.baseVolume);
                const qty = parseInt(itemWrapper.dataset.qty);
                totalQty += qty;

                // 1. Berekening zoals in je upload-form
                const scaledVolumeMm3 = baseVolume * Math.pow(scaleFactor, 3);
                const weightInGram = scaledVolumeMm3 * INFILL_FACTOR * PLA_DENSITY;
                const materialCost = weightInGram * GRAM_PRICE;
                let baseProfit = (weightInGram < 50) ? 3.50 : 4.00;
                let calculatedPriceAtScale = Math.max(4.00, (materialCost * PROFIT_MULT) + baseProfit);

                // 2. Bepaal referentie voor 100%
                const refVolume = baseVolume * INFILL_FACTOR * PLA_DENSITY;
                const refPrice = Math.max(4.00, ((refVolume * GRAM_PRICE) * PROFIT_MULT) + (refVolume < 50 ? 3.50 : 4.00));

                // 3. Pas correctiefactor toe om database prijs aan te houden
                const correctionFactor = basePrice / refPrice;
                const finalPricePerUnit = calculatedPriceAtScale * correctionFactor;
                const itemTotal = finalPricePerUnit * qty;

                document.getElementById('price-display-' + itemId).textContent = "€ " + itemTotal.toFixed(2).replace('.', ',');
                document.getElementById('calculated-price-hidden-' + itemId).value = itemTotal.toFixed(2);
                itemsSubTotal += itemTotal;
            });

            let shippingCosts = (itemsSubTotal >= SHIPPING_THRESHOLD) ? 0.00 : 8.50;
            let grandTotal = itemsSubTotal + shippingCosts;

            document.getElementById('checkout-total-display').textContent = "€ " + grandTotal.toFixed(2).replace('.', ',');
            document.getElementById('total_price_hidden').value = grandTotal.toFixed(2);
            document.getElementById('shipping-costs-display').textContent = (shippingCosts === 0) ? "GRATIS" : "€ " + shippingCosts.toFixed(2).replace('.', ',');

            const progress = Math.min((itemsSubTotal / SHIPPING_THRESHOLD) * 100, 100);
            document.getElementById('free-shipping-progress').style.width = progress + '%';
        }

        scaleSelectors.forEach(s => s.addEventListener('change', updateSummary));
        updateSummary();

        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.innerText = "Bezig met verwerken...";

                fetch("{{ route('catalog.process') }}", {
                    method: "POST",
                    body: new FormData(checkoutForm),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) window.location.href = data.stripe_url;
                        else throw new Error(data.message);
                    })
                    .catch(err => {
                        alert(err.message);
                        submitBtn.disabled = false;
                        submitBtn.innerText = "Aanvraag Bevestigen & Betalen";
                    });
            });
        }
    });
</script>
