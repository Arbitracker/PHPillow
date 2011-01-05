<?php
/**
 * Basic test cases for model connections
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowStreamConnectionTests extends PHPUnit_Framework_TestCase
{
    /**
     * Return test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
	public static function suite()
	{
		return new PHPUnit_Framework_TestSuite( __CLASS__ );
	}

    /**
     * Check that curl-wrappers are enabled, test cases fail otherwise.
     *
     * @return void
     */
    public function setUp()
    {
        ob_start();
        phpinfo();
        if ( strpos( ob_get_clean(), 'curlwrappers' ) === false )
        {
            $this->markTestSkipped( 'Enable --with-curlwrappers to run this test.' );
        }
    }

    /**
     * Reset database connection after each test run
     *
     * @return void
     */
    public function tearDown()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'handler' => 'phpillowStreamConnection' ) );
        phpillowTestEnvironmentSetup::resetTmpDir();
    }

    public function testNoConnectionPossible()
    {
        phpillowStreamConnection::createInstance( '127.0.0.1', 12345 );
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->get( '/test' );
            $this->fail( 'Expected phpillowConnectionException.' );
        }
        catch ( phpillowConnectionException $e )
        {
            $this->assertTrue(
                // Message depends on whether the internal stream wrapper or
                // the curlwrappers are used
                $e->getMessage() === 'Could not connect to server at 127.0.0.1:12345: fopen(http://127.0.0.1:12345/test): failed to open stream: operation failed' ||
                $e->getMessage() === 'Could not connect to server at 127.0.0.1:12345: fopen(http://127.0.0.1:12345/test): failed to open stream: Connection refused'
            );
        }
    }

    public function testCreateDatabase()
    {
        phpillowStreamConnection::createInstance();
        $db = phpillowConnection::getInstance();

        $response = $db->put( '/test' );

        $this->assertTrue(
            $response instanceof phpillowStatusResponse
        );

        $this->assertSame(
            true,
            $response->ok
        );
    }

    public function testForErrorOnDatabaseRecreation()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->put( '/test' );
            $this->fail( 'Expected phpillowResponseErrorException.' );
        }
        catch ( phpillowResponseErrorException $e )
        {
            $this->assertSame(
                array(
                    'error'  => 'file_exists',
                    'reason' => 'The database could not be created, the file already exists.',
                ),
                $e->getResponse()
            );
        }
    }

    public function testGetDatabaseInformation()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        $response = $db->get( '/' );

        $this->assertTrue(
            $response instanceof phpillowResponse
        );

        $this->assertSame(
            'Welcome',
            $response->couchdb
        );
    }

    public function testAddDocumentToDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        $response = $db->put( '/test/123', '{"_id":"123","data":"Foo"}' );

        $this->assertTrue(
            $response instanceof phpillowStatusResponse
        );

        $this->assertSame(
            true,
            $response->ok
        );
    }

    public function testGetAllDocsFormDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        $response = $db->put( '/test/123', '{"_id":"123","data":"Foo"}' );
        $response = $db->get( '/test/_all_docs' );

        $this->assertTrue(
            $response instanceof phpillowResultSetResponse
        );

        $this->assertSame(
            1,
            $response->total_rows
        );

        $this->assertSame(
            '123',
            $response->rows[0]['id']
        );
    }

    public function testGetSingleDocumentFromDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        $response = $db->put( '/test/123', '{"_id":"123","data":"Foo"}' );
        $response = $db->get( '/test/123' );

        $this->assertTrue(
            $response instanceof phpillowResponse
        );

        $this->assertSame(
            '123',
            $response->_id
        );

        try
        {
            $response->unknownProperty;
            $this->fail( 'Expected phpillowNoSuchPropertyException.' );
        }
        catch ( phpillowNoSuchPropertyException $e )
        { /* Expected exception */ }

        $this->assertTrue(
            isset( $response->_id )
        );

        $this->assertFalse(
            isset( $response->unknownProperty )
        );

        $response->_id = 'foo';
        $this->assertSame(
            '123',
            $response->_id
        );
    }

    public function testGetUnknownDocumentFromDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->get( '/test/not_existent' );
            $this->fail( 'Expected phpillowResponseNotFoundErrorException.' );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        { /* Expected exception */ }
    }

    public function testGetDocumentFromNotExistentDatabase()
    {
        $this->markTestSkipped( 'It is currently not possible to detect from the CouchDB response, see: https://issues.apache.org/jira/browse/COUCHDB-41' );

        phpillowStreamConnection::createInstance();
        phpillowConnection::setDatabase( 'test' );
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->delete( '/test' );
        }
        catch ( phpillowResponseErrorException $e )
        { /* Ignore */ }

        try
        {
            $response = $db->get( '/test/not_existent' );
            $this->fail( 'Expected phpillowDatabaseNotFoundErrorException.' );
        }
        catch ( phpillowDatabaseNotFoundErrorException $e )
        { /* Expected exception */ }
    }

    public function testDeleteUnknownDocumentFromDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->delete( '/test/not_existent' );
            $this->fail( 'Expected phpillowResponseErrorException.' );
        }
        catch ( phpillowResponseErrorException $e )
        { /* Expected exception */ }
    }

    public function testDeleteSingleDocumentFromDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        $response = $db->put( '/test/123', '{"_id":"123","data":"Foo"}' );
        $response = $db->get( '/test/123' );
        $db->delete( '/test/123?rev=' . $response->_rev );

        try
        {
            $response = $db->get( '/test/123' );
            $this->fail( 'Expected phpillowResponseNotFoundErrorException.' );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        { /* Expected exception */ }
    }

    public function testDeleteDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        $response = $db->delete( '/test' );

        $this->assertTrue(
            $response instanceof phpillowStatusResponse
        );

        $this->assertSame(
            true,
            $response->ok
        );
    }

    public function testArrayResponse()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        $response = $db->get( '/_all_dbs' );

        $this->assertTrue(
            $response instanceof phpillowArrayResponse
        );

        $this->assertTrue(
            is_array( $response->data )
        );

        $this->assertTrue(
            in_array( 'test', $response->data )
        );
    }

    public function testGetFullResponseBody()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        $response = $db->get( '/_all_dbs' );

        $body = $response->getFullDocument();

        $this->assertTrue(
            is_array( $body['data'] )
        );

        $this->assertTrue(
            in_array( 'test', $body['data'] )
        );
    }

    public function testCloseConnection()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();
        $db->setOption( 'keep-alive', false );

        $db->put( '/test/123', '{"_id":"123","data":"Foo"}' );
        $db->put( '/test/456', '{"_id":"456","data":"Foo"}' );
        $db->put( '/test/789', '{"_id":"789","data":"Foo"}' );
        $db->put( '/test/012', '{"_id":"012","data":"Foo"}' );

        $response = $db->get( '/test/_all_docs' );

        $this->assertTrue(
            $response instanceof phpillowResultSetResponse
        );

        $this->assertSame(
            4,
            $response->total_rows
        );
    }

    public function testKeepAliveConnection()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();
        $db->setOption( 'keep-alive', true );

        $db->put( '/test/123', '{"_id":"123","data":"Foo"}' );
        $db->put( '/test/456', '{"_id":"456","data":"Foo"}' );
        $db->put( '/test/789', '{"_id":"789","data":"Foo"}' );
        $db->put( '/test/012', '{"_id":"012","data":"Foo"}' );

        $response = $db->get( '/test/_all_docs' );

        $this->assertTrue(
            $response instanceof phpillowResultSetResponse
        );

        $this->assertSame(
            4,
            $response->total_rows
        );
    }

    public function testUnknownOption()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();

        try
        {
            $db->setOption( 'unknownOption', 42 );
            $this->fail( 'Expected phpillowOptionException.' );
        }
        catch( phpillowOptionException $e )
        { /* Expected */ }
    }

    public function testHttpLog()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test', 'handler' => 'phpillowStreamConnection' ) );
        $db = phpillowConnection::getInstance();
        $db->setOption( 'http-log', $logFile = tempnam( dirname( __FILE__ ) . '/../temp', __CLASS__ ) );

        $response = $db->put( '/test/123', '{"_id":"123","data":"Foo"}' );
        $response = $db->get( '/test/123' );

        $this->assertTrue(
            filesize( $logFile ) > 128
        );
    }

}

