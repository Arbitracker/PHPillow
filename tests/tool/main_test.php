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

    public function testDumpVersionOutput()
    {
        $tool = new phpillowTool( null, array( 'version' => false ) );
        ob_start();
        $this->assertEquals( 0, $tool->dump() );

        $this->assertTrue( (bool) preg_match( '(^PHPillow backup tool - version: .*$)', ob_get_clean() ) );
    }

    public function testLoadVersionOutput()
    {
        $tool = new phpillowTool( null, array( 'version' => false ) );
        ob_start();
        $this->assertEquals( 0, $tool->load() );

        $this->assertTrue( (bool) preg_match( '(^PHPillow backup tool - version: .*$)', ob_get_clean() ) );
    }
}
