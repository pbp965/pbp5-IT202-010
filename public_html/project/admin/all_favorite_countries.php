<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("Admin only", "danger");
    die(header("Location: " . get_url("login.php")));
}

$db = getDB();

$limit = intval(se($_GET, "limit", 10, false));
if ($limit < 1 || $limit > 100) $limit = 10;

$username = trim(se($_GET, "username", "", false));

$query = "SELECT 
            fc.user_id,
            fc.country_id,
            c.name,
            c.code,
            u.email,
            (SELECT COUNT(*) FROM favorite_countries f2 WHERE f2.country_id = c.id) as total_users
          FROM favorite_countries fc
          JOIN countries c ON fc.country_id = c.id
          JOIN Users u ON fc.user_id = u.id
          WHERE 1=1";

$params = [];

if ($username) {
    $query .= " AND u.email LIKE :uname";
    $params[":uname"] = "%$username%";
}

$query .= " LIMIT :limit";

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
    flash("Error loading data", "danger");
}
?>

<div class="container">
    <h3>All Favorite Countries (<?php echo count($results); ?>)</h3>

    <form method="GET">
        <input type="text" name="username" placeholder="Filter by email">
        <input type="number" name="limit" min="1" max="100" value="10">
        <input type="submit" value="Filter">
    </form>

    <?php if (!$results): ?>
        <p>No results available</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>User</th>
                    <th>Total Users</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <td><?php se($r, "name"); ?> (<?php se($r, "code"); ?>)</td>

                        <td>
                            <?php se($r, "email"); ?>
                        </td>

                        <td><?php se($r, "total_users"); ?></td>

                        <td>
                            <a href="<?php echo get_url("admin/view_country.php"); ?>?id=<?php echo $r["country_id"]; ?>">View</a> |
                            <a href="<?php echo get_url("toggle_favorite_country.php"); ?>?id=<?php echo $r["country_id"]; ?>">
                                Remove
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>