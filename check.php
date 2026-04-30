<?php
require 'C:\xampp\htdocs\piura_noticias_php\conexion.php';
global $pdo;
$stmt = $pdo->prepare("SELECT avatar_url FROM usuarios WHERE id=1");
$stmt->execute();
var_dump($stmt->fetchColumn());
