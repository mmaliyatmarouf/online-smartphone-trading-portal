<?php
// nav.php — place in: C:\xampp\htdocs\portal\includes\nav.php
if (session_status() === PHP_SESSION_NONE) session_start();

$user_name = $_SESSION['name'] ?? 'Guest';
$user_role = $_SESSION['role'] ?? 'user';
$current   = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>📱 TradePortal – <?= htmlspecialchars($page_title ?? 'Home') ?></title>
  <link rel="stylesheet" href="/portal/includes/style.css">
  <style>
    /* Dropdown styles */
    .nav-dropdown {
      position: relative;
    }
    .nav-dropdown > a {
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }
    .nav-dropdown > a::after {
      content: '▾';
      font-size: 0.7rem;
      opacity: 0.6;
    }
    .dropdown-menu {
      display: none;
      position: absolute;
      top: calc(100% + 8px);
      left: 0;
      min-width: 180px;
      background: rgba(17,25,40,0.95);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.125);
      border-radius: 14px;
      box-shadow: 0 12px 40px rgba(0,0,0,0.5);
      padding: 0.4rem;
      z-index: 200;
    }
    .nav-dropdown:hover .dropdown-menu {
      display: block;
    }
    .dropdown-menu a {
      display: block;
      padding: 0.5rem 0.9rem;
      border-radius: 10px;
      font-size: 0.82rem;
      font-weight: 600;
      color: rgba(255,255,255,0.7);
      text-decoration: none;
      transition: all 0.18s ease;
      border: 1px solid transparent;
    }
    .dropdown-menu a:hover {
      background: rgba(255,255,255,0.08);
      color: #fff;
      border-color: rgba(255,255,255,0.1);
    }
    .dropdown-menu a.active {
      background: rgba(96,165,250,0.2);
      border-color: rgba(96,165,250,0.3);
      color: var(--accent);
    }
    .dropdown-divider {
      border: none;
      border-top: 1px solid rgba(255,255,255,0.08);
      margin: 0.3rem 0.4rem;
    }
  </style>
</head>
<body>
<nav>
  <span class="nav-logo">📱 TradePortal</span>
  <div class="nav-links">

    <!-- My Phones -->
    <a href="/portal/my_phones.php" class="<?= $current==='my_phones' ? 'active' : '' ?>">My Phones</a>

    <!-- All Phones -->
    <a href="/portal/view_phones.php" class="<?= $current==='view_phones' ? 'active' : '' ?>">All Phones</a>

    <!-- Register Phone -->
    <a href="/portal/register_phone.php" class="<?= $current==='register_phone' ? 'active' : '' ?>">Register Phone</a>

    <!-- Marketplace dropdown -->
    <div class="nav-dropdown">
      <a href="/portal/marketplace.php" class="<?= in_array($current, ['marketplace','trade']) ? 'active' : '' ?>">Marketplace</a>
      <div class="dropdown-menu">
        <a href="/portal/marketplace.php" class="<?= $current==='marketplace' ? 'active' : '' ?>">🛒 Browse Listings</a>
        <a href="/portal/trade.php"       class="<?= $current==='trade'       ? 'active' : '' ?>">🔄 Send Trade</a>
      </div>
    </div>

    <!-- Trades dropdown -->
    <div class="nav-dropdown">
      <a href="/portal/incoming.php" class="<?= in_array($current, ['incoming','my_requests','transactions']) ? 'active' : '' ?>">Trades</a>
      <div class="dropdown-menu">
        <a href="/portal/incoming.php"    class="<?= $current==='incoming'     ? 'active' : '' ?>">📥 Incoming Requests</a>
        <a href="/portal/my_requests.php" class="<?= $current==='my_requests'  ? 'active' : '' ?>">📤 My Requests</a>
        <hr class="dropdown-divider">
        <a href="/portal/transactions.php" class="<?= $current==='transactions' ? 'active' : '' ?>">🧾 Transactions</a>
      </div>
    </div>

    <!-- Leave Review -->
    <a href="/portal/review.php" class="<?= $current==='review' ? 'active' : '' ?>">Leave Review</a>

    <!-- Admin (admin role only) -->
    <?php if ($user_role === 'admin'): ?>
      <a href="/portal/admin_dashboard.php"
         class="<?= str_starts_with($current,'admin') ? 'active' : '' ?>"
         style="color:var(--accent3);">Admin</a>
    <?php endif; ?>

    <!-- Logout -->
    <a href="/portal/logout.php" style="color:var(--accent4);">Logout</a>

  </div>
  <div class="nav-user">Logged in as <b><?= htmlspecialchars($user_name) ?></b></div>
</nav>
<div class="container">
