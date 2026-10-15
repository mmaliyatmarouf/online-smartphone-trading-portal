<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page_title = 'Admin Dashboard';

include 'includes/nav.php';
?>

<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Manage users, marketplace listings, model prices, and reports.</p>
</div>

<div class="phone-grid">

    <div class="phone-card">
        <div class="brand">Feature 6.1</div>
        <div class="model">Manage Users</div>
        <p class="owner">View all registered users and filter them by role.</p>
        <a href="admin_users.php" class="btn btn-primary btn-block">
            Open Users
        </a>
    </div>

    <div class="phone-card">
        <div class="brand">Feature 6.2</div>
        <div class="model">Manage Listings</div>
        <p class="owner">View, activate, deactivate, and filter marketplace listings.</p>
        <a href="admin_listings.php" class="btn btn-primary btn-block">
            Open Listings
        </a>
    </div>

    <div class="phone-card">
        <div class="brand">Feature 6.3</div>
        <div class="model">Update Model Prices</div>
        <p class="owner">Update the base price of registered phone models.</p>
        <a href="admin_models.php" class="btn btn-primary btn-block">
            Open Models
        </a>
    </div>

    <div class="phone-card">
        <div class="brand">Feature 6.5</div>
        <div class="model">Reports</div>
        <p class="owner">View user, listing, and brand statistics.</p>
        <a href="admin_reports.php" class="btn btn-primary btn-block">
            Open Reports
        </a>
    </div>

</div>

</div>
</body>
</html>