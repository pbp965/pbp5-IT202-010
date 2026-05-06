<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to favorite a city", "warning");
    die(header("Location: " . get_url("login.php")));
}

$user_id = get_user_id();
$city_id = intval(se($_GET, "id", -1, false));

$message = "";

if ($city_id < 1) {
    $message = "Invalid city id";
} else {

    $db = getDB();

    try {
        $check = $db->prepare("SELECT id FROM favorite_cities WHERE user_id = :user_id AND city_id = :city_id");
        $check->execute([
            ":user_id" => $user_id,
            ":city_id" => $city_id
        ]);

        $existing = $check->fetch();

        if ($existing) {
            $stmt = $db->prepare("DELETE FROM favorite_cities WHERE user_id = :user_id AND city_id = :city_id");
            $stmt->execute([
                ":user_id" => $user_id,
                ":city_id" => $city_id
            ]);
            $message = "Removed city from favorites";
        } else {
            $stmt = $db->prepare("INSERT INTO favorite_cities (user_id, city_id) VALUES (:user_id, :city_id)");
            $stmt->execute([
                ":user_id" => $user_id,
                ":city_id" => $city_id
            ]);
            $message = "Added city to favorites";
        }

    } catch (PDOException $e) {
        error_log("Favorite city error: " . var_export($e, true));
        $message = "Could not update favorite city";
    }
}
?>

<div class="container mt-4">

    <div class="alert alert-info">
        <?php echo $message; ?>
    </div>

    <a href="<?php echo get_url("admin/list_cities.php"); ?>" class="btn btn-primary">
        Back to Cities
    </a>

</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>