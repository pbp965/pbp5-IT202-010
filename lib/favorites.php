<?php

function toggle_favorite($table, $user_id, $entity_id, $column)
{
    $db = getDB();

    $check = $db->prepare("
        SELECT id 
        FROM $table 
        WHERE user_id = :user_id AND $column = :entity_id
    ");

    $check->execute([
        ":user_id" => $user_id,
        ":entity_id" => $entity_id
    ]);

    $existing = $check->fetch();

    if ($existing) {
        $stmt = $db->prepare("
            DELETE FROM $table 
            WHERE user_id = :user_id AND $column = :entity_id
        ");
    } else {
        $stmt = $db->prepare("
            INSERT INTO $table (user_id, $column)
            VALUES (:user_id, :entity_id)
        ");
    }

    $stmt->execute([
        ":user_id" => $user_id,
        ":entity_id" => $entity_id
    ]);

    return !$existing; // true = added, false = removed
}