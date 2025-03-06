# PHP Win32 File Picker

Calls Windows Explorer to select file(s).  

You are able to configure to select 1 or multiple files, 
filtering by extensions or let the user free to choose any.

Unlocked by the power of PHP's [FFI](https://www.php.net/manual/en/book.ffi.php)

_Not because we must do it, but because we can!_

Made by [Terremoth](https://github.com/terremoth/) with ⚡ & ❤

<div align="center">
    
[![Test Run Status](https://github.com/terremoth/php-win32-filepicker/actions/workflows/workflow.yml/badge.svg?branch=main)](https://github.com/terremoth/php-win32-filepicker/actions/workflows/workflow.yml)
[![License](https://img.shields.io/github/license/terremoth/php-win32-filepicker.svg?logo=gpl&color=41bb13)](https://github.com/terremoth/php-win32-filepicker/blob/main/LICENSE)
[![Latest Stable Version](https://poser.pugx.org/terremoth/php-win32-filepicker/v/stable)](https://packagist.org/packages/terremoth/php-win32-filepicker)
[![Total Downloads](https://poser.pugx.org/terremoth/php-win32-filepicker/downloads)](https://packagist.org/packages/terremoth/php-win32-filepicker)
[![Dynamic JSON Badge](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fdiscord.com%2Fapi%2Finvites%2FJxFhMVWu82%3Fwith_counts%3Dtrue&query=%24.approximate_member_count&logo=discord&logoColor=white&label=Users%20Total&color=green)](https://discord.gg/JxFhMVWu82)
</div>
<div align="center">

[![codecov](https://codecov.io/gh/terremoth/php-win32-filepicker/graph/badge.svg?token=OK19B0N657)](https://codecov.io/gh/terremoth/php-win32-filepicker)
[![Test Coverage](https://api.codeclimate.com/v1/badges/1ec5c2da0c5a366cecb7/test_coverage)](https://codeclimate.com/github/terremoth/php-win32-filepicker/test_coverage)
[![Psalm type coverage](https://shepherd.dev/github/terremoth/php-win32-filepicker/coverage.svg)](https://shepherd.dev/github/terremoth/php-win32-filepicker)
[![Psalm level](https://shepherd.dev/github/terremoth/php-win32-filepicker/level.svg)](https://shepherd.dev/github/terremoth/php-win32-filepicker)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/f0b186eb12a745a79b110fde625b645f)](https://app.codacy.com/gh/terremoth/php-win32-filepicker/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/1ec5c2da0c5a366cecb7/maintainability)](https://codeclimate.com/github/terremoth/php-win32-filepicker/maintainability)
</div>

## Installation

```sh
composer require terremoth/php-win32-filepicker
```

## Documentation

```php
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

// if no files were selected, an empty array will be returned 😉
```

That's it!  

See [demos/demo.php](demos/demo.php) for this example.
