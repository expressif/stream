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
  class Reader extends Event {

    /**
     * Entry point for read event
     */
    public function _trigger() {
      echo 'trigger !';
      if (feof($this->stream)) {
        $this->close();
      } else {
        $this->emit('data', fread($this->stream, 8192));
      }
    }

  }
}