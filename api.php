<?php

use App\App;

require_once __DIR__ . '/vendor/autoload.php';

$app = new App();
// TODO A: Improve the readability of this file through refactoring and documentation.
// TODO B: Clean up the following code so that it's easier to see the different
// routes and handlers for the API, and simpler to add new ones.
// TODO C: If there are performance concerns in the current code, please
// add comments on how you would fix them
// TODO D: Identify any potential security vulnerabilities in this code.
// TODO E: Document this code to make it more understandable
// for other developers.

header('Content-Type: application/json');

// Route and handle requests
handleRequest($app);

/**
 * Route and handle API requests.
 *
 * This function routes the incoming API requests to the appropriate handler
 * based on the request parameters. It helps to manage different API endpoints
 * in a clean and organized way.
 *
 * @param App $app The application instance.
 */
function handleRequest(App $app) {
    if (isListRequest()) {
        handleListRequest($app);
    } elseif (isPrefixSearchRequest()) {
        handlePrefixSearchRequest($app);
    } else {
        handleArticleRequest($app);
    }
}

/**
 * Check if the request is for the list of articles.
 *
 * This function checks if the request is intended to retrieve the list of
 * all articles. It does so by checking the absence of 'title' and 'prefixsearch'
 * parameters in the query string.
 *
 * @return bool True if the request is for the list of articles, false otherwise.
 */
function isListRequest(): bool {
    return !isset($_GET['title']) && !isset($_GET['prefixsearch']);
}

/**
 * Check if the request is a prefix search.
 *
 * This function checks if the request is intended to perform a prefix search
 * on the list of articles. It does so by checking for the presence of the
 * 'prefixsearch' parameter in the query string.
 *
 * @return bool True if the request is a prefix search, false otherwise.
 */
function isPrefixSearchRequest(): bool {
    return isset($_GET['prefixsearch']);
}

/**
 * Handle a request for the list of articles.
 *
 * This function handles requests that aim to retrieve the list of all articles.
 * It fetches the list from the application instance and returns it as a JSON response.
 *
 * @param App $app The application instance.
 */
function handleListRequest(App $app) {
    echo json_encode(['content' => $app->getListOfArticles()]);
}

/**
 * Handle a prefix search request.
 *
 * This function handles requests that aim to perform a prefix search on the
 * list of articles. It filters the list based on the given prefix and returns
 * the filtered list as a JSON response.
 *
 * @param App $app The application instance.
 */
function handlePrefixSearchRequest(App $app) {
    // Suggestion: Cache the list of articles or implement a search algorithm or indexing method.
    $filteredArticles = filterArticlesByPrefix($app->getListOfArticles(), $_GET['prefixsearch']);
    echo json_encode(['content' => $filteredArticles]);
}

/**
 * Handle a request for a specific article.
 *
 * This function handles requests that aim to retrieve a specific article.
 * It fetches the article content from the application instance and returns it as a JSON response.
 *
 * @param App $app The application instance.
 */
function handleArticleRequest(App $app) {
    // Performance Concern: Fetching an article might involve reading from the file system or database for each request.
    // Suggestion: Implement caching for articles to reduce file system/database access.
    // TODO D: Security Concern - Potential XSS (Cross-Site Scripting) vulnerability if $_GET parameters are not properly sanitized.
    // Suggestion: Ensure all input parameters are sanitized before use.
    echo json_encode(['content' => $app->fetch($_GET)]);
}

/**
 * Filter a list of articles by a given prefix.
 *
 * This function filters the provided list of articles to include only those
 * that start with the given prefix. It performs a case-insensitive search.
 *
 * @param array $articles The list of articles to filter.
 * @param string $prefix The prefix to filter by.
 * @return array The filtered list of articles.
 */
function filterArticlesByPrefix(array $articles, string $prefix): array {
    $filteredArticles = [];
    foreach ($articles as $article) {
        if (stripos($article, $prefix) === 0) {
            $filteredArticles[] = $article;
        }
    }
    // Performance Concern: Linear search has O(n) complexity. For large datasets, consider using a more efficient search method.
    // Suggestion: Implement a trie (prefix tree) or another data structure optimized for prefix searches.
    return $filteredArticles;
}

/**
 * TODO D: Security Concern - Input Sanitization
 * Sanitize input to prevent XSS attacks.
 *
 * This function sanitizes the input to prevent Cross-Site Scripting (XSS) attacks.
 *
 * @param string $input The input to sanitize.
 * @return string The sanitized input.
 */
function sanitizeInput(string $input): string {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Sanitize all input parameters to prevent XSS attacks.
$_GET = array_map('sanitizeInput', $_GET);

/**
 * TODO D: Security Concern - Secure Database Interaction
 * If the App::fetch method interacts with a database, ensure it's using prepared statements to prevent SQL injection.
 * Example:
 *
 * // Using PDO for database interaction
 * $stmt = $pdo->prepare("SELECT * FROM articles WHERE title = :title");
 * $stmt->execute(['title' => $_GET['title']]);
 * $article = $stmt->fetch();
 */

/**
 * TODO D: Security Concern - Error Handling
 * Implement proper error handling to prevent information disclosure.
 * Example:
 *
 * try {
 *     // Code that may throw an exception
 * } catch (Exception $e) {
 *     error_log($e->getMessage()); // Log detailed error message
 *     echo json_encode(['error' => 'An unexpected error occurred.']); // Display generic error message
 * }
 */

/**
 * TODO D: Security Concern - Insecure Configuration
 * Validate and sanitize file paths before including them.
 * Example:
 *
 * $filePath = realpath(__DIR__ . '/vendor/autoload.php');
 * if ($filePath && strpos($filePath, __DIR__) === 0) {
 *     require_once $filePath;
 * } else {
 *     throw new Exception('Invalid file path');
 * }
 */

?>
