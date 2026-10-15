<?php
// profile.php — place in: C:\xampp\htdocs\portal\profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /portal/login.php");
    exit();
}

require_once("includes/db.php");

//if user id exists, use it if not then use 0
$user_id = (int)($_GET['user_id'] ?? 0);

//if id is null or 0 then the execution stops
if (!$user_id) {
    $page_title = "Error";
    include("includes/nav.php");
    echo "<div class='alert alert-error'>Invalid user.</div></div></body></html>";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $page_title = "Error";
    include("includes/nav.php");
    echo "<div class='alert alert-error'>User not found.</div></div></body></html>";
    exit();
}

$page_title = $user['name'] . "'s Profile";
include("includes/nav.php");

//Get average rating for a user
$avgStmt = $conn->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
    FROM reviews
    WHERE reviewed_user_id = ?
");
$avgStmt->bind_param("i", $user_id);
$avgStmt->execute();
$avg = $avgStmt->get_result()->fetch_assoc();

// Get reviews with reviewer names
$revStmt = $conn->prepare("
    SELECT r.rating, r.comment, u.name AS reviewer_name
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.user_id
    WHERE r.reviewed_user_id = ?
    ORDER BY r.review_id DESC
");
$revStmt->bind_param("i", $user_id);
$revStmt->execute();
$reviews = $revStmt->get_result();

// Gets all the phones posted by the user
$phoneStmt = $conn->prepare("
    SELECT m.model_name, c.condition_type, p.storage, p.battery_health, p.estimated_price
    FROM phones p
    JOIN phone_models m    ON p.model_id    = m.model_id
    JOIN phone_condition c ON p.condition_id = c.condition_id
    WHERE p.user_id = ?
    ORDER BY p.phone_id DESC 
");// newest reviews first

$phoneStmt->bind_param("i", $user_id);
$phoneStmt->execute();
$phones = $phoneStmt->get_result();
?>

<!-- used AI-->

<div class="page-header">
  <h1>👤 <?= htmlspecialchars($user['name']) ?></h1>
  <p>Member profile</p>
</div>

<!-- Rating summary -->
<div class="glass-card" style="margin-bottom:1.5rem; display:flex; align-items:center; gap:1.5rem;">
  <div style="font-size:2.5rem; font-weight:800; color:var(--accent3);">
    <?= $avg['total'] > 0 ? number_format($avg['avg_rating'], 1) : '—' ?>
  </div>
  <div>
    <div style="font-size:1.1rem; font-weight:700;">
      <?php
        $stars = round($avg['avg_rating'] ?? 0);
        echo str_repeat('⭐', $stars) ?: '—';
      ?>
    </div>
    <div style="color:var(--text-muted); font-size:0.85rem;"><?= $avg['total'] ?> review(s)</div>
  </div>
  <?php if ($_SESSION['user_id'] !== $user_id): ?>
    <a href="/portal/review.php?reviewed_user_id=<?= $user_id ?>" class="btn btn-primary btn-sm" style="margin-left:auto;">⭐ Leave a Review</a>
  <?php endif; ?>
</div>

<!-- Their phones -->
<?php if ($phones->num_rows > 0): ?>
  <h2>📱 Their Phones</h2>
  <div class="phone-grid" style="margin-bottom:2rem;">
    <?php while ($p = $phones->fetch_assoc()): ?>
      <div class="phone-card">
        <div class="model"><?= htmlspecialchars($p['model_name']) ?></div>
        <div class="specs">
          <span class="spec-pill">💾 <?= htmlspecialchars($p['storage']) ?></span>
          <span class="spec-pill">🔋 <?= $p['battery_health'] ?>%</span>
        </div>
        <?php $c = strtolower($p['condition_type']); ?>
        <span class="badge badge-<?= in_array($c,['excellent','good','fair','poor']) ? $c : 'good' ?>"><?= htmlspecialchars($p['condition_type']) ?></span>
        <div class="price">৳ <?= number_format($p['estimated_price'], 2) ?></div>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

<!-- Reviews -->
<h2>💬 Reviews</h2>
<?php if ($reviews->num_rows === 0): ?>
  <div class="empty">
    <div class="icon">💬</div>
    <p>No reviews yet.</p>
  </div>
<?php else: ?>
  <?php while ($r = $reviews->fetch_assoc()): ?>
    <div class="glass-card" style="margin-bottom:1rem;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
        <b><?= htmlspecialchars($r['reviewer_name']) ?></b>
        <span style="color:var(--accent3);"><?= str_repeat('⭐', (int)$r['rating']) ?></span>
      </div>
      <p style="color:var(--text-muted); font-size:0.9rem;"><?= htmlspecialchars($r['comment']) ?></p>
    </div>
  <?php endwhile; ?>
<?php endif; ?>

</div><!-- .container -->
</body>
</html>
