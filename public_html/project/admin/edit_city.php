<?php
require(__DIR__ . "/../../../partials/nav.php");
require_once(__DIR__ . "/../../../lib/db_helpers.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$id = intval(se($_GET, "id", -1, false));
$db = getDB();

if ($id < 0) {
    flash("Invalid ID", "danger");
    die(header("Location: " . get_url("admin/list_cities.php")));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // whitelist editable fields (IMPORTANT)
    $allowed = ["name", "latitude", "longitude", "population", "country_code"];

    $city = [];

    foreach ($_POST as $k => $v) {
        if (in_array($k, $allowed)) {
            $city[$k] = $v;
        }
    }

    if (empty($city["name"])) {
        flash("City name is required", "danger");
    } else {
    $city["id"] = $id;

    try {
        update("cities", $city, ["id"]);
        flash("City updated successfully", "success");
    } catch (PDOException $e) {
        error_log("Update error: " . var_export($e, true));
        flash("Error updating city", "danger");
    }
}
}

$city = [];

$query = "SELECT name, latitude, longitude, population, country_code 
          FROM cities 
          WHERE id = :id";

try {
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetch();

    if ($result) {
        $city = $result;
    } else {
        flash("City not found", "danger");
        die(header("Location: " . get_url("admin/list_cities.php")));
    }
} catch (PDOException $e) {
    error_log("Fetch error: " . var_export($e, true));
    flash("Error fetching city", "danger");
}
?>

<div class="container-fluid">
    <h3>Edit City</h3>

    <form method="POST" onsubmit="return validateForm()">

        <div class="mb-3">
            <label>City Name</label>
            <input type="text" name="name" required value="<?php se($city, "name"); ?>">
        </div>

        <div class="mb-3">
            <label>Latitude</label>
            <input type="number" step="0.000001" name="latitude" value="<?php se($city, "latitude"); ?>">
        </div>

        <div class="mb-3">
            <label>Longitude</label>
            <input type="number" step="0.000001" name="longitude" value="<?php se($city, "longitude"); ?>">
        </div>

        <div class="mb-3">
            <label>Population</label>
            <input type="number" name="population" value="<?php se($city, "population"); ?>">
        </div>

        <div class="mb-3">
            <label>Country Code</label>
            <input type="text" name="country_code" maxlength="5" value="<?php se($city, "country_code"); ?>">
        </div>

        <input type="submit" value="Update City" class="btn btn-primary">
    </form>

    <br>
</div>

<script>
function validateForm() {
    let name = document.querySelector("[name='name']").value.trim();
    if (!name) {
        alert("City name is required");
        return false;
    }
    return true;
}
</script>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>