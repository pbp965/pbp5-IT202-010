<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$id = intval(se($_GET, "id", -1, false));
$db = getDB();

if ($id < 0) {
    flash("Invalid id passed", "danger");
    die(header("Location: " . get_url("admin/list_countries.php")));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // whitelist editable fields (IMPORTANT)
    $allowed = ["name", "code", "currency"];

    $country = [];

    foreach ($_POST as $k => $v) {
        if (in_array($k, $allowed)) {
            $country[$k] = $v;
        }
    }

    if (empty($country["name"]) || empty($country["code"])) {
        flash("Name and code are required", "danger");
    } else {

        $query = "UPDATE countries SET ";
        $params = [];

        foreach ($country as $k => $v) {
            if (!empty($params)) {
                $query .= ", ";
            }

            $query .= "`$k` = :$k";
            $params[":$k"] = $v;

            // normalize
            if ($k === "code" || $k === "currency") {
                $params[":$k"] = strtoupper($v);
            }
        }

        $query .= " WHERE id = :id";
        $params[":id"] = $id;

        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Country updated successfully", "success");
        } catch (PDOException $e) {
            error_log("Update error: " . var_export($e, true));
            flash("Error updating country", "danger");
        }
    }
}

$country = [];

$query = "SELECT name, code, currency 
          FROM countries 
          WHERE id = :id";

try {
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetch();

    if ($result) {
        $country = $result;
    } else {
        flash("Country not found", "danger");
        die(header("Location: " . get_url("admin/list_countries.php")));
    }
} catch (PDOException $e) {
    error_log("Fetch error: " . var_export($e, true));
    flash("Error fetching country", "danger");
}
?>

<div class="container-fluid">
    <h3>Edit Country</h3>

    <form method="POST" onsubmit="return validateForm()">

        <div class="mb-3">
            <label>Country Name</label>
            <input type="text" name="name" required value="<?php se($country, "name"); ?>">
        </div>

        <div class="mb-3">
            <label>Country Code</label>
            <input type="text" name="code" maxlength="5" required value="<?php se($country, "code"); ?>">
        </div>

        <div class="mb-3">
            <label>Currency</label>
            <input type="text" name="currency" maxlength="5" value="<?php se($country, "currency"); ?>">
        </div>

        <input type="submit" value="Update Country" class="btn btn-primary">
    </form>

    <br>

</div>

<script>
function validateForm() {
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