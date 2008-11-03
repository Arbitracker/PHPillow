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
class phpillowConnectionTests extends PHPUnit_Framework_TestCase
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
     * Reset database connection after each test run
     * 
     * @return void
     */
    public function tearDown()
    {
        phpillowTestEnvironmentSetup::resetDatabase();
        phpillowTestEnvironmentSetup::resetTmpDir();
    }

    public function testGetNonExitantConnection()
    {
        // To be dependant from test which has been run earlier
        phpillowConnectionTestHelper::reset();

        try
        {
            phpillowConnection::getInstance();
            $this->fail( 'Expected phpillowConnectionException.' );
        }
        catch ( phpillowConnectionException $e )
        { /* Expected exception */ }
    }

    public function testReCreateDefaultInstance()
    {
        phpillowConnection::createInstance();

        try
        {
            phpillowConnection::createInstance();
            $this->fail( 'Expected phpillowConnectionException.' );
        }
        catch ( phpillowConnectionException $e )
        { /* Expected exception */ }
    }

    public function testSetAndGetDatabase()
    {
        phpillowConnection::createInstance();

        phpillowConnection::setDatabase( 'test' );

        $this->assertSame(
            '/test/',
            phpillowConnection::getDatabase()
        );
    }

    public function testGetNotSetDatabase()
    {
        phpillowConnection::createInstance();

        try
        {
            phpillowConnection::getDatabase();
            $this->fail( 'Expected phpillowBackendCouchNoDatabaseException.' );
        }
        catch ( phpillowNoDatabaseException $e )
        { /* Expected exception */ }
    }

    public function testCreateDefaultInstance()
    {
        phpillowConnection::createInstance();

        $instance = phpillowConnection::getInstance();

        $this->assertTrue(
            $instance instanceof phpillowConnection
        );

        $this->assertAttributeSame(
            array(
                'host'       => 'localhost',
                'port'       => 5984,
                'ip'         => '127.0.0.1',
                'timeout'    => .01,
                'keep-alive' => true,
                'http-log'   => false,
            ),
            'options', $instance
        );
    }

    public function testCreateNonDefaultInstance()
    {
        phpillowConnection::createInstance( 'example.com', '80' );

        $instance = phpillowConnection::getInstance();

        $this->assertTrue(
            $instance instanceof phpillowConnection
        );

        $this->assertAttributeSame(
            array(
                'host'       => 'example.com',
                'port'       => 80,
                'ip'         => '127.0.0.1',
                'timeout'    => .01,
                'keep-alive' => true,
                'http-log'   => false,
            ),
            'options', $instance
        );
    }

    public function testSingletonIsSingleton()
    {
        phpillowConnection::createInstance();

        $this->assertSame(
            phpillowConnection::getInstance(),
            phpillowConnection::getInstance()
        );
    }

    public function testUnsupportedMethod()
    {
        phpillowConnection::createInstance();
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->unsupported( '/irrelevant' );
            $this->fail( 'Expected phpillowInvalidRequestException.' );
        }
        catch ( phpillowInvalidRequestException $e )
        { /* Expected exception */ }
    }

    public function testInvalidPath()
    {
        phpillowConnection::createInstance();
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->get( 'irrelevant' );
            $this->fail( 'Expected phpillowInvalidRequestException.' );
        }
        catch ( phpillowInvalidRequestException $e )
        { /* Expected exception */ }
    }

    public function testNoConnectionPossible()
    {
        phpillowConnection::createInstance( '127.0.0.1', 12345 );
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->get( '/test' );
            $this->fail( 'Expected phpillowConnectionException.' );
        }
        catch ( PHPUnit_Framework_Error $e )
        {
            $this->assertSame(
                'fsockopen(): unable to connect to 127.0.0.1:12345 (Connection refused)',
                $e->getMessage()
            );
        }
    }

    public function testCreateDatabase()
    {
        phpillowConnection::createInstance();
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
                    'reason' => 'The database could not becreated, the file already exists.',
                ),
                $e->getResponse()
            );
        }
    }

    public function testGetDatabaseInformation()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->get( '/test/not_existant' );
            $this->fail( 'Expected phpillowResponseNotFoundErrorException.' );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        { /* Expected exception */ }
    }

    public function testGetDocumentFromNotExistantDatabase()
    {
        $this->markTestSkipped( 'It is currently not possible to detect from the CouchDB response, see: https://issues.apache.org/jira/browse/COUCHDB-41' );

        phpillowConnection::createInstance();
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
            $response = $db->get( '/test/not_existant' );
            $this->fail( 'Expected phpillowDatabaseNotFoundErrorException.' );
        }
        catch ( phpillowDatabaseNotFoundErrorException $e )
        { /* Expected exception */ }
    }

    public function testDeleteUnknownDocumentFromDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
        $db = phpillowConnection::getInstance();

        try
        {
            $response = $db->delete( '/test/not_existant' );
            $this->fail( 'Expected phpillowResponseErrorException.' );
        }
        catch ( phpillowResponseErrorException $e )
        { /* Expected exception */ }
    }

    public function testDeleteSingleDocumentFromDatabase()
    {
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
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
        phpillowTestEnvironmentSetup::resetDatabase( array( 'database' => 'test' ) );
        $db = phpillowConnection::getInstance();
        $db->setOption( 'http-log', $logFile = tempnam( __DIR__ . '/../temp', __CLASS__ ) );

        $response = $db->put( '/test/123', '{"_id":"123","data":"Foo"}' );
        $response = $db->get( '/test/123' );

        $this->assertTrue(
            filesize( $logFile ) > 128
        );
    }

}

