<?php
session_start();
include('../../../php/db.php');

header('Content-Type: application/json');

$sql = "SELECT * FROM devices";
$stmt = $conn->prepare($sql);
$stmt->execute();
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($devices);





