# Quickly create, use and delete temporary directories

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![PHP Composer](https://github.com/hellomohsinhello/temp-dir/actions/workflows/php.yml/badge.svg)](https://github.com/hellomohsinhello/temp-dir/actions/workflows/php.yml)

This package allows you to quickly create, use and delete a temporary directory in the system's temporary directory.

Here's a quick example on how to create a temporary directory and delete it:

```php
use Hellomohsinhello\TempDir\TempDir;

$tempDir = (new TempDir())->create();

// Get a path inside the temporary directory
$tempDir->path('temporaryfile.txt');

// Delete the temporary directory and all the files inside it
$tempDir->delete();
```

## Installation

You can install the package via composer:

```bash
composer require hellomohsinhello/temp-dir
```

## Usage

### Creating a temporary directory

To create a temporary directory simply call the `create` method on a `TemporaryDirectory` object.

```php
(new TempDir())->create();
```

Alternatively, use the static `make` method on a `TempDir` object.

```php
TempDir::make();
```

By default, the temporary directory will be created in a timestamped directory in your system's temporary directory (usually `/tmp`).

### Naming your temporary directory

If you want to use a custom name for your temporary directory instead of the timestamp call the `name` method with a string `$name` argument before the `create` method.

```php
(new TempDir())
   ->name($name)
   ->create();
```

By default an exception will be thrown if a directory already exists with the given argument. You can override this behaviour by calling the `force` method in combination with the `name` method.

```php
(new TempDir())
   ->name($name)
   ->force()
   ->create();
```

### Setting a custom location for a temporary directory

You can set a custom location in which your temporary directory will be created by passing a string `$location` argument to the `TemporaryDirectory` constructor.

```php
(new TempDir($location))
   ->create();
```

The `make` method also accepts a `$location` argument.

```php
TempDir::make($location);
```

Finally, you can call the `location` method with a `$location` argument.

```php
(new TempDir())
   ->location($location)
   ->create();
```

### Determining paths within the temporary directory

You can use the `path` method to determine the full path to a file or directory in the temporary directory:

```php
$temporaryDirectory = (new TempDir())->create();
$temporaryDirectory->path('dumps/datadump.dat'); // return  /tmp/1485941876276/dumps/datadump.dat
```

### Emptying a temporary directory

Use the `empty` method to delete all the files inside the temporary directory.

```php
$temporaryDirectory->empty();
```

### Deleting a temporary directory

Once you're done processing your temporary data you can delete the entire temporary directory using the `delete` method. All files inside of it will be deleted.

```php
$temporaryDirectory->delete();
```

### Deleting a temporary directory when the object is destroyed

If you want to automatically have the filesystem directory deleted when the object instance has no more references in
its defined scope, you can enable `deleteWhenDestroyed()` on the TemporaryDirectory object.

```php
function handleTemporaryFiles()
{
    $temporaryDirectory = (new TempDir())
        ->deleteWhenDestroyed()
        ->create();

    // ... use the temporary directory

    return; // no need to manually call $temporaryDirectory->delete()!
}

handleTemporaryFiles();
```

You can also call `unset()` on an object instance.

## Testing

```bash
composer test
```

## Credits

- [Mohsin Ali](https://github.com/hellomohsinhello)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
