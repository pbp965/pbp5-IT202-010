<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to favorite countries", "warning");
    die(header("Location: " . get_url("login.php")));
}

$user_id = get_user_id();
$country_id = intval(se($_GET, "id", 0, false));

// Validate ID
if ($country_id <= 0) {
    flash("Invalid country selected", "danger");
    die(header("Location: " . get_url("list_countries.php")));
}

$db = getDB();

try {
    // Check if already favorited
    $check = $db->prepare("SELECT id FROM favorite_countries WHERE user_id = :uid AND country_id = :cid");
    $check->execute([
        ":uid" => $user_id,
        ":cid" => $country_id
    ]);

    if ($check->fetch()) {
        // EXISTS → REMOVE
        $delete = $db->prepare("DELETE FROM favorite_countries WHERE user_id = :uid AND country_id = :cid");
        $delete->execute([
            ":uid" => $user_id,
            ":cid" => $country_id
        ]);

        flash("Removed country from favorites", "info");
    } else {
        // NOT EXISTS → ADD
        $insert = $db->prepare("INSERT INTO favorite_countries (user_id, country_id) VALUES (:uid, :cid)");
        $insert->execute([
            ":uid" => $user_id,
            ":cid" => $country_id
        ]);

        flash("Added country to favorites", "success");
    }
} catch (PDOException $e) {
    error_log("Favorite country error: " . var_export($e, true));
    flash("An error occurred while updating favorites", "danger");
}

// Redirect back safely
$redirect = $_SERVER["HTTP_REFERER"] ?? get_url("list_countries.php");
die(header("Location: " . $redirect));