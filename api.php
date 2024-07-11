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
 * @return bool True if the request is for the list of articles, false otherwise.
 */
function isListRequest(): bool {
    return !isset($_GET['title']) && !isset($_GET['prefixsearch']);
}

/**
 * Check if the request is a prefix search.
 *
 * @return bool True if the request is a prefix search, false otherwise.
 */
function isPrefixSearchRequest(): bool {
    return isset($_GET['prefixsearch']);
}

/**
 * Handle a request for the list of articles.
 *
 * @param App $app The application instance.
 */
function handleListRequest(App $app) {
    echo json_encode(['content' => $app->getListOfArticles()]);
}

/**
 * Handle a prefix search request.
 *
 * @param App $app The application instance.
 */
function handlePrefixSearchRequest(App $app) {
    // Suggestion: Cache the list of articles or implement search algorithm or indexing method.
    $filteredArticles = filterArticlesByPrefix($app->getListOfArticles(), $_GET['prefixsearch']);
    echo json_encode(['content' => $filteredArticles]);
}

/**
 * Handle a request for a specific article.
 *
 * @param App $app The application instance.
 */
function handleArticleRequest(App $app) {
    //Performance Concern- Fetching an article might involve reading from the file system or database for each request.
    // Suggestion: Implement caching for articles to reduce file system/database access.
	// TODO D: Security Concern - Potential XSS (Cross-Site Scripting) vulnerability if $_GET parameters are not properly sanitized.
    // Suggestion: Ensure all input parameters are sanitized before use.
    echo json_encode(['content' => $app->fetch($_GET)]);
}


/**
 * Filter a list of articles by a given prefix.
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
    // Performance Concern - Linear search has O(n) complexity. For large datasets, consider using a more efficient search method.
    // Suggestion: Implement a trie (prefix tree) or another data structure optimized for prefix searches.
    return $filteredArticles;
}

/**
 * TODO D: Security Concern - Input Sanitization
 * Sanitize input to prevent XSS attacks.
 *
 * @param string $input The input to sanitize.
 * @return string The sanitized input.
 */
function sanitizeInput(string $input): string {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Sanitize all input parameters
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
