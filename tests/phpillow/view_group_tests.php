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
            $permissions[] = $row['value'];
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
            $permissions[] = $row['value'];
        }

        // Unique and sort for reproducability
        $permissions = array_unique( $permissions );
        sort( $permissions );

        $this->assertSame(
            array( 'close_bug', 'open_bug', 'view_bug' ),
            $permissions
        );
    }

    public function testFetchUsersPermissionsReduce()
    {
        $results = phpillowGroupView::user_permissions_reduced( array( 
            'key' => 'kore'
        ) );

        $this->assertEquals(
            array(
                'kore' => array(
                    'close_bug'  => true,
                    'open_bug'   => true,
                    'view_bug'   => true,
                    'delete_bug' => true,
                ),
            ),
            $results->rows[0]['value']
        );
    }

    public function testFetchUsersPermissionsReduceNewGroup()
    {
        $doc = phpillowGroupTestDocument::createNew();
        $doc->name = 'new_group';
        $doc->permissions = array( 'blubb' );
        $doc->users = array( 'kore' );
        $doc->save();

        $results = phpillowGroupView::user_permissions_reduced();

        $this->assertEquals(
            array(
                'norro' => array(
                    'close_bug'  => true,
                    'open_bug'   => true,
                    'view_bug'   => true,
                ),
                'kore' => array(
                    'blubb'      => true,
                    'close_bug'  => true,
                    'open_bug'   => true,
                    'view_bug'   => true,
                    'delete_bug' => true,
                ),
            ),
            $results->rows[0]['value']
        );
    }
}

