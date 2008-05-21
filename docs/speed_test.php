<?php

require_once __DIR__ . '/../../src/environment.php';

$puts = 1000;
$gets = 5000;
$views = 2000;

// Set up backend connection
phpillowConnection::createInstance();
phpillowConnection::setDatabase( 'test' );
$db = phpillowConnection::getInstance();

try {
    $db->delete( '/test' );
} catch ( Exception $e ) { /* Ignore */ }
$db->put( '/test' );

// /*
$start = microtime( true );
for ( $i = 0; $i < $puts; ++$i )
{
    $doc = phpillowManager::createDocument( 'user' );
    $doc->login = 'kore_' . $i;
    $doc->name = 'Kore Nordmann';
    $doc->save();
}
printf( "%d PUTs in %.2fs (%d req/s)\n",
    $puts,
    $time = microtime( true ) - $start,
    $puts / $time
); // */

$start = microtime( true );
for ( $i = 0; $i < $gets; ++$i )
{
    $doc = phpillowManager::fetchDocument( 'user', 'user-kore_0' );
}
printf( "%d GETs in %.2fs (%d req/s)\n",
    $gets,
    $time = microtime( true ) - $start,
    $gets / $time
);

$start = microtime( true );
$doc = phpillowUserView::user( array( 'key' => 'kore_0' ) );
printf( "First view in %.2fs (%d req/s)\n",
    $time = microtime( true ) - $start,
    1 / $time
);

$start = microtime( true );
for ( $i = 0; $i < $views; ++$i )
{
    $doc = phpillowUserView::user( array( 'key' => 'kore_0' ) );
}
printf( "%d views in %.2fs (%d req/s)\n",
    $views,
    $time = microtime( true ) - $start,
    $views / $time
);

// Cleanup
try {
    $db->delete( '/test' );
} catch ( Exception $e ) { /* Ignore */ }

