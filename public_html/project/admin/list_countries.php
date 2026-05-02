<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$db = getDB();

$name = trim(se($_GET, "name", "", false));
$code = trim(se($_GET, "code", "", false));
$limit = intval(se($_GET, "limit", 10, false));
$sort = se($_GET, "sort", "created", false);
$order = se($_GET, "order", "desc", false);

if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$allowed_sort = ["name", "code", "created"];
if (!in_array($sort, $allowed_sort)) {
    $sort = "created";
}

$order = strtolower($order) === "asc" ? "ASC" : "DESC";

$query = "SELECT id, name, code, currency, is_api 
          FROM countries 
          WHERE 1=1 AND is_deleted = 0";

$params = [];

if ($name) {
    $query .= " AND name LIKE :name";
    $params[":name"] = "%$name%";
}

if ($code) {
    $query .= " AND code = :code";
    $params[":code"] = strtoupper($code);
}

$query .= " ORDER BY $sort $order LIMIT :limit";

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
    error_log("Error fetching countries: " . var_export($e, true));
    flash("Error loading countries", "danger");
}
?>

<div class="container-fluid">
    <h3>Country List</h3>

    <form method="GET" class="row mb-3" onsubmit="return validateFilter()">

        <div class="col">
            <input type="text" name="name" placeholder="Country Name" value="<?php se($name); ?>">
        </div>

        <div class="col">
            <input type="text" name="code" placeholder="Code" value="<?php se($code); ?>">
        </div>

        <div class="col">
            <select name="sort">
                <option value="created">Created</option>
                <option value="name">Name</option>
                <option value="code">Code</option>
            </select>
        </div>

        <div class="col">
            <select name="order">
                <option value="desc">DESC</option>
                <option value="asc">ASC</option>
            </select>
        </div>

        <div class="col">
            <input type="number" name="limit" min="1" max="100" value="<?php se($limit); ?>">
        </div>

        <div class="col">
            <input type="submit" value="Filter" class="btn btn-primary">
        </div>
    </form>

    <?php if (count($results) == 0): ?>
        <p>No results available</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Currency</th>
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php se($row, "id"); ?></td>
                        <td><?php se($row, "name"); ?></td>
                        <td><?php se($row, "code"); ?></td>
                        <td><?php se($row, "currency", "N/A"); ?></td>
                        <td><?php echo $row["is_api"] ? "API" : "Manual"; ?></td>

                        <td>
                            <a href="<?php echo get_url("admin/view_country.php"); ?>?id=<?php se($row, "id"); ?>">View</a> |
                            <a href="<?php echo get_url("admin/edit_country.php"); ?>?id=<?php se($row, "id"); ?>">Edit</a> |
                            <a href="<?php echo get_url("admin/delete_country.php"); ?>?id=<?php se($row, "id"); ?>"
                               onclick="return confirm('Delete this country?');">
                               Delete
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