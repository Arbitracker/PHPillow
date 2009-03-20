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
class phpillowUserViewTests extends phpillowDataTestCase
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
        parent::setUp();
        phpillowManager::setDocumentClass( 'user', 'phpillowUserDocument' );
    }

    public function testFetchUserByLogin()
    {
        $view = new phpillowUserView();
        $results = $view->query( 'user', array( 
            'key' => 'kore'
        ) );

        $user = phpillowManager::fetchDocument(
            'user',
            $results->rows[0]['value']
        );

        $this->assertSame(
            'kore',
            $user->login
        );

        $this->assertSame(
            'Kore Nordmann',
            $user->name
        );
    }
}

