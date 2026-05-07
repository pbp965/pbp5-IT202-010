<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("Access denied", "danger");
    die(header("Location: " . get_url("landing.php")));
}

$db = getDB();

/* FILTERS */
$username = trim(se($_GET, "username", "", false));
$limit = intval(se($_GET, "limit", 10, false));
$sort = se($_GET, "sort", "name", false);
$order = strtolower(se($_GET, "order", "asc", false)) === "desc" ? "DESC" : "ASC";

if ($limit < 1 || $limit > 100) $limit = 10;

/* QUERY */
$query = "
SELECT 
    c.id as city_id,
    c.name,
    u.email,
    fc.user_id,
    COUNT(fc2.user_id) as total_users
FROM favorite_cities fc
JOIN Users u ON u.id = fc.user_id
JOIN cities c ON c.id = fc.city_id
LEFT JOIN favorite_cities fc2 ON fc2.city_id = c.id
WHERE 1=1
";

$params = [];

if ($username) {
    $query .= " AND u.username LIKE :username";
    $params[":username"] = "%$username%";
}

$query .= "
GROUP BY c.id, u.id
ORDER BY c.$sort $order
LIMIT :limit
";

$stmt = $db->prepare($query);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$results = [];

try {
    $stmt->execute();
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e);
    flash("Error loading associations", "danger");
}
?>

<div class="container mt-4">
<h3>All Favorite Cities (Admin)</h3>

<form method="GET" class="row mb-3">
    <div class="col">
        <input type="text" name="username" placeholder="Username" value="<?php se($username); ?>">
    </div>
    <div class="col">
        <input type="number" name="limit" value="<?php se($limit); ?>">
    </div>
    <div class="col">
        <button class="btn btn-primary">Filter</button>
    </div>
</form>

<?php if (!$results): ?>
    <p>No results available</p>
<?php else: ?>
<table class="table">
<thead>
<tr>
    <th>City</th>
    <th>User</th>
    <th># Users</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php foreach ($results as $r): ?>
<tr>
    <td>
        <a href="<?php echo get_url("admin/view_city.php"); ?>?id=<?php se($r,"city_id"); ?>">
            <?php se($r,"name"); ?>
        </a>
    </td>

    <td>
        <a href="<?php echo get_url("profile.php"); ?>?id=<?php se($r,"user_id"); ?>">
            <?php se($r,"email"); ?>
        </a>
    </td>

    <td><?php se($r,"total_users"); ?></td>

    <td>
        <a class="btn btn-danger btn-sm"
           href="<?php echo get_url("admin/toggle_favorite_city.php"); ?>?id=<?php se($r,"city_id"); ?>">
           Remove
        </a>
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
<?php endif; ?>
</div>