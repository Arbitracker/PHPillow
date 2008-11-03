<?php
/**
 * Basic test cases for model connections
 *
 * @version $Revision: 58 $
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowCustomConnectionTests extends PHPUnit_Framework_TestCase
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
        phpillowCustomConnection::createInstance();

        try
        {
            phpillowCustomConnection::createInstance();
            $this->fail( 'Expected phpillowConnectionException.' );
        }
        catch ( phpillowConnectionException $e )
        { /* Expected exception */ }
    }

    public function testGetExtendedConnectionHandler()
    {
        phpillowCustomConnection::createInstance();

        $this->assertTrue(
            phpillowConnection::getInstance() instanceof phpillowCustomConnection,
            'Expected instance of phpillowCustomConnection.'
        );
    }

    public function testSetAndGetDatabase()
    {
        phpillowCustomConnection::createInstance();

        phpillowConnection::setDatabase( 'test' );

        $this->assertSame(
            '/test/',
            phpillowConnection::getDatabase()
        );
    }

    public function testGetNotSetDatabase()
    {
        phpillowCustomConnection::createInstance();

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
        phpillowCustomConnection::createInstance();

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
        phpillowCustomConnection::createInstance( 'example.com', '80' );

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
        phpillowCustomConnection::createInstance();

        $this->assertSame(
            phpillowConnection::getInstance(),
            phpillowConnection::getInstance()
        );
    }

    public function testUnsupportedMethod()
    {
        phpillowCustomConnection::createInstance();
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
        phpillowCustomConnection::createInstance();
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
        phpillowCustomConnection::createInstance( '127.0.0.1', 12345 );
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
        phpillowCustomConnection::createInstance();
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
}

