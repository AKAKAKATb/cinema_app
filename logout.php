<?php
header('Access-Control-Allow-Origin: *');  // Разрешает запросы с любых источников (можно заменить на конкретный домен, например, http://127.0.0.1:5500)
header('Access-Control-Allow-Methods: GET');  // Разрешенные методы
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');  // Указываем, что ответ в формате JSON

if (isset($_COOKIE['userid'])) {
    setcookie('userid', '', time() - 60*60*24*7, '/');
    $response = ['status' => 'success', 'message' => 'Вы вышли из аккаунта'];
} else {
    $response = ['status' => 'error', 'message' => 'Пользователь не был авторизован'];
}
// Отправляем JSON-ответ
echo json_encode($response);
?>