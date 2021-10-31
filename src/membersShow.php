<?php

function membersShow($db) {
$stmt = $db->query('SELECT * FROM person ORDER BY first_name');
return $stmt->fetchAll();
}

