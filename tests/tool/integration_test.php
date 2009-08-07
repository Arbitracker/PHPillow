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
class phpillowToolIntegrationTests extends PHPUnit_Framework_TestCase
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

        phpillowTestEnvironmentSetup::resetDatabase(
            array( 
                'database' => 'test',
            )
        );
    }

    /**
     * Compare a dump against a recorded dump
     * 
     * @param string $name 
     * @param string $dump 
     * @return void
     */
    protected function compareDump( $name, $dump )
    {
        $fileName = dirname( __FILE__ ) . '/data/' . $name . '.dump';
        if ( !file_exists( $fileName ) )
        {
            file_put_contents(
                'tmp/' . basename( $fileName ),
                $dump
            );
            $this->markTestSkipped( "No comaprision file available." );
            return;
        }

        if ( !preg_match(
                $r = '(^' . str_replace(
                    '\\{boundary\\}',
                    '[a-f0-9]{1,32}',
                    preg_quote( 
                        preg_replace(
                            '(\s+)',
                            " ",
                            preg_replace(
                                '(\d+(?:-\d+)*)',
                                '1',
                                preg_replace(
                                    '(==[a-f0-9]{32}==)',
                                    '=={boundary}==',
                                    trim( file_get_contents( $fileName ) )
                                )
                            )
                        )
                    )
                ) . '$)',
                $s = preg_replace(
                    '(\d+(?:-\d+)*)',
                    '1',
                    preg_replace( '(\s+)', " ", trim( $dump ) )
                )
            ) )
        {
            $this->fail( "Dump does not match expectation:\n" . $dump );
        }
    }

    public function testDumpDocument()
    {
        $doc = phpillowUserTestDocument::createNew();
        $doc->login = 'http://xlogon.net/kore';
        $doc->save();

        $tool = new phpillowTool( 'http://localhost:5984/test', array( 'verbose' => false ) );
        $tool->setOutputStreams(
            $stdout = fopen( 'string://', 'w' ),
            $stderr = fopen( 'string://', 'w' )
        );
        $this->assertEquals( 0, $tool->dump() );

        fseek( $stdout, 0 );
        fseek( $stderr, 0 );

        $this->compareDump( __FUNCTION__, stream_get_contents( $stdout ) );
        $this->assertEquals( "Dumping document user-http_xlogon.net_kore\n", stream_get_contents( $stderr ) );
    }

    public function testDumpMultipleDocuments()
    {
        $doc = phpillowUserTestDocument::createNew();
        $doc->login = 'http://xlogon.net/kore';
        $doc->save();

        $doc = phpillowUserTestDocument::createNew();
        $doc->login = 'kore';
        $doc->save();

        $tool = new phpillowTool( 'http://localhost:5984/test', array( 'verbose' => false ) );
        $tool->setOutputStreams(
            $stdout = fopen( 'string://', 'w' ),
            $stderr = fopen( 'string://', 'w' )
        );
        $this->assertEquals( 0, $tool->dump() );

        fseek( $stdout, 0 );
        fseek( $stderr, 0 );

        $this->compareDump( __FUNCTION__, stream_get_contents( $stdout ) );
        $this->assertEquals( "Dumping document user-http_xlogon.net_kore\nDumping document user-kore\n", stream_get_contents( $stderr ) );
    }

    public function testDumpDocumentWithAttachment()
    {
        $doc = phpillowUserTestDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( $file = dirname( __FILE__ ) . '/../phpillow/data/image_png.png' );
        $doc->save();

        $tool = new phpillowTool( 'http://localhost:5984/test', array( 'verbose' => false ) );
        $tool->setOutputStreams(
            $stdout = fopen( 'string://', 'w' ),
            $stderr = fopen( 'string://', 'w' )
        );
        $this->assertEquals( 0, $tool->dump() );

        fseek( $stdout, 0 );
        fseek( $stderr, 0 );

        $this->compareDump( __FUNCTION__, stream_get_contents( $stdout ) );
        $this->assertEquals( "Dumping document user-kore\n", stream_get_contents( $stderr ) );
    }
}

