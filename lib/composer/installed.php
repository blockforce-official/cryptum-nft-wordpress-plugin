<?php return array(
    'root' => array(
        'pretty_version' => '2.0.4',
        'version' => '2.0.4.0',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => NULL,
        'name' => 'cryptum/cryptum-nft-wordpress-plugin',
        'dev' => true,
    ),
    'versions' => array(
        'cryptum/cryptum-nft-wordpress-plugin' => array(
            'pretty_version' => '2.0.4',
            'version' => '2.0.4.0',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => NULL,
            'dev_requirement' => false,
        ),
        'kornrunner/keccak' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'type' => 'library',
            'install_path' => __DIR__ . '/../kornrunner/keccak',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'reference' => 'c22f0e95c900d08d9a20f30122de948c73ba16c2',
            'dev_requirement' => false,
        ),
        'symfony/polyfill-mbstring' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-mbstring',
            'aliases' => array(
                0 => '1.26.x-dev',
            ),
            'reference' => '9344f9cb97f3b19424af1a21a3b0e75b0a7d8d7e',
            'dev_requirement' => false,
        ),
    ),
);
