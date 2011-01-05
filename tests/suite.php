<?php
/**
 * phpillow couchdb backend test suite
 *
 * @version $Revision$
 * @license GPLv3
 */

// Set up environment
if ( !defined( 'PHPILLOW_TEST_ENV_SET_UP' ) )
{
    require dirname( __FILE__ ) . '/test_environment.php';
}

/**
 * Suites
 */
require_once 'phpillow_suite.php';
require_once 'tool_suite.php';

/**
* Test suite for phpillow
*/
class phpillowTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'phpillow - PHP CouchDB wrapper' );

        $this->addTest( phpillowBackendTestSuite::suite() );
        $this->addTest( phpillowToolTestSuite::suite() );
    }

    /**
     * Return test suite
     * 
     * @return prpTestSuite
     */
    public static function suite()
    {
        return new phpillowTestSuite( __CLASS__ );
    }
}
