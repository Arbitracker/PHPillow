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
class phpillowViewTests extends phpillowDataTestCase
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

    public function testCreateView()
    {
        $view = phpillowUserView::createNew();
        $view->save();

        $this->assertSame(
            '_design/users',
            $view->_id
        );
    }

    public function testGetView()
    {
        $view = phpillowUserView::createNew();
        $view->save();

        $view = phpillowUserView::fetchById( '_design/users' );
        $this->assertSame(
            '_design/users',
            $view->_id
        );

        $this->assertTrue(
            isset( $view->views['user'] )
        );
    }

    public function testModifyView()
    {
        $view = phpillowUserView::createNew();
        $view->save();

        $view = phpillowUserView::fetchById( '_design/users' );
        $views = $view->views;
        $views['foo'] = $function = 'function( doc ) { return; }';
        $view->views = $views;
        $view->save();

        $view = phpillowUserView::fetchById( '_design/users' );
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
        $view = phpillowUserView::createNew();
        $view->verifyView();

        $view = phpillowUserView::fetchById( '_design/users' );
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
        $view = phpillowUserView::createNew();
        $view->save();

        $view = phpillowUserView::fetchById( '_design/users' );
        $views = $view->views;
        $views['foo'] = $function = 'function( doc ) { return; }';
        $view->views = $views;
        $view->save();

        $view = phpillowUserView::createNew();
        $view->verifyView();

        $view = phpillowUserView::fetchById( '_design/users' );
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
        $view = phpillowUserView::createNew();
        $results = $view->query( 'all' );

        $this->assertSame(
            2,
            count( $results->rows )
        );
    }

    public function testQueryExistantView()
    {
        $view = phpillowUserView::createNew();
        $view->save();

        $results = $view->query( 'all' );

        $this->assertSame(
            2,
            count( $results->rows )
        );
    }

    public function testQueryExistantViewByKey()
    {
        $view = phpillowUserView::createNew();
        $view->save();

        $results = $view->query( 'user', array( 'key' => 'kore' ) );

        $this->assertSame(
            1,
            count( $results->rows )
        );

        $this->assertSame(
            array(
                'id'    => 'user-kore',
                'key'   => 'kore',
                'value' => 'user-kore',
            ),
            $results->rows[0]
        );
    }

    public function testDirectStaticQuery()
    {
        $results = phpillowUserView::all();

        $this->assertSame(
            2,
            count( $results->rows )
        );
    }

    public function testDirectStaticQueryWithOptions()
    {
        $results = phpillowUserView::user( array( 'key' => 'kore' ) );

        $this->assertSame(
            1,
            count( $results->rows )
        );

        $this->assertSame(
            array(
                'id'    => 'user-kore',
                'key'   => 'kore',
                'value' => 'user-kore',
            ),
            $results->rows[0]
        );
    }

    public function testTransformKey()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?key=%5B%22foo%22%2C23%5D',
            $view->buildViewQuery( array( 
                'key' => array( 'foo', 23 ),
            ) )
        );
    }

    public function testTransformStartKey()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?startkey=%5B%22foo%22%2C23%5D',
            $view->buildViewQuery( array( 
                'startkey' => array( 'foo', 23 ),
            ) )
        );
    }

    public function testTransformEndKey()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?endkey=%5B%22foo%22%2C23%5D',
            $view->buildViewQuery( array( 
                'endkey' => array( 'foo', 23 ),
            ) )
        );
    }

    public function testTransformStartKeyDocId()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?startkey_docid=foo',
            $view->buildViewQuery( array( 
                'startkey_docid' => 'foo'
            ) )
        );
    }

    public function testTransformUpdate()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?update=true',
            $view->buildViewQuery( array( 
                'update' => true
            ) )
        );
    }

    public function testTransformDescending()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?descending=false',
            $view->buildViewQuery( array( 
                'descending' => null
            ) )
        );
    }

    public function testTransformSkip()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?skip=23',
            $view->buildViewQuery( array( 
                'skip' => 23
            ) )
        );
    }

    public function testTransformCount()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?count=42',
            $view->buildViewQuery( array( 
                'count' => 42.5
            ) )
        );
    }

    public function testTransformAll()
    {
        $view = new phpillowViewTestPublic();

        $this->assertSame(
            '?key=%5B%22foo%22%2C23%5D&startkey=%5B%22foo%22%2C23%5D&endkey=%5B%22foo%22%2C23%5D&startkey_docid=foo&update=true&descending=false&skip=23&count=42',
            $view->buildViewQuery( array( 
                'key' => array( 'foo', 23 ),
                'startkey' => array( 'foo', 23 ),
                'endkey' => array( 'foo', 23 ),
                'startkey_docid' => 'foo',
                'update' => true,
                'descending' => null,
                'skip' => 23,
                'count' => 42.5,
            ) )
        );
    }

    public function testTransformUnknown()
    {
        $view = new phpillowViewTestPublic();

        try
        {
            $view->buildViewQuery( array( 
                'unknown_query_param' => array( 'foo', 23 ),
            ) );
            $this->fail( 'Expected phpillowNoSuchPropertyException.' );
        }
        catch ( phpillowNoSuchPropertyException $e )
        { /* Expected exception */ }
    }

    public function testViewNameDefinitionMissing()
    {
        try
        {
            phpillowViewTestPublic::all();
            $this->fail( 'Expected phpillowRuntimeException.' );
        }
        catch ( phpillowRuntimeException $e )
        { /* Expected exception */ }
    }
}

