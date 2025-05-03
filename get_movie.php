<?php
require_once('db.php');
header('Access-Control-Allow-Origin: *');  // Разрешает запросы с любых источников (можно заменить на конкретный домен, например, http://127.0.0.1:5500)
header('Access-Control-Allow-Methods: GET');  // Разрешенные методы
header('Access-Control-Allow-Headers: Content-Type');

if(empty($_COOKIE['userid'])){
    echo json_encode(['status' => 'error', 'message' => 'Пользователь не авторизован.']);
    exit;
} else {
    $user_id = $_COOKIE['userid'];
}

$stmt = $conn->prepare(
    "SELECT idmovie, title, description, genre, year, director, actors, price 
    FROM movies");
$stmt->execute();
$result = $stmt->get_result(); // Получаем результат как mysqli_result
$movies = $result->fetch_all(MYSQLI_ASSOC); // Получаем все строки как ассоциативный массив


$stmt = $conn->prepare(
    "SELECT movie_id FROM rentals where user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rentedIds = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'movie_id');

$stmt = $conn->prepare(
    "SELECT movie_id FROM favorites where user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favoriteIds = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'movie_id');


// Добавляем флаг rent
foreach ($movies as &$movie) {
    $movie['rent'] = in_array($movie['idmovie'], $rentedIds);
    $movie['favorite'] = in_array($movie['idmovie'], $favoriteIds);
}
unset($movie);

echo json_encode(['status' => 'success', 'message' => $movies]);

$stmt->close();
$conn->close();
?>