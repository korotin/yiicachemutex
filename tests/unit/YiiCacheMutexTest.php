<?php

class YiiCacheMutexTest extends CTestCase {
    
    protected function createCM(array $params = array())
    {
        $cm = new YiiCacheMutex;
        foreach ($params as $k => $v) {
            $cm->$k = $v;
        }
        $cm->init();

        return $cm;
    }

    public function tearDown()
    {
        passthru('rm -rf '.escapeshellarg(Yii::app()->fileCache->cachePath).'/*');
    }

    public function cacheNames()
    {
        return array(
            array('memcacheCache'),
            array('memcachedCache'),
            array('fileCache'),
        );
    }

    /**
     * @dataProvider cacheNames
     */
    public function testBlockingMutex($cacheName)
    {
        $params = array(
            'cacheName' => $cacheName
        );
        $mutexName = 'test'.rand();

        $cm1 = $this->createCM($params);
        $cm2 = $this->createCM($params);

        $this->assertTrue($cm1->acquire($mutexName), 'Failed to acquire lock '.$mutexName);
        $this->assertTrue($cm1->acquire($mutexName), 'Failed to acquire self lock '.$mutexName);

        $startTime = microtime(true);
        $this->assertFalse($cm2->acquire($mutexName, true, 500000), 'Acquired foreign lock '.$mutexName);
        $this->assertGreaterThanOrEqual(0.5, microtime(true) - $startTime, 'Acquiring foreign lock '.$mutexName.' didnt block thread for 0.5s');
        
        $this->assertTrue($cm1->release($mutexName), 'Failed to release own lock '.$mutexName);
        $this->assertTrue($cm2->acquire($mutexName, true, 500000), 'Failed to acquire released lock '.$mutexName);
        $this->assertTrue($cm2->release($mutexName), 'Failed to release own lock '.$mutexName);
    }

    /**
     * @dataProvider cacheNames
     */
    public function testNonBlockingMutex($cacheName)
    {
        $params = array(
            'cacheName' => $cacheName
        );
        $mutexName = 'test'.rand();

        $cm1 = $this->createCM($params);
        $cm2 = $this->createCM($params);

        $this->assertTrue($cm1->acquire($mutexName), 'Failed to acquire lock '.$mutexName);
        $this->assertTrue($cm1->acquire($mutexName), 'Failed to acquire self lock '.$mutexName);

        $this->assertFalse($cm2->acquire($mutexName, false), 'Acquired foreign lock '.$mutexName);
        
        $this->assertTrue($cm1->release($mutexName), 'Failed to release own lock '.$mutexName);
        $this->assertTrue($cm2->acquire($mutexName, false), 'Failed to acquire released lock '.$mutexName);
        $this->assertTrue($cm2->release($mutexName), 'Failed to release own lock '.$mutexName);
    }

}