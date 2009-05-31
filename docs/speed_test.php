<?php

// Include all required classes
$autoload = require ( $base = dirname( __FILE__ ) . '/../src/' ) . 'classes/autoload.php';
foreach ( $autoload as $file )
{
    require_once $base . $file;
}

// Configure parameters for speed testing
$puts = 100;
$gets = 500;
$views = 200;

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
    $doc = new phpillowUserDocument();
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
    $doc = new phpillowUserDocument( 'user-kore_0' );
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

