<?php
require_once '../handlers/AuthHandler.php';
require_once '../handlers/PaymentsHandler.php';

$authHandler = new AuthHandler();
$paymentsHandler = new PaymentsHandler();

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

// Fetch payments recorded by this cashier
$payments = $paymentsHandler->getPaymentsByUser($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments | Kabras Sugar Store</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/cashier-payments.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <h1><i class="fas fa-money-check-alt"></i> Payments</h1>
        <div class="header-actions">
            <p>View and record customer payments.</p>
            <button class="btn add-customer" id="openModalBtn">Enter Customer Payment</button>
        </div>

        <!-- Payments Table -->
        <div class="payments-table-wrapper">
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Payment ID</th>
                        <th>Sale ID</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Amount (Ksh)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)):
                        $rowNum = 1;
                        foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= $rowNum++; ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($payment['payment_date'])); ?></td>
                                <td><?= $payment['id']; ?></td>
                                <td><?= $payment['sale_id']; ?></td>
                                <td><?= $payment['customer_id'] ?? '-'; ?></td>
                                <td><?= ucfirst($payment['method']); ?></td>
                                <td><?= number_format($payment['amount'], 2); ?></td>
                                <td><?= ucfirst($payment['status']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm view-btn"
                                        data-payment='<?= json_encode($payment); ?>'>
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="9" class="no-payments">No payments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- View Payment Receipt Modal -->
    <div id="viewPaymentModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>

            <!-- Header -->
            <div class="receipt-header">
                <h2>Payment #<span id="paymentNumber"></span></h2>
            </div>

            <!-- Body -->
            <div id="paymentDetails"></div>

            <!-- QR Code -->
            <div class="receipt-qrcode">
                <div id="paymentQR"></div>
            </div>

            <!-- Footer -->
            <div class="receipt-footer">
                <p>Processed by: <strong id="cashierName"></strong></p>
                <p id="paymentDate"></p>
            </div>

            <div class="action-buttons">
                <button id="downloadPaymentBtn" class="btn-download">Download PDF</button>
                <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
    </div>

    <!-- Record Customer Payment Modal -->
    <div id="recordPaymentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Record Customer Payment</h2>

            <form id="paymentForm">
                <!-- Customer selection -->
                <!-- Customer selection -->
                <label for="customerSelect">Customer</label>
                <select id="customerSelect" name="customer_id" required></select>

                <!-- Payment method -->
                <label for="paymentMethod">Payment Method</label>
                <select id="paymentMethod" name="method" required>
                    <option value="cash">Cash</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="card">Card</option>
                    <option value="bank">Bank Transfer</option>
                </select>

                <!-- Amount -->
                <label for="paymentAmount">Amount</label>
                <input type="number" step="0.01" id="paymentAmount" name="amount" required>

                <!-- Reference number -->
                <label for="reference_number">Reference Number</label>
                <input type="text" id="reference_number" name="reference_number">

                <!-- Notes -->
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes"></textarea>


                <!-- Hidden cashier ID -->
                <input type="hidden" name="user_id" value="<?= $currentUser['id']; ?>">


                <button type="submit" name="record_payment" class="btn-submit">Save Payment</button>
            </form>
        </div>
    </div>

    <script>
        const cashierName = "<?= htmlspecialchars($currentUser['name']); ?>";
    </script>
    <script src="../assets/js/payments.js" defer></script>
    <script>
        // cashier-payments.js
        document.addEventListener("DOMContentLoaded", () => {
            const cashierName = window.cashierName || "Cashier";

            /* ====== VIEW PAYMENT RECEIPT MODAL ====== */
            const paymentModal = document.getElementById("viewPaymentModal");
            const closeBtn = document.querySelector(".close-btn");
            const paymentDetails = document.getElementById("paymentDetails");
            const qrDiv = document.getElementById("paymentQR");

            document.querySelectorAll(".view-btn").forEach((btn) => {
                btn.addEventListener("click", () => {
                    const payment = JSON.parse(btn.getAttribute("data-payment"));
                    paymentModal.style.display = "flex";

                    const formattedDate = new Date(payment.payment_date).toLocaleString();

                    paymentDetails.innerHTML = `
                <p><strong>Sale ID:</strong> ${payment.sale_id}</p>
                <p><strong>Customer:</strong> ${payment.customer_id ?? "-"}</p>
                <p><strong>Method:</strong> ${payment.method}</p>
                <p><strong>Amount:</strong> <b>Ksh ${payment.amount}</b></p>
                <p><strong>Status:</strong> ${payment.status}</p>
            `;

                    document.getElementById("paymentNumber").textContent = payment.id;
                    document.getElementById("cashierName").textContent = cashierName;
                    document.getElementById("paymentDate").textContent = formattedDate;

                    // Generate QR
                    qrDiv.innerHTML = "";
                    new QRCode(qrDiv, {
                        text: `Payment Receipt #${payment.id} | Amount: Ksh ${payment.amount}\nMethod: ${payment.method} | Kabras Sugar`,
                        width: 120,
                        height: 120,
                    });
                });
            });

            // Download receipt as PDF
            const downloadBtn = document.getElementById("downloadPaymentBtn");
            if (downloadBtn) {
                downloadBtn.addEventListener("click", () => {
                    const element = document.querySelector("#viewPaymentModal .modal-content");
                    html2pdf().from(element).save(`payment_${Date.now()}.pdf`);
                });
            }

            // Close modal
            if (closeBtn) {
                closeBtn.onclick = () => (paymentModal.style.display = "none");
            }
            window.onclick = (e) => {
                if (e.target === paymentModal) paymentModal.style.display = "none";
            };

            /* ====== RECORD PAYMENT MODAL ====== */
            const recordModal = document.getElementById("recordPaymentModal");
            const openRecordBtn = document.getElementById("openModalBtn");
            const closeRecordBtn = recordModal.querySelector(".close");

            openRecordBtn.addEventListener("click", () => {
                recordModal.style.display = "flex";
            });

            closeRecordBtn.addEventListener("click", () => {
                recordModal.style.display = "none";
            });

            window.addEventListener("click", (e) => {
                if (e.target === recordModal) recordModal.style.display = "none";
            });
        });
    </script>


</body>

</html>