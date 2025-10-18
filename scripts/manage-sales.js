let currentPage = 1;
const itemsPerPage = 10;
let totalSalesCount = 0;

function fetchRecentSales() {
    const salesTableBody = document.getElementById('recentSalesTableBody');
    if (!salesTableBody) return;

    const url = `../apis/sales-api.php?action=getAll&limit=10`;

    fetch(url)
        .then(res => res.json())
        .then(response => {
            salesTableBody.innerHTML = '';
            
            const salesData = response.sales || [];
            
            if (Array.isArray(salesData) && salesData.length > 0) {
                salesData.forEach(sale => {
                    salesTableBody.innerHTML += `
                        <tr>
                            <td>#${sale.id}</td>
                            <td>${sale.user || 'N/A'}</td>
                            <td>Tshs ${Number(sale.total_price).toLocaleString()}</td>
                            <td>${sale.payment_method}</td>
                            <td>${new Date(sale.created_at).toLocaleDateString()}</td>
                            <td><button class="btn btn-primary" onclick="viewSale(${sale.id})">View</button></td>
                        </tr>
                    `;
                });
            } else {
                salesTableBody.innerHTML = '<tr><td colspan="6">No recent sales found.</td></tr>';
            }
        })
        .catch(err => console.error('Error fetching recent sales:', err));
}

function fetchSales(search = '', start = '', end = '', page = 1) {
    const salesTableBody = document.getElementById('salesTableBody');
    if (!salesTableBody) return;

    const url = `../apis/sales-api.php?action=getAll&page=${page}&limit=${itemsPerPage}&search=${encodeURIComponent(search)}&start_date=${start}&end_date=${end}`;

    fetch(url)
        .then(res => res.json())
        .then(response => {
            salesTableBody.innerHTML = '';
            
            const salesData = response.sales || [];
            totalSalesCount = response.total || salesData.length;
            
            if (Array.isArray(salesData) && salesData.length > 0) {
                salesData.forEach(sale => {
                  let actions = [];
                actions.push({
                    type: 'view',
                    label: 'View Details',
                    onclick: `viewSale(${sale.id}); actionDropdown.closeAll();`
                });

                if (window.userRole === 'admin') {
                    actions.push({
                        type: 'delete',
                        label: 'Delete Sale',
                        onclick: `confirmDelete(${sale.id}, 'sale', () => deleteSale(${sale.id})); actionDropdown.closeAll();`
                    });
                }

                const actionDropdownHTML = actionDropdown.createDropdown(actions);
                    salesTableBody.innerHTML += `
                        <tr>
                            <td>#${sale.id}</td>
                            <td>${sale.user || 'N/A'}</td>
                            <td>Tshs ${Number(sale.total_price).toLocaleString()}</td>
                            <td>${sale.payment_method}</td>
                            <td>${new Date(sale.created_at).toLocaleDateString()}</td>
                            <td class='actions'>
                                ${actionDropdownHTML}
                            </td>
                        </tr>
                    `;
                });
            } else {
                salesTableBody.innerHTML = '<tr><td colspan="6">No sales found.</td></tr>';
            }

            renderPagination(totalSalesCount);
        })
        .catch(err => console.error('Error fetching sales:', err));
}

function openAddSaleModal() {
    document.getElementById('addModalTitle').innerText = 'Add New Sale';
    document.getElementById('addModalBody').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="addProductSelect">Product</label>
                    <select id="addProductSelect" class="form-control"></select>
                </div>
                <div class="form-group">
                    <label for="addProductQuantity">Quantity</label>
                    <input type="number" id="addProductQuantity" class="form-control" value="1" min="1">
                </div>
                <button type="button" class="btn btn-primary" id="addToCartBtn">Add to Cart</button>
            </div>
            <div class="col-md-6">
                <h4>Items in Cart</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cartItemsTableBody"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total:</th>
                            <th id="cartTotal">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="form-group">
                    <label for="addSalePaymentMethod">Payment Method</label>
                    <select id="addSalePaymentMethod" name="payment_method" class="form-control" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile_money">Mobile</option>
                    </select>
                </div>
                <button type='button' class="btn btn-success" id="finalizeSaleBtn">Complete Sale</button>
            </div>
        </div>
    `;

    let cart = [];
     openModal("addModal");
    loadProductsSelect('addProductSelect');
    
    document.getElementById('addToCartBtn').onclick = () => addToCart(cart);
    document.getElementById('finalizeSaleBtn').onclick = () => addSale(cart)
   
     window.currentCart = cart;
}

function addSale() {
    let cart = window.currentCart; 
    if (cart.length === 0) {
        showToast('Cart is empty. Please add products to proceed.', 'error');
        return;
    }

    const paymentMethod = document.getElementById('addSalePaymentMethod').value;
    const total_price = parseFloat(document.getElementById('cartTotal').innerText);

    const saleData = {
        payment_method: paymentMethod,
        total_price: total_price,
        items: cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            price: item.price
        }))
    };
    
    fetch('../apis/sales-api.php?action=add', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(saleData)
    })
    .then(res => res.json())
    .then(result => {
        closeModal("addModal");
        showToast(result.success ? 'Sale added successfully' : result.message, result.success ? 'success' : 'error');
        if (result.success) {
            window.currentCart = [];
            if (window.userRole === 'cashier') {
                fetchCashierSales(); 
            } else {
                fetchSales(); 
            }
        }
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}


function viewSale(id) {
    fetch(`../apis/sales-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(sale => {
            if (!sale) {
                showToast('Sale not found', 'error');
                return;
            }
            
            let itemsHtml = '';
            if (sale.items && Array.isArray(sale.items)) {
                itemsHtml = sale.items.map(item => `
                    <tr>
                        <td>${item.product}</td>
                        <td>${item.quantity}</td>
                        <td>Tshs ${Number(item.price).toLocaleString()}</td>
                        <td>Tshs ${Number(item.quantity * item.price).toLocaleString()}</td>
                    </tr>
                `).join('');
            }
            
            document.getElementById('viewModalTitle').innerText = 'Sale Details';
            document.getElementById('viewModalBody').innerHTML = `
                <p><strong>ID:</strong> #${sale.id}</p>
                <p><strong>User:</strong> ${sale.user}</p>
                <p><strong>Total Price:</strong> Tshs ${Number(sale.total_price).toLocaleString()}</p>
                <p><strong>Payment Method:</strong> ${sale.payment_method}</p>
                <p><strong>Date:</strong> ${new Date(sale.created_at).toLocaleString()}</p>
                <h4>Items:</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
            `;
            openModal('viewModal');
        })
        .catch(err => {
            console.error('Error fetching sale details:', err);
            showToast('Error fetching sale details', 'error');
        });
}


function deleteSale(id) {
    fetch(`../apis/sales-api.php?action=delete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(result => {
        closeModal('deleteModal');
        showToast(result.success ? 'Sale deleted successfully' : result.message, result.success ? 'success' : 'error');
        if (result.success) {
            fetchSales();
        }
    })
    .catch(err => {
        closeModal('deleteModal');
        console.error('An error occurred during deletion:', err);
        showToast('An unexpected error occurred.', 'error');
    });
}


function fetchCashierRecentSales() {
    const salesTableBody = document.getElementById('recentSalesTableBody');
    if (!salesTableBody) return;

    const today = new Date().toISOString().slice(0, 10);
    const start = today;
    const end = today + ' 23:59:59';

    const url = `../apis/sales-api.php?action=getCashierSales&start_date=${start}&end_date=${end}&limit=6`;

    fetch(url)
        .then(res => res.json())
        .then(response => {
            salesTableBody.innerHTML = '';
            
            if (response.sales !== undefined) {
                const salesData = response.sales;
                
                if (salesData.length > 0) {
                    salesData.forEach(sale => {
                        salesTableBody.innerHTML += `
                            <tr>
                                <td>#${sale.id}</td>
                                <td>${sale.products}</td>
                                <td>${sale.quantity || 1}</td>
                                <td>Tshs ${Number(sale.total_price).toLocaleString()}</td>
                                <td>${sale.payment_method}</td>
                                <td>${new Date(sale.created_at).toLocaleDateString()}</td>
                                <td><button class="btn btn-primary" onclick="viewSale(${sale.id})">View</button></td>
                            </tr>
                        `;
                    });
                } else {
                    salesTableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                No sales found for today.
                            </td>
                        </tr>
                    `;
                }
            } 
            else if (response.success !== undefined) {
                if (!response.success) {
                    console.error("API Error:", response.message);
                    salesTableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; color: red; padding: 20px;">
                                ${response.message || 'Unauthorized access'}
                            </td>
                        </tr>
                    `;
                }
            }
            else {
                console.error("Invalid response format:", response);
                salesTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; color: red; padding: 20px;">
                            Invalid data format received.
                        </td>
                    </tr>
                `;
            }
        })
        .catch(err => {
            console.error('Error fetching recent cashier sales:', err);
            salesTableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: red; padding: 20px;">
                        Network error: Could not load sales.
                    </td>
                </tr>
            `;
        });
}

function fetchCashierSales(search = '', page = 1) {
    const salesTableBody = document.getElementById('salesTableBody');
    if (!salesTableBody) return;

    const today = new Date().toISOString().slice(0, 10);
    const start = today;
    const end = today + ' 23:59:59';
    
    const url = `../apis/sales-api.php?action=getCashierSales&search=${encodeURIComponent(search)}&start_date=${start}&end_date=${end}&page=${page}&limit=${itemsPerPage}`;
    
    fetch(url)
        .then(res => res.json())
        .then(response => {
            salesTableBody.innerHTML = '';
            
            if (response.sales !== undefined) {
                const salesData = response.sales;
                
                if (salesData.length > 0) {
                    salesData.forEach(sale => {
                        let actions = [
                            {
                                type: 'view',
                                label: 'View Details',
                                onclick: `viewSale(${sale.id}); actionDropdown.closeAll();`
                            },
                            {
                                type: 'print',
                                label: 'Print Sale',
                                onclick: `printReceipt(${sale.id}); actionDropdown.closeAll();`
                            }
                        ];

                        const actionDropdownHTML = actionDropdown.createDropdown(actions);
                        salesTableBody.innerHTML += `
                            <tr>
                                <td>#${sale.id}</td>
                                <td>${sale.products}</td>
                                <td>${sale.quantity || 1}</td>
                                <td>Tshs ${Number(sale.total_price).toLocaleString()}</td>
                                <td>${sale.payment_method}</td>
                                <td>${new Date(sale.created_at).toLocaleDateString()}</td>
                                <td>${actionDropdownHTML}</td>
                            </tr>
                        `;
                    });
                } else {
                    salesTableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                No sales found for today.
                            </td>
                        </tr>
                    `;
                }
            } 
            else if (response.success !== undefined && !response.success) {
                console.error("API Error:", response.message);
                salesTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; color: red; padding: 20px;">
                            ${response.message || 'Access denied'}
                        </td>
                    </tr>
                `;
            }
            else {
                // Actual invalid format
                console.error("Invalid response format:", response);
                salesTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; color: red; padding: 20px;">
                            Error loading sales data.
                        </td>
                    </tr>
                `;
            }
        })
        .catch(err => {
            console.error('Error fetching cashier sales:', err);
            showToast('An unexpected error occurred.', 'error');
        });
}

function printReceipt(saleId) {
    fetch(`../apis/sales-api.php?action=getById&id=${saleId}`)
        .then(res => res.json())
        .then(response => {
            const sale = response.data || response;
            
            if (sale && sale.id) {
                const printWindow = window.open('', '_blank', 'width=400,height=600');
                
                // Generate the POS-compatible receipt
                const receiptHtml = generateReceiptHtml(sale);
                
                printWindow.document.write(receiptHtml);
                printWindow.document.close();
                
                // Auto-print for POS systems
                setTimeout(() => {
                    printWindow.print();
                    // Don't auto-close - let user decide
                }, 500);
            } else {
                showToast('Error: Sale details not found.', 'error');
            }
        })
        .catch(err => {
            console.error('Error fetching sale details:', err);
            showToast('Error fetching sale details.', 'error');
        });
}


function generateReceiptHtml(sale) {
    let itemsHtml = '';
    let total = 0;
    
    if (sale.items && Array.isArray(sale.items)) {
        itemsHtml = sale.items.map(item => {
            const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
            total += itemTotal;
            return `
    <tr>
        <td>${(item.product_name || item.product).substring(0, 20)}</td>
        <td style="text-align:center">${item.quantity}</td>
        <td style="text-align:right">${Number(item.price).toLocaleString()}</td>
        <td style="text-align:right">${Number(itemTotal).toLocaleString()}</td>
    </tr>`;
        }).join('');
    } else {
        // Single product sale
        itemsHtml = `
    <tr>
        <td>${sale.product.substring(0, 20)}</td>
        <td style="text-align:center">${sale.quantity || 1}</td>
        <td style="text-align:right">${Number(sale.total_price).toLocaleString()}</td>
        <td style="text-align:right">${Number(sale.total_price).toLocaleString()}</td>
    </tr>`;
        total = parseFloat(sale.total_price);
    }

    return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #${sale.id}</title>
    <style>
        /* POS THERMAL PRINTER COMPATIBLE STYLES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            width: 80mm; /* Standard thermal paper width */
            margin: 0 auto;
            padding: 5px;
            color: #000;
            background: #fff;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }
        
        .hotel-name {
            font-weight: bold;
            font-size: 14px;
            margin: 3px 0;
            text-transform: uppercase;
        }
        
        .receipt-info {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .receipt-info p {
            margin: 1px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 11px;
        }
        
        th, td {
            padding: 2px 1px;
            text-align: left;
            border-bottom: 1px dotted #ccc;
        }
        
        th {
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        
        .total-row {
            border-top: 2px solid #000;
            margin-top: 5px;
            padding-top: 5px;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .barcode {
            text-align: center;
            margin: 5px 0;
            font-family: 'Libre Barcode 128', cursive;
            font-size: 24px;
        }
        
        /* Thermal printer specific optimizations */
        @media print {
            body {
                width: 80mm !important;
                margin: 0 !important;
                padding: 2mm !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            /* Force black text for thermal printers */
            * {
                color: #000 !important;
                background: #fff !important;
            }
        }
        
        .no-print {
            text-align: center;
            margin-top: 15px;
        }
        
        .btn {
            padding: 5px 10px;
            margin: 2px;
            border: 1px solid #000;
            background: #f0f0f0;
            cursor: pointer;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="receipt-header">
        <div class="hotel-name">NEW DF HOTEL</div>
        <div style="font-size:10px;">
            Bukombe, Geita<br>
            S.L.P 02<br>
            +255 712 345 678
        </div>
    </div>

    <div class="receipt-info">
        <p><strong>Receipt:</strong> #${sale.id}</p>
        <p><strong>Date:</strong> ${new Date(sale.created_at).toLocaleString()}</p>
        <p><strong>Payment:</strong> ${sale.payment_method.toUpperCase()}</p>
        <p><strong>Cashier:</strong> ${sale.user || 'System'}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Price</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            ${itemsHtml}
        </tbody>
    </table>

    <div class="total-row" style="text-align:right;">
        <strong>GRAND TOTAL: Tshs ${total.toLocaleString()}</strong>
    </div>

    <div class="barcode">
        *SALE-${sale.id}* <!-- Text barcode alternative -->
    </div>

    <div class="footer">
        <p>** THANK YOU FOR YOUR BUSINESS **</p>
        <p>Printed: ${new Date().toLocaleString()}</p>
        <p>Have a great day!</p>
    </div>

    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Print Receipt</button>
        <button class="btn" onclick="window.close()">❌ Close</button>
    </div>

    <script>
        // Auto-print for POS systems
        window.onload = function() {
            // Try to auto-print after a short delay
            setTimeout(function() {
                if (!window.matchMedia || !window.matchMedia('print').matches) {
                    window.print();
                }
            }, 500);
        };

        // Handle print dialog close
        window.onafterprint = function() {
            // Optional: auto-close after printing
            // setTimeout(function() { window.close(); }, 1000);
        };
    </script>
</body>
</html>`;
}

function fetchTotalSalesToday() {
    const totalSalesElement = document.getElementById('totalSalesToday');
    if (!totalSalesElement) return;

    fetch('../apis/sales-api.php?action=getTotalSalesToday')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                totalSalesElement.innerText = `Tshs ${data.total_sales}`;
            } else {
                totalSalesElement.innerText = 'N/A';
                console.error('Failed to fetch total sales:', data.message);
            }
        })
        .catch(err => {
            totalSalesElement.innerText = 'N/A';
            console.error('Error fetching total sales:', err);
        });
}

function renderPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = document.getElementById('pagination-container');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = '';

    if (totalPages <= 1) {
        return;
    }

    const prevButton = `<button class="btn btn-secondary" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;
    const nextButton = `<button class="btn btn-secondary" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
    const pageInfo = `<span>Page ${currentPage} of ${totalPages}</span>`;

    paginationContainer.innerHTML = `${prevButton}${pageInfo}${nextButton}`;
}

function changePage(newPage) {
    const totalPages = Math.ceil(totalSalesCount / itemsPerPage);
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        const userRole = window.userRole;
        const search = document.getElementById('searchSales')?.value || '';
        const start = document.getElementById('startDate')?.value || '';
        const end = document.getElementById('endDate')?.value || '';

        if (userRole === 'cashier') {
            fetchCashierSales(search, start, end, currentPage);
        } else {
            fetchSales(search, start, end, currentPage);
        }
    }
}

//calling the function to fetch the sales according to the role of the user
document.addEventListener("DOMContentLoaded", () => {
    const userRole = window.userRole;

    if (userRole === 'admin' || userRole === 'manager') {
        fetchRecentSales();
        fetchSales();
    } else if (userRole === 'cashier') {
        fetchCashierRecentSales();
        fetchCashierSales();
        fetchTotalSalesToday();
    }

    // Existing search and filter event listeners
    const searchSales = document.getElementById('searchSales');
    const filterSales = document.getElementById('filterSales');

    if (searchSales) {
        searchSales.addEventListener('input', () => {
            currentPage = 1; // Reset to first page on new search
            if (userRole === 'cashier') {
                fetchCashierSales(searchSales.value);
            } else {
                fetchSales(searchSales.value);
            }
        });
    }

    if (filterSales) {
        filterSales.addEventListener('click', () => {
            currentPage = 1;
            const search = document.getElementById('searchSales')?.value || '';
            const start = document.getElementById('startDate')?.value || '';
            const end = document.getElementById('endDate')?.value || '';
            fetchSales(search, start, end);
        });
    }
});


window.fetchCashierSales=fetchCashierSales;
window.fetchCashierRecentSales=fetchCashierRecentSales;
window.fetchSales = fetchSales;
window.fetchRecentSales=fetchRecentSales;
window.openAddSaleModal = openAddSaleModal;
window.viewSale = viewSale;
window.changePage=changePage;