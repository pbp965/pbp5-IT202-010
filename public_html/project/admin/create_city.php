<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

require_once(__DIR__ . "/../../../lib/cities_api.php");

$city = [];

if (isset($_POST["action"])) {
    $action = $_POST["action"];

    $namePrefix = trim(se($_POST, "namePrefix", "", false));
    $namePrefix = explode(" ", $namePrefix)[0];

    if ($action === "fetch") {
        if ($namePrefix) {
            $results = fetch_city($namePrefix);

            if (!empty($results)) {

                $city = $results[0];
                $city["is_api"] = 1;

                $db = getDB();

                // check duplicate using API id
                if (!empty($city["api_id"])) {
                    $check = $db->prepare("SELECT id FROM cities WHERE api_id = :api_id");
                    $check->execute([":api_id" => $city["api_id"]]);

                    if ($check->fetch()) {
                        flash("City already exists in database", "warning");
                    } else {

                        $allowed = ["name", "latitude", "longitude", "population", "country_code", "api_id", "is_api"];

                        $insertCity = array_intersect_key($city, array_flip($allowed));

                        $columns = [];
                        $params = [];

                        foreach ($insertCity as $k => $v) {
                            $columns[] = "`$k`";
                            $params[":$k"] = $v;
                        }

                        $query = "INSERT INTO cities (" . join(",", $columns) . ")
                              VALUES (" . join(",", array_keys($params)) . ")";

                        try {
                            require_once(__DIR__ . "/../../../lib/db_helpers.php");
                            insert("cities", $insertCity);
                            flash("Inserted Record", "success");
                        } catch (PDOException $e) {
                            error_log($e);
                            flash("Error saving city from API", "danger");
                        }
                    }
                }
            } else {
                flash("No cities found from API", "warning");
            }
        } else {
            flash("City name is required", "warning");
        }
    } else if ($action === "create") {

        $allowed = ["name", "latitude", "longitude", "population", "country_code"];

        foreach ($_POST as $k => $v) {
            if (!in_array($k, $allowed)) {
                unset($_POST[$k]);
            }
        }

        $city = $_POST;
        $city["is_api"] = 0;

        if (empty($city["name"])) {
            flash("City name is required", "danger");
            return;
        }

        try {
            require_once(__DIR__ . "/../../../lib/db_helpers.php");
            $result = insert("cities", $city);
            flash("City created successfully (ID: " . $result["lastInsertId"] . ")", "success");
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), "Duplicate")) {
                flash("This city already exists in the database", "warning");
            } else {
                error_log($e);
                flash("An error occurred while creating the city", "danger");
            }
        }
    }
}
?>

<div class="container-fluid">
    <h3>Create or Fetch City</h3>

    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('fetch')">Fetch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('create')">Create</a>
        </li>
    </ul>


    <div id="fetch" class="tab-target">
        <form method="POST" onsubmit="return validateFetch()">
            <div class="mb-3">
                <label for="namePrefix">City Name</label>
                <input type="text" name="namePrefix" id="namePrefix" required>
            </div>
            <input type="hidden" name="action" value="fetch">
            <input type="submit" value="Fetch from API" class="btn btn-primary">
        </form>
    </div>


    <div id="create" style="display:none;" class="tab-target">
        <form method="POST" onsubmit="return validateCreate()">
            <div class="mb-3">
                <label>City Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="mb-3">
                <label>Latitude</label>
                <input type="number" step="0.000001" name="latitude">
            </div>

            <div class="mb-3">
                <label>Longitude</label>
                <input type="number" step="0.000001" name="longitude">
            </div>

            <div class="mb-3">
                <label>Population</label>
                <input type="number" name="population">
            </div>

            <div class="mb-3">
                <label>Country Code</label>
                <input type="text" name="country_code" maxlength="5">
            </div>

            <input type="hidden" name="action" value="create">
            <input type="submit" value="Create City" class="btn btn-primary">
        </form>
    </div>


    <?php if (!empty($city)): ?>
        <hr>
        <h4>Preview</h4>
        <pre><?php echo htmlspecialchars(print_r($city, true)); ?></pre>
    <?php endif; ?>
</div>

<script>
    function switchTab(tab) {
        let sections = document.getElementsByClassName("tab-target");
        for (let s of sections) {
            s.style.display = (s.id === tab) ? "block" : "none";
        }
    }

    function validateFetch() {
        let name = document.getElementById("namePrefix").value.trim();
        if (!name) {
            alert("City name is required");
            return false;
        }
        return true;
    }

    function validateCreate() {
        let name = document.querySelector("[name='name']").value.trim();
        if (!name) {
            alert("City name is required");
            return false;
        }
        return true;
    }
</script>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>