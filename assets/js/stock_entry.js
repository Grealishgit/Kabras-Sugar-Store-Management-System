// Modal logic
function showAddProductModal() { document.getElementById('addProductModal').style.display = 'flex'; }
function hideAddProductModal() { document.getElementById('addProductModal').style.display = 'none'; }
function showViewProductModal() { document.getElementById('viewProductModal').style.display = 'flex'; }
function hideViewProductModal() { document.getElementById('viewProductModal').style.display = 'none'; }
function showEditProductModal() { document.getElementById('editProductModal').style.display = 'flex'; }
function hideEditProductModal() { document.getElementById('editProductModal').style.display = 'none'; }
function showDeleteProductModal() { document.getElementById('deleteProductModal').style.display = 'flex'; }
function hideDeleteProductModal() { document.getElementById('deleteProductModal').style.display = 'none'; }

// AJAX for view/edit
function viewProduct(id) {
    fetch('stock_entry.php?view_id=' + id)
        .then(res => res.json())
        .then(data => {
            let html = '<div class="flex-row">';
            let keys = Object.keys(data);
            for (let i = 0; i < keys.length; i += 2) {
                html += '<div>' + '<strong>' + keys[i].replace('_', ' ') + '</strong><br>' + (data[keys[i]] ?? '') + '</div>';
                if (keys[i + 1]) {
                    html += '<div>' + '<strong>' + keys[i + 1].replace('_', ' ') + '</strong><br>' + (data[keys[i + 1]] ?? '') + '</div>';
                }
            }
            html += '</div>';
            document.getElementById('viewProductInfo').innerHTML = html;
            showViewProductModal();
        });
}
function editProduct(id) {
    fetch('stock_entry.php?view_id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_category').value = data.category;
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_price').value = data.price;
            document.getElementById('edit_stock_quantity').value = data.stock_quantity;
            document.getElementById('edit_unit').value = data.unit;
            document.getElementById('edit_batch_number').value = data.batch_number;
            document.getElementById('edit_expiry_date').value = data.expiry_date;
            document.getElementById('edit_production_date').value = data.production_date;
            document.getElementById('edit_supplier').value = data.supplier;
            showEditProductModal();
        });
}
function confirmDeleteProduct(id) {
    document.getElementById('delete_id').value = id;
    showDeleteProductModal();
}
