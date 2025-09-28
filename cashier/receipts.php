<?php
require_once '../handlers/AuthHandler.php';
require_once '../handlers/SalesHandler.php';

$authHandler = new AuthHandler();
$salesHandler = new SalesHandler();

// Ensure user is logged in
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

// Restrict only Cashier
if ($currentUser['role'] !== 'Cashier') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}

// Get all sales for this cashier
$sales = $salesHandler->getSalesByCashier($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts | Kabras Sugar Store</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/cashier-receipts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <h1><i class="fas fa-receipt"></i> Receipts</h1>
        <p>View and print receipts for processed sales.</p>
        <div class="receipts-search-filter" style="margin-bottom:20px;display:flex;gap:16px;align-items:center;">
            <input type="text" id="receiptSearch" placeholder="Search by Sale ID, Customer ID, or Product Name..." style="padding:8px 12px;width:260px;border-radius:4px;border:1px solid #ccc;">
            <select id="receiptFilter" style="padding:8px 12px;border-radius:4px;border:1px solid #ccc;">
                <option value="">All</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
        <div class="receipts-table-wrapper">
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('receiptSearch');
                    const filterSelect = document.getElementById('receiptFilter');
                    const rows = document.querySelectorAll('.receipts-table tbody tr');
                    searchInput.addEventListener('input', function() {
                        const val = this.value.toLowerCase();
                        rows.forEach(row => {
                            const saleId = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                            const customerId = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
                            const productName = row.querySelector('td:nth-child(7)')?.textContent.toLowerCase() || '';
                            if (saleId.includes(val) || customerId.includes(val) || productName.includes(val)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                    filterSelect.addEventListener('change', function() {
                        const filter = this.value;
                        const now = new Date();
                        rows.forEach(row => {
                            const dateText = row.querySelector('td:nth-child(2)')?.textContent;
                            if (!dateText) return;
                            const rowDate = new Date(dateText.replace(/-/g, '/'));
                            let show = true;
                            if (filter === 'today') {
                                show = rowDate.toDateString() === now.toDateString();
                            } else if (filter === 'week') {
                                const weekAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                                show = rowDate >= weekAgo && rowDate <= now;
                            } else if (filter === 'month') {
                                show = rowDate.getMonth() === now.getMonth() && rowDate.getFullYear() === now.getFullYear();
                            }
                            row.style.display = show ? '' : 'none';
                        });
                    });
                });
            </script>
            <table class="receipts-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Sale ID</th>
                        <th>Customer ID</th>
                        <th>Cashier ID</th>
                        <th>Amount (Ksh)</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sales)):
                        $rowNum = 1;
                        foreach ($sales as $sale): ?>
                            <tr>
                                <td><?= $rowNum++; ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                                <td><?= $sale['id']; ?></td>
                                <td><?= $sale['customer_id'] ?? '-'; ?></td>
                                <td><?= $sale['user_id']; ?></td>
                                <td><?= number_format($sale['total_amount'], 2); ?></td>
                                <td><?= htmlspecialchars($sale['product_name']); ?></td>
                                <td><?= $sale['quantity']; ?></td>
                                <td><?= number_format($sale['unit_price'], 2); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm print-btn" data-sale='<?= json_encode($sale); ?>'>
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="10" class="no-sales">No receipts found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Receipt Modal -->
    <!-- Receipt Modal -->
    <div id="receiptModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>

            <!-- Printable Section -->
            <div id="receiptPrintable">
                <!-- Receipt Header -->
                <div class="receipt-header">
                    <h2>Receipt #<span id="receiptNumber"></span></h2>
                </div>

                <!-- Receipt Body -->
                <div id="receiptDetails"></div>

                <!-- QR Code Centered -->
                <div class="receipt-qrcode">
                    <div id="qrcode"></div>
                </div>

                <!-- Footer -->
                <div class="receipt-footer">
                    <p class="processed-by">Processed by: <strong id="cashierName"></strong></p>
                    <p class="receipt-date" id="receiptDate"></p>
                </div>
            </div>
            <!-- End Printable Section -->

            <!-- Buttons (NOT included in PDF) -->
            <div class="action-buttons">
                <button id="downloadBtn" class="btn-download">Download PDF</button>
                <button onclick="window.print()" class="btn-print">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>



    <script>
        const modal = document.getElementById('receiptModal');
        const closeBtn = document.querySelector('.close-btn');
        const receiptDetails = document.getElementById('receiptDetails');
        const qrcodeDiv = document.getElementById('qrcode');

        document.querySelectorAll('.print-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const sale = JSON.parse(btn.getAttribute('data-sale'));
                modal.style.display = 'flex';

                // Format date
                const formattedDate = new Date(sale.sale_date).toLocaleString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });

                // Fill structured receipt details
                receiptDetails.innerHTML = `
      <div class="row">
        <p><strong>Sale ID:</strong> ${sale.id}</p>
        <p><strong>Customer ID:</strong> ${sale.customer_id ?? '-'}</p>
      </div>

      <div class="row">
        <p><strong>Product:</strong> ${sale.product_name}</p>
        <p><strong>Quantity:</strong> ${sale.quantity}</p>
      </div>

      <div class="column">
        <p><strong>Unit Price:</strong> Ksh ${sale.unit_price}</p>
        <p><strong>Total Amount:</strong> <b>Ksh ${sale.total_amount}</b></p>
      </div>
    `;

                // Header & footer
                document.getElementById('receiptNumber').textContent = sale.id;
                document.getElementById('cashierName').textContent = "<?= $currentUser['name']; ?>";
                document.getElementById('receiptDate').textContent = formattedDate;

                // QR Code
                qrcodeDiv.innerHTML = "";
                new QRCode(qrcodeDiv, {
                    text: `Receipt Number ${sale.id} | Total: Ksh ${sale.total_amount}\nThank You For Doing Business With Us! | Kabras Sugar`,
                    width: 120,
                    height: 120
                });

            });
        });

        document.getElementById('downloadBtn').addEventListener('click', () => {
            const element = document.getElementById('receiptPrintable'); // only the receipt section
            const opt = {
                margin: 0.5,
                filename: `receipt_${Date.now()}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A4',
                    orientation: 'portrait'
                }
            };
            html2pdf().set(opt).from(element).save();
        });



        closeBtn.onclick = () => {
            modal.style.display = 'none';
        };

        window.onclick = (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>

</html>