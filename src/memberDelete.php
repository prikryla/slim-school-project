<?php

function memberDelete($db, $id_person){
    $stmt = $db->prepare("DELETE FROM person WHERE id_person = :id_person");
    $stmt->bindValue(":id_person", $id_person);
    $stmt->execute();
    return True;
}



