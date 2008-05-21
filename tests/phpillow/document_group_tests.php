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
class phpillowDocumentGroupTests extends PHPUnit_Framework_TestCase
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
        phpillowTestEnvironmentSetup::resetDatabase(
            array( 
                'database' => 'test',
            )
        );
    }

    public function testCreateDocument()
    {
        $doc = phpillowGroupDocument::createNew();

        $this->assertTrue(
            $doc instanceof phpillowGroupDocument
        );
    }

    public function testCheckRequirements()
    {
        $doc = phpillowGroupDocument::createNew();

        $this->assertSame(
            array(
                'name',
            ),
            $doc->checkRequirements()
        );
    }

    public function testIdGeneration()
    {
        $doc = phpillowGroupTestDocument::createNew();
        $doc->name = 'Maintainer';

        $this->assertSame(
            'maintainer',
            $doc->generateId()
        );
    }
}
