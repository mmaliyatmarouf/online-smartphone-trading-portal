<?php

//resumes the login session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /portal/login.php");
    exit();
}

//require once--> we only load these files once
require_once("includes/db.php"); //connection to the db
require_once("includes/price_calc.php"); //connection to the price_calc

$success_msg = "";
$error_msg   = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") { //Checks whether the form was submitted or the page was just opened 
    if (
        isset($_POST['user_id'], $_POST['model_id'], $_POST['storage'],
              $_POST['battery_health'], $_POST['condition_id'])
    ) { 
      //checks that all the required datas were submitted

        $user_id       = (int)$_POST['user_id'];
        $model_id      = (int)$_POST['model_id'];
        $storage       = $conn->real_escape_string($_POST['storage']); // since it is a string it cannot be int casted so real_escape_string ensures no dangerous characters are present
        $battery_health = (int)$_POST['battery_health'];
        $condition_id  = (int)$_POST['condition_id'];
        //int casting for security

        //Get model info
        $modelResult = $conn->query("SELECT * FROM phone_models WHERE model_id = $model_id");
        $model       = $modelResult->fetch_assoc();
        $base_price  = $model['base_price'];
        $release_year = $model['release_year'];

        //Get condition multiplier
        $condResult = $conn->query("SELECT * FROM phone_condition WHERE condition_id = $condition_id");
        $condition  = $condResult->fetch_assoc();
        $multiplier = $condition['price_multiplier'];

        //Use shared price calc function
        $estimated_price = calculateEstimatedPrice($base_price, $release_year, $multiplier); //calling the function
        //Also factor in battery health--> reduce the price estimation based battery health
        $estimated_price = round($estimated_price * ($battery_health / 100), 2); //keeps 2 decimal places

        $stmt = $conn->prepare("
            INSERT INTO phones (user_id, model_id, storage, battery_health, condition_id, estimated_price)
            VALUES (?, ?, ?, ?, ?, ?)
        "); //? are the place holders for when the values will be inserted later 
        $stmt->bind_param("iisidi", $user_id, $model_id, $storage, $battery_health, $condition_id, $estimated_price);
        //connects the php variable to those placeholders 
        //iisidi--> tells us which values are integers, strings, decimals
        $stmt->execute();
		   $new_phone_id = $conn->insert_id;
   $listStmt = $conn->prepare("INSERT INTO market_listing (phone_id, status) VALUES (?, 'inactive')");
   $listStmt->bind_param("i", $new_phone_id);
   $listStmt->execute();
        

        $success_msg = "Phone registered! Estimated price: ৳ " . number_format($estimated_price, 2);
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}
//AI used
$page_title = "Register Phone";
include("includes/nav.php");
?>

<div class="page-header">
  <h1>📱 Register Phone</h1>
  <p>Add a new phone to your TradePortal listing</p>
</div>

<?php if ($success_msg): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<div class="glass-card" style="max-width: 600px;">
  <form action="register_phone.php" method="POST">

    <div class="form-group">
      <label>User</label>
      <select name="user_id" required>
        <option value="">Select user</option>
        <?php
        $users = $conn->query("SELECT * FROM users ORDER BY name");
        while ($u = $users->fetch_assoc()):
          $sel = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $u['user_id']) ? 'selected' : '';
        ?>
          <option value="<?= $u['user_id'] ?>" <?= $sel ?>><?= htmlspecialchars($u['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Model</label>
      <select name="model_id" required>
        <option value="">Select model</option>
        <?php
        $models = $conn->query("SELECT * FROM phone_models ORDER BY model_name");
        while ($m = $models->fetch_assoc()):
        ?>
          <option value="<?= $m['model_id'] ?>"><?= htmlspecialchars($m['model_name']) ?> (<?= $m['release_year'] ?>)</option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Storage</label>
      <input type="text" name="storage" placeholder="e.g. 128GB" required>
    </div>

    <div class="form-group">
      <label>Battery Health (%)</label>
      <input type="number" name="battery_health" min="1" max="100" placeholder="1 – 100" required>
    </div>

    <div class="form-group">
      <label>Condition</label>
      <div class="radio-group">
        <?php
        $conds = $conn->query("SELECT * FROM phone_condition ORDER BY condition_id");
        while ($c = $conds->fetch_assoc()):
          $cid  = $c['condition_id'];
          $ctype = htmlspecialchars($c['condition_type']);
          $cls  = strtolower($c['condition_type']);
        ?>
          <label class="radio-option">
            <input type="radio" name="condition_id" value="<?= $cid ?>" required style="accent-color:var(--accent);">
            <span class="badge badge-<?= $cls ?>" style="margin:0;"><?= $ctype ?></span>
          </label>
        <?php endwhile; ?>
      </div>
    </div>

    <div style="margin-top: 1.5rem;">
      <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem; padding: 0.75rem 1.4rem;">
        📊 Calculate &amp; Register
      </button>
    </div>

  </form>
</div>

</div><!-- .container -->
</body>
</html>
