<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page_title = 'Update Model Prices';
$success = '';

if (isset($_POST['update_price'])) {

    $model_id = (int)$_POST['model_id'];
    $base_price = (float)$_POST['base_price'];

    $conn->query("
        UPDATE phone_models
        SET base_price = $base_price
        WHERE model_id = $model_id
    ");

    $success = "Model price updated successfully.";
}

$models = $conn->query("
    SELECT 
        pm.model_id,
        pm.model_name,
        pm.release_year,
        pm.base_price,
        pb.brand_name

    FROM phone_models pm

    JOIN phone_brands pb
        ON pm.brand_id = pb.brand_id

    ORDER BY pb.brand_name, pm.model_name
")->fetch_all(MYSQLI_ASSOC);

include 'includes/nav.php';
?>

<div class="page-header">
    <h1>Update Model Prices</h1>
    <p>Update the base price of registered phone models.</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (empty($models)): ?>

    <div class="empty">
        <div class="icon">📱</div>
        <p>No phone models found.</p>
    </div>

<?php else: ?>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Release Year</th>
                    <th>Base Price</th>
                    <th>Update Price</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($models as $m): ?>

                    <tr>
                        <td>#<?= $m['model_id'] ?></td>

                        <td><?= htmlspecialchars($m['brand_name']) ?></td>

                        <td>
                            <b><?= htmlspecialchars($m['model_name']) ?></b>
                        </td>

                        <td><?= htmlspecialchars($m['release_year']) ?></td>

                        <td>
                            <b>৳<?= number_format($m['base_price'], 2) ?></b>
                        </td>

                        <td>
                            <form method="POST"
                                  style="display:flex;gap:0.5rem;align-items:center;">

                                <input type="hidden"
                                       name="model_id"
                                       value="<?= $m['model_id'] ?>">

                                <input type="number"
                                       name="base_price"
                                       value="<?= $m['base_price'] ?>"
                                       min="0"
                                       step="0.01"
                                       required
                                       style="padding:0.35rem 0.7rem;
                                              border-radius:8px;
                                              border:1px solid var(--glass-border);
                                              background:rgba(255,255,255,0.5);
                                              font-size:0.82rem;
                                              width:130px;">

                                <button type="submit"
                                        name="update_price"
                                        class="btn btn-ghost btn-sm">
                                    Update
                                </button>

                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

<?php endif; ?>

</div>
</body>
</html>