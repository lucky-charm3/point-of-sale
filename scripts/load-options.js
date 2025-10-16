let productsList = [];

function loadProductsSelect(selectId, selectedProductId = null) {
    fetch('../apis/products-api.php?action=getAll')
        .then(res => res.json())
        .then(response => {
            const products = response.data; 
            
            productsList=products;

            const select = document.getElementById(selectId);
            
            if (select && Array.isArray(products) && products.length > 0) {
                select.innerHTML = ''; 
                
                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = product.name;
                    if (selectedProductId !== null && product.id == selectedProductId) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            } else {
                console.error('Error: Select element not found or no product data available.');
            }
        })
        .catch(err => console.error('Error loading products:', err));
}


function addToCart(cart) {
    const productId = document.getElementById('addProductSelect').value;
    const quantity = parseInt(document.getElementById('addProductQuantity').value);
    
    if (!productId || quantity <= 0) {
        showToast('Please select a product and valid quantity.', 'error');
        return;
    }

    const product = productsList.find(p => p.id == productId);
    if (!product) return;

    const existingItem = cart.find(item => item.product_id == productId);
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            product_id: productId,
            name: product.name,
            quantity: quantity,
            price: product.price
        });
    }

    updateCartUI(cart);
}

function updateCartUI(cart) {
    const tableBody = document.getElementById('cartItemsTableBody');
    const totalElement = document.getElementById('cartTotal');
    let total = 0;
    
    tableBody.innerHTML = '';
    cart.forEach((item, index) => {
        const subtotal = item.quantity * item.price;
        total += subtotal;
        tableBody.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${parseFloat(item.price).toFixed(2)}</td>
                <td>${subtotal.toFixed(2)}</td>
                <td><button type='button' class="btn btn-danger btn-sm" onclick="removeFromCart(${index}, cart)">Remove</button></td>
            </tr>
        `;
    });
    totalElement.innerText = total.toFixed(2);
}

function removeFromCart(index) {
    if (window.currentCart && window.currentCart[index]) {
        window.currentCart.splice(index, 1);
        updateCartUI(window.currentCart);
        showToast('Product removed from cart', 'success');
    }
}


function loadUsersSelect(selectId, selectedId = null) {
    fetch('../apis/users-api.php?action=getAll')
        .then(res => res.json())
        .then(users => {
            const select = document.getElementById(selectId);
            let options = '<option value="">Select User</option>';
            users.forEach(u => {
                options += `<option value="${u.id}" ${selectedId == u.id ? 'selected' : ''}>${u.username}</option>`;
            });
            select.innerHTML = options;
        })
        .catch(err => console.error("Error loading users:", err));
}

function loadCategoriesSelect(selectId, selectedId = null) {
    fetch('../apis/categories-api.php?action=getAll')
        .then(res => res.json())
        .then(categories => {
            const select = document.getElementById(selectId);
            let options = '<option value="">Select Category</option>';
            categories.forEach(cat => {
                options += `<option value="${cat.id}" ${selectedId == cat.id ? 'selected' : ''}>${cat.name}</option>`;
            });
            select.innerHTML = options;
        })
        .catch(err => console.error("Error loading categories:", err));
}

window.removeFromCart = removeFromCart;
window.addToCart = addToCart;