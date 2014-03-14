<?php

return array(
    'basePath' => __DIR__.'/..',
    'extensionPath' => __DIR__.'/../../',
    'import'=>array(
        'ext.*',
    ),

    'components' => array(
        'memcacheCache' => array(
            'class' => 'system.caching.CMemCache',
        ),

        'memcachedCache' => array(
            'class' => 'system.caching.CMemCache',
            'useMemcached' => true,
        ),

        'fileCache' => array(
            'class' => 'system.caching.CFileCache',
            'cachePath' => __DIR__.'/../cache/',
        ),
    ),
);