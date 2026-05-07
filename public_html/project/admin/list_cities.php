<?php
require(__DIR__ . "/../../../partials/nav.php");
if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: login.php"));
}
$db = getDB();

if (isset($_POST["action"]) && $_POST["action"] === "delete") {
    $id = se($_POST, "id", -1, false);

    if ($id > 0) {
        $query = "DELETE FROM cities WHERE id = :id";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute([":id" => $id]);
            flash("City deleted successfully", "success");
        } catch (PDOException $e) {
            error_log("Delete city error: " . var_export($e, true));
            flash("Failed to delete city", "danger");
        }
    } else {
        flash("Invalid city id", "warning");
    }
}

$name = trim(se($_GET, "name", "", false));
$country = trim(se($_GET, "country_code", "", false));
$sort = se($_GET, "sort", "created", false);
$order = se($_GET, "order", "desc", false);
$limit = intval(se($_GET, "limit", 10, false));

if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$allowed_sort = ["name", "population", "created"];
if (!in_array($sort, $allowed_sort)) {
    $sort = "created";
}

$order = strtolower($order) === "asc" ? "ASC" : "DESC";

$query = "SELECT id, name, latitude, longitude, population, country_code, is_api 
          FROM cities 
          WHERE is_deleted = 0";

$params = [];

if ($name) {
    $query .= " AND name LIKE :name";
    $params[":name"] = "%$name%";
}

if ($country) {
    $query .= " AND country_code = :country";
    $params[":country"] = $country;
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
    error_log("Error fetching cities: " . var_export($e, true));
    flash("Error loading cities", "danger");
}
?>

<div class="container-fluid">
    <h3>City List</h3>

    <form method="GET" class="row mb-3" onsubmit="return validateFilter()">
        <div class="col">
            <input type="text" name="name" placeholder="City Name" value="<?php se($name); ?>">
        </div>
        <div class="col">
            <input type="text" name="country_code" placeholder="Country Code" value="<?php se($country); ?>">
        </div>
        <div class="col">
            <select name="sort">
                <option value="created">Created</option>
                <option value="name">Name</option>
                <option value="population">Population</option>
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
                    <th>Population</th>
                    <th>Country</th>
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php se($row, "id"); ?></td>
                        <td><?php se($row, "name"); ?></td>
                        <td><?php se($row, "population", "N/A"); ?></td>
                        <td><?php se($row, "country_code", "N/A"); ?></td>
                        <td><?php echo $row["is_api"] ? "API" : "Manual"; ?></td>

                        <td>
                            <a href="<?php echo get_url("admin/toggle_favorite_city.php"); ?>?id=<?php se($row, "id"); ?>">Favorite</a> |
                            <?php if (has_role("Admin")) : ?>
                            <a href="<?php echo get_url("admin/edit_city.php"); ?>?id=<?php se($row, "id"); ?>">Edit</a> |
                            <?php endif; ?>
                            <a href="<?php echo get_url("admin/view_city.php"); ?>?id=<?php se($row, "id"); ?>">View</a>
                            <?php if (has_role("Admin")) : ?>
                            | <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this city?');">
                                <input type="hidden" name="id" value="<?php se($row, "id"); ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-link p-0 m-0 align-baseline text-danger">
                                    Delete
                                </button>
                            </form>
                            <?php endif; ?>
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