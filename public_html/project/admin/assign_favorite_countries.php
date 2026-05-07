<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("Admin only", "danger");
    die(header("Location: " . get_url("login.php")));
}

$db = getDB();

$country_query = trim(se($_GET, "country", "", false));
$user_query = trim(se($_GET, "user", "", false));

$countries = [];
$users = [];

/* ===== SEARCH COUNTRIES ===== */
if ($country_query) {
    $stmt = $db->prepare("SELECT id, name, code 
                          FROM countries 
                          WHERE name LIKE :name 
                          LIMIT 25");
    $stmt->execute([":name" => "%$country_query%"]);
    $countries = $stmt->fetchAll();
}

/* ===== SEARCH USERS ===== */
if ($user_query) {
    $stmt = $db->prepare("SELECT id, email 
                          FROM Users 
                          WHERE email LIKE :email 
                          LIMIT 25");
    $stmt->execute([":email" => "%$user_query%"]);
    $users = $stmt->fetchAll();
}

/* ===== APPLY ASSOCIATIONS ===== */
if (isset($_POST["apply"])) {

    $selected_countries = $_POST["countries"] ?? [];
    $selected_users = $_POST["users"] ?? [];

    foreach ($selected_users as $uid) {
        foreach ($selected_countries as $cid) {

            // check existing
            $check = $db->prepare("SELECT id FROM favorite_countries 
                                   WHERE user_id = :uid AND country_id = :cid");
            $check->execute([
                ":uid" => $uid,
                ":cid" => $cid
            ]);

            if ($check->fetch()) {
                // remove
                $del = $db->prepare("DELETE FROM favorite_countries 
                                     WHERE user_id = :uid AND country_id = :cid");
                $del->execute([
                    ":uid" => $uid,
                    ":cid" => $cid
                ]);
            } else {
                // insert
                $ins = $db->prepare("INSERT INTO favorite_countries (user_id, country_id) 
                                     VALUES (:uid, :cid)");
                $ins->execute([
                    ":uid" => $uid,
                    ":cid" => $cid
                ]);
            }
        }
    }

    flash("Associations updated", "success");
}
?>

<div class="container">
    <h3>Assign Favorite Countries</h3>

    <!-- SEARCH FORM -->
    <form method="GET">
        <input type="text" name="country" placeholder="Search countries">
        <input type="text" name="user" placeholder="Search users">
        <input type="submit" value="Search">
    </form>

    <form method="POST">
        <div class="row">

            <!-- COUNTRIES -->
            <div class="col">
                <h4>Countries</h4>
                <?php foreach ($countries as $c): ?>
                    <div>
                        <input type="checkbox" name="countries[]" value="<?php echo $c["id"]; ?>">
                        <?php echo htmlspecialchars($c["name"] . " (" . $c["code"] . ")"); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- USERS -->
            <div class="col">
                <h4>Users</h4>
                <?php foreach ($users as $u): ?>
                    <div>
                        <input type="checkbox" name="users[]" value="<?php echo $u["id"]; ?>">
                        <?php echo htmlspecialchars($u["email"]); ?>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <br>
        <input type="submit" name="apply" value="Apply Associations" class="btn btn-primary">
    </form>
</div>