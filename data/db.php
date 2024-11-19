<?php
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'foodbanks';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // echo 'Connected successfully';
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>
