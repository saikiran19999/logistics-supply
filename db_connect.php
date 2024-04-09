<?php

$servername = "db";
$username = "sai";
$password = "sai";
$db = "cms_db";

$conn = new mysqli_connect($servername,$username,$password,$db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
