<?php
	// TODO A: Improve the readability of this file through refactoring and documentation.
// TODO B: Review the HTML structure and make sure that it is valid and contains
// required elements. Edit and re-organize the HTML as needed.
// TODO C: Review the index.php entrypoint for security and performance concerns
// and provide fixes. Note any issues you don't have time to fix.
// TODO D: The list of available articles is hardcoded. Add code to get a
// dynamically generated list.
// TODO E: Are there performance problems with the word count function? How
// could you optimize this to perform well with large amounts of data? Code
// comments / psuedo-code welcome.
// TODO F (optional): Implement a unit test that operates on part of App.php
// Import App class from App namespace
use App\App;

// Include the Composer autoload to manage dependencies
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the application
$app = new App();
/**
 * Sanitize input data to prevent XSS attacks
 *
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
/**
 * Generate the header HTML with word count
 *
 * @param int $wordCount Word count
 * @return string HTML for the header
 */
function getHeaderHTML($wordCount) {
    return "<div id='header' class='header'>
        <a href='/'>Article Editor</a><div>$wordCount</div>
    </div>";
}
/**
 * Generate the form HTML for creating/editing an article
 *
 * @param string $title Article title
 * @param string $body Article body
 * @return string HTML for the form
 */
function getFormHTML($title, $body) {
    return "<h2>Create/Edit Article</h2>
    <p>Create a new article by filling out the fields below. Edit an article by typing the beginning of the title in the title field, selecting the title from the auto-complete list, and changing the text in the text field.</p>
    <form action='index.php' method='post'>
	<div class='suggestions-container'><input name='title' type='text' placeholder='Article title...' value='" . sanitize($title) . "' autocomplete='off'></div>
        <br />
        <textarea name='body' placeholder='Article body...'>" . sanitize($body) . "</textarea>
        <br />
        <button type='submit' class='submit-button'>Submit</button>
        <br />
        <h2>Preview</h2>
        <div>" . sanitize($title) . "</div>
        <div>" . sanitize($body) . "</div>
        <h2>Articles</h2>
        <ul>" . getArticlesList() . "</ul>
    </form>";
}
/**
 * Generate a list of available articles dynamically
 *
 * @return string HTML list of articles
 */
function getArticlesList() {
    $articlesList = '';
    $directory = new DirectoryIterator('articles/');
    foreach ($directory as $fileinfo) {
        if ($fileinfo->isFile()) {
            $filename = $fileinfo->getFilename();
            $articleTitle = pathinfo($filename, PATHINFO_FILENAME);
            $articlesList .= "<li><a href='index.php?title=" . urlencode($articleTitle) . "'>" . sanitize($articleTitle) . "</a></li>";
        }
    }
    return $articlesList;
}
/**
 * Calculate and return the total word count of all articles
 *
 * @return string Word count message
 */
function wfGetWc() {
    $wgBaseArticlePath = 'articles/';
    $wc = 0;
    $dir = new DirectoryIterator($wgBaseArticlePath);
    foreach ($dir as $fileinfo) {
        if ($fileinfo->isDot()) {
            continue;
        }
        $handle = fopen($wgBaseArticlePath . $fileinfo->getFilename(), "r");
        while (($line = fgets($handle)) !== false) {
            $wc += str_word_count($line);
        }
        fclose($handle);
    }
    return "$wc words written";
}

// Initialize variables for title and body
$title = '';
$body = '';
// Check if 'title' is set in query parameters and sanitize the input
if (isset($_GET['title'])) {
    $title = sanitize($_GET['title']);
    // Fetch the article body from the application or file
    if (file_exists(sprintf('articles/%s', $title))) {
        $body = file_get_contents(sprintf('articles/%s', $title));
    } else {
        $body = $app->fetch($_GET); // Assuming fetch is a secure method
    }
}
// Process the form submission
if ($_POST) {
    $title = sanitize($_POST['title']);
    $body = sanitize($_POST['body']);
    $app->save(sprintf("articles/%s", $title), $body);
}
// Calculate the total word count
$wordCount = wfGetWc();
// Output the HTML structure
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Article Editor</title>
    <link rel='stylesheet' href='http://design.wikimedia.org/style-guide/css/build/wmui-style-guide.min.css'>
    <link rel='stylesheet' href='styles.css'>
    <script src='main.js'></script>
</head>
<body>";
echo getHeaderHTML($wordCount);
echo "<div class='page'>";
echo "<div class='main'>";
echo getFormHTML($title, $body);
echo "</div>";
echo "</div>";
echo "</body>
</html>";
?>
