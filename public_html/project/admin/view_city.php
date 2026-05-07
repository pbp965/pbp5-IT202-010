<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: login.php"));
}

$id = se($_GET, "id", -1, false);

if ($id < 1) {
    flash("Invalid or missing city ID", "danger");
    die(header("Location: " . get_url("admin/list_cities.php")));
}

$db = getDB();
$city = [];

try {
    $stmt = $db->prepare("SELECT id, name, latitude, longitude, population, country_code, is_api, created 
                          FROM cities 
                          WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $city = $stmt->fetch();

    if (!$city) {
        flash("City not found", "warning");
        die(header("Location: " . get_url("admin/list_cities.php")));
    }
} catch (PDOException $e) {
    error_log("Error fetching city: " . var_export($e, true));
    flash("Error loading city", "danger");
    die(header("Location: " . get_url("admin/list_cities.php")));
}
?>

<div class="container mt-4">
    <div class="card shadow-lg p-4">
        <h3 class="mb-3 text-primary">City Details</h3>

        <div class="row mb-2">
            <div class="col-4 fw-bold">ID:</div>
            <div class="col-8"><?php se($city, "id"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Name:</div>
            <div class="col-8"><?php se($city, "name"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Latitude:</div>
            <div class="col-8"><?php se($city, "latitude", "N/A"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Longitude:</div>
            <div class="col-8"><?php se($city, "longitude", "N/A"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Population:</div>
            <div class="col-8"><?php se($city, "population", "N/A"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Country Code:</div>
            <div class="col-8"><?php se($city, "country_code", "N/A"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Source:</div>
            <div class="col-8">
                <?php echo $city["is_api"] ? "<span class='badge bg-success'>API</span>" : "<span class='badge bg-secondary'>Manual</span>"; ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-4 fw-bold">Created:</div>
            <div class="col-8"><?php se($city, "created"); ?></div>
        </div>

        <!-- Navigation -->
        <div class="mt-3">
            <a href="<?php echo get_url("admin/list_cities.php"); ?>" class="btn btn-secondary">Back</a>
            <a href="<?php echo get_url("admin/edit_city.php"); ?>?id=<?php se($city, "id"); ?>" class="btn btn-primary">Edit</a>
            <a href="<?php echo get_url("admin/toggle_favorite_city.php"); ?>?id=<?php se($city, "id"); ?>"class="btn btn-warning">Toggle Favorite</a>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>