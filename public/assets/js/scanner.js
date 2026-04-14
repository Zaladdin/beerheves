(function () {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-document-editor]').forEach((root) => {
            initDocumentEditor(root);
        });
    });

    function initDocumentEditor(root) {
        const form = root.querySelector('#document-form');
        const barcodeInput = root.querySelector('#barcode-input');
        const docTypeSelect = root.querySelector('#doc-type-select');
        const sourceSelect = root.querySelector('#source-warehouse-select');
        const targetSelect = root.querySelector('#target-warehouse-select');
        const sourceWrap = root.querySelector('[data-source-warehouse-wrap]');
        const targetWrap = root.querySelector('[data-target-warehouse-wrap]');
        const itemsBody = root.querySelector('#document-items-body');
        const totalElement = root.querySelector('#document-grand-total');
        const itemsPayloadInput = root.querySelector('#items-payload');
        const documentStatusInput = root.querySelector('#document-status');
        const feedback = root.querySelector('#scan-feedback');
        const quickModalElement = root.querySelector('#quickProductModal');
        const quickProductForm = root.querySelector('#quick-product-form');
        const quickProductError = root.querySelector('#quick-product-error');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const quickModal = quickModalElement && window.bootstrap?.Modal
            ? new window.bootstrap.Modal(quickModalElement)
            : null;

        if (!form || !barcodeInput || !docTypeSelect || !itemsBody || !totalElement || !itemsPayloadInput || !documentStatusInput) {
            return;
        }

        let items = parseInitialItems(root.dataset.initialItems || '[]');

        updateWarehouseVisibility();
        renderItems();
        focusBarcode();

        barcodeInput.addEventListener('keydown', async (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            const barcode = barcodeInput.value.trim();

            if (!barcode) {
                return;
            }

            await handleBarcode(barcode);
        });

        docTypeSelect.addEventListener('change', () => {
            updateWarehouseVisibility();
            focusBarcode();
        });

        sourceSelect?.addEventListener('change', focusBarcode);
        targetSelect?.addEventListener('change', focusBarcode);

        itemsBody.addEventListener('input', (event) => {
            const target = event.target;
            const row = target.closest('tr[data-index]');

            if (!row) {
                return;
            }

            const index = Number(row.dataset.index);
            if (!items[index]) {
                return;
            }

            if (target.matches('[data-qty-input]')) {
                items[index].qty = Math.max(0.001, toNumber(target.value, 1));
            }

            if (target.matches('[data-price-input]')) {
                items[index].price = Math.max(0, toNumber(target.value, 0));
            }

            renderItems();
        });

        itemsBody.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-index]');

            if (!button) {
                return;
            }

            const index = Number(button.dataset.removeIndex);
            items.splice(index, 1);
            renderItems();
            showFeedback('Позиция удалена.', 'default');
            focusBarcode();
        });

        form.querySelectorAll('[data-submit-status]').forEach((button) => {
            button.addEventListener('click', () => {
                documentStatusInput.value = button.dataset.submitStatus || 'draft';
                itemsPayloadInput.value = JSON.stringify(serializeItems(items));
            });
        });

        form.addEventListener('submit', () => {
            itemsPayloadInput.value = JSON.stringify(serializeItems(items));
        });

        quickProductForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!quickProductForm) {
                return;
            }

            quickProductError?.classList.add('d-none');
            const payload = new URLSearchParams(new FormData(quickProductForm));
            payload.set('_token', csrfToken);

            try {
                const response = await fetch(root.dataset.quickCreateUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-Token': csrfToken,
                    },
                    body: payload,
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Не удалось создать товар.');
                }

                addOrIncrementItem(data.product);
                quickModal?.hide();
                quickProductForm.reset();
                showFeedback(`Товар создан и добавлен: ${data.product.name}`, 'success');
                playTone(true);
                focusBarcode();
            } catch (error) {
                if (quickProductError) {
                    quickProductError.textContent = error.message || 'Ошибка при создании товара.';
                    quickProductError.classList.remove('d-none');
                }
                playTone(false);
            }
        });

        async function handleBarcode(barcode) {
            barcodeInput.disabled = true;
            showFeedback(`Поиск barcode ${barcode}...`, 'default');

            try {
                const url = new URL(root.dataset.lookupUrl, window.location.origin);
                url.searchParams.set('barcode', barcode);

                const warehouseId = getRelevantWarehouseId();
                if (warehouseId) {
                    url.searchParams.set('warehouse_id', warehouseId);
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Ошибка поиска товара.');
                }

                if (!data.found) {
                    openQuickCreate(barcode);
                    showFeedback(`Barcode ${barcode} не найден. Открыл форму быстрого создания.`, 'error');
                    playTone(false);
                    return;
                }

                addOrIncrementItem(data.product);
                showFeedback(`Добавлен товар: ${data.product.name}`, 'success');
                playTone(true);
            } catch (error) {
                showFeedback(error.message || 'Ошибка поиска товара.', 'error');
                playTone(false);
            } finally {
                barcodeInput.disabled = false;
                barcodeInput.value = '';
                focusBarcode();
            }
        }

        function addOrIncrementItem(product) {
            const normalized = normalizeProduct(product);
            const existing = items.find((item) => String(item.product_id) === String(normalized.product_id));

            if (existing) {
                existing.qty = round(existing.qty + 1, 3);
                existing.available_qty = normalized.available_qty;
            } else {
                items.push(normalized);
            }

            renderItems();
        }

        function renderItems() {
            if (items.length === 0) {
                itemsBody.innerHTML = '<tr class="empty-row"><td colspan="8" class="text-center text-muted py-5">Отсканируйте товар, чтобы добавить его в документ</td></tr>';
                totalElement.textContent = '$0.00';
                itemsPayloadInput.value = '[]';
                return;
            }

            itemsBody.innerHTML = items.map((item, index) => {
                const total = round(item.qty * item.price, 2);

                return `
                    <tr data-index="${index}">
                        <td>${escapeHtml(item.barcode)}</td>
                        <td>
                            <div class="fw-semibold">${escapeHtml(item.name)}</div>
                            <small class="text-muted">${escapeHtml(item.unit || '')}</small>
                        </td>
                        <td>${escapeHtml(item.article || '')}</td>
                        <td class="text-end">${formatQty(item.available_qty)}</td>
                        <td class="text-end">
                            <input type="number" min="0.001" step="0.001" class="form-control form-control-sm text-end" data-qty-input value="${item.qty}">
                        </td>
                        <td class="text-end">
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" data-price-input value="${item.price}">
                        </td>
                        <td class="text-end fw-semibold">$${formatMoney(total)}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-index="${index}">Удалить</button>
                        </td>
                    </tr>
                `;
            }).join('');

            const grandTotal = items.reduce((sum, item) => sum + round(item.qty * item.price, 2), 0);
            totalElement.textContent = `$${formatMoney(grandTotal)}`;
            itemsPayloadInput.value = JSON.stringify(serializeItems(items));
        }

        function updateWarehouseVisibility() {
            const type = docTypeSelect.value;
            const needSource = type === 'sale' || type === 'writeoff' || type === 'transfer';
            const needTarget = type === 'incoming' || type === 'transfer';

            toggleWrap(sourceWrap, needSource);
            toggleWrap(targetWrap, needTarget);
        }

        function toggleWrap(element, visible) {
            if (!element) {
                return;
            }

            element.classList.toggle('d-none', !visible);
            const field = element.querySelector('select');

            if (field) {
                field.required = visible;
            }
        }

        function openQuickCreate(barcode) {
            if (!quickProductForm) {
                return;
            }

            quickProductForm.reset();
            quickProductForm.elements.barcode.value = barcode;
            quickProductForm.elements.unit.value = 'pcs';
            quickProductForm.elements.purchase_price.value = '0.00';
            quickProductForm.elements.sale_price.value = '0.00';
            quickProductError?.classList.add('d-none');
            quickModal?.show();
        }

        function getRelevantWarehouseId() {
            const type = docTypeSelect.value;

            if (type === 'incoming') {
                return Number(targetSelect?.value || 0);
            }

            return Number(sourceSelect?.value || 0);
        }

        function showFeedback(message, state) {
            if (!feedback) {
                return;
            }

            feedback.textContent = message;
            feedback.classList.remove('is-success', 'is-error');

            if (state === 'success') {
                feedback.classList.add('is-success');
            }

            if (state === 'error') {
                feedback.classList.add('is-error');
            }
        }

        function focusBarcode() {
            window.setTimeout(() => barcodeInput.focus(), 40);
        }
    }

    function parseInitialItems(raw) {
        try {
            const parsed = JSON.parse(raw);

            if (!Array.isArray(parsed)) {
                return [];
            }

            return parsed.map((item) => normalizeProduct(item, true));
        } catch (error) {
            return [];
        }
    }

    function normalizeProduct(product, preserveQty) {
        const qty = preserveQty ? Math.max(0.001, toNumber(product.qty, 1)) : 1;
        const purchasePrice = toNumber(product.purchase_price ?? product.price, 0);
        const salePrice = toNumber(product.sale_price ?? product.price, 0);
        const docType = document.querySelector('#doc-type-select')?.value || 'incoming';

        return {
            product_id: Number(product.product_id || product.id),
            barcode: String(product.barcode || ''),
            name: String(product.name || product.product_name || ''),
            article: String(product.article || ''),
            unit: String(product.unit || 'pcs'),
            qty,
            purchase_price: purchasePrice,
            sale_price: salePrice,
            price: preserveQty ? toNumber(product.price, 0) : getDefaultPrice(docType, purchasePrice, salePrice),
            available_qty: toNumber(product.available_qty ?? product.stock_qty, 0),
        };
    }

    function getDefaultPrice(docType, purchasePrice, salePrice) {
        if (docType === 'sale') {
            return salePrice;
        }

        return purchasePrice;
    }

    function serializeItems(items) {
        return items.map((item) => ({
            product_id: item.product_id,
            barcode: item.barcode,
            name: item.name,
            article: item.article,
            unit: item.unit,
            qty: round(item.qty, 3),
            price: round(item.price, 2),
            purchase_price: round(item.purchase_price, 2),
            sale_price: round(item.sale_price, 2),
            available_qty: round(item.available_qty, 3),
        }));
    }

    function formatMoney(value) {
        return Number(value || 0).toFixed(2);
    }

    function formatQty(value) {
        return Number(value || 0).toFixed(3).replace(/\.?0+$/, '');
    }

    function toNumber(value, fallback) {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function round(value, precision) {
        const multiplier = 10 ** precision;
        return Math.round((Number(value) + Number.EPSILON) * multiplier) / multiplier;
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function playTone(success) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;

        if (!AudioContextClass) {
            return;
        }

        try {
            const context = new AudioContextClass();
            const oscillator = context.createOscillator();
            const gainNode = context.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(context.destination);
            oscillator.type = 'sine';
            oscillator.frequency.value = success ? 880 : 220;
            gainNode.gain.value = 0.05;
            oscillator.start();

            window.setTimeout(() => {
                oscillator.stop();
                context.close();
            }, 120);
        } catch (error) {
            // Ignore audio errors in browsers that restrict auto-play.
        }
    }
})();
