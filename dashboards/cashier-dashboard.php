<?php
session_start();
require_once '../includes/db.php';
include "../includes/modals.php";
include "../controllers/sales-controller.php";
include "../controllers/users-controller.php";

if($_SESSION["role"]!=="cashier") {
  Header('Location:../auth/login.php');
  exit();
}

$user=getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cashier Dashboard - HotelPro POS</title>
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
            <div class="menu-item" data-target="reports">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </div>
            <div class="menu-item" data-target="account">
                <i class="fas fa-user-cog"></i>
                <span>Account</span>
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
                <h1>Cashier Dashboard</h1>
            </div>
            <div class="header-actions">
                <div class="user-profile">
                    <div class="user-avatar">C</div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($_SESSION["username"])??"Cashier";?></div>
                        <div class="user-role">Cashier</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard -->
        <div class="content-section active" id="dashboard">
            <div class="dashboard-cards">
                <div class="card stat-card">
                    <div class="stat-icon sales-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                       <div class="stat-info">
                      <h3 id="totalSalesToday"></h3> 
                      <p>Total Sales for today</p>
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
                                <th>ID</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Payment method</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id='recentSalesTableBody'></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sales -->
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
                <h3>Sales for Today Records</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="sales-table-body" id="salesTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reports -->
        <div class="content-section" id="reports">
            <div class="section-header">
                <h2>Reports</h2>
            </div>

            <div class="card">
                <h3>Generate Sales Reports</h3>
                <div class="form-group">
                    <label for="reportType">Report Type</label>
                    <select class="form-control" id="reportType">
                        <option value="sales">Sales Report</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="dateRange">Date Range</label>
                    <select class="form-control" id="dateRange">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
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
                                <th>Generated by</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id='reportsTableBody'></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Account -->
        <div class="content-section" id="account">
            <div class="section-header">
                <h2>Account Settings</h2>
            </div>

            <div class="card">
                <h3>Profile Information</h3>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($_SESSION['username']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" value="<?= htmlspecialchars($user['phone']); ?>">
                </div>

                <button class="btn btn-primary" onclick="updateProfile(<?= $_SESSION['user_id']; ?>)">Update Profile</button>
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

                <button class="btn btn-primary" onclick="changePassword(<?= $_SESSION['user_id']; ?>)">Change Password</button>
            </div>
        </div>
    </div>

    <script>
        window.userRole='cashier';
    </script>

<script src="../scripts/utils.js"></script>
<script src="../scripts/load-options.js"></script>
<script src="../scripts/action-dropdown.js"></script>
<script src="../scripts/manage-sales.js"></script>
<script src="../scripts/manage-reports.js"></script>
<script src="../scripts/manage-account.js"></script>
</body>
</html>
