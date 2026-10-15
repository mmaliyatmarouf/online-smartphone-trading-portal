<?php
// my_phones.php — place in: C:\xampp\htdocs\portal\my_phones.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /portal/login.php");
    exit();
}

require_once("includes/db.php");

$page_title = "My Phones";
include("includes/nav.php");

$user_id = $_SESSION['user_id'];

$result = $conn->query("
    SELECT p.phone_id, m.model_name, c.condition_type,
           p.storage, p.battery_health, p.estimated_price
    FROM phones p
    JOIN phone_models m   ON p.model_id    = m.model_id
    JOIN phone_condition c ON p.condition_id = c.condition_id
    WHERE p.user_id = $user_id
");
?>

<div class="page-header">
  <h1>📱 My Phones</h1>
  <p>Phones you have registered for trading</p>
</div>

<div style="margin-bottom:1.5rem;">
  <a href="/portal/register_phone.php" class="btn btn-primary">+ Register New Phone</a>
  <a href="/portal/view_phones.php"    class="btn btn-ghost"   style="margin-left:0.7rem;">View All Phones</a>
  <a href="/portal/review.php"         class="btn btn-ghost"   style="margin-left:0.7rem;">⭐ Leave a Review</a>
</div>

<?php if ($result->num_rows === 0): ?>
  <div class="empty">
    <div class="icon">📭</div>
    <p>You have no phones registered yet.</p>
    <a href="/portal/register_phone.php" class="btn btn-primary" style="margin-top:1rem;">Register Your First Phone</a>
  </div>
<?php else: ?>
  <div class="phone-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="phone-card">
        <div class="model"><?= htmlspecialchars($row['model_name']) ?></div>
        <div class="specs">
          <span class="spec-pill">💾 <?= htmlspecialchars($row['storage']) ?></span>
          <span class="spec-pill">🔋 <?= htmlspecialchars($row['battery_health']) ?>%</span>
        </div>
        <?php
          $cond  = strtolower($row['condition_type']);
          $badge = in_array($cond, ['excellent','good','fair','poor']) ? "badge-$cond" : "badge-good";
        ?>
        <span class="badge <?= $badge ?>"><?= htmlspecialchars($row['condition_type']) ?></span>
        <div class="price">৳ <?= number_format($row['estimated_price'], 2) ?></div>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

</div><!-- .container -->
</body>
</html>
