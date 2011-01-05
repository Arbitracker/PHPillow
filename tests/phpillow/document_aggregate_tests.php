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
class phpillowDocumentAggregateTests extends PHPUnit_Framework_TestCase
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
        phpillowManager::setDocumentClass( 'user', 'phpillowUserDocument' );
    }

    public function testCreateDocumentWithAggregation()
    {
        $user = phpillowUserDocument::createNew();
        $user->login = 'kore';
        $user->save();

        $group = phpillowGroupDocument::createNew();
        $group->name = 'users';
        $group->userDocs = array( $user );
        $group->save();
    }

    public function testLoadAggregatedDocumentWithIDs()
    {
        $user = phpillowUserDocument::createNew();
        $user->login = 'kore';
        $userID = $user->save();

        $group = phpillowGroupDocument::createNew();
        $group->name = 'users';
        $group->userDocs = array( $user );
        $groupID = $group->save();

        $group = new phpillowGroupDocument();
        $group->fetchById( $groupID );
        $this->assertSame(
            array( $userID ),
            $group->userDocs
        );
    }

    public function testConflictForUnstoredChildDocument()
    {
        $user = phpillowUserDocument::createNew();
        $user->login = 'kore';

        $group = phpillowGroupDocument::createNew();
        $group->name = 'users';

        try
        {
            $group->userDocs = array( $user );
            $groupID = $group->save();
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        { /* Expected */ }
    }

    public function testLoadAggregateWithDocuments()
    {
        $user = phpillowUserDocument::createNew();
        $user->login = 'kore';

        $group = phpillowGroupDocument::createNew();
        $group->name = 'users';

        try
        {
            $group->userDocs = array( $user );
            $groupID = $group->save();
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        { /* Expected */ }
    }
}

