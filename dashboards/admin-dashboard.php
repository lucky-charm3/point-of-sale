<?php
session_start();
require_once '../includes/db.php';
include "../includes/modals.php";
include "../controllers/users-controller.php";
include "../controllers/products-controller.php";
include "../controllers/expenses-controller.php";
include "../controllers/sales-controller.php";
include "../controllers/banking-controller.php";

if($_SESSION["role"]!=="admin")
{
  Header('Location:../auth/login.php');
}

$userId=$_SESSION['user_id'];
$user=getUserById($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HotelPro POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/modal-styles.css"/>
    <link rel="stylesheet" href="../css/admin-dashboard.css"/>
    <link rel="stylesheet" href="../css/dashboard.css"/>
    
</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-hotel fa-2x"></i>
            <h2>New DF Hotel POS</h2>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" data-target="dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item" data-target="sales">
                <i class="fas fa-cash-register"></i>
                <span>Sales Management</span>
            </div>
            <div class="menu-item" data-target="money">
                <i class="fas fa-money-bill-wave"></i>
                <span>Money Management</span>
            </div>
            <div class="menu-item" data-target="reports">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </div>
            <div class="menu-item" data-target="account">
                <i class="fas fa-user-cog"></i>
                <span>Account</span>
            </div>
            <div class="menu-item" data-target="manage-users">
            <i class="fas fa-users-cog"></i>
            <span>Manage Users</span>
             </div>
            <div class="menu-item" data-target="products">
                <i class="fas fa-barcode"></i>
                <span>Product Management</span>
            </div>
            <div class="menu-item" data-target="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </div>
        </div>
    </div>

  

    <div class="main-content">
        <div class="header">
            <div class="header-title">
                <h1>Admin Dashboard</h1>
            </div>
            <div class="header-actions">
                <div class="user-profile">
                    <div class="user-avatar">A</div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($_SESSION["username"])??"ADMIN";?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-section active" id="dashboard">
            <div class="dashboard-cards">
                <div class="card stat-card">
                    <div class="stat-icon sales-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tshs<?= number_format(getTotalSales(), 2); ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
                <div class="card stat-card">
                    <div class="stat-icon expenses-icon">
                        <i class="fas fa-money-bill"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tshs<?= number_format(getTotalExpenses(), 2); ?></h3>
                        <p>Total Expenses</p>
                    </div>
                </div>
                <div class="card stat-card">
                    <div class="stat-icon products-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= getTotalProducts(); ?></h3>
                        <p>Products</p>
                    </div>
                </div>
                <div class="card stat-card">
                    <div class="stat-icon users-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= getTotalUsers(); ?></h3>
                        <p>Users</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-header">
                    <h2>Recent Sales</h2>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>User ID</th>
                                <th>Total Price</th>
                                <th>Payment method</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id='recentSalesTableBody'>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


         
        <div class="content-section" id="sales">
            <div class="section-header">
                <h2>Sales Management</h2>
                 <button class="btn btn-primary" onclick="openAddSaleModal()">Add New Sale</button>
            </div>

            <div class="filterer">
  <div class="filter-item">
    <label for="searchSales">Search:</label>
    <input type="text" id="searchSales" placeholder="Product or Payment Method">
  </div>

  <div class="filter-item">
    <label for="startDate">Start Date:</label>
    <input type="date" id="startDate">
  </div>

  <div class="filter-item">
    <label for="endDate">End Date:</label>
    <input type="date" id="endDate">
  </div>

  <div class="filter-item">
    <button id="filterSales">Search</button>
  </div>
</div>
            
            <div class="card">
                <h3>All Sales Records</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <tr>
                                <th>Sale ID</th>
                                <th>User ID</th>
                                <th>Total Price</th>
                                <th>Payment method</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                            </tr>
                        </thead>
                        <tbody class="sales-table-body" id="salesTableBody">
                       </tbody>
                    </table>
                </div>
                 <div id="pagination-container" class="pagination-container">
        </div>
            </div>
        </div>



        <div class="content-section" id="money">
            <div class="section-header">
                <h2>Money Management</h2>
                 <button class="btn btn-primary" onclick="openAddExpenseModal()">Add New Expense</button>
    <button class="btn btn-success" onclick="openAddBankingModal()">Add Banking Transaction</button>
            </div>
            
            <div class="card">
                <h3>Expenses Approval</h3>
                <input type="text" id="expenseSearch" class="form-control"
                 style="margin-top:1rem; margin-bottom:1rem"
                placeholder="Search expenses by name or category...">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Expense ID</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Category</th>
                                <th>Created by</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                           <tbody id="expensesTableBody">
                            </tbody>
                    </table>
                </div>
                 <div id="expenses-pagination-container" class="pagination-container">
        </div>
            </div>
            


            <div class="card">
                <h3>Banking Transactions</h3>
                <input type="text" id="bankingSearch" class="form-control"
                style="margin-top:1rem; margin-bottom:1rem"
                 placeholder="Search banking transactions...">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Created _by</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bankingTableBody">
                        </tbody>
                    </table>
                </div>
                <div id="banking-pagination-container" class="pagination-container">
        </div>
            </div>
        </div>


        <div class="content-section" id="reports">
            <div class="section-header">
                <h2>Reports</h2>
            </div>
            
            <div class="card">
                <h3>Generate Reports</h3>
                <div class="form-group">
                    <label for="reportType">Report Type</label>
                    <select class="form-control" id="reportType">
                        <option value="sales">Sales Report</option>
                        <option value="income">Income Report</option>
                        <option value="expenses">Expenses Report</option>
                        <option value="banking">Banking Report</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dateRange">Date Range</label>
                    <select class="form-control" id="dateRange">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

    <button type="submit" class="btn btn-primary" onclick="generateReport()">Generate Report</button>
            </div>
            
            <div class="card">
                <h3>Recent Reports</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Type</th>
                                <th>Date Range</th>
                                <th>Generated On</th>
                                <th>Generated_by</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                         <tbody id='reportsTableBody'>
                             </tbody>
                    </table>
                </div>
            </div>
        </div>



        <div class="content-section" id="account">
            <div class="section-header">
                <h2>Account Settings</h2>
            </div>
            
            <div class="card">
                <h3>Profile Information</h3>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="fullName">Phone</label>
                    <input type="text" class="form-control" id="phone" value="<?= htmlspecialchars($user['phone']); ?>">
                </div>
                
                <button class="btn btn-primary"  onclick="updateProfile()">Update Profile</button>
            </div>
            
            <div class="card">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" class="form-control" id="currentPassword">
                </div>
                
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" class="form-control" id="newPassword">
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword">
                </div>   
                
                <button class="btn btn-primary" onclick="changePassword()">Change Password</button>
                <button class="btn btn-danger" onclick="lockAccount()">Lock Account</button>
            </div>
        </div>



        <div class="content-section" id="manage-users">
    <div class="section-header">
        <h2>Manage Users</h2>
        <button class="btn btn-primary" onclick="openAddUserModal()">Add New User</button>
    </div>

    <div class="card">
        <h3>All Users</h3>
        <div class="table-responsive">
             <input type="text" id="searchUsers" 
         placeholder="Search by name,role,number or email" class="form-control">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>


        <div class="content-section" id="products">
    <div class="section-header">
        <h2>Product Management</h2>
        <button class="btn btn-primary" onclick="openAddProductModal()">Add New Product</button>
    </div>

    <div class="card">
        <h3>Total Products: <span id="totalProducts">0</span></h3>
    </div>

    <div class="card">
        <input type="text" id="searchProduct" placeholder="Search by product name..." class="form-control">
    </div>

    <div class="card">
        <h3>All Products</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Barcode</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    window.userRole='admin';
</script>


<script src="../scripts/utils.js"></script>
<script src="../scripts/load-options.js"></script>
<script src="../scripts/action-dropdown.js"></script>
<script src="../scripts/manage-sales.js"></script>
<script src="../scripts/manage-money.js"></script>
<script src="../scripts/manage-products.js"></script>
<script src="../scripts/manage-account.js"></script>
<script src="../scripts/manage-reports.js"></script>
<script src="../scripts/manage-users.js"></script>
</body>
</html>