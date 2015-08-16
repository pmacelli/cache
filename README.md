# comodojo/cache

[![Build Status](https://api.travis-ci.org/comodojo/cache.png)](http://travis-ci.org/comodojo/cache) [![Latest Stable Version](https://poser.pugx.org/comodojo/cache/v/stable)](https://packagist.org/packages/comodojo/cache) [![Total Downloads](https://poser.pugx.org/comodojo/cache/downloads)](https://packagist.org/packages/comodojo/cache) [![Latest Unstable Version](https://poser.pugx.org/comodojo/cache/v/unstable)](https://packagist.org/packages/comodojo/cache) [![License](https://poser.pugx.org/comodojo/cache/license)](https://packagist.org/packages/comodojo/cache) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/comodojo/cache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/comodojo/cache/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/comodojo/cache/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/comodojo/cache/?branch=master)

Very fast PHP data caching across multiple storage engines.

## Introduction

This library provides a fast, framework-independent, data caching layer for PHP applications; it can handle all types of data that PHP can serialize.

It integrates also a Cache Manager to handle multiple cache types (and providers) at the same time using several data retrieval algorithms.

Cache items are key/value objects, organized in namespaces, valid until the time to live has expired.

Main features:

- cache any kind of data, regardless of the type;
- support for cache namespaces (or cache tags);
- multiple, concurrent cache providers;
- native logging via Monolog (if provided).

## Installation

Install [composer](https://getcomposer.org/), then:

`` composer require comodojo/cache dev-master ``

## Basic usage

First, a cache provider should be inited (look at next section to know more about providers).

In following examples, `$cache` is a generic `\Comodojo\Cache\FileCache` provider like:

```php
$cache = new \Comodojo\Cache\FileCache();

```

### Set/get cache namespace

The cache namespace defines the scope of a single cache item.

If not specified, cache items will be defined into *GLOBAL* namespace.

Namespaces are alphanumeric, case-insensitive *strings* of a maximum length of 64 chars.

```php
// set the namespace
$cache->setNamespace('CUSTOMNAMESPACE');

// get the current namespace
$current_namespace = $cache->getNamespace();

```

### Set/get cache items

A cache item should be defined as a key/value object with an optional time to live (in seconds).

If no ttl is defined, lib will check if `COMODOJO_CACHE_DEFAULT_TTL` constant is defined; if not, the item will have a default ttl of 3600 secs.

```php
// set an item
$result = $cache->set('my_data', 'sample data', 3600);

// get an item
$my_data = $cache->get('my_data');

```

### Delete a cache item

```php
// delete an item
$result = $cache->delete('my_data');

```

### Delete the entire namespace

```php
// delete current namespace
$result = $cache->delete();

```

### Flush whole cache

```php
// delete current namespace
$result = $cache->flush();

```

### Get provider's statistics

Each provider implements the `status()` method that will return an array like:

```php
array (
    "provider"  => "file", //provider type
    "enabled"   => true, //provider status
    "objects"   => 42, //number of objects in cache
    "options"   => array ( //different from one provider to another, contains extended info
        "xattr_supported"   =>  true,
        "xattr_enabled"     =>  true
    )
)

```

### Handling errors

A custom `\Comodojo\Exception\CacheException` will be thrown if an error has occurred.

`set()`, `get()`, `delete()` and `flush()` methods **will never throw exception**; instead, they will return a (boolean) false if both key is undefined or an error has occurred.

Additionally, in case of error:

- if provided, error will be logged in the log handler;
- the internal *ERROR* status will be set and could be checked with the `$cache->getErrorState()` method.

### Additional methods

- Set/get current (relative) time

    ```php
    // define time
    $time = time();
    
    // set provider (relative) time
    $cache->setTime($time);
    
    // get provider (relative) time
    $time = $cache->getTime();
    
    ```

- Set/get time to live

    ```php
    // define ttl
    $ttl = 60;
    
    // set provider ttl
    $cache->setTtl($ttl);
    
    // get provider ttl
    $ttl = $cache->getTtl();
    
    ```

- Administratively enable/disable provider

    ```php
    // disable provider
    $cache->disable();
    
    // enable provider
    $cache->enable();
    
    // get provider status
    $enabled = $cache->isEnabled();
    
    ```

- Manage error state

    ```php
    // put provider in error state
    $cache->setErrorState();
    
    // get current error state
    $state = $cache->getErrorState();
    
    // reset error flag
    $cache->resetErrorState();
    
    ```

## Cache providers

Providers are standardized interfaces to connecto to different storage engines; each provider can be used independently or via CacheManager.

Currently supported storage engines:

- [Alternative PHP Cache (APC)](http://php.net/manual/en/book.apc.php) via `\Comodojo\Cache\ApcCache`
- Multiple Databases (using [comodojo/database](https://github.com/comodojo/database) - currently tested on MySQL/MariaDB and SQLite) via `\Comodojo\Cache\DatabaseCache`
- Filesystem (using xattrs if supported) via `\Comodojo\Cache\FileCache`
- Memcached (using [PECL memcached extension](http://php.net/manual/en/book.memcached.php)) via `\Comodojo\Cache\MemcachedCache`
- Redis (using [PhpRedis](https://github.com/phpredis/phpredis)) via `\Comodojo\Cache\PhpRedisCache`
- [XCache](http://xcache.lighttpd.net/) via `\Comodojo\Cache\XCacheCache`

Following, a brief guide to providers' initialization.

### ApcCache

```php
// create an instance of \Comodojo\Cache\ApcCache
$cache = new \Comodojo\Cache\ApcCache();

```

### DatabaseCache

```php
// create an EnhancedDatabase instance
$edb = \Comodojo\Cache\DatabaseCache::getDatabase('MYSQLI', '127.0.0.1', 3306, 'user', 'password');

// init cache using $edb and 'cache' as table
$cache = new \Comodojo\Cache\DatabaseCache($edb, 'cache');

```

### FileCache

```php
// folder to store files
$cache_folder = "/mycachefolder/";    

// create an instance of \Comodojo\Cache\FileCache
$cache = new \Comodojo\Cache\FileCache($cache_folder);

```

### MemcachedCache

```php
// create an instance of \Comodojo\Cache\MemcachedCache
// Parameters: $port, $weight, $persistent_id are optional
$cache = new new \Comodojo\Cache\MemcachedCache('127.0.0.1', 11211, 0, null);

```

### PhpRedisCache

```php
// create an instance of \Comodojo\Cache\PhpRedisCache
// parameters $port and $timeout are optional
$cache = new \Comodojo\Cache\PhpRedisCache('127.0.0.1');

```

### XCacheCache

```php
// create an instance of \Comodojo\Cache\XCacheCache
$cache = new \Comodojo\Cache\XCacheCache();

```

## Cache Manager

The Cache Manager provides an easy way to manage different cache providers at the same time.

It follow these principles:

- Manager should implement same methods of providers;
- *set*, *delete* and *flush* actions should be propagated to all registered providers;
- a value should be retrieved from one or more provider depending on the pick algorithm.

### Pick algorithms

Currently supported algorithms:

- `\Comodojo\Cache\CacheManager::PICK_FIRST`: get value from first registered provider;
- `\Comodojo\Cache\CacheManager::PICK_LAST`: get value from last registered provider;
- `\Comodojo\Cache\CacheManager::PICK_RANDOM`: get value from a random provider;
- `\Comodojo\Cache\CacheManager::PICK_BYWEIGHT`: get value from provider that has the highest weight;
- `\Comodojo\Cache\CacheManager::PICK_ALL`: get values from all registered providers and compare them before return them.

Please note that `\Comodojo\Cache\CacheManager::PICK_ALL` is significantly slower than other algorithms; it should be used for testing purposes only.

### Init the Manager

```php
$manager = new CacheManager( CacheManager::PICK_FIRST );

```

### Registering providers

Providers are registered in CacheManager via `addProvider()` method:

```php
// pX will be the provider's unique id
$p1 = $manager->addProvider( new DatabaseCache($edb, 'cache', 'comodojo_') );
$p2 = $manager->addProvider( new FileCache($cache_folder) );
$p3 = $manager->addProvider( new MemcachedCache('127.0.0.1') );
$p4 = $manager->addProvider( new PhpRedisCache('127.0.0.1') );

```

Providers could be retrieved using the `getProviders()` and removed using the `removeProvider()` method.

```php
// get the whole providers' list
$providers = $manager->getProviders();

// get Apc providers only and remove them
$apc_providers = $manager->getProviders('ApcCache');

foreach ( $apc_providers as $id => $type ) {

    $manager->removeProvider($id);
    
}

```

### Set/get a cache item

```php
// add item to all providers
$manager->set('my_data', 'sample data', 3600);

// get item
$result = $manager->get('my_data');

// get cache source
$provider_id = $manager->getSelectedCache();

```

## Documentation

- [API](https://api.comodojo.org/libs/Comodojo/Cache.html)

## Contributing

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

`` comodojo/cache `` is released under the MIT License (MIT). Please see [License File](LICENSE) for more information.