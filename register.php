<?php include 'db.php'; ?>


<h2>Register Phone</h2>

<form action="register_phone.php" method="POST"> 

<label>User:</label>
<select name="user_id" required>
    <option value="">Select user</option>

    <?php
    $users = $conn->query("SELECT * FROM users"); //building the user drop down--> fetches all the users from the users table
    while ($u = $users->fetch_assoc()) {
        echo "<option value='{$u['user_id']}'>{$u['name']}</option>";
    }
    ?>

</select>
<br><br>

<label>Model:</label>
<select name="model_id" required>
    <option value="">Select model</option>
    <?php
    $models = $conn->query("SELECT * FROM phone_models");
    while ($m = $models->fetch_assoc()) {
        echo "<option value='{$m['model_id']}'>{$m['model_name']} ({$m['release_year']})</option>";
    }
    ?>
</select>
<br><br>

<label>Storage:</label>
<input type="text" name="storage" required>
<br><br>

<label>Battery (%):</label>
<input type="number" name="battery_health" min="1" max="100" required>
<br><br>


<label>Condition:</label><br>
<?php
$conds = $conn->query("SELECT * FROM phone_condition"); //fetches all the condition types
while ($c = $conds->fetch_assoc()) {
    echo "<input type='radio' name='condition_id' value='{$c['condition_id']}' required> {$c['condition_type']}<br>";
}
?> 
<br>

<button type="submit">Calculate Price</button>

</form>