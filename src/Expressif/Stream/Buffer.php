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

    protected $buffer;
    protected $stream;

    /**
     * Initialize a new stream listener
     */
    public function __construct($stream) {
      $this->stream = $stream;
      stream_set_blocking($this->stream, 0);
      $this->buffer = event_buffer_new($this->stream, array($this, '_read'), NULL, array($this, '_error'));
      event_buffer_base_set($this->buffer, Loop::$instance->base);
      event_buffer_timeout_set($this->buffer, 2, 5);
      event_buffer_watermark_set($this->buffer, EV_READ, 0, 0xffffff);
      event_buffer_priority_set($this->buffer, 10);
      event_buffer_enable($this->buffer, EV_READ | EV_PERSIST);
    }

    /**
     * Free from loop
     */
    public function __destruct() {
      if (!empty($this->buffer)) {
        event_buffer_disable($this->buffer, EV_READ | EV_WRITE);
        event_buffer_free($this->buffer);
        unset($this->stream, $this->buffer);
      }
    }

  /**
   * Internal function for reading an event
   */
  public function _read() {
    $buffer = '';
    while ($read = event_buffer_read($this->buffer, 1024)) {
        $buffer .= $read;
    }
    if (!empty($buffer)) {
      $this->emit('data', array($buffer));
    }
  }

  /**
   * Internal function for intercepting an error
   */
  public function _error($buffer, $error) {
    $this->emit('error', array($error));
    return $this->close();
  }

  public function send($data) {
    fwrite($this->stream, $data);
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