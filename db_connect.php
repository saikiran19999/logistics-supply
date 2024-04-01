<?php

$servername = "3.99.241.216";
$username = "sai";
$password = "sai";
$db = "cms_db";
$port= = 3307; 

$conn = new mysqli($servername,$username,$password,$db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
