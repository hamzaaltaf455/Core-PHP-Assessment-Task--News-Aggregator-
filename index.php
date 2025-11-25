<?php

require_once 'config.php';

$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$searchKeywordClean = mysqli_real_escape_string($conn, $searchKeyword);
$filterCategoryClean = mysqli_real_escape_string($conn, $filterCategory);

$whereConditions = [];

if (!empty($searchKeywordClean)) {
    $whereConditions[] = "(title LIKE '%$searchKeywordClean%' OR description LIKE '%$searchKeywordClean%')";
}

if (!empty($filterCategoryClean)) {
    $whereConditions[] = "category = '$filterCategoryClean'";
}

$whereClause = '';
if (count($whereConditions) > 0) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

$countSql = "SELECT COUNT(*) as total FROM articles $whereClause";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$totalArticles = $countRow['total'];
$totalPages = ceil($totalArticles / $perPage);

$sql = "SELECT * FROM articles $whereClause ORDER BY published_date DESC LIMIT $perPage OFFSET $offset";
$result = mysqli_query($conn, $sql);

$categoriesSql = "SELECT DISTINCT category FROM articles ORDER BY category";
$categoriesResult = mysqli_query($conn, $categoriesSql);
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    if (!empty($row['category'])) {
        $categories[] = $row['category'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Aggregator</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>News Aggregator</h1>
            <p>Stay updated with the latest news from around the world</p>
        </div>
    </header>

    <main class="container">
        <div class="filters">
            <form method="GET" action="index.php" class="filter-form">
                <div class="search-box">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search articles..." 
                        value="<?php echo htmlspecialchars($searchKeyword); ?>"
                    >
                    <button type="submit" class="btn-search">Search</button>
                </div>
                
                <div class="category-filter">
                    <label for="category">Filter by Category:</label>
                    <select name="category" id="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                <?php echo ($filterCategory === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($cat)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (!empty($searchKeyword) || !empty($filterCategory)): ?>
                    <a href="index.php" class="btn-clear">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="results-info">
            <p>Found <?php echo $totalArticles; ?> article<?php echo $totalArticles !== 1 ? 's' : ''; ?></p>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="articles-grid">
                <?php while ($article = mysqli_fetch_assoc($result)): ?>
                    <article class="article-card">
                        <?php if (!empty($article['image_url'])): ?>
                            <div class="article-image">
                                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($article['title']); ?>"
                                     onerror="this.parentElement.style.display='none'">
                            </div>
                        <?php endif; ?>
                        
                        <div class="article-content">
                            <div class="article-meta">
                                <?php if (!empty($article['category'])): ?>
                                    <span class="category-badge <?php echo strtolower($article['category']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($article['category'])); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($article['source'])): ?>
                                    <span class="source"><?php echo htmlspecialchars($article['source']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <h2 class="article-title">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </h2>
                            
                            <?php if (!empty($article['description'])): ?>
                                <p class="article-description">
                                    <?php echo htmlspecialchars($article['description']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="article-footer">
                                <span class="publish-date">
                                    <?php echo date('M d, Y', strtotime($article['published_date'])); ?>
                                </span>
                                <a href="<?php echo htmlspecialchars($article['url']); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="btn-read-more">
                                    Read More
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchKeyword); ?>&category=<?php echo urlencode($filterCategory); ?>" 
                           class="page-link">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchKeyword); ?>&category=<?php echo urlencode($filterCategory); ?>" 
                           class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchKeyword); ?>&category=<?php echo urlencode($filterCategory); ?>" 
                           class="page-link">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <p>No articles found. Try adjusting your search or filter criteria.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> News Aggregator. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
<?php mysqli_close($conn); ?>
