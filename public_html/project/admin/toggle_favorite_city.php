<?php
require(__DIR__ . "/../../../partials/nav.php");
require_once(__DIR__ . "/../../../lib/favorites.php");

if (!is_logged_in()) {
    flash("You must be logged in to favorite a city", "warning");
    die(header("Location: " . get_url("login.php")));
}

$user_id = get_user_id();
$city_id = intval(se($_GET, "id", -1, false));

if ($city_id < 1) {
    flash("Invalid city id", "danger");
    die(header("Location: " . get_url("admin/list_cities.php")));
}

$added = false;
$message = "";

try {
    $added = toggle_favorite("favorite_cities", $user_id, $city_id, "city_id");
    $message = $added ? "City added to favorites" : "City removed from favorites";
} catch (Exception $e) {
    error_log("City favorite toggle error: " . var_export($e, true));
    $message = "Could not update favorite city";
}
?>

<div class="container mt-4">
    <div class="alert alert-info">
        <?php echo htmlspecialchars($message); ?>
    </div>

    <a href="<?php echo get_url("admin/list_cities.php"); ?>" class="btn btn-primary">
        Back to Cities List
    </a>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>