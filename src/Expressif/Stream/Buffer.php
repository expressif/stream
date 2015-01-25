<?php
/**
 * Expressif- stream implementation
 * @author Ioan CHIRIAC
 * @license MIT
 */
namespace Expressif\Stream {

  /**
   * Buffer wrapper
   */
  class Buffer extends EventEmitter {

    public $event;
    public $stream;

    /**
     * Initialize a new stream listener
     */
    public function __construct($stream) {
      $this->stream = $stream;
      stream_set_blocking($this->stream, 0);
      $this->event = event_buffer_new($this->stream, array($this, '_read'), array($this, '_write'), array($this, '_error'));
      Loop::attachBuffer($this);
      event_buffer_timeout_set($this->event, 2, 5);
      event_buffer_watermark_set($this->event, EV_READ, 0, 0xffffff);
      event_buffer_priority_set($this->event, 10);
      event_buffer_enable($this->event, EV_READ | EV_PERSIST);
    }

    /**
     * Free from loop
     */
    public function __destruct() {
      if (!empty($this->event)) {
        event_buffer_disable($this->event, EV_READ | EV_WRITE);
        event_buffer_free($this->event);
        unset($this->stream, $this->event);
      }
    }

    /**
     * Listen to read events
     * @alias on('data', $fn)
     */
    public function read(callable $fn) {
      return $this->on('data', $fn);
    }

    /**
     * Internal function for reading an event
     */
    public function _read() {
      $buffer = '';
      while ($read = event_buffer_read($this->event, 1024)) {
          $buffer .= $read;
      }
      if (!empty($buffer)) {
        $this->emit('data', array($buffer));
      }
    }

    /**
     * Internal function trigger a write event
     */
    public function _write() {
      $this->emit('write');
    }

    /**
     * Internal function for intercepting an error
     */
    public function _error($buffer, $error) {
      $this->emit('error', array($error));
      return $this->close();
    }

    /**
     * Sends some data
     */
    public function write($data) {
      event_buffer_write($this->event, $data);
      return $this;
    }

    /**
     * Closing the current connection
     */
    public function close() {
      $this->emit('close');
      fclose($this->stream);
      $this->__destruct();
      return $this;
    }

  }
}