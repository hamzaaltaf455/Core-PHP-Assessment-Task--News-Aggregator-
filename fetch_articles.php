<?php

require_once 'config.php';

function fetchNewsFromAPI($category = 'general', $country = 'us') {
    $url = NEWS_API_URL . '?country=' . urlencode($country) . '&category=' . urlencode($category) . '&apiKey=' . NEWS_API_KEY;
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'User-Agent: News Aggregator App'
    ));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch) . "\n";
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo "API returned HTTP code: $httpCode\n";
        return false;
    }
    
    $data = json_decode($response, true);
    
    if (!$data || $data['status'] !== 'ok') {
        echo "API Error: " . ($data['message'] ?? 'Unknown error') . "\n";
        return false;
    }
    
    return $data['articles'] ?? [];
}

function sanitizeString($str) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($str));
}

function storeArticle($article, $category) {
    global $conn;
    
    $title = sanitizeString($article['title'] ?? '');
    $description = sanitizeString($article['description'] ?? '');
    $url = sanitizeString($article['url'] ?? '');
    $imageUrl = sanitizeString($article['urlToImage'] ?? '');
    $source = sanitizeString($article['source']['name'] ?? '');
    $publishedAt = sanitizeString($article['publishedAt'] ?? '');
    $categoryClean = sanitizeString($category);
    
    if (empty($title) || empty($url)) {
        return false;
    }
    
    $publishedDate = date('Y-m-d H:i:s', strtotime($publishedAt));
    
    $checkSql = "SELECT id FROM articles WHERE url = '$url'";
    $checkResult = mysqli_query($conn, $checkSql);
    
    if (mysqli_num_rows($checkResult) > 0) {
        return false;
    }
    
    $sql = "INSERT INTO articles (title, description, url, image_url, source, category, published_date) 
            VALUES ('$title', '$description', '$url', '$imageUrl', '$source', '$categoryClean', '$publishedDate')";
    
    return mysqli_query($conn, $sql);
}

$categories = ['general', 'business', 'entertainment', 'health', 'science', 'sports', 'technology'];

$totalFetched = 0;
$totalStored = 0;

foreach ($categories as $category) {
    echo "Fetching articles for category: $category...\n";
    
    $articles = fetchNewsFromAPI($category);
    
    if ($articles === false) {
        echo "Failed to fetch articles for category: $category\n";
        continue;
    }
    
    $totalFetched += count($articles);
    
    foreach ($articles as $article) {
        if (storeArticle($article, $category)) {
            $totalStored++;
        }
    }
    
    echo "Stored articles from category: $category\n";
    
    sleep(1);
}

echo "\n=== Fetch Summary ===\n";
echo "Total articles fetched: $totalFetched\n";
echo "Total articles stored: $totalStored\n";
echo "Duplicates skipped: " . ($totalFetched - $totalStored) . "\n";

mysqli_close($conn);

?>
