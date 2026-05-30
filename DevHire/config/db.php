<?php

$host="localhost";

$user="root";

$password="";

$database="devhire";

$conn=new mysqli(
$host,
$user,
$password,
$database
);

// Load shared helpers (sanitize, validateEmail, logError)
require_once __DIR__ . '/../includes/helpers.php';

if(
$conn->connect_error
)
{
die(
"Connection failed: ".
$conn->connect_error
);
}

?>