# Streams with libevent

Evented streams wrapper (used by expressif/http and expressif/cluster)

## Requirements

- php 5.6+
- libevent 0.1.0

## Installation

Get the libevent library from pecl :
http://php.net/manual/fr/libevent.installation.php

Note : For windows you can download libraries from here :
https://github.com/expressif/win-dist

Add this lib as a dependency `composer require expressif/stream`

## Usage

### EventEmitter

This class handles callback listeners and events emission :

```
<?php
  require 'vendor/autoload.php';
  use Expressif\Stream\EventEmitter;

  class Foo extends EventEmitter {
    public function bar() {
      $this->emit('bar', ['baz']);
    }
  }

  $foo = new Foo();
  $foo->on('bar', function($what) {
    echo "Foo $what !\n";
  });
  $foo->bar();
```

### Timers

This helper provides a way to periodically executes specified code :

```
<?php
  require 'vendor/autoload.php';
  use Expressif\Stream\Loop;
  Loop::setInterval(function() {
    echo 'Now is ' . date('H:i:s') . "\n";
  }, 1000);
```

### Buffers

This class handles buffered reads and writes :

```
<?php
  require 'vendor/autoload.php';
  use Expressif\Stream\Loop;

  $buffer = Loop::buffer('tcp://173.194.66.104:80');
  $buffer->read(function($response) {
    echo '<- ' . $response;
  });
  $buffer->on('write', function() {
    echo "-> Request sent\n";
  });
  $buffer->on('close', function() {
    echo "*** response is finished ***\n";
  });
  $buffer->write("GET / HTTP/1.0\r\nHost: www.google.com\r\n\r\n");
```