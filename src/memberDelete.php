<?php

function memberDelete($db, $id_person){
    $stmt = $db->prepare("UPDATE person SET is_deleted = true WHERE id_person = :id_person");
    $stmt->bindValue(":id_person", $id_person);
    $stmt->execute();
    return True;
}



