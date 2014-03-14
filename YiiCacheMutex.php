<?php

class YiiCacheMutex extends CApplicationComponent {

    public $cacheName   = 'cache';
    public $sleepTime   = 50;
    public $expireTime  = 300;

    protected $cache = null;
    protected $acqiured = array();

    public function init()
    {
        if (!Yii::app()->hasComponent($this->cacheName))
            throw new CException('Cannot find component '.$this->cacheName);

        $this->cache = Yii::app()->getComponent($this->cacheName);
        if (!($this->cache instanceof ICache))
            throw new CException('Cache component must implements ICache interface');

        parent::init();
    }

    protected function getLockName($name)
    {
        return 'lock:'.$name;
    }

    protected function getLockInfo()
    {
        return true;
    }

    public function acquire($name, $blocking = true, $timeout = null)
    {
        if (isset($this->acqiured[$name])) return true;

        if ($timeout !== null) {
            $endTime = microtime(true) + $timeout;
        }

        $lockName = $this->getLockName();
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

    public function release($name)
    {
        if (!isset($this->acqiured[$name])) return false;

        $lockName = $this->getLockName($name);

        $this->cache->delete($lockName);
        unset($this->acqiured[$name]);

        return true;
    }

}