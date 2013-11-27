RatchetModelPush
================

[![Build Status](https://travis-ci.org/WyriHaximus/RatchetModelPush.png)](https://travis-ci.org/WyriHaximus/RatchetModelPush)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/RatchetModelPush/v/stable.png)](https://packagist.org/packages/WyriHaximus/RatchetModelPush)
[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/WyriHaximus/ratchetmodelpush/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

Model Push for the [CakePHP Ratchet plugin](http://wyrihaximus.net/projects/cakephp/ratchet/).

## What is Ratchet ##

Ratchet for CakePHP brings [the Ratchet websocket](http://socketo.me/) server to CakePHP. Websockets allow you to utilize near-real-time communication between your application and it's visitors. For example notifying a page the associated record in the database has been updated using the [Pushable behaviour](http://wyrihaximus.net/projects/cakephp/ratchet/documentation/model-push.html).

## Getting started ##

#### 1. Requirements ####

This plugin depends on the following plugins and their dependencies and are pulled in by composer later on:

- [Ratchet](https://github.com/Wyrihaximus/Ratchet)
- [RatchetCommands](https://github.com/WyriHaximus/RatchetCommands)

#### 2. Composer ####

Make sure you have [composer](http://getcomposer.org/) installed and configured with the autoloader registering during bootstrap as described [here](http://ceeram.github.io/blog/2013/02/22/using-composer-with-cakephp-2-dot-x/). Make sure you have a composer.json and add the following to your required section.

```json
"wyrihaximus/ratchet-model-push": "dev-master"
```

When you've set everything up, run `composer install`.

#### 3. Setup the plugin ####

Make sure you load `RatchetModelPush` and all the plugins it depends on and their dependecies in your bootstrap and set then up properly.

#### 4. Setting up the behavior ####

The following code example attaches the Pushable behavior to the `WyriProject` model. The events do the following in order:

1. Fires on creating on a new record and binds to `WyriProject.created`.
2. Fires on updating any record and binds to `WyriProject.updated`.
3. Fires on any updating record and binds to `WyriProject.updated.{id}` where `{id}` is the `id` for that record. So if `id` is `1` it binds to `WyriProject.updated.1`.

The data is passed into the event as a 1 dimensional array.

```php
<?php

class Model extends AppModel {
    public $actsAs = array(
        'Ratchet.Pushable' => array(
            'events' => array(
                array(
                    'eventName' => 'WyriProject.created',
                    'created' => true,
                ),
                array(
                    'eventName' => 'WyriProject.updated',
                    'created' => false,
                ),
                array(
                    'eventName' => 'WyriProject.updated.{id}',
                    'created' => false,
                    'fields' => true,
                ),
            ),
        ), 
    );
}
```

#### 5. Client side ####

On the client side the only thing required is subscribing to the event:

```javascript
cakeWamp.subscribe('Rachet.WampServer.ModelUpdate.WyriProject.updated.1', function(topicUri, event) {
	// Do your stuff with the data
});
```

*Note: the actual event you're subscribing to is prefixed by *`Rachet.WampServer.ModelUpdate.`*.*
