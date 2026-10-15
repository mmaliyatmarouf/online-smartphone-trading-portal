<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$page_title = 'Admin Reports';

$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_listings = $conn->query("SELECT COUNT(*) AS total FROM market_listing")->fetch_assoc()['total'];
$active_listings = $conn->query("SELECT COUNT(*) AS total FROM market_listing WHERE status='active'")->fetch_assoc()['total'];
$inactive_listings = $conn->query("SELECT COUNT(*) AS total FROM market_listing WHERE status='inactive'")->fetch_assoc()['total'];

$brands = $conn->query("
    SELECT 
        pb.brand_name, 
        COUNT(ml.listing_id) AS total_listed
    FROM market_listing ml
    JOIN phones p ON ml.phone_id = p.phone_id
    JOIN phone_models pm ON p.model_id = pm.model_id
    JOIN phone_brands pb ON pm.brand_id = pb.brand_id
    GROUP BY pb.brand_name
    ORDER BY total_listed DESC
")->fetch_all(MYSQLI_ASSOC);

include 'includes/nav.php';
?>

<div class="page-header">
  <h1>Admin Reports</h1>
  <p>View platform summary, listing status, and most listed brands.</p>
</div>

<div class="phone-grid" style="margin-bottom:1.5rem;">

  <div class="phone-card">
    <div class="brand">Total Users</div>
    <div class="model"><?= $total_users ?></div>
    <p class="owner">Registered users</p>
  </div>

  <div class="phone-card">
    <div class="brand">Total Listings</div>
    <div class="model"><?= $total_listings ?></div>
    <p class="owner">Marketplace listings</p>
  </div>

  <div class="phone-card">
    <div class="brand">Active Listings</div>
    <div class="model"><?= $active_listings ?></div>
    <p class="owner">Currently visible listings</p>
  </div>

  <div class="phone-card">
    <div class="brand">Inactive Listings</div>
    <div class="model"><?= $inactive_listings ?></div>
    <p class="owner">Closed or hidden listings</p>
  </div>

</div>

<div class="glass-card">
  <h3>Most Listed Brands</h3>

  <?php if (empty($brands)): ?>
    <div class="empty">
      <div class="icon">📊</div>
      <p>No brand listing data found.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Brand</th>
            <th>Total Listed</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($brands as $b): ?>
            <tr>
              <td><b><?= htmlspecialchars($b['brand_name']) ?></b></td>
              <td><?= $b['total_listed'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

</div></body></html>