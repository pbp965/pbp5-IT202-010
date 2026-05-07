<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in", "warning");
    die(header("Location: " . get_url("login.php")));
}

$db = getDB();
$user_id = get_user_id();

/* =========================
   FILTERS + VALIDATION
========================= */
$name = trim(se($_GET, "name", "", false));
$limit = intval(se($_GET, "limit", 10, false));
$sort = se($_GET, "sort", "name", false);
$order = strtolower(se($_GET, "order", "asc", false)) === "desc" ? "DESC" : "ASC";

if ($limit < 1 || $limit > 100) $limit = 10;

$allowed_sort = ["name", "population", "created"];
if (!in_array($sort, $allowed_sort)) $sort = "name";

/* =========================
   REMOVE ALL FAVORITES
========================= */
if (isset($_POST["clear_all"])) {
    $stmt = $db->prepare("DELETE FROM favorite_cities WHERE user_id = :uid");
    $stmt->execute([":uid" => $user_id]);
    flash("All favorite cities removed", "success");
}

/* =========================
   MAIN QUERY
========================= */
$query = "
SELECT c.id, c.name, c.population, c.country_code, fc.created
FROM favorite_cities fc
JOIN cities c ON c.id = fc.city_id
WHERE fc.user_id = :uid
";

$params = [":uid" => $user_id];

if ($name) {
    $query .= " AND c.name LIKE :name";
    $params[":name"] = "%$name%";
}

$query .= " ORDER BY c.$sort $order LIMIT :limit";

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
    flash("Error loading favorites", "danger");
}

/* =========================
   COUNT
========================= */
$countStmt = $db->prepare("
SELECT COUNT(*) as total
FROM favorite_cities
WHERE user_id = :uid
");
$countStmt->execute([":uid" => $user_id]);
$total = $countStmt->fetch()["total"];
?>

<div class="container mt-4">

<h3>My Favorite Cities</h3>
<p><b>Total Favorites:</b> <?php echo $total; ?> |
   <b>Showing:</b> <?php echo count($results); ?></p>

<form method="GET" class="row mb-3">

    <div class="col">
        <input type="text" name="name" placeholder="City Name" value="<?php se($name); ?>">
    </div>

    <div class="col">
        <select name="sort">
            <option value="name">Name</option>
            <option value="population">Population</option>
        </select>
    </div>

    <div class="col">
        <select name="order">
            <option value="asc">ASC</option>
            <option value="desc">DESC</option>
        </select>
    </div>

    <div class="col">
        <input type="number" name="limit" min="1" max="100" value="<?php se($limit); ?>">
    </div>

    <div class="col">
        <button class="btn btn-primary">Filter</button>
    </div>
</form>

<form method="POST">
    <button name="clear_all" class="btn btn-danger mb-3"
        onclick="return confirm('Remove all favorites?');">
        Remove All Favorites
    </button>
</form>

<?php if (empty($results)): ?>
    <p>No results available</p>
<?php else: ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Population</th>
            <th>Country</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($results as $r): ?>
        <tr>
            <td>
                <a href="<?php echo get_url("admin/view_city.php"); ?>?id=<?php se($r,"id"); ?>">
                    <?php se($r,"name"); ?>
                </a>
            </td>
            <td><?php se($r,"population"); ?></td>
            <td><?php se($r,"country_code"); ?></td>
            <td>
                <a class="btn btn-sm btn-danger"
                   href="<?php echo get_url("admin/toggle_favorite_city.php"); ?>?id=<?php se($r,"id"); ?>">
                    Remove
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

</div>