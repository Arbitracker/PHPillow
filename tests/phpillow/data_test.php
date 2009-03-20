<?php
/**
 * Basic test cases for model
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowDataTestCase extends PHPUnit_Framework_TestCase
{
    protected $dataArray = array(
        'phpillowUserDocument' => array(
            array(
                'login' => 'kore',
                'name' => 'Kore Nordmann',
            ),
            array(
                'login' => 'norro',
                'name' => 'Arne Nordmann',
            ),
        ),
        'phpillowGroupDocument' => array(
            array(
                'name' => 'Maintainer',
                'permissions' => array( 'close_bug', 'open_bug', 'view_bug', 'delete_bug' ),
                'users' => array(
                    'kore',
                ),
            ),
            array(
                'name' => 'Developer',
                'permissions' => array( 'close_bug', 'open_bug', 'view_bug' ),
                'users' => array(
                    'kore',
                    'norro',
                ),
            ),
        ),
    );

    public function setUp()
    {
        phpillowTestEnvironmentSetup::resetDatabase(
            array( 
                'database' => 'test',
            )
        );

        $this->insertData( $this->dataArray );
    }

    protected function insertData( array $data )
    {
        foreach ( $data as $type => $documents )
        {
            foreach ( $documents as $nr => $document )
            {
                $doc = call_user_func(array($type, 'createNew'));

                foreach ( $document as $property => $value )
                {
                    if ( is_array( $value ) )
                    {
                        foreach ( $value as $key => $subvalue )
                        {
                            if ( !preg_match( '(^(phpillow\w+),(\d+)$)', $subvalue, $match ) )
                            {
                                continue;
                            }

                            $value[$key] = $data[$match[1]][$match[2]];
                        }
                    }

                    if ( is_string( $value ) && preg_match( '(,\d+$)', $value ) )
                    {
                        list( $docType, $docNr ) = explode( ',', $value );
                        $value = $data[$docType][$docNr];
                    }

                    $doc->$property = $value;
                }

                $doc->save();
                $data[$type][$nr] = $doc;
            }
        }
    }

    public function tearDown()
    {
        // Remove / clear test database
        $db = phpillowConnection::getInstance();

        try
        {
            $db->delete( '/test' );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        { /* Ignore */ }

        phpillowConnectionTestHelper::reset();
    }
}
