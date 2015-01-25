<?php

// testing the event loop
require __DIR__ . '/../vendor/autoload.php';
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

$i = 1;
Loop::setInterval(function($timer) use(&$i) {
  echo "$i sec\n";
  $i++;
  if ($i > 10) {
    Loop::clearInterval($timer);
  }
}, 1000);
