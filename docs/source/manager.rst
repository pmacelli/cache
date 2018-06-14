.. _cache-manager:

Cache Manager
=============

.. _PSR-6: https://www.php-fig.org/psr/psr-6/
.. _PSR-16: https://www.php-fig.org/psr/psr-16/

The Cache Manager component is a state-aware container that can use one or more cache provider at the same time.

In other words, Cache Manager can be configured to use one or more cache providers with a flexible selection strategy (pick algorithm).

.. note:: This library provides two different implementation of manager:

    - `Comodojo\Cache\Manager` (`PSR-6`_)
    - `Comodojo\SimpleCache\Manager` (`PSR-16`_)

Let's consider this example:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\Cache\Manager;
    use \Comodojo\Cache\Providers\Memcached;
    use \Comodojo\Cache\Providers\Memory;

    $manager = new Manager(Manager::PICK_FIRST);

    $memcached_cache = new Memcached();
    $memory_cache = new Memory();

    $manager->addProvider($memcached_cache);
    $manager->addProvider($memory_cache);

    $item = $this->manager->getItem('Ford');

In this example, manager was feeded with two different providers (memcached and memory); according to pick algorithm (PICK_FIRST), the item Ford is retrieved from the first provider in stack (memcached). In case of memcached failure, first provider will be suspended and memory will be used instead.

This is particularly useful to ensure that application will continue to have an active cache layer also if preferred one is failing.

Selection Strategy (Pick Algorithm)
-----------------------------------

Providers are organized placed on a stack and picked according to the selected strategy.

Currently the manager supports six different pick algorithms.

Manager::PICK_FIRST
...................

Select the first (enabled) provider in stack; do not traverse the stack if value is missing.

.. note:: this is the default algorithm.

Manager::PICK_LAST
..................

Select the last (enabled) provider in stack; do not traverse the stack if value is missing.

Manager::PICK_RANDOM
....................

Select a random (enabled) provider in stack; do not traverse the stack if value is missing.

Manager::PICK_BYWEIGHT
......................

Select a provider by weight, stop at first enabled one.

Weight is an integer (tipically 1 to 100); selection is made considering the greather weight of available (and enabled) providers.

Manager::PICK_ALL
.................

Ask to all (enabled) providers and match responses.

This is useful during tests but not really convenient in production because of the latency introduced that increase linearly with number of providers into the stack.

Manager::PICK_TRAVERSE
......................

Select the first (enabled) provider, in case of null response traverse the stack.

Align cache between providers
-----------------------------

By default manager will try to set/update/delete cache items in any active provider. This beaviour is particularly convenient to ensure availability of cache information also in case the master provider fails.

On the other side, cache performances can really get worse: the total number of iteration for a single, atomic transaction will increase linearly with the number of providers defined into the stack.

This feature can be disabled during class init:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\Cache\Manager;
    use \Comodojo\Cache\Providers\Memcached;
    use \Comodojo\Cache\Providers\Memory;

    // init the manager
    // PICK_FIRST strategy
    // null logger
    // do not align cache between providers
    $manager = new Manager(Manager::PICK_FIRST, null, false);

Using the Manager
-----------------

The manager is itself a provider, therefore can be used like any other `PSR-6`_ or `PSR-16`_ provider. It also supports :ref:`extended-features`.

Just to make a working example:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\Cache\Manager;
    use \Comodojo\Cache\Providers\Memcached;
    use \Comodojo\Cache\Providers\Memory;

    // init the manager
    // PICK_FIRST strategy
    $manager = new Manager(Manager::PICK_BYWEIGHT);

    // push two providers to manager's stack
    // memcached will be the preferred provider due to its weight
    $memcached_cache = new Memcached();
    $memory_cache = new Memory();
    $manager->addProvider($memcached_cache, 100);
    $manager->addProvider($memory_cache, 10);

    // create a 'foo' cache item,
    // set its value to "Ford Perfect",
    // declare a ttl of 600 secs
    $item = new Item('foo');
    $item->set('Ford Perfect')
        ->expiresAfter(600);

    // item 'foo' will be saved in both providers
    $manager->save($item);

    // retrieve item 'foo' from preferred provider
    $retrieved = $manager->getItem('foo');
    $hit = $retrieved->isHit(); // returns true

    // update item with value 'Marvin'
    // since the align_cache flag was leaved to default (true), the update operation will be performed into both providers
    $retrieved->set('Marvin');
    $manager->save($retrieved);

    // delete 'foo'
    $manager->deleteItem('foo');
    // item is deleted from both providers
