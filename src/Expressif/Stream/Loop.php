<?php/** * Expressif- stream implementation * @author Ioan CHIRIAC * @license MIT */namespace Expressif\Stream {  class Loop {    /**     * The main loop instance     */    public static $instance;    /**     * Gets the loop status     */    private $run = false;    /**     * List of attached items     */    private $items;    /**     * The event base     */    public $base;    /**     * Initialise a new loop     */    public function __construct() {      if (!empty(self::$instance)) {        throw new \Exception(          'A loop is already instanciated, use Loop::$instance to get it'        );      }      $this->base = event_base_new();      $this->items = new \SplObjectStorage();    }    /**     * Calls the specified callback at each interval (ms)     */    public function setInterval($fn, $interval) {      $e = new Timer($fn, $interval);      $this->items->attach($e);      return $e;    }    /**     * Clear the specified interval     */    public function clearInterval(Timer $timer) {      $this->items->detach($timer);      $timer->__destruct();      return $this;    }    /**     * Attach the specified callback on the specified descriptor     * @return Event     */    public function stop() {      $this->run = false;      event_base_loopexit($this->base, 1000);      return $this;    }    /**     * Check if the loop is started     */    public function isStarted() {      return $this->run;    }    /**     * Starts the event loop     */    public function start() {      $this->run = true;      while($this->run) {        if (event_base_loop($this->base, EVLOOP_ONCE) === 1) {          break; // loop is empty        }      }      $this->run = false;    }  }  // MAGIC ! automatically run after the script was initialized  define('IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');  Loop::$instance = new Loop();  register_shutdown_function(function() {    if (Loop::$instance && !Loop::$instance->isStarted()) {      Loop::$instance->start();    }  });}