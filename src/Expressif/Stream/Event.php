<?php
/**
 * Expressif- stream implementation
 * @author Ioan CHIRIAC
 * @license MIT
 */
namespace Expressif\Stream {

  /**
   * Listen on the specified descriptor
   */
  class Event {
    private $fd;
    private $events = [];
    /**
     * Initialise the event with the specified descriptor
     */
    public function __construct($fd) {
      $this->fd = $fd;
    }
    public function __destruct() {
      $this->close();
    }

    /**
     * Closing the current stream
     */
    public function close() {
      foreach($this->events as $ev) {
        Loop::$instance->clear($ev);
      }
      if (!feof($this->fd)) {
        fclose($this->fd);
      }
      unset($this->events);
      unset($this->fd);
    }

    /**
     * Listen the read event
     */
    public function onRead($fn) {
      $this->events[] = Loop::$instance->onRead($this->fd, $fn);
      return $this;
    }
    /**
     * Listen the write event
     */
    public function onWrite($fn) {
      $this->events[] = Loop::$instance->onWrite($this->fd, $fn);
      return $this;
    }
  }