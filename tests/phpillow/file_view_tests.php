<?php
/**
 * Basic test cases for model connections
 *
 * @version $Revision: 94 $
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowFileViewTests extends phpillowDataTestCase
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
        phpillowManager::setDocumentClass( 'userview', 'phpillowUserFileView' );
    }

    public function testCreateView()
    {
        $view = phpillowUserFileView::createNew();
        $view->save();

        $this->assertSame(
            '_design/users',
            $view->_id
        );
    }

    public function testGetView()
    {
        $view = phpillowUserFileView::createNew();
        $view->save();

        $view = phpillowManager::fetchDocument( 'userview', '_design/users' );
        $this->assertTrue(
            $view instanceof phpillowUserFileView
        );

        $this->assertSame(
            '_design/users',
            $view->_id
        );
    }

    public function testModifyView()
    {
        $view = phpillowUserFileView::createNew();
        $view->save();

        $view = phpillowManager::fetchDocument( 'userview', '_design/users' );
        $views = $view->views;
        $views['foo'] = $function = 'function( doc ) { return; }';
        $view->views = $views;
        $view->save();

        $view = phpillowManager::fetchDocument( 'userview', '_design/users' );
        $this->assertSame(
            '_design/users',
            $view->_id
        );

        $this->assertSame(
            $function,
            $view->views['foo']
        );
    }

    public function testVerifyNotExistantView()
    {
        $view = phpillowUserFileView::createNew();
        $view->verifyView();

        $view = phpillowManager::fetchDocument( 'userview', '_design/users' );
        $this->assertSame(
            '_design/users',
            $view->_id
        );

        $this->assertTrue(
            isset( $view->views['user'] )
        );
    }

    public function testVerifyExistantView()
    {
        $view = phpillowUserFileView::createNew();
        $view->save();

        $view = phpillowManager::fetchDocument( 'userview', '_design/users' );
        $views = $view->views;
        $views['foo'] = $function = 'function( doc ) { return; }';
        $view->views = $views;
        $view->save();

        $view = phpillowUserFileView::createNew();
        $view->verifyView();

        $view = phpillowManager::fetchDocument( 'userview', '_design/users' );
        $this->assertSame(
            '_design/users',
            $view->_id
        );

        $this->assertTrue(
            isset( $view->views['user'] )
        );

        $this->assertFalse(
            isset( $view->views['foo'] )
        );
    }

    public function testQueryNotExistantView()
    {
        $view = phpillowUserFileView::createNew();
        $results = $view->query( 'all' );

        $this->assertSame(
            2,
            count( $results->rows )
        );
    }

    public function testQueryExistantView()
    {
        $view = phpillowUserFileView::createNew();
        $view->save();

        $results = $view->query( 'all' );

        $this->assertSame(
            2,
            count( $results->rows )
        );
    }

    public function testQueryExistantViewByKey()
    {
        $view = phpillowUserFileView::createNew();
        $view->save();

        $results = $view->query( 'user', array( 'key' => 'kore' ) );

        $this->assertSame(
            1,
            count( $results->rows )
        );

        $this->assertSame(
            array(
                'key'   => null,
                'value' => 1,
            ),
            $results->rows[0]
        );
    }

    public function testDirectStaticQuery()
    {
        if ( version_compare( PHP_VERSION, '5.3', '<' ) )
        {
            $this->markTestSkipped( 'PHP 5.3 is minimum requirement for this test.' );
        }

        $results = phpillowUserFileView::all();

        $this->assertSame(
            2,
            count( $results->rows )
        );
    }

    public function testDirectStaticQueryWithOptions()
    {
        if ( version_compare( PHP_VERSION, '5.3', '<' ) )
        {
            $this->markTestSkipped( 'PHP 5.3 is minimum requirement for this test.' );
        }

        $results = phpillowUserFileView::user( array( 'key' => 'kore' ) );

        $this->assertSame(
            1,
            count( $results->rows )
        );

        $this->assertSame(
            array(
                'key'   => null,
                'value' => 1,
            ),
            $results->rows[0]
        );
    }

    public function testDirectStaticQueryViewNameDefinitionMissing()
    {
        if ( version_compare( PHP_VERSION, '5.3', '<' ) )
        {
            $this->markTestSkipped( 'PHP 5.3 is minimum requirement for this test.' );
        }

        try
        {
            phpillowViewTestPublic::all();
            $this->fail( 'Expected phpillowRuntimeException.' );
        }
        catch ( phpillowRuntimeException $e )
        { /* Expected exception */ }
    }
}

