<?php
$filename = isset($_GET['file']) ? basename($_GET['file']) : null;
$imagePath = __DIR__ ."/" . $filename;

if ($filename && file_exists($imagePath)) {
    $mimeType = mime_content_type($imagePath); // Detect MIME type
    header("Content-Type: $mimeType");
    readfile($imagePath);
} else {
    http_response_code(404);
    echo "Image not found".$imagePath;

    echo $_SERVER['SERVER_NAME']; 
}
