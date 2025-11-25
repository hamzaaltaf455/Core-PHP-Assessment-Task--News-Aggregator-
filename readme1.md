News Aggregator — Core PHP app fetching and displaying top headlines.
Features: fetch via NewsAPI, MySQL storage, search, category filter, pagination.
Requirements: PHP 7.4+, MySQL, cURL, web server.
Create DB: run `database.sql` or use `setup_web.php`.
Configure `config.php` with DB credentials and your `NEWS_API_KEY`.
Populate articles: run `php fetch_articles.php`.
Serve locally: `php -S localhost:8000 -t .` or use XAMPP and place files in `htdocs/news-aggregator`.
View: open `http://localhost:8000/index.php`.
