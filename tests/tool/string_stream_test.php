<?php
/**
 * Basic test cases for the model manager
 *
 * @version $Revision: 113 $
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowStringStreamTests extends PHPUnit_Framework_TestCase
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

    public function testGetContents()
    {
        $string = 'foo';
        $stream = fopen( 'string://' . $string, 'r' );
        $this->assertEquals( $string, stream_get_contents( $stream ) );
    }

    public function testGetContents2()
    {
        $string = 'foo';
        $this->assertEquals( $string, file_get_contents( 'string://' . $string ) );
    }

    public function testReadLine()
    {
        $string = ( $line = "foo\r\n" ) . "bar";
        $stream = fopen( 'string://' . $string, 'r' );
        $this->assertEquals( $line, fgets( $stream ) );
    }

    public function testReadLine2()
    {
        $string = ( $line = "foo\n" ) . "bar";
        $stream = fopen( 'string://' . $string, 'r' );
        $this->assertEquals( $line, fgets( $stream ) );
    }
}
