// Load customers dynamically into dropdown
async function loadCustomers() {
    try {
        const res = await fetch("../api/payments_api.php?action=get_customers");
        const customers = await res.json();

        const select = document.getElementById("customerSelect");
        select.innerHTML = "<option value=''>-- Select Customer --</option>";

        customers.forEach(c => {
            const option = document.createElement("option");
            option.value = c.id;
            option.textContent = c.name;
            select.appendChild(option);
        });
    } catch (err) {
        console.error("Error loading customers:", err);
    }
}

// Add payment to DB
async function addPayment() {
    const customerId = document.getElementById("customerSelect").value;
    const amount = document.getElementById("paymentAmount").value;
    const method = document.getElementById("paymentMethod").value;
    const referenceNumber = document.getElementById("reference_number").value;
    const notes = document.getElementById("notes").value;

    if (!customerId || !amount || !method) {
        alert("Please fill in all fields");
        return;
    }

    try {
        const res = await fetch("../api/payments_api.php?action=add_payment", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                customer_id: customerId,
                amount: amount,
                method: method,
                reference_number: referenceNumber,
                notes: notes
            })
        });

        const data = await res.json();
        if (data.success) {
            alert("✅ Payment added successfully");
            document.getElementById("paymentForm").reset();
        } else {
            alert("❌ " + (data.message || "Failed to add payment"));
        }
    } catch (err) {
        console.error("Error adding payment:", err);
    }
}


// Initialize
document.addEventListener("DOMContentLoaded", () => {
    loadCustomers();
    document.getElementById("paymentForm").addEventListener("submit", (e) => {
        e.preventDefault();
        addPayment();
    });
});
