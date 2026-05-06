<?php
require(__DIR__ . "/../../../partials/nav.php");


if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$id = se($_GET, "id", -1, false);

if ($id < 1) {
    flash("Invalid or missing country ID", "danger");
    die(header("Location: " . get_url("admin/list_countries.php")));
}

$db = getDB();
$country = [];

try {
    $stmt = $db->prepare("SELECT id, name, code, currency, is_api, created 
                          FROM countries 
                          WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $country = $stmt->fetch();

    if (!$country) {
        flash("Country not found", "warning");
        die(header("Location: " . get_url("admin/list_countries.php")));
    }
} catch (PDOException $e) {
    error_log("Error fetching country: " . var_export($e, true));
    flash("Error loading country", "danger");
    die(header("Location: " . get_url("admin/list_countries.php")));
}
?>

<div class="container mt-4">
    <div class="card shadow-lg p-4">
        <h3 class="mb-3 text-primary">Country Details</h3>

        <!-- ✅ Entity Output -->
        <div class="row mb-2">
            <div class="col-4 fw-bold">ID:</div>
            <div class="col-8"><?php se($country, "id"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Name:</div>
            <div class="col-8"><?php se($country, "name"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Code:</div>
            <div class="col-8"><?php se($country, "code"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Currency:</div>
            <div class="col-8"><?php se($country, "currency", "N/A"); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-4 fw-bold">Source:</div>
            <div class="col-8">
                <?php echo $country["is_api"] ? "<span class='badge bg-success'>API</span>" : "<span class='badge bg-secondary'>Manual</span>"; ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-4 fw-bold">Created:</div>
            <div class="col-8"><?php se($country, "created"); ?></div>
        </div>

        <!-- Navigation -->
        <div class="mt-3">
            <a href="<?php echo get_url("admin/list_countries.php"); ?>" class="btn btn-secondary">Back</a>
            <a href="<?php echo get_url("admin/edit_country.php"); ?>?id=<?php se($country, "id"); ?>" class="btn btn-primary">Edit</a>
            <a href="<?php echo get_url("admin/toggle_favorite_country.php"); ?>?id=<?php se($country, "id"); ?>"class="btn btn-warning">Toggle Favorite</a>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>