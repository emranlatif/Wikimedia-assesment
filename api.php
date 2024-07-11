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

function handleRequest(App $app) {
    if (isListRequest()) {
        handleListRequest($app);
    } elseif (isPrefixSearchRequest()) {
        handlePrefixSearchRequest($app);
    } else {
        handleArticleRequest($app);
    }
}

function isListRequest(): bool {
    return !isset($_GET['title']) && !isset($_GET['prefixsearch']);
}

function isPrefixSearchRequest(): bool {
    return isset($_GET['prefixsearch']);
}

function handleListRequest(App $app) {
    echo json_encode(['content' => $app->getListOfArticles()]);
}

function handlePrefixSearchRequest(App $app) {
    $filteredArticles = filterArticlesByPrefix($app->getListOfArticles(), $_GET['prefixsearch']);
    echo json_encode(['content' => $filteredArticles]);
}

function handleArticleRequest(App $app) {
    echo json_encode(['content' => $app->fetch($_GET)]);
}

function filterArticlesByPrefix(array $articles, string $prefix): array {
    $filteredArticles = [];
    foreach ($articles as $article) {
        if (stripos($article, $prefix) === 0) {
            $filteredArticles[] = $article;
        }
    }
    return $filteredArticles;
}
