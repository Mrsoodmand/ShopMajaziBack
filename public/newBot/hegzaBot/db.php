<?php
require_once __DIR__ . '/../../../bootstrap/bot-env.php';
$servername = "localhost";
$username = "shomarem_shomare";
$password = bot_env('LEGACY_DB_PASSWORD');
$DB = bot_env('LEGACY_DB_PASSWORD');
$mysqli = new mysqli($servername, $username, $password, $username);
mysqli_set_charset($mysqli, "utf8");
$req = json_encode($request);
$mysqli->query("INSERT INTO `hegza_bot`(`data`) VALUES ('$req')");

