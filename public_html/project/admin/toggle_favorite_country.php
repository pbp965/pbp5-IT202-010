<?php
require(__DIR__ . "/../../../partials/nav.php");

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

$db = getDB();

$message = "";

try {
    $check = $db->prepare("SELECT id FROM favorite_countries WHERE user_id = :user_id AND country_id = :country_id");
    $check->execute([
        ":user_id" => $user_id,
        ":country_id" => $country_id
    ]);

    $existing = $check->fetch();

    if ($existing) {
        $stmt = $db->prepare("DELETE FROM favorite_countries WHERE user_id = :user_id AND country_id = :country_id");
        $stmt->execute([
            ":user_id" => $user_id,
            ":country_id" => $country_id
        ]);
        $message = "Removed country from favorites";
    } else {
        $stmt = $db->prepare("INSERT INTO favorite_countries (user_id, country_id) VALUES (:user_id, :country_id)");
        $stmt->execute([
            ":user_id" => $user_id,
            ":country_id" => $country_id
        ]);
        $message = "Added country to favorites";
    }

} catch (PDOException $e) {
    error_log("Favorite country error: " . var_export($e, true));
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