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

    public function testFetchGroupByName()
    {
        $results = phpillowGroupView::group( array( 
            'key' => 'Maintainer'
        ) );

        $user = phpillowGroupDocument::fetchById(
            $results->rows[0]->value
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
        $results = phpillowGroupView::user_permissions( array( 
            'key' => 'kore'
        ) );

        $this->assertSame(
            7,
            count( $results->rows )
        );

        // Extract the permissions from result
        $permissions = array();
        foreach ( $results->rows as $row )
        {
            $permissions[] = $row->value;
        }

        // Unique and sort for reproducability
        $permissions = array_unique( $permissions );
        sort( $permissions );

        $this->assertSame(
            array( 'close_bug', 'delete_bug', 'open_bug', 'view_bug' ),
            $permissions
        );
    }

    public function testFetchUsersPermissions2()
    {
        $results = phpillowGroupView::user_permissions( array( 
            'key' => 'norro'
        ) );

        $this->assertSame(
            3,
            count( $results->rows )
        );

        // Extract the permissions from result
        $permissions = array();
        foreach ( $results->rows as $row )
        {
            $permissions[] = $row->value;
        }

        // Unique and sort for reproducability
        $permissions = array_unique( $permissions );
        sort( $permissions );

        $this->assertSame(
            array( 'close_bug', 'open_bug', 'view_bug' ),
            $permissions
        );
    }
}

