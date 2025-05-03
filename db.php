<?php
$servername = "localhost";
$username_db = "root";  // Имя пользователя БД
$password_db = "";      // Пароль (если пусто, то для localhost — обычно пусто)
$dbname = "cinema_app";       // Имя базы данных

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Проверяем подключение
if ($conn->connect_error) {
   die("Ошибка подключения: " . $conn->connect_error);
}

?>