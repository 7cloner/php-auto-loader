# Cloner PHP Auto Loader
A simple solution to load an existing class inside a project with nested folders

## Installation

Download the file and add it to your project source.

In the first step, include the file into your project like this:

```php
require_once 'autoloader.php';
```

Now, just create an instance of the ClonerAutoLoader class and give it the path of the root folder that you want to scan to find the file as an input parameter, like this:

```php
new ClonerAutoloader(__DIR__);
```