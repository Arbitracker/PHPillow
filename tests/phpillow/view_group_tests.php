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
class phpillowGroupViewTests extends phpillowDataTestCase
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
        phpillowManager::setDocumentClass( 'group', 'phpillowGroupDocument' );
    }

    public function testFetchGroupByName()
    {
        $view = new phpillowGroupView();
        $results = $view->query( 'group', array(
            'key' => 'Maintainer'
        ) );

        $user = phpillowManager::fetchDocument(
            'group',
            $results->rows[0]['value']
        );

        $this->assertSame(
            'Maintainer',
            $user->name
        );

        $this->assertSame(
            array( 'close_bug', 'open_bug', 'view_bug', 'delete_bug' ),
            $user->permissions
        );
    }

    public function testFetchUsersPermissions1()
    {
        $view = new phpillowGroupView();
        $results = $view->query( 'user_permissions', array(
            'key' => 'kore'
        ) );

        $this->assertSame(
            2,
            count( $results->rows )
        );

        // Extract the permissions from result
        $permissions = array();
        foreach ( $results->rows as $row )
        {
            $permissions = array_merge( $permissions, $row['value'] );
        }

        // Unique and sort for reproducibility
        $permissions = array_unique( $permissions );
        sort( $permissions );

        $this->assertSame(
            array( 'close_bug', 'delete_bug', 'open_bug', 'view_bug' ),
            $permissions
        );
    }

    public function testFetchUsersPermissions2()
    {
        $view = new phpillowGroupView();
        $results = $view->query( 'user_permissions', array(
            'key' => 'norro'
        ) );

        $this->assertSame(
            1,
            count( $results->rows )
        );

        // Extract the permissions from result
        $permissions = array();
        foreach ( $results->rows as $row )
        {
            $permissions = array_merge( $permissions, $row['value'] );
        }

        // Unique and sort for reproducibility
        $permissions = array_unique( $permissions );
        sort( $permissions );

        $this->assertSame(
            array( 'close_bug', 'open_bug', 'view_bug' ),
            $permissions
        );
    }

    public function testFetchUsersPermissionsReduce()
    {
        $view = new phpillowGroupView();
        $results = $view->query( 'user_permissions_reduced', array(
            'key' => 'kore'
        ) );
        $permissions = $results->rows[0]['value'];
        sort( $permissions );

        $this->assertEquals(
            array(
                'close_bug',
                'delete_bug',
                'open_bug',
                'view_bug',
            ),
            $permissions
        );
    }

    public function testFetchUsersPermissionsReduceNewGroup()
    {
        $doc = phpillowGroupTestDocument::createNew();
        $doc->name = 'new_group';
        $doc->permissions = array( 'blubb' );
        $doc->users = array( 'kore' );
        $doc->save();

        $view = new phpillowGroupView();
        $results = $view->query( 'user_permissions_reduced' );
        $permissions = $results->rows[0]['value'];
        sort( $permissions );

        $this->assertEquals(
            array(
                'blubb',
                'close_bug',
                'delete_bug',
                'open_bug',
                'view_bug',
            ),
            $permissions
        );
    }
}

