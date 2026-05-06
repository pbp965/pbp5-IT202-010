<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in", "warning");
    die(header("Location: login.php"));
}

$user_id = get_user_id();
$city_id = intval(se($_GET, "id", 0, false));

if ($city_id <= 0) {
    flash("Invalid city", "danger");
    die(header("Location: " . get_url("list_cities.php")));
}

$db = getDB();

try {

    $stmt = $db->prepare("SELECT id FROM favorite_cities WHERE user_id = :uid AND city_id = :cid");
    $stmt->execute([
        ":uid" => $user_id,
        ":cid" => $city_id
    ]);

    if ($stmt->fetch()) {

        $delete = $db->prepare("DELETE FROM favorite_cities WHERE user_id = :uid AND city_id = :cid");
        $delete->execute([
            ":uid" => $user_id,
            ":cid" => $city_id
        ]);
        flash("Removed from favorites", "info");
    } else {

        $insert = $db->prepare("INSERT INTO favorite_cities (user_id, city_id) VALUES (:uid, :cid)");
        $insert->execute([
            ":uid" => $user_id,
            ":cid" => $city_id
        ]);
        flash("Added to favorites", "success");
    }
} catch (PDOException $e) {
    error_log($e);
    flash("Error updating favorite", "danger");
}


die(header("Location: " . ($_SERVER["HTTP_REFERER"] ?? get_url("list_cities.php"))));