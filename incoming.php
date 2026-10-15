<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id    = $_SESSION['user_id'];
$page_title = 'Incoming Requests';
$success = $error = '';

// Feature 4.1 — Accept or Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trade_id = (int)$_POST['trade_id'];
    $action   = $_POST['action'] ?? '';

    $trade = $conn->query("
        SELECT tr.*, p_my.phone_id AS my_phone_id,
               tr.requester_phone_id AS req_phone_id,
               tr.requester_user_id
        FROM trade_requests tr
        JOIN phones p_my ON tr.target_phone_id = p_my.phone_id
        WHERE tr.trade_id = $trade_id AND p_my.user_id = $user_id AND tr.status = 'Pending'
    ")->fetch_assoc();

    if (!$trade) {
        $error = "Trade not found or already processed.";
    } elseif ($action === 'reject') {
        // Feature 4.1 — Reject
        $conn->query("UPDATE trade_requests SET status='Rejected' WHERE trade_id=$trade_id");
        $success = "Trade request #$trade_id rejected.";

    } elseif ($action === 'accept') {
        $req_phone = $trade['req_phone_id'];
        $tgt_phone = $trade['my_phone_id'];
        $req_user  = $trade['requester_user_id'];

        // Feature 4.2 — Transfer ownership of both phones
        $conn->query("UPDATE phones SET user_id=$user_id  WHERE phone_id=$req_phone");
        $conn->query("UPDATE phones SET user_id=$req_user WHERE phone_id=$tgt_phone");

        // Feature 4.3 — Update both phones status to Traded
        $conn->query("UPDATE phones SET status='Traded' WHERE phone_id IN ($req_phone, $tgt_phone)");

        // Feature 4.3 — Close marketplace listings for both phones
        $conn->query("UPDATE market_listing SET status='inactive' WHERE phone_id = $req_phone");
    $conn->query("UPDATE market_listing SET status='inactive' WHERE phone_id = $tgt_phone");

        // Feature 4.1 — Update trade status to Accepted
        $conn->query("UPDATE trade_requests SET status='Accepted' WHERE trade_id=$trade_id");

        // Reject all other pending requests involving these phones
        $conn->query("UPDATE trade_requests SET status='Rejected'
                      WHERE trade_id != $trade_id AND status='Pending'
                      AND (requester_phone_id IN ($req_phone,$tgt_phone)
                      OR target_phone_id IN ($req_phone,$tgt_phone))");

        // Feature 4.4 — Create transaction record
        $conn->query("INSERT INTO transactions (trade_id, transaction_date) VALUES ($trade_id, NOW())");

        $success = "Trade accepted! Ownership transferred and transaction recorded.";
    }
}

// Getting all the requests that are coming to me
$requests = $conn->query("
    SELECT tr.trade_id, tr.request_date, tr.cash_difference, tr.status,
           req_pb.brand_name AS req_brand, req_pm.model_name AS req_model,
           my_pb.brand_name AS my_brand, my_pm.model_name AS my_model,
           req_u.name AS requester_name,
           p_req.estimated_price AS req_price, p_my.estimated_price AS my_price
    FROM trade_requests tr
    JOIN phones p_req ON tr.requester_phone_id = p_req.phone_id
    JOIN phone_models req_pm ON p_req.model_id = req_pm.model_id
    JOIN phone_brands req_pb ON req_pm.brand_id = req_pb.brand_id
    JOIN users req_u ON tr.requester_user_id = req_u.user_id
    JOIN phones p_my ON tr.target_phone_id = p_my.phone_id
    JOIN phone_models my_pm ON p_my.model_id = my_pm.model_id
    JOIN phone_brands my_pb ON my_pm.brand_id = my_pb.brand_id
    WHERE p_my.user_id = $user_id
    ORDER BY tr.request_date DESC
")->fetch_all(MYSQLI_ASSOC);

include 'includes/nav.php';
?>

<div class="page-header">
  <h1>Incoming Requests</h1>
  <p>Accept or reject trade requests on your phones.</p>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if (empty($requests)): ?>
  <div class="empty"><div class="icon">📥</div><p>No incoming requests yet.</p></div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th><th>From</th><th>Their Phone</th><th>Your Phone</th>
          <th>Cash Diff</th><th>Date</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <?php $diff = (float)$r['cash_difference']; $sc = strtolower($r['status']); ?>
          <tr>
            <td>#<?= $r['trade_id'] ?></td>
            <td><b><?= htmlspecialchars($r['requester_name']) ?></b></td>
            <td>
              <b><?= $r['req_brand'] ?> <?= $r['req_model'] ?></b><br>
              <span style="font-size:0.78rem;color:var(--text-muted)">৳<?= number_format($r['req_price'],0) ?></span>
            </td>
            <td>
              <b><?= $r['my_brand'] ?> <?= $r['my_model'] ?></b><br>
              <span style="font-size:0.78rem;color:var(--text-muted)">৳<?= number_format($r['my_price'],0) ?></span>
            </td>
            <td>
              <?php if ($diff > 0): ?>
                <span class="cash-receive">৳<?= number_format($diff,0) ?> you receive</span>
              <?php elseif ($diff < 0): ?>
                <span class="cash-pay">৳<?= number_format(abs($diff),0) ?> you pay</span>
              <?php else: ?>
                <span class="cash-even">Even Swap</span>
              <?php endif; ?>
            </td>
            <td><?= date('d M Y', strtotime($r['request_date'])) ?></td>
            <td><span class="badge badge-<?= $sc ?>"><?= $r['status'] ?></span></td>
            <td>
              <?php if ($r['status'] === 'Pending'): ?>
                <div style="display:flex;gap:0.5rem;">
                  <form method="POST" onsubmit="return confirm('Accept this trade? Ownership will be transferred.')">
                    <input type="hidden" name="trade_id" value="<?= $r['trade_id'] ?>">
                    <input type="hidden" name="action" value="accept">
                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                  </form>
                  <form method="POST" onsubmit="return confirm('Reject this trade?')">
                    <input type="hidden" name="trade_id" value="<?= $r['trade_id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                  </form>
                </div>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

</div></body></html>