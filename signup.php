<?php
require_once('db.php');
header('Access-Control-Allow-Origin: *');  // Разрешает запросы с любых источников (можно заменить на конкретный домен, например, http://127.0.0.1:5500)
header('Access-Control-Allow-Methods: POST');  // Разрешенные методы
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');  // Указываем, что ответ в формате JSON

$data = json_decode(file_get_contents("php://input"));

$username = $data->username;
$email = $data->email;
$password = $data->password;
$birth_date = $data->birth_date;

/*
$username = $_POST["username"];
$email = $_POST["email"];
$password = $_POST["password"];
$birth_date = $_POST["birth_date"];
*/

$usernameErrors  = validateUsername($username);
$emailError = validateEmail($email);
$passwordErrors = validatePassword($password);
$birthDateError = validateBirthDate($birth_date);

// Создаем массив ошибок
$errors = [
   'username' => !empty($usernameErrors) ? $usernameErrors : null,
   'email' => !empty($emailError) ? $emailError : null,
   'password' => !empty($passwordErrors) ? $passwordErrors : null,
   'birth_date' => !empty($birthDateError) ? $birthDateError : null
];

// Удаляем пустые значения
$errors = array_filter($errors, function($value) {
   return $value !== null && 
   (is_array($value) && count($value) > 0) || 
   (is_string($value) && trim($value) !== '');
});

if (!empty($errors)) {
   echo json_encode([
       'status' => 'validate error', 
       'errors' => $errors, 
       'message' => 'Неправильный формат данных!'
   ]);
   exit;
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
   echo json_encode(['status' => 'error', 'message' => 'Пользователь с таким логином уже существует!']);
   $stmt->close();
   $conn->close();
   exit;
}

$stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
   echo json_encode(['status' => 'error', 'message' => 'Пользователь с такой почтой уже существует!']);
   $stmt->close();
   $conn->close();
   exit;
}

$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, birth_date) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $passwordHash, $birth_date);  // Привязка параметров

if ($stmt->execute()) {
   $newUserId = $conn->insert_id; // Получаем ID новой записи
   setcookie('userid', $newUserId, time() + 60*60*24*7, '/');
   echo json_encode(['status' => 'success', 'message' => 'Пользователь успешно зарегистрирован.']);
} else {
   echo json_encode(['status' => 'error', 'message' => 'Ошибка при добавлении пользователя: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

function validateUsername($username){
   $errorsUsername = [];
   
   // Минимум 4 символа
   if (strlen($username) < 4) {
      $errorsUsername[] = "имя пользователя должно содержать минимум 4 символа";
   }
   if (!preg_match('/[A-Za-z]/', $username)) {
      $errorsUsername[] = "имя пользователя должно содержать только латиницу";
   }
   // Без пробелов
   if (preg_match('/\s/', $username)) {
      $errorsUsername[] = "имя пользователя не должно содержать пробелов";
   }
   return $errorsUsername;
}

function validateEmail($email){
   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errorsEmail = "неверный формат email";
   }
   return $errorsEmail;
}

function validatePassword($password){
   $errorsPassword = [];
   
  // Минимум 8 символов
  if (strlen($password) < 8) {
     $errorsPassword[] = "пароль должен содержать минимум 8 символов";
  }
  // Только латиница
  if (!preg_match('/[A-Za-z]/', $password)) {
     $errorsPassword[] = "пароль должен содержать только латиницу";
  }
  // Хотя бы 1 цифра
  if (!preg_match('/\d/', $password)) {
     $errorsPassword[] = "пароль должен содержать хотя бы одну цифру";
  }
  // Хотя бы 1 заглавная буква
  if (!preg_match('/[A-Z]/', $password)) {
     $errorsPassword[] = "пароль должен содержать хотя бы одну заглавную букву";
  }
  // Хотя бы 1 строчная буква
  if (!preg_match('/[a-z]/', $password)) {
     $errorsPassword[] = "пароль должен содержать хотя бы одну строчную букву";
  }
  // Хотя бы 1 спецсимвол
  if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
     $errorsPassword[] = "пароль должен содержать хотя бы один спецсимвол";
  }
  // Без пробелов
  if (preg_match('/\s/', $password)) {
     $errorsPassword[] = "пароль не должен содержать пробелов";
  }

  return $errorsPassword;
}

function validateBirthDate($birth_date){
   $d = DateTime::createFromFormat('Y-m-d', $birth_date); 
   if (!($d && $d->format('Y-m-d') === $birth_date)) {
      $errorsEmail = "неверный формат даты";
   }
   return $errorsEmail;
}

?>