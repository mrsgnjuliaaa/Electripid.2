<?php

session_start();
require_once '../../connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents("php://input"), true);

$answer1 = strtolower(trim($data['answer1'] ?? ''));
$answer2 = strtolower(trim($data['answer2'] ?? ''));
$answer3 = strtolower(trim($data['answer3'] ?? ''));
$answer4 = strtolower(trim($data['answer4'] ?? ''));
$answer5 = strtolower(trim($data['answer5'] ?? ''));
if (
    empty($answer1) ||
    empty($answer2) ||
    empty($answer3) ||
    empty($answer4) ||
    empty($answer5)
) {
    echo json_encode([
        'success' => false,
        'error' => 'Please answer all security questions.'
    ]);
    exit;
}

/* HASH ANSWERS */

$hashed1 = password_hash($answer1, PASSWORD_DEFAULT);
$hashed2 = password_hash($answer2, PASSWORD_DEFAULT);
$hashed3 = password_hash($answer3, PASSWORD_DEFAULT);
$hashed4 = password_hash($answer4, PASSWORD_DEFAULT);
$hashed5 = password_hash($answer5, PASSWORD_DEFAULT);

/* SAVE */

$query = "
UPDATE USER SET

security_answer_1 = ?,
security_answer_2 = ?,
security_answer_3 = ?,
security_answer_4 = ?,
security_answer_5 = ?,

security_setup_completed = 1

WHERE user_id = ?
";

$stmt = $conn->prepare($query);

$stmt->bind_param(
    "sssssi",
    $hashed1,
    $hashed2,
    $hashed3,
    $hashed4,
    $hashed5,
    $user_id
);

if ($stmt->execute()) {

    echo json_encode([
        'success' => true
    ]);

} else {

    echo json_encode([
        'success' => false,
        'error' => 'Failed to save security questions.'
    ]);
}
?>