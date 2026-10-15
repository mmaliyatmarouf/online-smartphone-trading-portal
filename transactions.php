<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id    = $_SESSION['user_id'];
$page_title = 'Transaction History';

// Feature 4.5 — View full transaction history
$transactions = $conn->query("
    SELECT
        t.transactions_id, t.transaction_date,
        tr.trade_id, tr.cash_difference,
        tr.requester_user_id,
        req_pb.brand_name AS req_brand, req_pm.model_name AS req_model,
        p_req.estimated_price AS req_price,
        tg_pb.brand_name AS tg_brand, tg_pm.model_name AS tg_model,
        p_tg.estimated_price AS tg_price,
        req_u.name AS req_user_name,
        tg_u.name AS tg_user_name
    FROM transactions t
    JOIN trade_requests tr ON t.trade_id = tr.trade_id
    JOIN phones p_req ON tr.requester_phone_id = p_req.phone_id
    JOIN phone_models req_pm ON p_req.model_id = req_pm.model_id
    JOIN phone_brands req_pb ON req_pm.brand_id = req_pb.brand_id
    JOIN users req_u ON tr.requester_user_id = req_u.user_id
    JOIN phones p_tg ON tr.target_phone_id = p_tg.phone_id
    JOIN phone_models tg_pm ON p_tg.model_id = tg_pm.model_id
    JOIN phone_brands tg_pb ON tg_pm.brand_id = tg_pb.brand_id
    JOIN users tg_u ON p_tg.user_id = tg_u.user_id
    WHERE tr.requester_user_id = $user_id OR p_tg.user_id = $user_id
    ORDER BY t.transaction_date DESC
")->fetch_all(MYSQLI_ASSOC);

include 'includes/nav.php';
?>

<div class="page-header">
  <h1>Transaction History</h1>
  <p>All completed trades you have been part of.</p>
</div>

<?php if (empty($transactions)): ?>
  <div class="empty">
    <div class="icon">🧾</div>
    <p>No completed transactions yet.</p>
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Txn #</th>
          <th>Date</th>
          <th>Phone Given</th>
          <th>Phone Received</th>
          <th>Cash</th>
          <th>Traded With</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transactions as $t): ?>
          <?php
            $diff = (float)$t['cash_difference'];
            $i_am_requester = ($t['requester_user_id'] == $user_id);
          ?>
          <tr>
            <td>#<?= $t['transactions_id'] ?></td>
            <td><?= date('d M Y', strtotime($t['transaction_date'])) ?></td>
            <td>
              <?php if ($i_am_requester): ?>
                <b><?= $t['req_brand'] ?> <?= $t['req_model'] ?></b><br>
                <span style="font-size:0.78rem;color:var(--text-muted)">৳<?= number_format($t['req_price'],0) ?></span>
              <?php else: ?>
                <b><?= $t['tg_brand'] ?> <?= $t['tg_model'] ?></b><br>
                <span style="font-size:0.78rem;color:var(--text-muted)">৳<?= number_format($t['tg_price'],0) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($i_am_requester): ?>
                <b><?= $t['tg_brand'] ?> <?= $t['tg_model'] ?></b><br>
                <span style="font-size:0.78rem;color:var(--text-muted)">৳<?= number_format($t['tg_price'],0) ?></span>
              <?php else: ?>
                <b><?= $t['req_brand'] ?> <?= $t['req_model'] ?></b><br>
                <span style="font-size:0.78rem;color:var(--text-muted)">৳<?= number_format($t['req_price'],0) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($diff > 0): ?>
                <?php echo $i_am_requester ? '<span class="cash-pay">Paid ৳'.number_format($diff,0).'</span>' : '<span class="cash-receive">Received ৳'.number_format($diff,0).'</span>'; ?>
              <?php elseif ($diff < 0): ?>
                <?php echo $i_am_requester ? '<span class="cash-receive">Received ৳'.number_format(abs($diff),0).'</span>' : '<span class="cash-pay">Paid ৳'.number_format(abs($diff),0).'</span>'; ?>
              <?php else: ?>
                <span class="cash-even">Even Swap</span>
              <?php endif; ?>
            </td>
            <td>
              <?php echo $i_am_requester ? htmlspecialchars($t['tg_user_name']) : htmlspecialchars($t['req_user_name']); ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

</div></body></html>
