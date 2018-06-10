Installation
============

.. highlight:: php

.. _cache: https://github.com/comodojo/cache
.. _composer: https://getcomposer.org/
.. _install composer: https://getcomposer.org/doc/00-intro.md

First `install composer`_, then:

.. code:: bash

    composer require comodojo/cache ^2.0

Requirements
************

To work properly, comodojo/cache requires PHP >=5.6.0.

Some package is optional but reccomended:

- ext-xattr: Fastest cache files handling via extended attributes
- ext-redis: Enable redis provider
- ext-memcached: Enable Memcached provider
- ext-apc: Enable Apc provider (apcu_bc also supported)
- ext-apcu: Enable Apcu provider
