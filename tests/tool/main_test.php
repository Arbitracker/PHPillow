<?php
/**
 * Basic test cases for the model manager
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowToolTests extends PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        if ( !array_search( 'string', stream_get_wrappers() ) )
        {
            stream_wrapper_register( 'string', 'phpillowToolStringStream' );
        }
    }

    public function testDumpVersionOutput()
    {
        $tool = new phpillowTool( null, array( 'version' => false ) );
        $tool->setOutputStreams(
            $stdout = fopen( 'string://', 'w' ),
            $stderr = fopen( 'string://', 'w' )
        );
        $this->assertEquals( 0, $tool->dump() );

        fseek( $stdout, 0 );
        fseek( $stderr, 0 );

        $this->assertTrue( (bool) preg_match( '(^PHPillow backup tool - version: .*$)', stream_get_contents( $stdout ) ) );
        $this->assertEquals( '', stream_get_contents( $stderr ) );
    }

    public function testLoadVersionOutput()
    {
        $tool = new phpillowTool( null, array( 'version' => false ) );
        $tool->setOutputStreams(
            $stdout = fopen( 'string://', 'w' ),
            $stderr = fopen( 'string://', 'w' )
        );
        $this->assertEquals( 0, $tool->load() );

        fseek( $stdout, 0 );
        fseek( $stderr, 0 );

        $this->assertTrue( (bool) preg_match( '(^PHPillow backup tool - version: .*$)', stream_get_contents( $stdout ) ) );
        $this->assertEquals( '', stream_get_contents( $stderr ) );
    }

    public function testDumpParseBrokenDsn()
    {
        $tool = new phpillowTool( ':/' );
        $tool->setOutputStreams(
            $stdout = fopen( 'string://', 'w' ),
            $stderr = fopen( 'string://', 'w' )
        );
        $this->assertEquals( 1, $tool->dump() );

        fseek( $stdout, 0 );
        fseek( $stderr, 0 );

        $this->assertEquals( '', stream_get_contents( $stdout ) );
        $this->assertEquals( "Could not parse provided DSN: :/\n", stream_get_contents( $stderr ) );
    }

    public function testLoadParseBrokenDsn()
    {
        $tool = new phpillowTool( ':/' );
        $tool->setOutputStreams(
            $stdout = fopen( 'string://', 'w' ),
            $stderr = fopen( 'string://', 'w' )
        );
        $this->assertEquals( 1, $tool->load() );

        fseek( $stdout, 0 );
        fseek( $stderr, 0 );

        $this->assertEquals( '', stream_get_contents( $stdout ) );
        $this->assertEquals( "Could not parse provided DSN: :/\n", stream_get_contents( $stderr ) );
    }

    public static function getDsnConfigurations()
    {
        return array(
            array(
                'http://localhost:5984/database',
                array(
                ),
                array(
                    'host' => 'localhost',
                    'port' => '5984',
                    'user' => null,
                    'pass' => null,
                    'path' => '/database',
                ),
            ),
            array(
                'http://example.com:445/my_database',
                array(
                ),
                array(
                    'host' => 'example.com',
                    'port' => '445',
                    'user' => null,
                    'pass' => null,
                    'path' => '/my_database',
                ),
            ),
            array(
                'http://user:pass@example.com:445/my_database',
                array(
                ),
                array(
                    'host' => 'example.com',
                    'port' => '445',
                    'user' => 'user',
                    'pass' => 'pass',
                    'path' => '/my_database',
                ),
            ),
            array(
                'http://example.com:445/my_database',
                array(
                    'username' => 'user',
                    'password' => 'pass',
                ),
                array(
                    'host' => 'example.com',
                    'port' => '445',
                    'user' => 'user',
                    'pass' => 'pass',
                    'path' => '/my_database',
                ),
            ),
            array(
                'http://foo:bar@example.com:445/my_database',
                array(
                    'username' => 'user',
                    'password' => 'pass',
                ),
                array(
                    'host' => 'example.com',
                    'port' => '445',
                    'user' => 'user',
                    'pass' => 'pass',
                    'path' => '/my_database',
                ),
            ),
        );
    }

    /**
     * @dataProvider getDsnConfigurations
     */
    public function testParseDsn( $dsn, array $options, array $expected )
    {
        $tool = new phpillowTestTool( $dsn, $options );
        $this->assertEquals( $expected, $tool->getConnectionInformation() );
    }
}
