<?php
/**
 * Expressif- stream implementation
 * @author Ioan CHIRIAC
 * @license MIT
 */
namespace Expressif\Stream {

  /**
   * A tiny event helper
   */
  class Event {
    private $handlers = [];
    private $forward;

    /**
     * Forwards events to the specified destination
     */
    public function forward($event, event $dest) {
      if (!isset($this->forward[$event])) {
        $this->forward[$event] = [];
      }
      $this->forward[$event][] = $dest;
      return $this;
    }
    /**
     * Listen to the specified event
     */
    public function on($event, $fn) {
      if (!isset($this->handlers[$event])) {
        $this->handlers[$event] = [];
      }
      $this->handlers[$event][] = $fn;
      return $this;
    }

    /**
     * Emits the specified event
     */
    public function emit($event, array $args = []) {
      if (!empty($this->handlers[$event])) {
        foreach($this->handlers[$event] as $fn) {
          if (call_user_func_array($fn, $args) === false) {
            break;
          }
        }
      }
      if (!empty($this->forward[$event])) {
        foreach($this->forward[$event] as $dest) {
          $dest->emit($event, $args);
        }
      }
      return $this;
    }
  }
}