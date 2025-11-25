<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'news_aggregator');

define('NEWS_API_KEY', 'YOUR_API_KEY_HERE');
define('NEWS_API_URL', 'https://newsapi.org/v2/top-headlines');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

?>
