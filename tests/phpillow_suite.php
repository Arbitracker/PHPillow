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
 * Couchdb backend tests
 */
require 'phpillow/data_test.php';

require 'phpillow/connection_tests.php';
require 'phpillow/stream_connection_tests.php';
require 'phpillow/custom_connection_tests.php';
require 'phpillow/manager_tests.php';

require 'phpillow/validator_tests.php';
require 'phpillow/document_validator_tests.php';

require 'phpillow/document_tests.php';
require 'phpillow/document_aggregate_tests.php';
require 'phpillow/attachment_tests.php';
require 'phpillow/document_user_tests.php';
require 'phpillow/document_group_tests.php';

require 'phpillow/view_tests.php';
require 'phpillow/file_view_tests.php';
require 'phpillow/view_user_tests.php';
require 'phpillow/view_group_tests.php';

/**
* Test suite for phpillow
*/
class phpillowBackendTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'Database backend tests' );

        $this->addTest( phpillowConnectionTests::suite() );
        $this->addTest( phpillowStreamConnectionTests::suite() );
        $this->addTest( phpillowCustomConnectionTests::suite() );
        $this->addTest( phpillowManagerTests::suite() );

        $this->addTest( phpillowValidatorTests::suite() );
        $this->addTest( phpillowDocumentValidatorTests::suite() );

        $this->addTest( phpillowDocumentTests::suite() );
        $this->addTest( phpillowDocumentAggregateTests::suite() );
        $this->addTest( phpillowDocumentAttachmentTests::suite() );
        $this->addTest( phpillowDocumentUserTests::suite() );
        $this->addTest( phpillowDocumentGroupTests::suite() );

        $this->addTest( phpillowFileViewTests::suite() );
        $this->addTest( phpillowViewTests::suite() );
        $this->addTest( phpillowUserViewTests::suite() );
        $this->addTest( phpillowGroupViewTests::suite() );
    }

    /**
     * Return test suite
     *
     * @return phpillowBackendTestSuite
     */
    public static function suite()
    {
        return new phpillowBackendTestSuite( __CLASS__ );
    }
}
