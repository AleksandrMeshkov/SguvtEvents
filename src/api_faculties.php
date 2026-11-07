<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$instituteId = filter_input(INPUT_GET, 'institute_id', FILTER_VALIDATE_INT);

if ($instituteId) {
    $faculties = $facultyModel->getByInstituteId($instituteId);
} else {
    $faculties = $facultyModel->getAll();
}
echo json_encode($faculties);
