<?php
require_once('db.php');
header('Access-Control-Allow-Origin: *');  // Разрешает запросы с любых источников (можно заменить на конкретный домен, например, http://127.0.0.1:5500)
header('Access-Control-Allow-Methods: GET');  // Разрешенные методы
header('Access-Control-Allow-Headers: Content-Type');

$stmt = $conn->prepare(
    "SELECT idmovie, title, description, genre, year, director, actors, price, trailer_url, rental_date, expiry_date
    FROM movies join rentals on rentals.movie_id = movies.idmovie");
if ($stmt->execute()){
    $result = $stmt->get_result(); // Получаем результат как mysqli_result
    $rentals = $result->fetch_all(MYSQLI_ASSOC); // Получаем все строки как ассоциативный массив
    echo json_encode(['status' => 'success', 'message' => $rentals]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при получении избранных']);
}



$stmt->close();
$conn->close();
?>