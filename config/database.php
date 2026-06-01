<?php

$servername = 'localhost';
$db_username = 'root';
$db_password = '';
$dbname = 'bcste';

$dbhandle = new mysqli($servername, $db_username, $db_password, $dbname);
if ($dbhandle->connect_error) {
    die('Database connection failed: ' . $dbhandle->connect_error);
}
