<?php

$servername = "localhost";
$username = "sai";
$password = "sai";
$db = "cms_db";

$conn = new mysqli($servername,$username,$password,$db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
