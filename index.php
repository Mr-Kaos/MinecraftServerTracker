<?php

/**
 * This file is a very basic page routing system. It hides GET variables from URL strings and paths from the web server.
 */
$request = explode('?', $_SERVER['REQUEST_URI'])[0];
$request = substr($request, 1);
$page = 'home.php';

// Obtain the MIME type of the requested item.
$pattern = '/[.].*/';
preg_match($pattern, $request, $matches);
$contentType = getContentTypeHeader($matches[0] ?? '');

// If the MIME type is not "none", load the requested resource with its specified MIME type. Else, load file as .php.
if ($contentType !== 'none') {
    header('Content-Type:' . $contentType);
    include($request);
    die();
} else {
    // echo "$request <br>";
    // echo 'pages/' . $request;
    if (!str_contains($request, '.php')) {
        $request .= '.php';
    }
    if (file_exists('pages/' . $request)) {
        $page = $request;
    }
}

if (str_contains($_SERVER['REQUEST_URI'], '.php')) {
    header('location:' . str_replace('.php', '', $page) . '?' . $_SERVER['QUERY_STRING']);
    die();
}
include('pages/' . $page);
die();

/**
 * Returns a MIME type based on the given file extension.
 * @param string $fileExtension
 * @return string The MIME type for the specified file extension.
 */
function getContentTypeHeader(string $fileExtension): string
{
    $mimeType = 'text/html';
    switch ($fileExtension) {
            //Image Types
        case '.jpg':
        case '.jpeg':
        case '.jpe':
            $mimeType = 'image/jpeg';
            break;
        case '.png':
            $mimeType = 'image/png';
            break;
        case '.gif':
            $mimeType = 'image/gif';
            break;
        case '.svg':
            $mimeType = 'image/svg+xml';
            break;
        case '.ico':
            $mimeType = 'image/x-icon';
            break;
            // Text Files
        case '.css':
            $mimeType = 'text/css';
            break;
        case '.js':
            $mimeType = 'application/javascript';
            break;
        case '.json':
            $mimeType = 'application/json';
            break;
        default:
            $mimeType = 'none';
            break;
    }

    return $mimeType;
}
