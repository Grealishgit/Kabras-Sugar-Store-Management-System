// sales.js - Cashier Sales Table Pagination & UI

document.addEventListener('DOMContentLoaded', function () {
    // Stats card tab switching
    const tabBtns = document.querySelectorAll('.stats-tabs .tab-btn');
    const statsPeriods = document.querySelectorAll('.stats-period');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const period = this.getAttribute('data-period');
            statsPeriods.forEach(card => {
                card.classList.add('hidden');
            });
            if (period === 'today') {
                document.getElementById('today-stats').classList.remove('hidden');
            } else if (period === 'week') {
                document.getElementById('week-stats').classList.remove('hidden');
            } else if (period === 'month') {
                document.getElementById('month-stats').classList.remove('hidden');
            }
        });
    });
    // Pagination for sales table
    const salesTable = document.getElementById('salesTable');
    if (salesTable) {
        let currentPage = 1;
        const rowsPerPage = 10;
        const rows = Array.from(salesTable.querySelectorAll('tbody tr'));
        const totalPages = Math.ceil(rows.length / rowsPerPage);

        function showPage(page) {
            rows.forEach((row, i) => {
                row.style.display = (i >= (page - 1) * rowsPerPage && i < page * rowsPerPage) ? '' : 'none';
            });
            document.getElementById('salesPageNum').textContent = `Page ${page} of ${totalPages}`;
        }

        document.getElementById('salesPrev').onclick = function () {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        };
        document.getElementById('salesNext').onclick = function () {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        };
        showPage(currentPage);
    }

    // Product search filter
    const productSearch = document.getElementById('productSearch');
    if (productSearch) {
        productSearch.addEventListener('input', function () {
            const val = this.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.display = card.dataset.productName.includes(val) ? '' : 'none';
            });
        });
    }

    // Quantity controls
    document.querySelectorAll('.qty-btn.plus').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.querySelector(`input[data-product='${this.dataset.product}']`);
            input.value = parseInt(input.value) + 1;
            input.dispatchEvent(new Event('input'));
        });
    });
    document.querySelectorAll('.qty-btn.minus').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.querySelector(`input[data-product='${this.dataset.product}']`);
            input.value = Math.max(0, parseInt(input.value) - 1);
            input.dispatchEvent(new Event('input'));
        });
    });

    // Sale summary update
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('input', updateSummary);
    });
    function updateSummary() {
        let subtotal = 0;
        let items = [];
        document.querySelectorAll('.qty-input').forEach(input => {
            const qty = parseInt(input.value);
            if (qty > 0) {
                const price = parseFloat(input.dataset.price);
                subtotal += qty * price;
                items.push({
                    name: input.closest('.product-card').querySelector('h3').textContent,
                    qty,
                    price
                });
            }
        });
        document.getElementById('subtotal').textContent = `Ksh ${subtotal.toFixed(2)}`;
        document.getElementById('total').textContent = `Ksh ${subtotal.toFixed(2)}`;
        const summaryItems = document.getElementById('summaryItems');
        summaryItems.innerHTML = items.length ? items.map(item => `<div>${item.qty} x ${item.name} @ Ksh ${item.price}</div>`).join('') : '<p class="empty-cart">No items selected</p>';
    }
    updateSummary();

    // Clear all button
    document.getElementById('clearAll').onclick = function () {
        document.querySelectorAll('.qty-input').forEach(input => {
            input.value = 0;
            input.dispatchEvent(new Event('input'));
        });
    };
});
