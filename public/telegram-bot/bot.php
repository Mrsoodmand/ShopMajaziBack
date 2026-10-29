<?php

$request = file_get_contents('php://input');
$request = json_decode($request, TRUE);
