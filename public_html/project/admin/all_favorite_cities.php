<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("Admin only", "danger");
    die(header("Location: " . get_url("login.php")));
}

$db = getDB();

$name = trim(se($_GET, "name", "", false));
$username = trim(se($_GET, "username", "", false));
$limit = intval(se($_GET, "limit", 10, false));
$sort = se($_GET, "sort", "name", false);
$order = se($_GET, "order", "asc", false);

/* ===== VALIDATION ===== */
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$allowed_sort = ["name", "population"];
if (!in_array($sort, $allowed_sort)) {
    $sort = "name";
}

$order = strtolower($order) === "desc" ? "DESC" : "ASC";

/* ===== QUERY ===== */
$query = "SELECT 
            fc.user_id,
            fc.city_id,
            c.name,
            c.population,
            c.country_code,
            u.email,
            COUNT(fc2.user_id) as total_users
          FROM favorite_cities fc
          JOIN cities c ON fc.city_id = c.id
          JOIN Users u ON fc.user_id = u.id
          LEFT JOIN favorite_cities fc2 ON fc2.city_id = c.id
          WHERE 1=1";

$params = [];

/* ===== FILTERS ===== */
if ($name) {
    $query .= " AND c.name LIKE :name";
    $params[":name"] = "%$name%";
}

if ($username) {
    $query .= " AND u.email LIKE :username";
    $params[":username"] = "%$username%";
}

/* ===== GROUP / SORT / LIMIT ===== */
$query .= " GROUP BY fc.user_id, fc.city_id, c.name, c.population, c.country_code, u.email";
$query .= " ORDER BY $sort $order LIMIT :limit";

$stmt = $db->prepare($query);

/* ===== BIND ===== */
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);

$results = [];

try {
    $stmt->execute();
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching favorite cities: " . var_export($e, true));
    flash("Error loading favorite cities", "danger");
}
?>

<div class="container-fluid">
    <h3>
        All Favorite Cities 
        (<?php echo count($results); ?> shown)
    </h3>

    <form method="GET" class="row mb-3" onsubmit="return validateFilter()">

        <div class="col">
            <input type="text" name="name" placeholder="City Name" value="<?php se($name); ?>">
        </div>

        <div class="col">
            <input type="text" name="username" placeholder="User Email" value="<?php se($username); ?>">
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
            <input type="submit" value="Filter">
        </div>
    </form>

    <?php if (count($results) == 0): ?>
        <p>No results available</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>City</th>
                    <th>Country Code</th>
                    <th>Population</th>
                    <th>User</th>
                    <th>Total Users</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php se($row, "name"); ?></td>
                        <td><?php se($row, "country_code", "N/A"); ?></td>
                        <td><?php se($row, "population", "0"); ?></td>
                        <td><?php se($row, "email"); ?></td>
                        <td><?php se($row, "total_users"); ?></td>

                        <td>
                            <a href="<?php echo get_url("admin/view_city.php"); ?>?id=<?php se($row, "city_id"); ?>">
                                View
                            </a> |
                            <a href="<?php echo get_url("toggle_favorite_city.php"); ?>?id=<?php se($row, "city_id"); ?>">
                                Remove
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function validateFilter() {
    let limit = document.querySelector("[name='limit']").value;
    if (limit < 1 || limit > 100) {
        alert("Limit must be between 1 and 100");
        return false;
    }
    return true;
}
</script>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>