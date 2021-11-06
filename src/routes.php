<?php

include "memberDelete.php";
include "membersShow.php";

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response, $args) {
    // Render index view
    return $this->view->render($response, 'index.latte');
})->setName('index');

$app->get('/members', function (Request $request, Response $response, $args) {
    $stmt = $this->db->query('SELECT * FROM person ORDER BY first_name');
    $tplVars['members'] = $stmt->fetchAll();
    return $this->view->render($response, 'members.latte', $tplVars);
});

$app->post('/search', function (Request $request, Response $response, $args) {
    $input = $request->getParsedBody();
    if (!empty($input)) {
        $stmt = $this->db->prepare('SELECT * FROM person WHERE first_name = :fname');
        $stmt->bindParam("fname", $input['person_name']);
        $stmt->execute();
        $tplVars['osoby'] = $stmt->fetchAll();
        return $this->view->render($response, 'members.latte', $tplVars);
    }
})->setName('search');

$app->get('/member/{id}/profile', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    if (empty($id)) {
        exit('ID PERSON IS MISSING!');
    } else {
        $query = $this->db->prepare('SELECT * FROM person WHERE id_person = :id_person');
        $query->bindValue(':id_person', $id);
        $query->execute();
        $tplVars['person'] = $query->fetch();
        if (empty($tplVars['person'])) {
            exit("MEMBER NOT FOUND!");
        } else {
            return $this->view->render($response, 'memberProfile.latte', $tplVars);
        }
    }
})->setName('memberProfile');

$app->get('/members/new', function (Request $request, Response $response, $args) {
    $tplVars['formData'] = [
        'first_name' => '',
        'last_name' => '',
        'nickname' => '',
        'id_location' => null,
        'gender' => '',
        'height' => '',
        'birth_day' => ''
    ];
    return $this->view->render($response, 'memberAdd.latte', $tplVars);
})->setName('newMember');


/* Obsluha formu pre novu osoby*/
$app->post('/members/new', function (Request $request, Response $response, $args) {
    $formData = $request->getParsedBody();
    if (empty($formData['first_name']) || empty($formData['last_name']) || empty($formData['nickname'])) {
        $tplVars['message'] = "PLEASE FILL REQUIRED FIELDS!";
    } else {
        try {
            $stmt = $this->db->prepare("INSERT INTO person 
            (nickname, first_name, last_name, id_location, birth_day, height, gender)
            VALUES (:nickname, :first_name, :last_name, :id_location, :birth_day, :height, :gender)");
            $stmt->bindValue(":nickname", $formData['nickname']);
            $stmt->bindValue(":last_name", $formData['last_name']);
            $stmt->bindValue(":first_name", $formData['first_name']);
            $stmt->bindValue(":id_location", empty($formData['id_location']) ? null : $formData['id_location']);
            $stmt->bindValue(":gender", empty($formData['gender']) ? null : $formData['gender']);
            $stmt->bindValue(":height", empty($formData['height']) ? null : $formData['height']);
            $stmt->bindValue(":birth_day", empty($formData['birth_day']) ? null : $formData['birth_day']);
            $stmt->execute();
            $tplVars['message'] = "MEMBER WAS SUCCESSFULLY ADDED!";
        } catch (PDOException $e) {
            $tplVars['message'] = "Error occured " . $e->getMessage();
        }
    }
    $tplVars['formData'] = $formData;
    return $this->view->render($response, 'memberAdd.latte', $tplVars);
});

$app->get('/member/{id}/update', function (Request $request, Response $response, $args) {
    $id = $args['id'];
//    $params = $request->getQueryParams(); //ziskaj vsetky parametre z url
    if (empty($id)) {
        exit('ID PERSON IS MISSING!');
    } else {
        $stmt = $this->db->prepare("SELECT * FROM person WHERE id_person = :id_person");
        $stmt->bindValue(':id_person', $id);
        $stmt->execute();
        $tplVars['formData'] = $stmt->fetch();
        if (empty($tplVars['formData'])) {
            exit("MEMBER NOT FOUND!");
        } else {
            return $this->view->render($response, 'memberUpdate.latte', $tplVars);
        }
    }
})->setName('member_update');


/* obsluha formu pre update osoby */
$app->post('/member/{id}/update', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $formData = $request->getParsedBody();

    if (empty($formData['first_name']) || empty($formData['last_name']) || empty($formData['nickname']) || empty($id)) {
        $tplVars['message'] = "PLEASE FILL REQUIRED FIELDS";
    } else {
        try {
            $stmt = $this->db->prepare("UPDATE person SET 
                                first_name = :fn,
                                last_name = :ln,
                                nickname = :nn,
                                birth_day = :bd,
                                gender = :gn,
                                height = :hg
                            WHERE id_person = :idp");
            $stmt->bindValue(":fn", $formData['first_name']);
            $stmt->bindValue(":ln", $formData['last_name']);
            $stmt->bindValue(":nn", $formData['nickname']);
            $stmt->bindValue(":gn", empty($formData['gender']) ? null : $formData['gender']);
            $stmt->bindValue(":hg", empty($formData['height']) ? null : $formData['height']);
            $stmt->bindValue(":bd", empty($formData['birth_day']) ? null : $formData['birth_day']);
            $stmt->bindValue(":idp", $id);
            $stmt->execute();
            $tplVars['message'] = "MEMBER UPDATED!";
        } catch (PDOexception $e) {
            $tplVars['message'] = 'ERROR OCCURED ' . $e->getMessage();
        }
    }

    $tplVars['formData'] = $formData;
    return $this->view->render($response, 'memberUpdate.latte', $tplVars);
});

$app->get('/member/{id}/delete', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    if (!empty($id)) {
        try {
            memberDelete($this->db, $id);
            $tplVars['message'] = ('MEMBER WAS DELETED!');
        } catch (PDOException $exception) {
            $tplVars['message'] = ('ERROR OCCURED' . $exception->getMessage());
        }
        $tplVars['members'] = membersShow($this->db);
        return $this->view->render($response, 'members.latte', $tplVars);
    } else {
        exit("ID PERSON IS MISSING!");
    }
})->setName('memberDelete');



