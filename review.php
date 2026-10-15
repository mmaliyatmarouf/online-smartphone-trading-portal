<?php
// review.php — place in: C:\xampp\htdocs\portal\review.php
//gets the currently logged in user's ID from the session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /portal/login.php");
    exit();
}

require_once("includes/db.php");

//setting variables
$page_title  = "Leave a Review";
$success     = "";
$error       = "";
$reviewer_id = $_SESSION['user_id']; //logged in user's id will be the reviwer id

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reviewed_user_id = (int)$_POST['reviewed_user_id']; //get the data from the form
    $rating           = (int)$_POST['rating'];
    $comment          = $conn->real_escape_string(trim($_POST['comment']));

    if ($rating < 1 || $rating > 5) {
        $error = "Rating must be between 1 and 5.";
    } elseif ($reviewer_id == $reviewed_user_id) {
        $error = "You cannot review yourself.";
    } else {
        // Check if already reviewed this user 
        $check = $conn->prepare("
            SELECT review_id FROM reviews
            WHERE reviewer_id = ? AND reviewed_user_id = ?
        "); //checks the db whether this user has reviewd the other user alr
        $check->bind_param("ii", $reviewer_id, $reviewed_user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "You have already reviewed this user.";
        } else {
          //entering the review into the database
            $stmt = $conn->prepare("
                INSERT INTO reviews (reviewer_id, reviewed_user_id, rating, comment)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiis", $reviewer_id, $reviewed_user_id, $rating, $comment);

            if ($stmt->execute()) {
                $success = "✅ Review submitted successfully!";
            } else {
                // Fallback: maybe the table has a transaction_id column try with NULL
                // db table has a column transaction_id but we are not connecting the review to the transaction so we ensure id becomes NULL
                $stmt2 = $conn->prepare("
                    INSERT INTO reviews (transaction_id, reviewer_id, reviewed_user_id, rating, comment)
                    VALUES (NULL, ?, ?, ?, ?)
                ");
                $stmt2->bind_param("iiis", $reviewer_id, $reviewed_user_id, $rating, $comment);
                if ($stmt2->execute()) {
                    $success = "✅ Review submitted successfully!";
                } else {
                    $error = "Could not submit review: " . $conn->error;
                }
            }
        }
    }
}

include("includes/nav.php");
?>


<div class="page-header">
  <h1>⭐ Leave a Review</h1>
  <p>Share your experience with another TradePortal user</p>
</div>

<?php if ($success): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="glass-card" style="max-width:520px;">
  <form method="POST" action="review.php">

    <div class="form-group">
      <label>User to Review</label>
      <select name="reviewed_user_id" required>
        <option value="">Select user</option>
        <?php
        $users = $conn->query("SELECT user_id, name FROM users WHERE user_id != $reviewer_id ORDER BY name");
        while ($u = $users->fetch_assoc()):
        ?>
          <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Rating</label>
      <select name="rating" required>
        <option value="">Select rating</option>
        <option value="5">⭐⭐⭐⭐⭐ – Excellent</option>
        <option value="4">⭐⭐⭐⭐ – Good</option>
        <option value="3">⭐⭐⭐ – Okay</option>
        <option value="2">⭐⭐ – Poor</option>
        <option value="1">⭐ – Terrible</option>
      </select>
    </div>

    <div class="form-group">
      <label>Comment</label>
      <textarea name="comment" rows="4"
        style="width:100%; background:rgba(255,255,255,0.07); border:1px solid var(--glass-border);
               color:var(--text); border-radius:12px; padding:0.65rem 1rem;
               font-family:'Nunito',sans-serif; font-size:0.9rem; outline:none; resize:vertical;"
        placeholder="Describe your experience..."></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Submit Review</button>
  </form>
</div>

</div><!-- .container -->
</body>
</html>
