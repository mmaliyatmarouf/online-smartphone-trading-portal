<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id    = $_SESSION['user_id'];
$page_title = 'Send Trade Request';
$success = $error = '';
$preselect = (int)($_GET['target_phone'] ?? 0);

$myPhones = $conn->query("SELECT p.phone_id, pb.brand_name, pm.model_name, p.estimated_price 
FROM phones p JOIN phone_models pm ON p.model_id=pm.model_id 
JOIN phone_brands pb ON pm.brand_id=pb.brand_id 
WHERE p.user_id=$user_id")->fetch_all(MYSQLI_ASSOC);

$marketPhones = $conn->query("SELECT p.phone_id, pb.brand_name, pm.model_name, p.estimated_price, u.name AS owner_name 
FROM market_listing ml JOIN phones p ON ml.phone_id=p.phone_id 
JOIN phone_models pm ON p.model_id=pm.model_id 
JOIN phone_brands pb ON pm.brand_id=pb.brand_id 
JOIN users u ON p.user_id=u.user_id 
WHERE ml.status='active' AND p.user_id!=$user_id")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $my_phone_id     = (int)$_POST['my_phone_id'];
    $target_phone_id = (int)$_POST['target_phone_id'];
    $p1 = $conn->query("SELECT estimated_price FROM phones WHERE phone_id=$my_phone_id")->fetch_assoc();
    $p2 = $conn->query("SELECT estimated_price FROM phones WHERE phone_id=$target_phone_id")->fetch_assoc();

    $cash_diff = round($p2['estimated_price'] - $p1['estimated_price'], 2);

    $conn->query("INSERT INTO trade_requests (request_date, cash_difference, status, requester_user_id, requester_phone_id, target_phone_id) 
    VALUES (NOW(), $cash_diff, 'Pending', $user_id, $my_phone_id, $target_phone_id)");
    $success = "Trade request sent! Cash difference: ৳" . number_format(abs($cash_diff), 0) . ($cash_diff > 0 ? " (you pay)" : ($cash_diff < 0 ? " (you receive)" : " (even swap)"));
}
include 'includes/nav.php';
?>

<div class="page-header">
  <h1>Send Trade Request</h1>
  <p>Select your phone and the phone you want to trade for.</p>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="glass-card" style="max-width:600px;">
  <form method="POST">
    <div class="form-group">
      <label>Your Phone (offering)</label>
      <select name="my_phone_id" id="myPhone" required onchange="updateDiff()">
        <option value="">-- Select your phone --</option>
        <?php foreach ($myPhones as $p): ?>
          <option value="<?= $p['phone_id'] ?>" data-price="<?= $p['estimated_price'] ?>">
            <?= $p['brand_name'] ?> <?= $p['model_name'] ?> — ৳<?= number_format($p['estimated_price'], 0) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Target Phone (you want)</label>
      <select name="target_phone_id" id="targetPhone" required onchange="updateDiff()">
        <option value="">-- Select target phone --</option>
        <?php foreach ($marketPhones as $p): ?>
          <option value="<?= $p['phone_id'] ?>" data-price="<?= $p['estimated_price'] ?>" <?= $p['phone_id']===$preselect?'selected':'' ?>>
            <?= $p['brand_name'] ?> <?= $p['model_name'] ?> — ৳<?= number_format($p['estimated_price'], 0) ?> (<?= $p['owner_name'] ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="diffBox" style="display:none;background:rgba(255,255,255,0.35);border:1px solid var(--glass-border);border-radius:12px;padding:1rem;margin-bottom:1rem;text-align:center;">
      <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:0.3rem;">Cash Difference</div>
      <div id="diffVal" style="font-size:1.3rem;font-weight:800;"></div>
    </div>

    <button type="submit" class="btn btn-primary">Send Trade Request</button>
    <a href="marketplace.php" class="btn btn-ghost" style="margin-left:0.5rem;">← Back</a>
  </form>
</div>

<script>
function updateDiff() {
    const my = document.getElementById('myPhone');
    const tg = document.getElementById('targetPhone');
    const box = document.getElementById('diffBox');
    const val = document.getElementById('diffVal');
    if (!my.value || !tg.value) { box.style.display='none'; return; }
    const diff = parseFloat(tg.options[tg.selectedIndex].dataset.price) - parseFloat(my.options[my.selectedIndex].dataset.price);
    box.style.display = 'block';
    if (diff > 0) { val.textContent = '+৳' + Math.abs(diff).toLocaleString() + ' you pay'; val.className = 'cash-pay'; }
    else if (diff < 0) { val.textContent = '+৳' + Math.abs(diff).toLocaleString() + ' you receive'; val.className = 'cash-receive'; }
    else { val.textContent = 'Even Swap'; val.className = 'cash-even'; }
}
window.addEventListener('DOMContentLoaded', updateDiff);
</script>

</div></body></html>
