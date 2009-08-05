<?php
/**
 * Basic test cases for the model manager
 *
 * @version $Revision: 114 $
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowMultipartParserTests extends PHPUnit_Framework_TestCase
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

    public function testFailOnInvalidString()
    {
        $parser = new phpillowToolMultipartParser(
            fopen( 'string://foo', 'r' )
        );

        $this->assertSame( false, $parser->getDocument() );
    }

    public function testFailOnInvalidType()
    {
        try
        {
            $parser = new phpillowToolMultipartParser(
                fopen( "string://Content-Type: text/text; boundary=foo\r\n", 'r' )
            );
            $this->fail( "Expected phpillowMultipartParserException." );
        }
        catch ( phpillowMultipartParserException $e )
        { /* Expected */ }
    }

    public function testFailOnInvalidString2()
    {
        $parser = new phpillowToolMultipartParser(
            fopen( 'string://Content-Type: multipart/mixed; boundary=foo

--foo', 'r' )
        );

        $this->assertSame( false, $parser->getDocument() );
    }

    public function testFailOnEmptyDocument()
    {
        $parser = new phpillowToolMultipartParser(
            fopen( 'string://Content-Type: multipart/mixed; boundary=foo

--foo
--foo--', 'r' )
        );

        $this->assertSame( false, $parser->getDocument() );
    }

    public function testParseSimpleDocument()
    {
        $parser = new phpillowToolMultipartParser(
            fopen( 'string://Content-Type: multipart/mixed; boundary=foo

--foo
Content-Type: text/text

Hello world!
--foo--', 'r' )
        );

        $this->assertSame( array(
                'Content-Type' => 'text/text',
                'body'         => 'Hello world!',
            ),
            $parser->getDocument()
        );
        $this->assertSame( false, $parser->getDocument() );
    }

    public function testParseStackedMultipartDocument()
    {
        $parser = new phpillowToolMultipartParser(
            fopen( 'string://Content-Type: multipart/mixed; boundary=foo

--foo
Content-Type: multipart/mixed; boundary="bar"

--bar
Content-Type: text/text

Hello world!
--bar--
--foo--', 'r' )
        );

        $this->assertSame( array(
                'Content-Type' => 'multipart/mixed; boundary="bar"',
                'body'         => array(
                    array(
                        'Content-Type' => 'text/text',
                        'body'         => 'Hello world!',
                    ),
                ),
            ),
            $parser->getDocument()
        );
    }
}
