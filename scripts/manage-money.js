let currentExpensesPage = 1;
let totalBankingCount = 0;
let totalExpensesCount = 0;

//debounce effect
const debounce = (func, delay) => {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
};

// Expenses Management
function fetchExpenses(search = '', page = 1) {
    const expensesTableBody = document.getElementById('expensesTableBody');
    if (!expensesTableBody) return;

    const url = `../apis/expenses-api.php?action=getAll&search=${encodeURIComponent(search)}&page=${page}&limit=${itemsPerPage}`;

    fetch(url)
        .then(res => res.json())
        .then(response => {
            expensesTableBody.innerHTML = '';
            totalExpensesCount = response.total;

            if (response.data && response.data.length > 0) {
                response.data.forEach(e => {
                    let actions = [];
                
                actions.push({
                    type: 'view',
                    label: 'View Details',
                    onclick: `viewExpense(${e.id}); actionDropdown.closeAll();`
                });

                if (window.userRole === 'admin' || window.userRole === 'manager') {
                    actions.push({
                        type: 'edit',
                        label: 'Edit Expense',
                        onclick: `openEditExpenseModal(${e.id}); actionDropdown.closeAll();`
                    });
                }

                if (window.userRole === 'admin') {
                    actions.push({
                        type: 'delete',
                        label: 'Delete Expense',
                        onclick: `confirmDelete(${e.id}, 'expense', () => deleteExpense(${e.id})); actionDropdown.closeAll();`
                    });
                }

                const actionDropdownHTML = actionDropdown.createDropdown(actions);
                    expensesTableBody.innerHTML += `
                        <tr>
                            <td>#${e.id}</td>
                            <td>${e.name}</td>
                            <td>${e.amount}</td>
                            <td>${e.category_name}</td>
                            <td>${e.user}</td>
                            <td>${new Date(e.created_at).toLocaleDateString()}</td>
                            <td>${actionDropdownHTML}</td>
                        </tr>
                    `;
                });
            } else {
                expensesTableBody.innerHTML = '<tr><td colspan="7">No expenses found.</td></tr>';
            }
            
            currentExpensesPage = page;
            renderExpensesPagination(totalExpensesCount);
        })
        .catch(err => console.error("Error fetching expenses:", err));
}

function openAddExpenseModal() {
    document.getElementById('addModalTitle').innerText = 'Add New Expense';
    document.getElementById('addModalBody').innerHTML = `
        <div class="form-group">
            <label for="addExpenseIncome">Expense name:</label>
            <input type="text" id="addExpenseIncome" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="addExpenseAmount">Amount</label>
            <input type="number" step="0.01" id="addExpenseAmount" name="amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="addExpenseCategory">Category</label>
            <select id="addExpenseCategory" name="category" class="form-control" required></select>
        </div>
    `;
    
    document.getElementById('addForm').onsubmit = (e) => { e.preventDefault(); addExpense(); };
    loadCategoriesSelect('addExpenseCategory')
    openModal("addModal");
}

function addExpense() {
    const formData = new FormData(document.getElementById('addForm'));

    fetch('../apis/expenses-api.php?action=add', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        closeModal("addModal");
        showToast(result.success ? 'Expense added successfully' : 'Error adding expense', result.success ? 'success' : 'error');
        if (result.success) fetchExpenses();
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function openEditExpenseModal(id) {
    document.getElementById('editModalTitle').innerText = 'Edit Expense';
    document.getElementById('editId').value = '';
    document.getElementById('editModalBody').innerHTML = `
        <div class="loading-spinner">Loading expense data...</div>
    `;
    
    openModal('editModal');

    document.getElementById('editModalTitle').innerText = 'Edit Expense';
    document.getElementById('editId').value = '';
    document.getElementById('editModalBody').innerHTML = `
        <div class="loading-spinner">Loading expense data...</div>
    `;
    
    openModal('editModal');

    fetch(`../apis/expenses-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(expense => {
            document.getElementById('editModalTitle').innerText = 'Edit Expense';
            document.getElementById('editId').value = expense.id;
            document.getElementById('editModalBody').innerHTML = `
                <div class="form-group">
                    <label for="editExpenseIncome">Expense name</label>
                    <input type="text" id="editExpenseIncome" name="name" value="${expense.name}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editExpenseAmount">Amount</label>
                    <input type="number" step="0.01" id="editExpenseAmount" name="amount" value="${expense.amount}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editExpenseCategory">Category</label>
                    <select id="editExpenseCategory" name="category"  class="form-control" required></select>
                </div>
            `;
            document.getElementById('editForm').onsubmit = (e) => { e.preventDefault(); updateExpense(id); };
            loadCategoriesSelect('editExpenseCategory',expense.category_id);
        });
}

function updateExpense(id) {
    const formData = new FormData(document.getElementById('editForm'));
    const data = Object.fromEntries(formData.entries());
    data.id = id;

    fetch('../apis/expenses-api.php?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        closeModal('editModal');
        showToast(result.success ? 'Expense updated successfully' : 'Error updating expense', result.success ? 'success' : 'error');
        if (result.success) fetchExpenses();
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function viewExpense(id) {
   document.getElementById('viewModalTitle').innerText = 'Expense Details';
    document.getElementById('viewModalBody').innerHTML = `
        <div class="loading-spinner">Loading expense details...</div>
    `;
    
    openModal('viewModal');

    fetch(`../apis/expenses-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(expense => {
            document.getElementById('viewModalTitle').innerText = 'Expense Details';
            document.getElementById('viewModalBody').innerHTML = `
                <p><strong>ID:</strong> #${expense.id}</p>
                <p><strong>Amount:</strong> Tshs ${Number(expense.amount).toLocaleString()}</p>
                <p><strong>Category:</strong> ${expense.category}</p>
                <p><strong>User:</strong> ${expense.user}</p>
                <p><strong>Date:</strong> ${new Date(expense.created_at).toLocaleString()}</p>
            `;
        });
}

function deleteExpense(id) {
    fetch(`../apis/expenses-api.php?action=delete&id=${id}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}`
    })
        .then(res => res.json())
        .then(result => {
            closeModal('deleteModal');
            showToast(result.success ? 'Expense deleted successfully' : result.message, result.success ? 'success' : 'error');
            if (result.success) {
                fetchExpenses();
            }
        })
        .catch(err => {
            closeModal('deleteModal');
            console.error('An error occurred during deletion:', err);
            showToast('An unexpected error occurred.', 'error');
        });
}

function renderExpensesPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = document.getElementById('expenses-pagination-container');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = '';

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '<span>Page 1 of 1</span>';
        return;
    }

    const prevButton = `<button class="btn btn-secondary" onclick="changeExpensesPage(${currentExpensesPage - 1})" ${currentExpensesPage === 1 ? 'disabled' : ''}>Previous</button>`;
    const nextButton = `<button class="btn btn-secondary" onclick="changeExpensesPage(${currentExpensesPage + 1})" ${currentExpensesPage === totalPages ? 'disabled' : ''}>Next</button>`;
    const pageInfo = `<span>Page ${currentExpensesPage} of ${totalPages}</span>`;

    paginationContainer.innerHTML = `${prevButton}${pageInfo}${nextButton}`;
}

function changeExpensesPage(newPage) {
    const totalPages = Math.ceil(totalExpensesCount / itemsPerPage);
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        const search = document.getElementById('expenseSearch')?.value || '';
        fetchExpenses(search, currentPage);
    }
}


// Banking Management
function fetchBanking(search='',page=1) {
    const bankingTableBody = document.getElementById('bankingTableBody');
    if (!bankingTableBody) return;
    
    fetch(`../apis/banking-api.php?action=getAll&search=${encodeURIComponent(search)}&limit=${itemsPerPage}&page=${page}`)
        .then(res => res.json())
        .then(response => {
    bankingTableBody.innerHTML = '';
    totalBankingCount = response.total; 
    if (response.data && Array.isArray(response.data)) {
        response.data.forEach(b => {
    let actions = [];
                
                actions.push({
                    type: 'view',
                    label: 'View Details',
                    onclick: `viewTransaction(${b.id}); actionDropdown.closeAll();`
                });

                if (window.userRole === 'admin' || window.userRole === 'manager') {
                    actions.push({
                        type: 'edit',
                        label: 'Edit Banking',
                        onclick: `openEditBankingModal(${b.id}); actionDropdown.closeAll();`
                    });
                }

                if (window.userRole === 'admin') {
                    actions.push({
                        type: 'delete',
                        label: 'Delete Banking',
                        onclick: `confirmDelete(${b.id}, 'banking', () => deleteBanking(${b.id})); actionDropdown.closeAll();`
                    });
                }

                const actionDropdownHTML = actionDropdown.createDropdown(actions);
    bankingTableBody.innerHTML += `
        <tr>
            <td>#${b.id}</td>
            <td>${b.type}</td>
            <td>Tshs ${Number(b.amount).toLocaleString()}</td>
            <td>${b.user}</td>
            <td>${new Date(b.created_at).toLocaleDateString()}</td>
            <td>${actionDropdownHTML}</td>
        </tr>
    `;
});
             }
             else {
                bankingTableBody.innerHTML = '<tr><td colspan="5">No banking records found.</td></tr>';
            }

            renderBankingPagination(totalBankingCount);
        })
        .catch(err => console.error('Error fetching banking data:', err));
}

function openAddBankingModal() {
    document.getElementById('addModalTitle').innerText = 'Add Banking Transaction';
    document.getElementById('addModalBody').innerHTML = `
        <div class="form-group">
            <label for="addBankingType">Transaction Type</label>
            <select id="addBankingType" name="type" class="form-control" required>
                <option value="deposit">Deposit</option>
                <option value="withdrawal">Withdrawal</option>
                <option value="transfer">Transfer</option>
            </select>
        </div>
        <div class="form-group">
            <label for="addBankingAmount">Amount</label>
            <input type="number" step="0.01" id="addBankingAmount" name="amount" class="form-control" required>
        </div>
    `;
    document.getElementById('addForm').onsubmit = (e) => { e.preventDefault(); addBanking(); };
    openModal("addModal");
}

function addBanking() {
    const formData = new FormData(document.getElementById('addForm'));

    fetch('../apis/banking-api.php?action=add', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        closeModal("addModal");
        showToast(result.success ? 'Banking transaction added successfully' : 'Error adding transaction', result.success ? 'success' : 'error');
        if (result.success) fetchBanking();
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function openEditBankingModal(id) {
    document.getElementById('editModalTitle').innerText = 'Edit Expense';
    document.getElementById('editId').value = '';
    document.getElementById('editModalBody').innerHTML = `
        <div class="loading-spinner">Loading banking data...</div>
    `;
    openModal('editModal');

    fetch(`../apis/banking-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(transaction => {
            document.getElementById('editModalTitle').innerText = 'Edit Banking Transaction';
            document.getElementById('editId').value = transaction.id;
            document.getElementById('editModalBody').innerHTML = `
                <div class="form-group">
                    <label for="editBankingType">Transaction Type</label>
                    <select id="editBankingType" name="type" class="form-control" required>
                        <option value="deposit" ${transaction.type === 'deposit' ? 'selected' : ''}>Deposit</option>
                        <option value="withdrawal" ${transaction.type === 'withdrawal' ? 'selected' : ''}>Withdrawal</option>
                        <option value="transfer" ${transaction.type === 'transfer' ? 'selected' : ''}>Transfer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editBankingAmount">Amount</label>
                    <input type="number" step="0.01" id="editBankingAmount" name="amount" value="${transaction.amount}" class="form-control" required>
                </div>
            `;
            document.getElementById('editForm').onsubmit = (e) => { e.preventDefault(); updateBanking(id); };
        });
}

function updateBanking(id) {
    const formData = new FormData(document.getElementById('editForm'));
    const data = Object.fromEntries(formData.entries());
    data.id = id;

    fetch('../apis/banking-api.php?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        closeModal('editModal');
        showToast(result.success ? 'Banking transaction updated successfully' : 'Error updating transaction', result.success ? 'success' : 'error');
        if (result.success) fetchBanking();
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function viewTransaction(id) {
    document.getElementById('viewModalTitle').innerText = 'Expense Details';
    document.getElementById('viewModalBody').innerHTML = `
        <div class="loading-spinner">Loading transaction details...</div>
    `;
    
    openModal('viewModal');

    fetch(`../apis/banking-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(transaction => {
            document.getElementById('viewModalTitle').innerText = 'Transaction Details';
            document.getElementById('viewModalBody').innerHTML = `
                <p><strong>ID:</strong> #${transaction.id}</p>
                <p><strong>Type:</strong> ${transaction.type}</p>
                <p><strong>Amount:</strong> Tshs ${Number(transaction.amount).toLocaleString()}</p>
                <p><strong>User:</strong> ${transaction.user}</p>
                <p><strong>Date:</strong> ${new Date(transaction.created_at).toLocaleString()}</p>
            `;
        });
}

function deleteBanking(id) {
    fetch(`../apis/banking-api.php?action=delete&id=${id}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}`
    })
        .then(res => res.json())
        .then(result => {
            closeModal('deleteModal');
            showToast(result.success ? 'Banking record deleted successfully' : result.message, result.success ? 'success' : 'error');
            if (result.success) {
                fetchBanking();
            }
        })
        .catch(err => {
            closeModal('deleteModal');
            console.error('An error occurred during deletion:', err);
            showToast('An unexpected error occurred.', 'error');
        });
}

//pagination for banking
function renderBankingPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = document.getElementById('banking-pagination-container');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = '';

    if (totalPages <= 1) {
        return;
    }

    const prevButton = `<button class="btn btn-secondary" onclick="changeBankingPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;
    const nextButton = `<button class="btn btn-secondary" onclick="changeBankingPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
    const pageInfo = `<span>Page ${currentPage} of ${totalPages}</span>`;

    paginationContainer.innerHTML = `${prevButton}${pageInfo}${nextButton}`;
}

function changeBankingPage(newPage) {
    const totalPages = Math.ceil(totalBankingCount / itemsPerPage);
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        const search = document.getElementById('bankingSearch')?.value || '';
        fetchBanking(search, currentPage);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    fetchExpenses();
    fetchBanking();

    const expenseSearchInput = document.getElementById('expenseSearch');
    if (expenseSearchInput) {
         const debouncedFetchExpenses = debounce(() => {
            currentPage = 1; 
            fetchExpenses(expenseSearchInput.value);
        }, 300);
        expenseSearchInput.addEventListener('input', (e) => {
            debouncedFetchExpenses(e.target.value);
        });
    }

    const bankingSearchInput = document.getElementById('bankingSearch');
    if (bankingSearchInput) {
        const debouncedFetchBanking = debounce(() => {
            currentPage = 1;
            fetchBanking(bankingSearchInput.value);
        }, 300);
        bankingSearchInput.addEventListener('input', debouncedFetchBanking);
    }
});



// Make functions globally available
window.fetchExpenses = fetchExpenses;
window.fetchBanking = fetchBanking;
window.openAddExpenseModal = openAddExpenseModal;
window.openAddBankingModal = openAddBankingModal;
window.openEditExpenseModal = openEditExpenseModal;
window.openEditBankingModal = openEditBankingModal;
window.viewExpense = viewExpense;
window.viewTransaction = viewTransaction;
window.changeBankingPage = changeBankingPage;
window.changeExpensesPage=changeExpensesPage;