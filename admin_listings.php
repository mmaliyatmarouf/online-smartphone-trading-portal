<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page_title = 'Manage Listings';

/* Mark listing inactive */
if (isset($_GET['remove_id'])) {

    $listing_id = (int)$_GET['remove_id'];

    $conn->query("
        UPDATE market_listing
        SET status = 'inactive'
        WHERE listing_id = $listing_id
        AND status = 'active'
    ");

    header("Location: admin_listings.php");
    exit;
}

/* Mark listing active */
if (isset($_GET['activate_id'])) {

    $listing_id = (int)$_GET['activate_id'];

    $conn->query("
        UPDATE market_listing
        SET status = 'active'
        WHERE listing_id = $listing_id
        AND status = 'inactive'
    ");

    header("Location: admin_listings.php");
    exit;
}

/* Filter */
$filter = "";
$current_status = $_GET['status'] ?? "";

if ($current_status == "active" || $current_status == "inactive") {
    $filter = " WHERE ml.status = '$current_status'";
}

/* Listings */
$listings = $conn->query("
    SELECT 
        ml.listing_id,
        ml.status,
        ml.listing_date,
        p.phone_id,
        pb.brand_name,
        pm.model_name,
        u.name AS owner_name

    FROM market_listing ml

    JOIN phones p 
        ON ml.phone_id = p.phone_id

    JOIN phone_models pm 
        ON p.model_id = pm.model_id

    JOIN phone_brands pb 
        ON pm.brand_id = pb.brand_id

    JOIN users u 
        ON p.user_id = u.user_id

    $filter

    ORDER BY ml.listing_id DESC
")->fetch_all(MYSQLI_ASSOC);

include 'includes/nav.php';
?>

<div class="page-header">
    <h1>Manage Listings</h1>
    <p>View, filter, activate, and deactivate marketplace listings.</p>
</div>

<div class="glass-card" style="margin-bottom:1.5rem;">
    <a href="admin_listings.php" class="btn btn-primary">
        All Listings
    </a>

    <a href="admin_listings.php?status=active" class="btn btn-primary">
        Active
    </a>

    <a href="admin_listings.php?status=inactive" class="btn btn-primary">
        Inactive
    </a>
</div>

<?php if (empty($listings)): ?>

    <div class="empty">
        <div class="icon">📭</div>
        <p>No listings found.</p>
    </div>

<?php else: ?>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Phone ID</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Owner</th>
                    <th>Status</th>
                    <th>Listing Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($listings as $l): ?>

                    <?php
                        $status = strtolower($l['status']);

                        if ($status == "active") {
                            $badgeClass = "badge-good";
                        } else {
                            $badgeClass = "badge-fair";
                        }
                    ?>

                    <tr>
                        <td>#<?= $l['listing_id'] ?></td>

                        <td><?= $l['phone_id'] ?></td>

                        <td><?= htmlspecialchars($l['brand_name']) ?></td>

                        <td>
                            <b><?= htmlspecialchars($l['model_name']) ?></b>
                        </td>

                        <td><?= htmlspecialchars($l['owner_name']) ?></td>

                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($l['status']) ?>
                            </span>
                        </td>

                        <td>
                            <?= date('d M Y', strtotime($l['listing_date'])) ?>
                        </td>

                        <td>
                            <?php if ($l['status'] === 'active'): ?>

                                <a href="admin_listings.php?remove_id=<?= $l['listing_id'] ?>"
                                   class="btn btn-danger btn-sm">
                                    Mark Inactive
                                </a>

                            <?php else: ?>

                                <a href="admin_listings.php?activate_id=<?= $l['listing_id'] ?>"
                                   class="btn btn-ghost btn-sm">
                                    Mark Active
                                </a>

                            <?php endif; ?>
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