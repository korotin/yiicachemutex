YiiCacheMutex
=============

**YiiCacheMutex** is a mutex implementation for Yii framework based on Yii cache component.

Usage
-----

First, copy files from this repo to `extensions/yiicachemutex` and add **YiiCacheMutex** to Yii application:

```php
return array(
...
    'components'=>array(
        ...
        'cacheMutex' => array(
            'class' => 'ext.yiicachemutex.YiiCacheMutex',
            // Cache component name.
            // Useful when you want to use separate cache for mutexes.
            'cacheName' => 'cache',
            // Time in microseconds to sleep between cache pollings.
            'sleepTime' => 100,
            // Time in seconds for mutex to expire.
            // If set to 0 mutex will never expire but this is not recommended.
            'expireTime' => 300,
        ),
        ...
    ),
...
);
```

After that you can create mutexes by calling `Yii::app()->cacheMutex->acqiure()` and release them by calling `Yii::app()->cacheMutex->release()`.
Mutex acquiring may be blocking or nonblocking. For blocking acquiring a timeout may be set. Also mutexes may have expire time which is highly recommended to avoid deadlocks.

Usage examples:

```php
// Get mutex 'test' if it's free, otherwise wait for it's release forever.
Yii::app()->cacheMutex->acquire('test');

// Get mutex 'test' if it's free, otherwise return false immeditely.
Yii::app()->cacheMutex->acquire('test', 0);

// Wait 0.5s for mutex 'test' to be released.
Yii::app()->cacheMutex->acquire('test', 500000);

// Release acquired by current thread mutex 'test'.
Yii::app()->cacheMutex->release('test');
```