<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* Role check — uses the role column on the users table (same pattern as nav.php) */
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: my_phones.php");
    exit;
}

$page_title = 'All Trades';

/* Optional status filter */
$filter_status = $_GET['status'] ?? '';
$where         = "WHERE tr.status != ''"; // always true baseline
if ($filter_status !== '') {
    $safe = $conn->real_escape_string($filter_status);
    $where = "WHERE tr.status = '$safe'";
}

$trades = $conn->query("
    SELECT
        tr.trade_id,
        tr.status,
        tr.request_date,
        tr.cash_difference,
        u1.name            AS requester_name,
        u1.user_id         AS requester_id,
        pb1.brand_name     AS req_brand,
        pm1.model_name     AS req_model,
        p1.estimated_price AS req_price,
        u2.name            AS target_name,
        u2.user_id         AS target_id,
        pb2.brand_name     AS tg_brand,
        pm2.model_name     AS tg_model,
        p2.estimated_price AS tg_price
    FROM trade_requests tr
    JOIN phones       p1  ON tr.requester_phone_id = p1.phone_id
    JOIN phone_models pm1 ON p1.model_id  = pm1.model_id
    JOIN phone_brands pb1 ON pm1.brand_id = pb1.brand_id
    JOIN users        u1  ON tr.requester_user_id  = u1.user_id
    JOIN phones       p2  ON tr.target_phone_id    = p2.phone_id
    JOIN phone_models pm2 ON p2.model_id  = pm2.model_id
    JOIN phone_brands pb2 ON pm2.brand_id = pb2.brand_id
    JOIN users        u2  ON p2.user_id   = u2.user_id
    $where
    ORDER BY tr.trade_id DESC
")->fetch_all(MYSQLI_ASSOC);

include 'includes/nav.php';
?>

<div class="page-header">
  <h1>🔄 All Trades</h1>
  <p>Admin view of every trade request on the platform.</p>
</div>

<!-- Status filter buttons -->
<div class="glass-card" style="margin-bottom:1.5rem;display:flex;gap:0.6rem;flex-wrap:wrap;">
  <?php
    $statuses = ['' => 'All', 'Pending' => 'Pending', 'Accepted' => 'Accepted', 'Rejected' => 'Rejected', 'Cancelled' => 'Cancelled'];
    foreach ($statuses as $val => $label):
      $active = ($filter_status === $val) ? 'btn-primary' : 'btn-ghost';
  ?>
    <a href="admin_trades.php<?= $val !== '' ? '?status='.$val : '' ?>"
       class="btn <?= $active ?> btn-sm">
      <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($trades)): ?>
  <div class="empty">
    <div class="icon">🔄</div>
    <p>No trades found<?= $filter_status ? " with status \"$filter_status\"" : '' ?>.</p>
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Requester</th>
          <th>Their Phone</th>
          <th>Target Owner</th>
          <th>Target Phone</th>
          <th>Cash Diff</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($trades as $t): ?>
          <?php
            $diff = (float)$t['cash_difference'];
            $sc   = strtolower($t['status']);
          ?>
          <tr>
            <td>#<?= $t['trade_id'] ?></td>
            <td><?= date('d M Y', strtotime($t['request_date'])) ?></td>
            <td>
              <a href="profile.php?user_id=<?= $t['requester_id'] ?>">
                <b><?= htmlspecialchars($t['requester_name']) ?></b>
              </a>
            </td>
            <td>
              <b><?= htmlspecialchars($t['req_brand']) ?> <?= htmlspecialchars($t['req_model']) ?></b><br>
              <span style="font-size:0.78rem;color:var(--text-muted)">৳<?= number_format($t['req_price'], 0) ?></span>
            </td>
            <td>
              <a href="profile.php?user_id=<?= $t['target_id'] ?>">
                <b><?= htmlspecialchars($t['target_name']) ?></b>
              </a>
            </td>
            <td>
              <b><?= htmlspecialchars($t['tg_brand']) ?> <?= htmlspecialchars($t['tg_model']) ?></b><br>
              <span style="font-size:0.78rem;color:var(--text-muted)">৳<?= number_format($t['tg_price'], 0) ?></span>
            </td>
            <td>
              <?php if ($diff > 0): ?>
                <span class="cash-pay">৳<?= number_format($diff, 0) ?></span>
              <?php elseif ($diff < 0): ?>
                <span class="cash-receive">৳<?= number_format(abs($diff), 0) ?></span>
              <?php else: ?>
                <span class="cash-even">Even</span>
              <?php endif; ?>
            </td>
            <td><span class="badge badge-<?= $sc ?>"><?= htmlspecialchars($t['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
