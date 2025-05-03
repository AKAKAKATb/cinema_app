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

$response = ['status' => 'error', 'message' => 'Произошла ошибка при удалении из избранного.'];
$data = json_decode(file_get_contents("php://input"));

$movie_id = $data->movie_id;

$stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? and movie_id = ?");
$stmt->bind_param("ii", $user_id, $movie_id);  // Привязка параметров
if ($stmt->execute()){
    $response = ['status' => 'success', 'message' => 'Фильм успешно удален из избранного.'];
} 

echo json_encode($response);
$stmt->close();
$conn->close();
?>