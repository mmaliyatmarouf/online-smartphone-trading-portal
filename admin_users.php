<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$page_title = 'Manage Users';

$filter = "";
$current_role = $_GET['role'] ?? "";

if ($current_role != "") {
    $filter = " WHERE role='$current_role'";
}

$users = $conn->query("SELECT user_id, name, email, role FROM users" . $filter)->fetch_all(MYSQLI_ASSOC);

include 'includes/nav.php';
?>

<div class="page-header">
  <h1>Manage Users</h1>
  <p>View and filter users by role.</p>
</div>

<div class="glass-card" style="margin-bottom:1.5rem;">
  <a href="admin_users.php" class="btn btn-primary">All Users</a>
  <a href="admin_users.php?role=admin" class="btn btn-primary">Admins</a>
  <a href="admin_users.php?role=user" class="btn btn-primary">Normal Users</a>
</div>

<?php if (empty($users)): ?>
  <div class="empty">
    <div class="icon">👤</div>
    <p>No users found.</p>
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= $u['user_id'] ?></td>
            <td><b><?= htmlspecialchars($u['name']) ?></b></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <span class="badge badge-<?= strtolower($u['role']) ?>">
                <?= htmlspecialchars($u['role']) ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

</div></body></html>