<?php
function calculateEstimatedPrice($base_price, $release_year, $multiplier) {
    $current_year = date("Y");
    $age = $current_year - $release_year;

    $depreciation_factor = max(0.20, 1 - ($age * 0.10));

    $estimated_price = $base_price * $depreciation_factor * $multiplier;

    return round($estimated_price, 2);
}
?>