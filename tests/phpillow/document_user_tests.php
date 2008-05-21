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
class phpillowDocumentUserTests extends PHPUnit_Framework_TestCase
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
        $doc = phpillowUserDocument::createNew();

        $this->assertTrue(
            $doc instanceof phpillowUserDocument
        );
    }

    public function testCheckRequirements()
    {
        $doc = phpillowUserDocument::createNew();

        $this->assertSame(
            array(
                'login',
            ),
            $doc->checkRequirements()
        );
    }

    public function testIdGeneration()
    {
        $doc = phpillowUserTestDocument::createNew();
        $doc->login = 'kore';

        $this->assertSame(
            'kore',
            $doc->generateId()
        );
    }

    public function testIdGenerationStrangeId()
    {
        $doc = phpillowUserTestDocument::createNew();
        $doc->login = 'http://xlogon.net/kore';

        $this->assertSame(
            'http_xlogon.net_kore',
            $doc->generateId()
        );
    }

    public function testStoreStrangeId()
    {
        $doc = phpillowUserTestDocument::createNew();
        $doc->login = 'http://xlogon.net/kore';
        $doc->save();

        $this->assertSame(
            'user-http_xlogon.net_kore',
            $doc->_id
        );
    }
}
