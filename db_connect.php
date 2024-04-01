<?php

$servername = "127.0.0.1";
$username = "sai";
$password = "sai";
$db = "cms_db";
$port= = 6033; 

$conn = new mysqli($servername,$username,$password,$db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
