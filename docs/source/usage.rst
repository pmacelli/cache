Using cache providers
=====================

.. _PSR-6: https://www.php-fig.org/psr/psr-6/
.. _PSR-16: https://www.php-fig.org/psr/psr-16/

Cache providers can be used as a standalone cache interface to most common cache engine.

.. info:: For an updated list of supported engines, please refer to :ref:`cache-providers`.

Each provider is available in two different namespace:

- `Comodojo\Cache\Providers` provides `PSR-6`_-compatible classes
- `Comodojo\SimpleCache\Providers` provides `PSR-16`_-compatible classes

PSR-6 (Caching Interface) usage
-------------------------------

Following a list of common methods offered by each provider. For a detailed description of each method, please refer to the `PSR-6`_ standard.

CRUD operations
...............

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\Cache\Providers\Memory;
    use \Comodojo\Cache\Item;

    // init provider
    $cache = new Memory();

    // create a 'foo' cache item,
    // set its value to "Ford Perfect",
    // declare a ttl of 600 secs
    $item = new Item('foo');
    $item->set('Ford Perfect')
        ->expiresAfter(600);

    // persist item 'foo'
    $cache->save($item);

    // retrieve item 'foo'
    $retrieved = $cache->getItem('foo');
    $hit = $retrieved->isHit(); // returns true

    // update item with value 'Marvin'
    $retrieved->set('Marvin');
    $cache->save($retrieved);

    // delete 'foo'
    $cache->deleteItem('foo');

Write-deferred
..............

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\Cache\Providers\Memory;
    use \Comodojo\Cache\Item;

    // init provider
    $cache = new Memory();

    // create a 'foo' cache item,
    // set its value to "Ford Perfect",
    // declare a ttl of 600 secs
    $item = new Item('foo');
    $item->set('Ford Perfect')
        ->expiresAfter(600);

    // send item 'foo' to cache provider for deferred commit
    $cache->saveDeferred($item);

    // do some other stuff...

    // commit item 'foo'
    $deferred = $cache->commit(); // returns true

Batch operations
................

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\Cache\Providers\Memory;
    use \Comodojo\Cache\Item;

    // init provider
    $cache = new Memory();

    // create two cache items 'foo' and 'boo'
    $foo = new Item('foo');
    $boo = new Item('boo');
    $foo->set('Ford Perfect');
    $boo->set('Marvin');

    // send items to cache provider for deferred commit
    $cache->saveDeferred($foo);
    $cache->saveDeferred($foo);

    // commit items 'foo' and 'boo'
    $deferred = $cache->commit(); // returns true

    // retrieve 'foo' and 'boo'
    $items = $cache->getItems(['foo', 'boo']);

.. note:: `tests/Comodojo/Cache` folder contains several practical examples to learn from.

PSR-16 (Common Interface for Caching Libraries) usage
-----------------------------------------------------

Following a list of common methods offered by each provider. For a detailed description of each method, please refer to the `PSR-16`_ standard.

CRUD operations
...............

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\SimpleCache\Providers\Memory;

    // init provider
    $cache = new Memory();

    // create a 'foo' cache item,
    // set its value to "Ford Perfect",
    // declare a ttl of 600 secs
    $cache->set('foo', 'Ford Perfect', 600);

    // retrieve item 'foo'
    $retrieved = $cache->get('foo');

    // update item with value 'Marvin'
    $cache->set('foo', 'Marvin', 600);

    // delete 'foo'
    $cache->delete('foo');

Managing multiple items
.......................

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\SimpleCache\Providers\Memory;

    // init provider
    $cache = new Memory();

    // create 'foo' and 'boo' cache items
    $cache->setMultiple([
        'foo' => 'Ford Perfect',
        'boo' => 'Marvin'
    ], 600);

    // retrieve items
    $retrieved = $cache->getMultiple(['foo', 'boo']);

.. note:: `tests/Comodojo/SimpleCache` folder contains several practical examples to learn from.

Extended cache functions
------------------------

In both flavours providers offer some extended functions that may be handy in some cases, mantaining compatibility with standards.

State-aware provider implementation
...................................

To handle failure of underlying cache engines, each provider offer a set of methods to know the provider's status.

Status updates are managed seamlessly by provider itself.

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\SimpleCache\Providers\Memcached;

    // init provider
    $cache = new Memcached();

    // get the provider state
    $cache->getState(); //return 0 if everything ok, 1 otherwise
    $cache->getStateTime(); //return a DateTime object containing the reference to the time of state definition

    // test the pool
    $cache->test(); // returns a bool indicating how the test ends and sets the state according to test result

Namespaces support
..................

Each item in cache is placed into a namespace ('GLOBAL' is the default one) and providers can switch from one namespace to another.

In other words, the entire cache space is partitioned by default, and different items can belong to a single partition at a time.

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\SimpleCache\Providers\Memory;

    // init provider
    $cache = new Memory();

    // set (a new) namespace to "CUSTOM"
    $cache->setNamespace('CUSTOM');

    // get the current namespace
    $cache->getNamespace(); //return 'CUSTOM'

    // save an item into 'CUSTOM' namespace
    $cache->set('foo', 'Ford Perfect', 600);

    // move to 'ANOTHER' namespace
    $cache->setNamespace('ANOTHER');

    // try to get back the 'foo' item
    $cache->get('foo'); // returns null: 'foo' is not in 'ANOTHER' namespace!

    // clear the 'ANOTHER' namespace
    $cache->clearNamespace();

    // since 'foo' belongs to 'CUSTOM' namespace, it was not deleted
    $cache->setNamespace('CUSTOM');
    $foo = $cache->get('foo'); // returns 'Ford Perfect'

Cache statistics
................

Stats about current provider can be accessed using the `$provider::getStats` method. It returns a `EnhancedCacheItemPoolStats` object.

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\SimpleCache\Providers\Memory;

    // init provider
    $cache = new Memory();

    // do some stuff with $cache...

    // get statistics about $cache
    $stats = $cache->getStats();

    // get n. of objects in pool
    $num = $stats->getObjects();
