<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Counter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background: #f4f6f8; }
        .table td, .table th { vertical-align: middle; }
        #success-panel { display: none; }
        .note-breakdown dt { font-weight: 600; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ config('app.name') }}</h1>
            <p class="text-muted mb-0">Retail counter</p>
        </div>
    </div>

    <div id="alert-area"></div>

    <div id="success-panel" class="alert alert-success">
        <h2 class="h5">Order created</h2>
        <div id="success-details"></div>
        <button type="button" class="btn btn-sm btn-outline-success mt-2" id="btn-new-order">New order</button>
    </div>

    <form id="order-form">
        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Customer</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="customer-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer-email" required autocomplete="email">
                                <div class="form-text" id="customer-search-status"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="customer-name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="customer-name" required autocomplete="name">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning-subtle">
                        <strong>Low stock</strong>
                    </div>
                    <ul class="list-group list-group-flush" id="low-stock-list">
                        <li class="list-group-item text-muted">Loading…</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Order items</h2>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-row">Add row</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered bg-white mb-0" id="items-table">
                        <thead class="table-light">
                        <tr>
                            <th style="min-width: 220px;">Product</th>
                            <th style="width: 100px;">Qty</th>
                            <th style="width: 110px;">Price</th>
                            <th style="width: 120px;">Line Subtotal</th>
                            <th style="width: 110px;">Line Tax</th>
                            <th style="width: 130px;">Line Grand Total</th>
                            <th style="width: 70px;"></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Summary</h2>
                        <dl class="row mb-0">
                            <dt class="col-6">Subtotal</dt>
                            <dd class="col-6 text-end" id="summary-subtotal">0.00</dd>
                            <dt class="col-6">Tax Amount</dt>
                            <dd class="col-6 text-end" id="summary-tax">0.00</dd>
                            <dt class="col-6">Grand Total</dt>
                            <dd class="col-6 text-end fw-bold" id="summary-grand">0.00</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Payment (UI only)</h2>
                        <label for="amount-given" class="form-label">Amount given</label>
                        <input type="number" min="0" step="0.01" class="form-control mb-3" id="amount-given" value="0">
                        <dl class="row mb-0">
                            <dt class="col-6">Balance / Change</dt>
                            <dd class="col-6 text-end fw-bold" id="balance-amount">0.00</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Note breakdown</h2>
                        <dl class="row mb-0 note-breakdown" id="note-breakdown">
                            <dd class="col-12 text-muted mb-0">Enter amount given to see notes.</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary" id="btn-submit">Place order</button>
            <button type="button" class="btn btn-outline-secondary" id="btn-reset">Reset</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<script>
(function ($) {
    const DENOMINATIONS = [500, 200, 100, 50, 20, 10, 5, 2, 1];
    let products = [];

    function money(value) {
        return (Math.round((Number(value) + Number.EPSILON) * 100) / 100).toFixed(2);
    }

    function showAlert(type, message) {
        $('#alert-area').html(
            `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`
        );
    }

    function clearAlert() {
        $('#alert-area').empty();
    }

    function productById(id) {
        return products.find((product) => String(product.id) === String(id));
    }

    function selectedProductIds(exceptSelect) {
        const ids = [];
        $('#items-table tbody select.product-select').each(function () {
            if (exceptSelect && this === exceptSelect) {
                return;
            }
            const value = $(this).val();
            if (value) {
                ids.push(String(value));
            }
        });
        return ids;
    }

    function buildProductOptions(selectedId, currentSelect) {
        const taken = selectedProductIds(currentSelect);
        let html = '<option value="">Select product</option>';

        products.forEach((product) => {
            const id = String(product.id);
            if (taken.includes(id) && id !== String(selectedId || '')) {
                return;
            }
            const selected = id === String(selectedId || '') ? ' selected' : '';
            html += `<option value="${id}"${selected}>${product.name} (${product.sku}) — ₹${money(product.selling_price)}</option>`;
        });

        return html;
    }

    function refreshAllProductSelects() {
        $('#items-table tbody select.product-select').each(function () {
            const current = $(this).val();
            $(this).html(buildProductOptions(current, this));
            $(this).val(current);
        });
    }

    function recalculateRow($row) {
        const product = productById($row.find('.product-select').val());
        const qty = parseInt($row.find('.qty-input').val(), 10) || 0;

        if (!product || qty < 1) {
            $row.find('.unit-price').text('0.00');
            $row.find('.line-subtotal').text('0.00');
            $row.find('.line-tax').text('0.00');
            $row.find('.line-grand').text('0.00');
            recalculateSummary();
            return;
        }

        const unitPrice = Number(product.selling_price);
        const taxRate = Number(product.tax_rate);
        const lineSubtotal = Math.round(unitPrice * qty * 100) / 100;
        const lineTax = Math.round((lineSubtotal * taxRate / 100) * 100) / 100;
        const lineGrand = Math.round((lineSubtotal + lineTax) * 100) / 100;

        $row.find('.unit-price').text(money(unitPrice));
        $row.find('.line-subtotal').text(money(lineSubtotal));
        $row.find('.line-tax').text(money(lineTax));
        $row.find('.line-grand').text(money(lineGrand));
        recalculateSummary();
    }

    function recalculateSummary() {
        let subtotal = 0;
        let tax = 0;
        let grand = 0;

        $('#items-table tbody tr').each(function () {
            subtotal += Number($(this).find('.line-subtotal').text()) || 0;
            tax += Number($(this).find('.line-tax').text()) || 0;
            grand += Number($(this).find('.line-grand').text()) || 0;
        });

        subtotal = Math.round(subtotal * 100) / 100;
        tax = Math.round(tax * 100) / 100;
        grand = Math.round(grand * 100) / 100;

        $('#summary-subtotal').text(money(subtotal));
        $('#summary-tax').text(money(tax));
        $('#summary-grand').text(money(grand));
        updatePayment();
    }

    function breakdownNotes(amount) {
        let remaining = Math.floor(Number(amount) || 0);
        const parts = [];

        DENOMINATIONS.forEach((note) => {
            const count = Math.floor(remaining / note);
            if (count > 0) {
                parts.push({ note, count });
                remaining -= count * note;
            }
        });

        return parts;
    }

    function updatePayment() {
        const grand = Number($('#summary-grand').text()) || 0;
        const given = Number($('#amount-given').val()) || 0;
        const balance = Math.round((given - grand) * 100) / 100;

        $('#balance-amount').text(money(balance));

        if (given <= 0) {
            $('#note-breakdown').html('<dd class="col-12 text-muted mb-0">Enter amount given to see notes.</dd>');
            return;
        }

        const changeForNotes = balance > 0 ? balance : given;
        const parts = breakdownNotes(changeForNotes);

        if (!parts.length) {
            $('#note-breakdown').html('<dd class="col-12 text-muted mb-0">No whole-rupee notes required.</dd>');
            return;
        }

        let html = '';
        parts.forEach((part) => {
            html += `<dt class="col-6">₹${part.note}</dt><dd class="col-6 text-end">× ${part.count}</dd>`;
        });

        if (balance > 0) {
            html = `<dd class="col-12 small text-muted mb-2">Change breakdown</dd>` + html;
        } else {
            html = `<dd class="col-12 small text-muted mb-2">Amount-given breakdown</dd>` + html;
        }

        $('#note-breakdown').html(html);
    }

    function addRow(selectedProductId) {
        const $row = $(`
            <tr>
                <td>
                    <select class="form-select product-select" required></select>
                </td>
                <td>
                    <input type="number" class="form-control qty-input" min="1" step="1" value="1" required>
                </td>
                <td class="unit-price text-end">0.00</td>
                <td class="line-subtotal text-end">0.00</td>
                <td class="line-tax text-end">0.00</td>
                <td class="line-grand text-end">0.00</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">&times;</button>
                </td>
            </tr>
        `);

        $row.find('.product-select').html(buildProductOptions(selectedProductId, null));
        if (selectedProductId) {
            $row.find('.product-select').val(String(selectedProductId));
        }

        $('#items-table tbody').append($row);
        refreshAllProductSelects();
        recalculateRow($row);
    }

    function loadProducts() {
        return $.getJSON('/api/products').done(function (response) {
            products = response.data || [];
            if ($('#items-table tbody tr').length === 0) {
                addRow();
            } else {
                refreshAllProductSelects();
                $('#items-table tbody tr').each(function () {
                    recalculateRow($(this));
                });
            }
        }).fail(function () {
            showAlert('danger', 'Failed to load products.');
        });
    }

    function loadLowStock() {
        return $.getJSON('/api/products/low-stock').done(function (response) {
            const items = response.data || [];
            const $list = $('#low-stock-list').empty();

            if (!items.length) {
                $list.append('<li class="list-group-item text-muted">No low-stock products.</li>');
                return;
            }

            items.forEach((product) => {
                $list.append(
                    `<li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">${product.name}</div>
                            <small class="text-muted">${product.sku}</small>
                        </div>
                        <span class="badge text-bg-warning">${product.qty} / ${product.min_qty_level}</span>
                    </li>`
                );
            });
        }).fail(function () {
            $('#low-stock-list').html('<li class="list-group-item text-danger">Failed to load low stock.</li>');
        });
    }

    function searchCustomer() {
        const email = $('#customer-email').val().trim();
        const $status = $('#customer-search-status');

        if (!email) {
            $status.text('');
            $('#customer-name').prop('readonly', false);
            return;
        }

        $status.text('Searching…');

        $.getJSON('/api/customers/search', { email })
            .done(function (response) {
                const customer = response.data;
                $('#customer-name').val(customer.name).prop('readonly', true);
                $status.text('Existing customer found.');
            })
            .fail(function (xhr) {
                if (xhr.status === 404) {
                    $('#customer-name').prop('readonly', false);
                    $status.text('New customer — enter name.');
                    return;
                }
                $status.text('Could not search customer.');
            });
    }

    function resetForm(keepAlerts) {
        if (!keepAlerts) {
            clearAlert();
        }

        $('#success-panel').hide();
        $('#order-form').show();
        $('#customer-email').val('');
        $('#customer-name').val('').prop('readonly', false);
        $('#customer-search-status').text('');
        $('#amount-given').val('0');
        $('#items-table tbody').empty();
        addRow();
        recalculateSummary();
        loadLowStock();
        loadProducts();
    }

    function showSuccess(order) {
        const itemsHtml = (order.items || []).map((item) => {
            return `<li>${item.product_name || ('#' + item.product_id)} × ${item.qty} — ₹${money(item.line_grand_total)}</li>`;
        }).join('');

        $('#success-details').html(`
            <p class="mb-1"><strong>Order #${order.id}</strong> for ${order.customer.name} (${order.customer.email})</p>
            <p class="mb-1">Subtotal ₹${money(order.subtotal)} · Tax ₹${money(order.tax_amount)} · Grand ₹${money(order.grand_total)}</p>
            <ul class="mb-0">${itemsHtml}</ul>
        `);

        $('#order-form').hide();
        $('#success-panel').show();
        loadProducts();
        loadLowStock();
    }

    $('#btn-add-row').on('click', function () {
        addRow();
    });

    $('#items-table').on('click', '.btn-remove-row', function () {
        const $rows = $('#items-table tbody tr');
        if ($rows.length === 1) {
            showAlert('warning', 'At least one item row is required.');
            return;
        }
        $(this).closest('tr').remove();
        refreshAllProductSelects();
        recalculateSummary();
    });

    $('#items-table').on('change', '.product-select', function () {
        refreshAllProductSelects();
        recalculateRow($(this).closest('tr'));
    });

    $('#items-table').on('input', '.qty-input', function () {
        recalculateRow($(this).closest('tr'));
    });

    $('#amount-given').on('input', updatePayment);

    let searchTimer = null;
    $('#customer-email').on('input blur', function (event) {
        clearTimeout(searchTimer);
        const delay = event.type === 'blur' ? 0 : 400;
        searchTimer = setTimeout(searchCustomer, delay);
    });

    $('#btn-reset').on('click', function () {
        resetForm(false);
    });

    $('#btn-new-order').on('click', function () {
        resetForm(false);
    });

    $('#order-form').on('submit', function (event) {
        event.preventDefault();
        clearAlert();

        const email = $('#customer-email').val().trim();
        const name = $('#customer-name').val().trim();
        const items = [];
        let valid = true;

        if (!email || !name) {
            showAlert('danger', 'Customer email and name are required.');
            return;
        }

        $('#items-table tbody tr').each(function () {
            const productId = $(this).find('.product-select').val();
            const qty = parseInt($(this).find('.qty-input').val(), 10);

            if (!productId || !qty || qty < 1) {
                valid = false;
                return;
            }

            items.push({
                product_id: Number(productId),
                qty: qty,
            });
        });

        if (!valid || items.length === 0) {
            showAlert('danger', 'Add at least one valid product and quantity.');
            return;
        }

        const productIds = items.map((item) => item.product_id);
        if (new Set(productIds).size !== productIds.length) {
            showAlert('danger', 'Duplicate products are not allowed.');
            return;
        }

        $('#btn-submit').prop('disabled', true).text('Placing…');

        $.ajax({
            url: '/api/orders',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                customer: { name, email },
                items,
            }),
        }).done(function (response) {
            showAlert('success', 'Order placed successfully.');
            showSuccess(response.data);
        }).fail(function (xhr) {
            const message = xhr.responseJSON?.message
                || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : null)
                || 'Order failed.';
            showAlert('danger', message);
        }).always(function () {
            $('#btn-submit').prop('disabled', false).text('Place order');
        });
    });

    loadProducts();
    loadLowStock();
    updatePayment();
})(jQuery);
</script>
</body>
</html>
