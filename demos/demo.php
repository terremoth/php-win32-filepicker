<?php

require_once 'vendor/autoload.php';

use Terremoth\Win32\FilePicker;

$selectedDirectory = $_SERVER['USERPROFILE'] ?? 'C:\\'; // optional

$fp = new FilePicker($selectedDirectory); // $selectedDirectory can be null without problem.

// all methods below are *optional*:
$fp
    ->selectMultipleFiles()
    ->addExtensionsFilter(['png', 'jpg', 'gif', 'avif', 'webp', 'jpeg', 'ico', 'bmp'])
    ->filterOnlySelectedExtensions()
    ->setDefaultExtensionSearch('png')
;

$selectedFiles = $fp->open();

print_r($selectedFiles);

// if no files were selected, and empty array will be returned 😉
