Docxerator
==========


**Docxerator is a simple MS Word DOCX template processor**


## Features

 * Customizing the label templates
 * Processing of fragmented labels

 
## Requirements

 * PHP version 5.6 or higher
 * XML extension
 * MBString extension

## Install with composer

To install with [Composer](https://getcomposer.org/), simply require the
latest version of this package.

```bash
composer require direct808/docxerator
```

Make sure that the autoload file from Composer is loaded.

```php
// somewhere early in your project's loading, require the Composer autoloader
// see: http://getcomposer.org/doc/00-intro.md
require 'vendor/autoload.php';
```

## Usage

Docxerator is very easy to use:

```php
// reference the Docxerator namespace
use Direct808\Docxerator\Docxerator;

// instantiate and use the Docxerator class
$docxerator = new Docxerator();

// open docx template file (contains the label #MARK#)
$docxerator->open('./you_docx_document.docx');

// Replace the label
$docxerator->replace('MARK', 'Your replaced content');

// Save the processing document
$processingDocumentPath = $docxerator->save();
```

### Custom label format

```php
$docxerator = new Docxerator();

// Docxerator will processing labels of the format {MARK}
$docxerator->setMarkPattern('/\{(\w+)\}/i');

$docxerator->open('./you_docx_document.docx');

$processingDocumentPath = $docxerator->save();
```