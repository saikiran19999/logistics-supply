<?php

$servername = "db";
$username = "root";
$password = "root";
$db = "cms_db";

$conn = new mysqli($servername,$username,$password,$db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
