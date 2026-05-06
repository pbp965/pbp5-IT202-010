<?php
require(__DIR__ . "/../../../partials/nav.php");
require_once(__DIR__ . "/../../../lib/favorites.php");

if (!is_logged_in()) {
    flash("You must be logged in to favorite a country", "warning");
    die(header("Location: " . get_url("login.php")));
}

$user_id = get_user_id();
$country_id = intval(se($_GET, "id", -1, false));

if ($country_id < 1) {
    flash("Invalid country id", "danger");
    die(header("Location: " . get_url("admin/list_countries.php")));
}

$added = false;
$message = "";

try {
    $added = toggle_favorite("favorite_countries", $user_id, $country_id, "country_id");
    $message = $added ? "Country added to favorites" : "Country removed from favorites";
} catch (Exception $e) {
    error_log("Country favorite toggle error: " . var_export($e, true));
    $message = "Could not update favorite country";
}
?>

<div class="container mt-4">
    <div class="alert alert-info">
        <?php echo htmlspecialchars($message); ?>
    </div>

    <a href="<?php echo get_url("admin/list_countries.php"); ?>" class="btn btn-primary">
        Back to Countries List
    </a>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>