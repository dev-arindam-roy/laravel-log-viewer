# LARAVEL LOG VIEWER
=======================

### A package for viewing or rendering the Laravel logs. It also extract logs in different level.

## Installation
==================

> **No dependency on PHP version and LARAVEL version**

### STEP 1: Run the composer command:

```shell
composer require creative-syntax/laravel-logviewer
```

### STEP 2: Laravel without auto-discovery:

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
CreativeSyntax\LogViewer\CreativeSyntaxLogViewer::class,
```

### STEP 3: Publish the package config:

```php
php artisan vendor:publish --provider="CreativeSyntax\LogViewer\CreativeSyntaxLogViewer" --force
```

## How to use?:

> **DIRECT USE BY ROUTE**
---
<dl>
  <dt>>> <code>Just install and run the below route </span></code></dt>
</dl>

```php
Ex: http://your-website/onex/log-viewer

Ex: http://localhost:8000/onex/log-viewer
```

#### You can modify the configuration settings in - "config/log-viewer.php":

```php
/** If you want to disable the route or this feature, then make it false */
'is_route_enabled' => true,
```

```php
/** If you want to change the route prefix */
'route_prefix' => 'onex',
```

```php
/** If you want to change the route name or path */
'route_name' => 'log-viewer',
```

```php
/** If you want to change the page heading */
'page_heading' => 'Logs Viewer',
```

```php
/** If you want to enable the securiry for access the system information
 *  Then make it ('is_enabled') true and also you can set login-id and password 
 */
'authentication' => [
    'is_enabled' => env('LOGVIEWER_AUTH_ENABLED', false),
    'login_id' => env('LOGVIEWER_LOGIN_ID', 'onexadmin'),
    'password' => env('LOGVIEWER_LOGIN_PASSWORD', 'onexpassword')
]
```

## license:
MIT