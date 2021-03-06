<?php
/**
 * Expressif- stream implementation
 * @author Ioan CHIRIAC
 * @license MIT
 */
namespace Expressif\Stream {

  /**
   * Timer wrapper
   */
  class Timer {

    protected $event;
    protected $interval;
    protected $fn;

    /**
     * Initialize a new stream listener
     */
    public function __construct($fn, $interval) {
      $this->fn = $fn;
      $this->interval = $interval;
      $this->event = event_timer_new();
      event_timer_set($this->event, array($this, 'tick'));
      Loop::attachEvent($this->event, $this->interval * 1000);
    }

    /**
     * Ticks at the specified interval
     */
    public function tick() {
      call_user_func_array($this->fn, array($this));
      if (!empty($this->event)) {
        event_add($this->event, $this->interval * 1000);
      }
    }

    /**
     * Free from loop
     */
    public function __destruct() {
      if (!empty($this->event)) {
        event_del($this->event);
        event_free($this->event);
        unset($this->event);
      }
    }

  }
}