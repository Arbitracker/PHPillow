<?php
/**
 * arbit couchdb backend test suite
 *
 * @version $Revision$
 * @license GPLv3
 */

// Set up environment
require __DIR__ . '/test_environment.php';

/**
 * Couchdb backend tests
 */
require 'phpillow/data_test.php';

require 'phpillow/connection_tests.php';
require 'phpillow/manager_tests.php';

require 'phpillow/validator_tests.php';
require 'phpillow/document_validator_tests.php';

require 'phpillow/document_tests.php';
require 'phpillow/document_user_tests.php';
require 'phpillow/document_group_tests.php';

require 'phpillow/view_tests.php';
require 'phpillow/view_user_tests.php';
require 'phpillow/view_group_tests.php';

/**
* Test suite for arbit
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

        $this->addTest( phpillowConnectionTests::suite() );
        $this->addTest( phpillowManagerTests::suite() );

        $this->addTest( phpillowValidatorTests::suite() );
        $this->addTest( phpillowDocumentValidatorTests::suite() );

        $this->addTest( phpillowDocumentTests::suite() );
        $this->addTest( phpillowDocumentUserTests::suite() );
        $this->addTest( phpillowDocumentGroupTests::suite() );

        $this->addTest( phpillowViewTests::suite() );
        $this->addTest( phpillowUserViewTests::suite() );
        $this->addTest( phpillowGroupViewTests::suite() );
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
