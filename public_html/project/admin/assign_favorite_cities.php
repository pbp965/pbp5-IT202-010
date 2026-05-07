<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("Access denied", "danger");
    die(header("Location: " . get_url("landing.php")));
}

$db = getDB();

$city_search = trim(se($_GET, "city", "", false));
$user_search = trim(se($_GET, "user", "", false));

$cities = [];
$users = [];

/* FETCH CITIES */
if ($city_search) {
    $stmt = $db->prepare("SELECT id, name FROM cities WHERE name LIKE :name LIMIT 25");
    $stmt->execute([":name" => "%$city_search%"]);
    $cities = $stmt->fetchAll();
}

/* FETCH USERS */
if ($user_search) {
    $stmt = $db->prepare("SELECT id, email FROM Users WHERE username LIKE :name LIMIT 25");
    $stmt->execute([":name" => "%$user_search%"]);
    $users = $stmt->fetchAll();
}

/* APPLY ASSOCIATIONS */
if (isset($_POST["apply"])) {
    $selectedCities = $_POST["cities"] ?? [];
    $selectedUsers = $_POST["users"] ?? [];

    foreach ($selectedUsers as $uid) {
        foreach ($selectedCities as $cid) {

            $check = $db->prepare("
                SELECT id FROM favorite_cities
                WHERE user_id = :uid AND city_id = :cid
            ");
            $check->execute([":uid"=>$uid, ":cid"=>$cid]);

            if ($check->fetch()) {
                // remove
                $stmt = $db->prepare("
                    DELETE FROM favorite_cities
                    WHERE user_id = :uid AND city_id = :cid
                ");
            } else {
                // add
                $stmt = $db->prepare("
                    INSERT INTO favorite_cities (user_id, city_id)
                    VALUES (:uid, :cid)
                ");
            }

            $stmt->execute([":uid"=>$uid, ":cid"=>$cid]);
        }
    }

    flash("Associations updated", "success");
}
?>

<div class="container mt-4">
<h3>Assign Favorite Cities</h3>

<form method="GET" class="row mb-3">
    <div class="col">
        <input name="city" placeholder="Search Cities">
    </div>
    <div class="col">
        <input name="user" placeholder="Search Users">
    </div>
    <div class="col">
        <button class="btn btn-primary">Search</button>
    </div>
</form>

<form method="POST">

<div class="row">

<div class="col">
<h5>Cities</h5>
<?php foreach ($cities as $c): ?>
    <div>
        <input type="checkbox" name="cities[]" value="<?php se($c,"id"); ?>">
        <?php se($c,"name"); ?>
    </div>
<?php endforeach; ?>
</div>

<div class="col">
<h5>Users</h5>
<?php foreach ($users as $u): ?>
    <div>
        <input type="checkbox" name="users[]" value="<?php se($u,"id"); ?>">
        <?php se($u,"username"); ?>
    </div>
<?php endforeach; ?>
</div>

</div>

<button name="apply" class="btn btn-success mt-3">Apply Associations</button>
</form>
</div>