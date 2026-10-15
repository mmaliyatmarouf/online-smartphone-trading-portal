<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id    = $_SESSION['user_id'];
$page_title = 'My Requests';
$success    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trade_id = (int)$_POST['cancel_trade_id'];
    $conn->query("UPDATE trade_requests SET status='Cancelled' WHERE trade_id=$trade_id AND requester_user_id=$user_id AND status='Pending'");
    $success = "Request #$trade_id cancelled.";
}

$requests = $conn->query("
    SELECT tr.trade_id, tr.request_date, tr.cash_difference, tr.status,
           my_pb.brand_name AS my_brand, my_pm.model_name AS my_model,
           tg_pb.brand_name AS tg_brand, tg_pm.model_name AS tg_model,
           tg_u.name AS target_owner
    FROM trade_requests tr
    JOIN phones p_my ON tr.requester_phone_id=p_my.phone_id
    JOIN phone_models my_pm ON p_my.model_id=my_pm.model_id
    JOIN phone_brands my_pb ON my_pm.brand_id=my_pb.brand_id
    JOIN phones p_tg ON tr.target_phone_id=p_tg.phone_id
    JOIN phone_models tg_pm ON p_tg.model_id=tg_pm.model_id
    JOIN phone_brands tg_pb ON tg_pm.brand_id=tg_pb.brand_id
    JOIN users tg_u ON p_tg.user_id=tg_u.user_id
    WHERE tr.requester_user_id=$user_id
    ORDER BY tr.request_date DESC
")->fetch_all(MYSQLI_ASSOC);
include 'includes/nav.php';
?>

<div class="page-header">
  <h1>My Trade Requests</h1>
  <p>View and manage trade requests you have sent.</p>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<?php if (empty($requests)): ?>
  <div class="empty"><div class="icon">📤</div><p>No requests sent yet. <a href="marketplace.php">Browse marketplace.</a></p></div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Your Phone</th><th>Target Phone</th><th>Owner</th><th>Cash Diff</th><th>Date</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <?php $diff = (float)$r['cash_difference']; $sc = strtolower($r['status']); ?>
          <tr>
            <td>#<?= $r['trade_id'] ?></td>
            <td><b><?= $r['my_brand'] ?> <?= $r['my_model'] ?></b></td>
            <td><b><?= $r['tg_brand'] ?> <?= $r['tg_model'] ?></b></td>
            <td><?= $r['target_owner'] ?></td>
            <td>
              <?php if ($diff > 0): ?>
                <span class="cash-pay">৳<?= number_format($diff,0) ?> you pay</span>
              <?php elseif ($diff < 0): ?>
                <span class="cash-receive">৳<?= number_format(abs($diff),0) ?> you receive</span>
              <?php else: ?>
                <span class="cash-even">Even Swap</span>
              <?php endif; ?>
            </td>
            <td><?= date('d M Y', strtotime($r['request_date'])) ?></td>
            <td><span class="badge badge-<?= $sc ?>"><?= $r['status'] ?></span></td>
            <td>
              <?php if ($r['status'] === 'Pending'): ?>
                <form method="POST" onsubmit="return confirm('Cancel this request?')">
                  <input type="hidden" name="cancel_trade_id" value="<?= $r['trade_id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                </form>
              <?php else: ?> — <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

</div></body></html>
