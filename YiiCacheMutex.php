<?php
/**
 * YiiCacheMutex
 *
 * Mutex implementation based on Yii cache component.
 * 
 * @author  Martin Stolz <herr.offizier@gmail.com>
 * @package ext.yiicachemutex
 */

class YiiCacheMutex extends CApplicationComponent {

    /**
     * Cache component name.
     * Default value is 'cache', but you can use component with different name
     * if you want to create separate cache for mutexes.
     * 
     * @var string
     */
    public $cacheName   = 'cache';

    /**
     * Sleep interval in microseconds between cache pollings.
     * 
     * @var integer
     */
    public $sleepTime   = 100;

    /**
     * Interval in seconds for mutex to expire. 
     * If set to 0 mutex will never expire but this is not recommended.
     * 
     * @var integer
     */
    public $expireTime  = 300;

    /**
     * Cache object.
     * 
     * @var CCache
     */
    protected $cache = null;

    /**
     * Mutexes acquired by current thread.
     * 
     * @var array
     */
    protected $acqiured = array();

    public function init()
    {
        if (!Yii::app()->hasComponent($this->cacheName))
            throw new CException('Cannot find component '.$this->cacheName);

        $this->cache = Yii::app()->getComponent($this->cacheName);
        if (!($this->cache instanceof ICache))
            throw new CException('Cache component must implement ICache interface');

        parent::init();
    }

    /**
     * Cache item key for given lock name.
     * 
     * @param  string $name
     * @return string
     */
    protected function getLockName($name)
    {
        return 'lock:'.$name;
    }

    /**
     * Lock info stored into cache.
     * 
     * @return mixed
     */
    protected function getLockInfo()
    {
        return true;
    }

    /**
     * Acqiure mutex with given name.
     * 
     * @param  string  $name
     * @param  boolean $blocking if false, false will be returned immediately if mutex if owned by another thread
     * @param  int  $timeout     if not null, maximum time in microseconds to wait for acquiring
     * @return bool
     */
    public function acquire($name, $blocking = true, $timeout = null)
    {
        if (isset($this->acqiured[$name])) return true;

        if ($timeout !== null && $timeout > 0) {
            $endTime = microtime(true) + $timeout / 1000000;
        }
        else {
            $timeout = null;
        }

        $lockName = $this->getLockName($name);
        $info = $this->getLockInfo();

        while ($timeout === null || microtime(true) < $endTime) {
            if ($this->cache->add($lockName, $info, $this->expireTime)) {
                $this->acqiured[$name] = true;
                return true;
            }

            if (!$blocking) return false;

            usleep($this->sleepTime);
        }

        return false;
    }

    /**
     * Release acquired mutex.
     * 
     * @param  string $name
     * @return bool
     */
    public function release($name)
    {
        if (!isset($this->acqiured[$name])) return false;

        $lockName = $this->getLockName($name);

        $this->cache->delete($lockName);
        unset($this->acqiured[$name]);

        return true;
    }

}