<?php
require_once('db.php');
header('Access-Control-Allow-Origin: *');  // Разрешает запросы с любых источников (можно заменить на конкретный домен, например, http://127.0.0.1:5500)
header('Access-Control-Allow-Methods: POST');  // Разрешенные методы
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');


$response = ['status' => 'error', 'message' => 'Неверный логин или пароль!'];
$data = json_decode(file_get_contents("php://input"));

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$login = $data->login;
$password = $data->password; 

// Проверяем, что это email или username
if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
    // Если введен email
    $field = 'email';
} else {
    // Если введен username
    $field = 'username';
}

if (empty($login) || empty($password)) {
    $response = ['status' => 'error', 'message' => 'Пустые данные при входе в аккаунт.'];
} else{
    // Проверка пользователя в базе данных (простейший пример)
    $stmt = $conn->prepare("SELECT iduser, username, password_hash FROM users WHERE $field = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        // Если пользователь найден, проверяем пароль
        $stmt->bind_result($iduser, $login, $stored_hash);
        $stmt->fetch();

        // Проверка пароля
        if (password_verify($password, $stored_hash)) {
            // Успешный вход, можно установить сессию
            setcookie('userid', $iduser, time() + 60*60*24*7, '/');
            $response = ['status' => 'success', 'message' => 'Добро пожаловать.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Нет такого пользователя'];
    }
}
echo json_encode($response);

$stmt->close();
$conn->close();

//header('Location: /OSPO_project/');
?>