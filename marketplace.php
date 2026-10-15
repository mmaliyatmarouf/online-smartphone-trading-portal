<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$page_title = 'Marketplace';
$message = '';

/* Search/filter inputs */
$search = $_GET['search_text'] ?? '';
$condition = $_GET['condition'] ?? '';
$release_year = $_GET['release_year'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$your_phone_id = $_GET['your_phone_id'] ?? '';

$your_phone_price = null;

/* List logged-in user's own inactive phone only */
if (isset($_POST['list_phone'])) {

    $phone_id = intval($_POST['phone_id']);

    /* Check if phone belongs to logged-in user */
    $ownCheck = $conn->query("
        SELECT phone_id
        FROM phones
        WHERE phone_id = $phone_id
        AND user_id = $user_id
    ");

    if ($ownCheck && $ownCheck->num_rows > 0) {

        /* Prevent duplicate active listing */
        $activeCheck = $conn->query("
            SELECT listing_id
            FROM market_listing
            WHERE phone_id = $phone_id
            AND status = 'active'
        ");

        if ($activeCheck && $activeCheck->num_rows > 0) {

            $message = "This phone is already listed in marketplace.";

        } else {

            /* Activate inactive listing */
            $inactiveCheck = $conn->query("
                SELECT listing_id
                FROM market_listing
                WHERE phone_id = $phone_id
                AND status = 'inactive'
            ");

            if ($inactiveCheck && $inactiveCheck->num_rows > 0) {

                $update = $conn->query("
                    UPDATE market_listing
                    SET status = 'active'
                    WHERE phone_id = $phone_id
                    AND status = 'inactive'
                ");

                if ($update) {
                    $message = "Phone listed successfully!";
                } else {
                    $message = "Could not list phone.";
                }

            } else {

                $message = "No inactive listing found for this phone.";

            }
        }

    } else {

        $message = "You can only list your own registered phone.";

    }
}

/* User inactive phones for dropdown */
$myInactivePhones = $conn->query("
    SELECT p.phone_id
    FROM phones p
    JOIN market_listing ml
    ON p.phone_id = ml.phone_id

    WHERE p.user_id = $user_id
    AND ml.status = 'inactive'

    ORDER BY p.phone_id DESC
")->fetch_all(MYSQLI_ASSOC);

/* Compare feature */
if ($your_phone_id !== '') {

    $selected_phone_id = intval($your_phone_id);

    $price_result = $conn->query("
        SELECT p.estimated_price

        FROM phones p
        JOIN market_listing ml
        ON p.phone_id = ml.phone_id

        WHERE p.phone_id = $selected_phone_id
        AND p.user_id = $user_id
        AND ml.status = 'active'
    ");

    if ($price_result && $price_result->num_rows > 0) {

        $price_data = $price_result->fetch_assoc();
        $your_phone_price = floatval($price_data['estimated_price']);

    } else {

        $message = "Compare phone must be your own active phone.";

    }
}

/* Marketplace listings */
/* Hide logged-in user's own phones */

$sql = "SELECT
            ma.listing_id,
            ph.phone_id,
            pb.brand_name,
            pmo.model_name,
            pmo.release_year,
            con.condition_type,
            ph.estimated_price,
            ph.storage,
            ph.battery_health,
            u.name

        FROM market_listing ma

        JOIN phones ph
        ON ma.phone_id = ph.phone_id

        JOIN phone_models pmo
        ON ph.model_id = pmo.model_id

        JOIN phone_brands pb
        ON pmo.brand_id = pb.brand_id

        JOIN phone_condition con
        ON ph.condition_id = con.condition_id

        JOIN users u
        ON ph.user_id = u.user_id

        WHERE ma.status = 'active'
        AND ph.user_id != $user_id";

/* Search */
if ($search !== '') {

    $safe_search = $conn->real_escape_string($search);

    $sql .= " AND (
        pb.brand_name LIKE '%$safe_search%'
        OR pmo.model_name LIKE '%$safe_search%'
    )";
}

/* Condition filter */
if ($condition !== '') {

    $safe_condition = $conn->real_escape_string($condition);

    $sql .= " AND con.condition_type = '$safe_condition'";
}

/* Release year filter */
if ($release_year !== '') {

    $sql .= " AND pmo.release_year = " . intval($release_year);
}

/* Min price */
if ($min_price !== '') {

    $sql .= " AND ph.estimated_price >= " . floatval($min_price);
}

/* Max price */
if ($max_price !== '') {

    $sql .= " AND ph.estimated_price <= " . floatval($max_price);
}

$sql .= " ORDER BY ma.listing_id DESC";

$listings = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

include 'includes/nav.php';
?>

<div class="page-header">
    <h1>Marketplace</h1>
    <p>Browse, list, search and compare active phone listings.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- List Phone -->
<div class="glass-card" style="margin-bottom:1.5rem;">

    <h3>List Your Inactive Phone</h3>

    <form method="POST"
          style="display:flex;gap:0.8rem;align-items:center;flex-wrap:wrap;">

        <?php if (!empty($myInactivePhones)): ?>

            <select name="phone_id"
                    required
                    style="padding:0.55rem 1rem;
                           border-radius:50px;
                           border:1px solid var(--glass-border);
                           background:rgba(255,255,255,0.5);
                           font-size:0.85rem;
                           min-width:220px;">

                <option value="">-- Select inactive phone --</option>

                <?php foreach ($myInactivePhones as $p): ?>

                    <option value="<?= $p['phone_id'] ?>">
                        <?= $p['phone_id'] ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit"
                    name="list_phone"
                    class="btn btn-primary">

                List on Marketplace

            </button>

        <?php else: ?>

            <p>No inactive registered phone available to list.</p>

        <?php endif; ?>

    </form>
</div>

<!-- Search Filter -->
<form method="GET"
      class="search-bar"
      style="margin-bottom:1.5rem;">

    <input type="number"
           name="your_phone_id"
           placeholder="Your active phone ID"
           value="<?= htmlspecialchars($your_phone_id) ?>">

    <input type="text"
           name="search_text"
           placeholder="🔍 Brand or model"
           value="<?= htmlspecialchars($search) ?>">

    <select name="condition">

        <option value="">All Conditions</option>

        <?php foreach (['Excellent','Good','Fair','Poor'] as $c): ?>

            <option value="<?= $c ?>"
                <?= $condition === $c ? 'selected' : '' ?>>

                <?= $c ?>

            </option>

        <?php endforeach; ?>

    </select>

    <input type="number"
           name="release_year"
           placeholder="Year"
           value="<?= htmlspecialchars($release_year) ?>">

    <input type="number"
           name="min_price"
           placeholder="Min ৳"
           value="<?= htmlspecialchars($min_price) ?>">

    <input type="number"
           name="max_price"
           placeholder="Max ৳"
           value="<?= htmlspecialchars($max_price) ?>">

    <button type="submit" class="btn btn-primary">
        Search
    </button>

    <a href="marketplace.php" class="btn btn-ghost">
        Reset
    </a>
</form>

<!-- Listings -->
<?php if (empty($listings)): ?>

    <div class="empty">

        <div class="icon">📭</div>
        <p>No listings found.</p>

    </div>

<?php else: ?>

    <div class="phone-grid" style="margin-bottom:2rem;">

        <?php foreach ($listings as $l): ?>

            <?php $condClass = strtolower($l['condition_type']); ?>

            <div class="phone-card">

                <div class="brand">
                    <?= htmlspecialchars($l['brand_name']) ?>
                </div>

                <div class="model">
                    <?= htmlspecialchars($l['model_name']) ?>
                </div>

                <div class="specs">

                    <span class="spec-pill">
                        📅 <?= htmlspecialchars($l['release_year']) ?>
                    </span>

                    <span class="spec-pill">
                        💾 <?= htmlspecialchars($l['storage']) ?>
                    </span>

                    <span class="spec-pill">
                        🔋 <?= htmlspecialchars($l['battery_health']) ?>%
                    </span>

                </div>

                <span class="badge badge-<?= htmlspecialchars($condClass) ?>">

                    <?= htmlspecialchars($l['condition_type']) ?>

                </span>

                <div class="price">
                    ৳<?= number_format($l['estimated_price'], 0) ?>
                </div>

                <?php if ($your_phone_price !== null): ?>

                    <?php
                    $difference =
                        $your_phone_price - floatval($l['estimated_price']);
                    ?>

                    <div style="margin-top:0.6rem;font-weight:700;">

                        <?php if ($difference > 0): ?>

                            <span class="cash-receive">
                                Gain: ৳<?= number_format($difference, 0) ?>
                            </span>

                        <?php elseif ($difference < 0): ?>

                            <span class="cash-pay">
                                Loss: ৳<?= number_format(abs($difference), 0) ?>
                            </span>

                        <?php else: ?>

                            <span class="cash-even">
                                No gain/loss
                            </span>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

                <div class="owner">

                    👤 <?= htmlspecialchars($l['name']) ?>
                    &nbsp;·&nbsp;
                    Listing #<?= $l['listing_id'] ?>

                </div>

                <a href="trade.php?target_phone=<?= $l['phone_id'] ?>"
                   class="btn btn-primary btn-block"
                   style="margin-top:0.5rem;">

                    Request Trade

                </a>

            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

</div>
</body>
</html>