<?php

// Set up PHPillow autoloading
include dirname( __FILE__ ) . '/../src/bootstrap.php';

// Set up database connection with default parameters
phpillowConnection::createInstance();

// Set database, which will be used and get connection ahndler
$db = phpillowConnection::getInstance();
phpillowConnection::setDatabase( 'test' );

// Delete maybe existing database, ignroing the error, if it does not exist 
// yet.
try {
    $db->delete( '/test' );
} catch ( Exception $e ) { /* Ignore */ }

// Create new database
$db->put( '/test' );

// Create a new document of the predefined user class
$doc = new phpillowUserDocument();
$doc->login = 'kore';
$doc->name = 'Kore Nordmann';
$docId = $doc->save();

// Fetch the document from the database
$doc = new phpillowUserDocument();
$doc->fetchById( $docId );

// Update the document
$doc->email = 'kore@php.net';
$doc->save();

// Fetch the document againa and dump the revisions
$doc = new phpillowUserDocument();
$doc->fetchById( $docId );

var_dump( $doc->revisions );

