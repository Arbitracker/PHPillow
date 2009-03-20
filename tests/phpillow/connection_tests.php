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

    public function testDefaultInstanceOptions()
    {
        phpillowCustomConnection::createInstance();

        $instance = phpillowConnection::getInstance();

        $this->assertTrue(
            $instance instanceof phpillowConnection
        );

        $this->assertAttributeSame(
            array(
                'host'       => '127.0.0.1',
                'port'       => 5984,
                'ip'         => '127.0.0.1',
                'timeout'    => .01,
                'keep-alive' => true,
                'http-log'   => false,
            ),
            'options', $instance
        );
    }

    public function testCreateDefaultInstance()
    {
        phpillowConnection::createInstance( 'example.com', '80' );

        $instance = phpillowConnection::getInstance();

        $this->assertTrue(
            $instance instanceof phpillowConnection
        );

        $this->assertTrue(
            $instance instanceof phpillowCustomConnection
        );
    }

    public function testCreateNonDefaultInstance()
    {
        phpillowCustomConnection::createInstance( 'example.com', '80' );

        $instance = phpillowConnection::getInstance();

        $this->assertTrue(
            $instance instanceof phpillowCustomConnection
        );
    }

    public function testCreateNonDefaultStreamInstance()
    {
        phpillowStreamConnection::createInstance( 'example.com', '80' );

        $instance = phpillowConnection::getInstance();

        $this->assertTrue(
            $instance instanceof phpillowStreamConnection
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
}

