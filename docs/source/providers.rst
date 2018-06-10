.. _xattr: http://man7.org/linux/man-pages/man5/attr.5.html
.. _Extended Attributes: http://man7.org/linux/man-pages/man5/attr.5.html

Cache providers
===============

Actually this library supports cache over:

- Apc
- Apcu
- Filesystem
- Memcached
- Memory
- Redis
- Vacuum

Each provider offers same functionalities and methods, but class constructors may accept different parameters.

.. note:: Both PSR-6 (Cache) and PSR-16 (SimpleCache) providers share same constructors.

    Following examples can, therefore, be applied in both cases.

Apc & Apcu
----------

Cache items using Alternative PHP Cache or APC User Cache.

.. note:: To enable these providers, apc (apcu_bc) or apcu extensions should be installed and enabled.

    In case of CLI SAPI, remember to add configuration param: `apc.enable_cli => On`

These providers do not accept parameters.

Code example:

.. code:: php

    <?php

    use \Comodojo\Cache\Providers\Apc;
    use \Comodojo\Cache\Providers\Apcu;

    $apcu_cache = new Apcu();
    $apc_cache = new Apc();

Filesystem
----------

This provider will store cached items on filesystem, using ghost-files or `xattr`_ (preferred) drivers to persist metadata.

Following parameters are expected:

- `cache_folder`: location (local or remote) to store file to.

Code example:

.. code:: php

    <?php

    use \Comodojo\Cache\Providers\Filesystem;

    $fs_cache = new Filesystem([
        'cache_folder' => "/my/cache/folder"
    ]);

.. topic:: About filesystem drivers

    This library offers two different filesystem-cache drivers, managed seamlessly from Filesystem provider. Both driver save cached data into single filesystem files, but each one uses a different strategy to save ttl information.

    If available, Filesystem provider will try to save ttl information inside file's `Extended Attributes`_. If not, a ghost file (.expire) will be created near data file (.cache).

    This duplication is made for performance reasons: xattr open/read/close a single file handler, ghost files duplicate the effort saving or reading iformations.

    Using 10k key-value pairs inside a docker container:

    .. code:: bash

        Runtime:       PHP 7.2.3

        > (GHOST) set 10k data: 1.3727879524231 secs
        > (GHOST) check 10k keys: 0.11777091026306 secs
        > (GHOST) get 10k data: 0.18857002258301 secs
        > (GHOST) total test time: 1.6791288852692 secs

        > (XATTR) set 10k data: 0.76364898681641 secs
        > (XATTR) check 10k keys: 0.048287868499756 secs
        > (XATTR) get 10k data: 0.12987494468689 secs
        > (XATTR) total test time: 0.94181180000305 secs

    Using 100k key-value pairs inside a docker container:

    .. code:: bash

        Runtime:       PHP 7.2.3

        > (GHOST) set 10k data: 15.756072998047 secs
        > (GHOST) check 10k keys: 16.93918800354 secs
        > (GHOST) get 10k data: 53.536478996277 secs
        > (GHOST) total test time: 86.231739997864 secs

        > (XATTR) set 10k data: 9.375433921814 secs
        > (XATTR) check 10k keys: 0.55717587471008 secs
        > (XATTR) get 10k data: 1.9446270465851 secs
        > (XATTR) total test time: 11.877236843109 secs

    To recap: in case of ghost file, two files will be created into cache folder for each item:

        - MYITEM-MYNAMESPACE.cache
        - MYITEM-MYNAMESPACE.expire

    The first one will hold data, the second one will mark the ttl.

    In case of xattr support, only one file (.cache) will be created; ttl will be stored into file's attributes and filesystem cache will perform better.

Memcached
---------

Cache items using a memcached instance.

.. note:: To enable this provider, memcached extension should be installed and enabled.

This provider accepts following parameters:

- `server`: (default '127.0.0.1')
- `port`: (default 11211)
- `weight`: (default 0)
- `persistent_id`: (default null)
- `username`: (default null)
- `password`: (default null)

Code example:

.. code:: php

    <?php

    use \Comodojo\Cache\Providers\Memcached;

    $memcached_cache = new Memcached([
        "server" => "memcached.example.com",
        "port" => 11212
    ]);

Memory
------

This provider will hold an array containing cached key value pairs; it does not accept parameters.

Code example:

.. code:: php

    <?php

    use \Comodojo\Cache\Providers\Memory;

    $memory_cache = new Memory();

PhpRedis
---------

Cache items using a redis instance.

.. note:: To enable this provider, redis extension should be installed and enabled.

This provider accepts following parameters:

- `server`: (default '127.0.0.1')
- `port`: (default 6379)
- `timeout`: (default 0)
- `password`: (default null)

Code example:

.. code:: php

    <?php

    use \Comodojo\Cache\Providers\PhpRedis;

    $memcached_cache = new PhpRedis([
        "server" => "redis.example.com",
        "port" => 6378
    ]);

Vacuum
------

This provider will offer a handy way to discard any cached data; in other words, every key-value pair that is cached inside a vacuum provider will be trashed.

This provider does not accept parameters.

Code example:

.. code:: php

    <?php

    use \Comodojo\Cache\Providers\Vacuum;

    $vacuum_cache = new Vacuum();
