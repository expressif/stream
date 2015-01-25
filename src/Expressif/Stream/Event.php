<?php
/**
 * Expressif- stream implementation
 * @author Ioan CHIRIAC
 * @license MIT
 */
namespace Expressif\Stream {

  /**
   * Event wrapper
   */
  class Event extends EventEmitter {

    public $event;
    public $stream;
    protected $flags = EV_READ | EV_PERSIST;
    /**
     * Initialize a new stream listener
     */
    public function __construct($stream) {
      $this->stream = $stream;
      stream_set_blocking($this->stream, 0);
      $this->event = event_new();
      event_set($this->event, $this->stream, $this->flags, array($this, '_trigger'));
      Loop::attachEvent($this->event);
    }

    /**
     * Free from loop
     */
    public function __destruct() {
      if (!empty($this->event)) {
        event_del($this->event);
        event_free($this->event);
        unset($this->stream, $this->event);
      }
    }

    /**
     * Entry point for read event
     */
    public function _trigger() {
      if (feof($this->stream)) {
        $this->close();
      } else {
        $this->emit('trigger');
      }
    }


    /**
     * Closing the current stream
     */
    public function close() {
      $this->emit('close');
      fclose($this->stream);
      $this->__destruct();
      return $this;
    }

  }
}