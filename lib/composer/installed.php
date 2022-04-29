<?php return array(
    'root' => array(
        'pretty_version' => '2.0.2',
        'version' => '2.0.2.0',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => NULL,
        'name' => 'cryptum/cryptum-nft-wordpress-plugin',
        'dev' => true,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.0.6',
            'version' => '1.0.6.0',
            'type' => 'composer-installer',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'reference' => 'b3bd071ea114a57212c75aa6a2eef5cfe0cc798f',
            'dev_requirement' => false,
        ),
        'cryptum/cryptum-nft-wordpress-plugin' => array(
            'pretty_version' => '2.0.2',
            'version' => '2.0.2.0',
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
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'symfony/polyfill-mbstring' => array(
            'pretty_version' => 'v1.25.0',
            'version' => '1.25.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-mbstring',
            'aliases' => array(),
            'reference' => '0abb51d2f102e00a4eefcf46ba7fec406d245825',
            'dev_requirement' => false,
        ),
    ),
);
