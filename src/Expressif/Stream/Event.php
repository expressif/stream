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
  class Reader extends EventEmitter {

    /**
     * @var boolean Indicates if you can write into the stream
     */
    public $writable = false;
    /**
     * @var boolean Indicates if you can read data from the stream
     */
    public $readable = false;
    /**
     * The current stream
     */
    public $stream;

    private $ev_read;
    private $ev_write;

    /**
     * Initialize a new stream listener
     */
    public function __construct($stream) {

      $this->stream = $stream;
      stream_set_blocking($this->stream, 0);
      $meta = stream_get_meta_data($this->stream);

      if (substr($meta['mode'], -1) === '+') {
        $this->writable = true;
        $this->readable = true;
      } else{
        if (strpos($meta['mode'], 'r') !== false) {
          $this->readable = true;
        }
        if (strpos($meta['mode'], 'w') !== false) {
          $this->writable = true;
        }
      }

      if ($this->readable) {
        $this->ev_read = event_new();
        event_set($this->ev_read, $this->stream, EV_READ | EV_PERSIST, array($this, '_read'));
        Loop::attachEvent($this->ev_read);
      }

      if ($this->writable) {
        $this->ev_write = event_new();
        event_set($this->ev_write, $this->stream, EV_WRITE | EV_PERSIST, array($this, '_write'));
        Loop::attachEvent($this->ev_write);
      }

    }

    /**
     * Free from loop and close the stream
     */
    public function __destruct() {
      if (!empty($this->ev_read)) {
        event_del($this->ev_read);
        event_free($this->ev_read);
        unset($this->ev_read);
      }
      if (!empty($this->ev_write)) {
        event_del($this->ev_write);
        event_free($this->ev_write);
        unset($this->ev_write);
      }
      if (!empty($this->stream)) {
        fclose($this->stream);
        unset($this->stream);
      }
    }

    /**
     * Entry point for read event
     */
    public function _read() {
      if (feof($this->stream)) {
        $this->close();
      } else {
        $this->emit('read', [fread($this->stream, 4048)]);
      }
    }

    /**
     * Writes some data to the stream
     */
    public function write($data) {
      if (!$this->writable) {
        throw new \Exception('The stream is not writable');
      }
      fwrite($this->stream, $data);
      return $this;
    }

    /**
     * Entry point for read event
     */
    public function _write() {
      if (empty($this->stream)) return;
      if (feof($this->stream)) {
        $this->close();
      } else {
        $this->emit('write');
      }
    }

    /**
     * Closing the current stream
     */
    public function close() {
      $this->readable = false;
      $this->writable = false;
      $this->emit('close');
      $this->__destruct();
      return $this;
    }

  }
}