<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * This file redirects all requests to the public directory.
 */

$publicPath = __DIR__.'/public';

// For all requests, redirect to public directory
$requestUri = $_SERVER['REQUEST_URI'];
if (strpos($requestUri, '/public/') !== 0) {
    $newLocation = '/public' . $requestUri;
    header("Location: $newLocation", true, 302);
    exit;
}

// If request is already for public directory, pass it through
require_once $publicPath . '/index.php';