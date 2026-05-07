<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("Admin only", "danger");
    die(header("Location: " . get_url("login.php")));
}

$db = getDB();

/* ===== INPUTS ===== */
$name = trim(se($_GET, "name", "", false));
$code = trim(se($_GET, "code", "", false));
$limit = intval(se($_GET, "limit", 10, false));
$sort = se($_GET, "sort", "name", false);
$order = se($_GET, "order", "asc", false);

/* ===== VALIDATION ===== */
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$allowed_sort = ["name", "code"];
if (!in_array($sort, $allowed_sort)) {
    $sort = "name";
}

$order = strtolower($order) === "desc" ? "DESC" : "ASC";

/* ===== QUERY ===== */
$query = "SELECT c.id, c.name, c.code, c.currency
          FROM countries c
          LEFT JOIN favorite_countries fc ON c.id = fc.country_id
          WHERE fc.id IS NULL";

$params = [];

/* ===== FILTERS ===== */
if ($name) {
    $query .= " AND c.name LIKE :name";
    $params[":name"] = "%$name%";
}

if ($code) {
    $query .= " AND c.code = :code";
    $params[":code"] = strtoupper($code);
}

/* ===== TOTAL COUNT ===== */
$countQuery = str_replace("SELECT c.id, c.name, c.code, c.currency", "SELECT COUNT(*)", $query);
$countStmt = $db->prepare($countQuery);

foreach ($params as $k => $v) {
    $countStmt->bindValue($k, $v);
}
$countStmt->execute();
$total = $countStmt->fetchColumn();

/* ===== FINAL QUERY ===== */
$query .= " ORDER BY $sort $order LIMIT :limit";
$stmt = $db->prepare($query);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);

$results = [];

try {
    $stmt->execute();
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e);
    flash("Error loading countries", "danger");
}
?>

<div class="container-fluid">
    <h3>Unassociated Countries</h3>
    <p>Total Results: <?php echo count($results); ?> / <?php echo $total; ?></p>

    <form method="GET" class="row mb-3" onsubmit="return validateFilter()">

        <div class="col">
            <input type="text" name="name" placeholder="Country Name" value="<?php se($name); ?>">
        </div>

        <div class="col">
            <input type="text" name="code" placeholder="Code" value="<?php se($code); ?>">
        </div>

        <div class="col">
            <select name="sort">
                <option value="name">Name</option>
                <option value="code">Code</option>
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
            <input type="submit" value="Filter" class="btn btn-primary">
        </div>
    </form>

    <?php if (count($results) == 0): ?>
        <p>No results available</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Currency</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php se($row, "name"); ?></td>
                        <td><?php se($row, "code"); ?></td>
                        <td><?php se($row, "currency", "N/A"); ?></td>
                        <td>
                            <a href="<?php echo get_url("admin/view_country.php"); ?>?id=<?php se($row, "id"); ?>">
                                View
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