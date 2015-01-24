<?php

// testing the event loop
require __DIR__ . '/../vendor/autoload.php';
$loop = Expressif\Stream\Loop::$instance;
$i = 1;
$loop->setInterval(function($timer) use(&$i, $loop) {
  echo "$i sec\n";
  $i++;
  if ($i > 10) {
    $loop->clearInterval($timer);
  }
}, 1000);
