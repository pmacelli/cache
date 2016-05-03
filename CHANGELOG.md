# Changelog

## Version 2.0.0-beta1

- Min php ver to 5.4.0
- No more COMODOJO_CACHE_DEFAULT_TTL constant
- New directory structure
- CacheObject and CacheInterface moved to AbstractProvider and ProviderInterface
- No more COMODOJO_CACHE_FOLDER constant, cache cache folder SHOULD be declared in constructor
- FileCache deprecated, FileSystemProvider in conjunction with CacheManager should be used instead
- ApcCache deprecated, ApcProvider in conjunction with CacheManager should be used instead
- XCacheCache deprecated, XCacheProvider in conjunction with CacheManager should be used instead
- MemcachedCache deprecated, MemcachedProvider in conjunction with CacheManager should be used instead
- PhpRedisCache deprecated, PhpRedisProvider in conjunction with CacheManager should be used instead
- DatabaseCache deprecated, DatabaseProvider in conjunction with CacheManager should be used instead
- No more DB config constant in DatabaseProvider

## Version 1.0.0

- Initial release

## Version 1.0.0-beta1

- Pre-release (stable)