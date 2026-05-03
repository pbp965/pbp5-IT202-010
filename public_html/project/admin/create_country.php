<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location:" . get_url("landing.php")));
}

require_once(__DIR__ . "/../../../lib/cities_api.php");

$countries = [];

if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $namePrefix = trim(se($_POST, "namePrefix", "", false));

    if ($action === "fetch") {
        if ($namePrefix) {
            $result = search_countries($namePrefix);

            error_log("API Data: " . var_export($result, true));

            if ($result) {
                $countries = $result;
            } else {
                flash("No countries found", "warning");
            }
        } else {
            flash("You must provide a country name", "warning");
        }
    }

    else if ($action === "create") {

        $allowed = ["name", "code", "currency"];

        foreach ($_POST as $k => $v) {
            if (!in_array($k, $allowed)) {
                unset($_POST[$k]);
            }
        }

        $_POST["is_api"] = 0;
        $countries = [$_POST];

        error_log("Manual country: " . var_export($countries, true));
    }

    // ===== INSERT =====
    if (count($countries) > 0) {
        $db = getDB();

        $columns = [];
        $params = [];

        foreach ($countries[0] as $k => $v) {
            $columns[] = "`$k`";
            $params[":$k"] = $v;
        }

        $query = "INSERT INTO countries (" . join(",", $columns) . ") 
                  VALUES (" . join(",", array_keys($params)) . ")";

        foreach ($countries as $country) {

            foreach ($country as $k => $v) {
                $params[":$k"] = $v;

                // normalize
                if ($k === "code" || $k === "currency") {
                    $params[":$k"] = strtoupper($v);
                }
            }

            try {
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                flash("Inserted country ID: " . $db->lastInsertId(), "success");
            } catch (PDOException $e) {

                if (str_contains($e->getMessage(), "Duplicate")) {
                    flash("Duplicate country skipped", "warning");
                } else {
                    error_log("Insert error: " . var_export($e, true));
                    flash("Error inserting country", "danger");
                }
            }
        }
    } else {
        flash("No data to insert", "warning");
    }
}
?>

<div class="container-fluid">
    <h3>Create or Fetch Countries</h3>

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
                <label>Country Name</label>
                <input type="text" name="namePrefix" required>
            </div>
            <input type="hidden" name="action" value="fetch">
            <input type="submit" value="Fetch Countries" class="btn btn-primary">
        </form>
    </div>

    <div id="create" style="display:none;" class="tab-target">
        <form method="POST" onsubmit="return validateCreate()">

            <div class="mb-3">
                <label>Country Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="mb-3">
                <label>Country Code</label>
                <input type="text" name="code" maxlength="5" required>
            </div>

            <div class="mb-3">
                <label>Currency</label>
                <input type="text" name="currency" maxlength="5">
            </div>

            <input type="hidden" name="action" value="create">
            <input type="submit" value="Create Country" class="btn btn-primary">
        </form>
    </div>

    <?php if (!empty($countries)): ?>
        <hr>
        <h4>Preview</h4>
        <pre><?php echo htmlspecialchars(print_r($countries, true)); ?></pre>
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
    let name = document.querySelector("[name='namePrefix']").value.trim();
    if (!name) {
        alert("Country name is required");
        return false;
    }
    return true;
}

function validateCreate() {
    let name = document.querySelector("[name='name']").value.trim();
    let code = document.querySelector("[name='code']").value.trim();

    if (!name || !code) {
        alert("Name and code are required");
        return false;
    }
    return true;
}
</script>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>