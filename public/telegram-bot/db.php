<?php
require_once __DIR__ . '/../../bootstrap/bot-env.php';
$servername = "localhost";
$username = "shop_majazi_user";
$password = bot_env('SHOP_BOT_DB_PASSWORD');
$DB = "shop_majazi";
$mysqli = new mysqli($servername, $username, $password, $DB);
mysqli_set_charset($mysqli, "utf8");
$req = json_encode($request);
$mysqli->query("INSERT INTO `hegza_bot`(`data`) VALUES ('$req')");

