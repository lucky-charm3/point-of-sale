function fetchProducts(search = '') {
    const productTableBody = document.getElementById('productsTableBody');
    if (!productTableBody) return;

    fetch(`../apis/products-api.php?action=getAll&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(response => {
    const products = response.data;
    if (!products) {
        console.error('No products data found in the response.');
        return;
    }
    
    productTableBody.innerHTML = '';
    document.getElementById('totalProducts').innerText = products.length;
    
    products.forEach(p => {
        let actions = [];
                actions.push({
                    type: 'view',
                    label: 'View Details',
                    onclick: `viewProduct(${p.id}); actionDropdown.closeAll();`
                });

                if (window.userRole === 'admin' || window.userRole === 'manager') {
                    actions.push({
                        type: 'edit',
                        label: 'Edit Product',
                        onclick: `openEditProductModal(${p.id}); actionDropdown.closeAll();`
                    });
                }

                if (window.userRole === 'admin') {
                    actions.push({
                        type: 'delete',
                        label: 'Delete Product',
                        onclick: `confirmDelete(${p.id}, 'product', () => deleteProduct(${p.id})); actionDropdown.closeAll();`
                    });
                }

                const actionDropdownHTML = actionDropdown.createDropdown(actions);
        productTableBody.innerHTML += `
            <tr>
                <td>#${p.id}</td>
                <td>${p.name}</td>
                <td>${p.barcode}</td>
                <td>Tshs ${Number(p.price).toLocaleString()}</td>
                <td>${p.stock}</td>
                <td>${actionDropdownHTML}</td>
            </tr>
        `;
    });
})
        .catch(err => console.error('Error fetching products:', err));
}

function openAddProductModal() {
    document.getElementById('addModalTitle').innerText = 'Add New Product';
    document.getElementById('addModalBody').innerHTML = `
        <div class="form-group">
            <label for="addProductName">Name</label>
            <input id="addProductName" name="name" class="form-control" required/>
        </div>
        <div class="form-group">
            <label for="addProductBarcode">Barcode</label>
            <input type="text" id="addProductBarcode" name="barcode" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="addProductPrice">Price</label>
            <input type="number" step="0.01" id="addProductPrice" name="price" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="addProductStock">Stock</label>
            <input type="number" id="addProductStock" name="stock" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="addProductCategory">Category</label>
            <select id="addProductCategory" name="category_id" class="form-control" required></select>
        </div>
    `;
    document.getElementById('addForm').onsubmit = (e) => { e.preventDefault(); addProduct(); };
    openModal("addModal");
    loadCategoriesSelect("addProductCategory");
}

function addProduct() {
    const formData = new FormData(document.getElementById('addForm'));
    const data = Object.fromEntries(formData.entries());

    fetch('../apis/products-api.php?action=add', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        closeModal("addModal");
        showToast(result.success ? 'Product added successfully' : result.message, result.success ? 'success' : 'error');
        if (result.success) fetchProducts();
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function openEditProductModal(id) {
    document.getElementById('editModalTitle').innerText = 'Edit Product';
    document.getElementById('editId').value = '';
    document.getElementById('editModalBody').innerHTML = `
        <div class="loading-spinner">Loading product data...</div>
    `;
    
    openModal('editModal');

    fetch(`../apis/products-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(product => {
            document.getElementById('editModalTitle').innerText = 'Edit Product';
            document.getElementById('editId').value = product.id;
            document.getElementById('editModalBody').innerHTML = `
                <div class="form-group">
                    <label for="editProductName">Name</label>
                    <input type="text" id="editProductName" name="name" value="${product.name}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editProductBarcode">Barcode</label>
                    <input type="text" id="editProductBarcode" name="barcode" value="${product.barcode}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editProductPrice">Price</label>
                    <input type="number" step="0.01" id="editProductPrice" name="price" value="${product.price}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editProductStock">Stock</label>
                    <input type="number" id="editProductStock" name="stock" value="${product.stock}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editProductCategory">Category</label>
                    <select id="editProductCategory" name="category_id" class="form-control" required></select>
                </div>
            `;
            document.getElementById('editForm').onsubmit = (e) => { e.preventDefault(); updateProduct(id); };
            loadCategoriesSelect("editProductCategory",product.category_id);
        });
}

function updateProduct(id) {
    const formData = new FormData(document.getElementById('editForm'));
    const data = Object.fromEntries(formData.entries());
    data.id = id;

    fetch('../apis/products-api.php?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        closeModal('editModal');
        showToast(result.success ? 'Product updated successfully' : result.message, result.success ? 'success' : 'error');
        if (result.success) fetchProducts();
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function viewProduct(id) {
    document.getElementById('viewModalTitle').innerText = 'Product Details';
    document.getElementById('viewModalBody').innerHTML = `
        <div class="loading-spinner">Loading product details...</div>
    `;
    
    openModal('viewModal');

    fetch(`../apis/products-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(product => {
            document.getElementById('viewModalTitle').innerText = 'Product Details';
            document.getElementById('viewModalBody').innerHTML = `
                <p><strong>ID:</strong> #${product.id}</p>
                <p><strong>Name:</strong> ${product.name}</p>
                <p><strong>Barcode:</strong> ${product.barcode}</p>
                <p><strong>Price:</strong> Tshs ${Number(product.price).toLocaleString()}</p>
                <p><strong>Stock:</strong> ${product.stock}</p>
                <p><strong>Category ID:</strong> ${product.category_id}</p>
                <p><strong>Created:</strong> ${new Date(product.created_at).toLocaleDateString()}</p>
            `;
            openModal('viewModal');
        });
}

function deleteProduct(id) {
    fetch(`../apis/products-api.php?action=delete`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(result => {
        closeModal('deleteModal');
        showToast(result.success ? 'Product deleted successfully' : result.message, result.success ? 'success' : 'error');
        if (result.success) {
            fetchProducts();
        }
    })
    .catch(err => {
        closeModal('deleteModal');
        console.error('An error occurred during deletion:', err);
        showToast('An unexpected error occurred.', 'error');
    });
}

document.addEventListener('DOMContentLoaded',()=>{
    fetchProducts();
    document.getElementById('searchProduct').addEventListener('input', (e) => {
        fetchProducts(e.target.value);
    });
})

// Make functions globally available
window.fetchProducts = fetchProducts;
window.openAddProductModal = openAddProductModal;
window.openEditProductModal = openEditProductModal;
window.viewProduct = viewProduct;