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

    protected $event;
    protected $stream;

    /**
     * Initialize a new stream listener
     */
    public function __construct($stream) {
      $this->stream = $stream;
      stream_set_blocking($this->stream, 0);
      $this->event = event_new();
      event_set($this->event, $this->stream, EV_READ | EV_PERSIST, array($this, 'emit'), 'read');
      event_base_set($this->event, Loop::$instance->base);
      event_add($this->event);
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