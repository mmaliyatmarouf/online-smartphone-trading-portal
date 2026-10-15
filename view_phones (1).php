<?php
// view_phones.php — place in: C:\xampp\htdocs\portal\view_phones.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /portal/login.php");
    exit();
}

require_once("includes/db.php");

$page_title = "All Phones";
include("includes/nav.php");

$result = $conn->query("
    SELECT p.phone_id, u.name AS user_name,
           m.model_name, c.condition_type,
           p.storage, p.battery_health,
           p.estimated_price, u.user_id
    FROM phones p
    JOIN users u           ON p.user_id     = u.user_id
    JOIN phone_models m    ON p.model_id    = m.model_id
    JOIN phone_condition c ON p.condition_id = c.condition_id
    ORDER BY p.phone_id DESC
");
?>

<div class="page-header">
  <h1>📱 All Phones</h1>
  <p>Browse all phones listed on TradePortal</p>
</div>

<div style="margin-bottom:1.5rem;">
  <a href="/portal/register_phone.php" class="btn btn-primary">+ Register Phone</a>
  <a href="/portal/review.php"         class="btn btn-ghost" style="margin-left:0.7rem;">⭐ Leave a Review</a>
</div>

<?php if ($result->num_rows === 0): ?>
  <div class="empty">
    <div class="icon">📭</div>
    <p>No phones have been registered yet.</p>
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Model</th>
          <th>Storage</th>
          <th>Battery</th>
          <th>Condition</th>
          <th>Est. Price</th>
          <th>Profile</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php
          $cond  = strtolower($row['condition_type']);
          $badge = in_array($cond, ['excellent','good','fair','poor']) ? "badge-$cond" : "badge-good";
        ?>
        <tr>
          <td><?= $row['phone_id'] ?></td>
          <td><?= htmlspecialchars($row['user_name']) ?></td>
          <td><?= htmlspecialchars($row['model_name']) ?></td>
          <td><?= htmlspecialchars($row['storage']) ?></td>
          <td><?= htmlspecialchars($row['battery_health']) ?>%</td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($row['condition_type']) ?></span></td>
          <td>৳ <?= number_format($row['estimated_price'], 2) ?></td>
          <td><a href="/portal/profile.php?user_id=<?= $row['user_id'] ?>" class="btn btn-ghost btn-sm">View</a></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

</div><!-- .container -->
</body>
</html>
