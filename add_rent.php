<?php
require_once('db.php');
header('Access-Control-Allow-Origin: *');  // Разрешает запросы с любых источников (можно заменить на конкретный домен, например, http://127.0.0.1:5500)
header('Access-Control-Allow-Methods: POST');  // Разрешенные методы
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if (empty($_COOKIE['userid'])) {
    // Куки не существует или пустое значение
    echo json_encode(['status' => 'error', 'message' => 'Пользователь не авторизован.']);
    exit;
} else {
    // Куки существует и содержит значение
    $user_id = $_COOKIE['userid'];
}

$response = ['status' => 'error', 'message' => 'Не удалось арендовать фильм.'];
$data = json_decode(file_get_contents("php://input"));

$movie_id = $data->movie_id;
$rental_date = date('Y-m-d H:i:s'); // Текущее время в формате 'ГГГГ-ММ-ДД ЧЧ:ММ:СС'
$expiry_date = date('Y-m-d H:i:s', strtotime($rental_date . ' +48 hours'));

$stmt = $conn->prepare("INSERT INTO rentals (user_id, movie_id, rental_date, expiry_date) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $user_id, $movie_id, $rental_date, $expiry_date);
if($stmt->execute()){
    $response = ['status' => 'error', 'message' => 'Фильм успешно арендован до.' . $expiry_date];
}

echo json_encode($response);
$stmt->close();
$conn->close();
?>