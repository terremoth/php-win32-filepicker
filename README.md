# PHP Win32 File Picker

Calls Windows Explorer to select file(s).  

You are able to configure to select 1 or multiple files, 
filtering by extensions or let the user free to choose any.

Unlocked by the power of PHP's [FFI](https://www.php.net/manual/en/book.ffi.php)

_Not because we must do it, but because we can!_

Made by [Terremoth](https://github.com/terremoth/) with ⚡ & ❤

<div align="center">
    
[![Test Run Status](https://github.com/terremoth/{project-url}/actions/workflows/workflow.yml/badge.svg?branch=main)](https://github.com/terremoth/{project-url}/actions/workflows/workflow.yml)
[![License](https://img.shields.io/github/license/terremoth/{project-url}.svg?logo=mit&color=41bb13)](https://github.com/terremoth/{project-url}/blob/main/LICENSE)
[![Latest Stable Version](https://poser.pugx.org/terremoth/{project-url}/v/stable)](https://packagist.org/packages/terremoth/{project-url})
[![Total Downloads](https://poser.pugx.org/terremoth/{project-url}/downloads)](https://packagist.org/packages/terremoth/{project-url})
[![Dynamic JSON Badge](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fdiscord.com%2Fapi%2Finvites%2FJxFhMVWu82%3Fwith_counts%3Dtrue&query=%24.approximate_member_count&logo=discord&logoColor=white&label=Users%20Total&color=green)](https://discord.gg/JxFhMVWu82)
</div>
<div align="center">

[![codecov](https://codecov.io/github/terremoth/{project-url}/graph/badge.svg?token={COV_TOKEN})](https://codecov.io/github/terremoth/{project-url})
[![Test Coverage](https://api.codeclimate.com/v1/badges/{CC_TOKEN}/test_coverage)](https://codeclimate.com/github/terremoth/{project-url}/test_coverage)
[![Psalm type coverage](https://shepherd.dev/github/terremoth/{project-url}/coverage.svg)](https://shepherd.dev/github/terremoth/{project-url})
[![Psalm level](https://shepherd.dev/github/terremoth/{project-url}/level.svg)](https://shepherd.dev/github/terremoth/{project-url})
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/{CODACY_TOKEN)](https://app.codacy.com/gh/terremoth/{project-url}/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/{CC_TOKEN}/maintainability)](https://codeclimate.com/github/terremoth/{project-url}/maintainability)
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

// if no files were selected, and empty array will be returned 😉
```
  
See [demos/demo.php](demos/demo.php) for example.


