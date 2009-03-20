<?php
/**
 * Basic test cases for the model manager
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowManagerTests extends PHPUnit_Framework_TestCase
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

    public function testGetView()
    {
        phpillowManager::setViewClass( 'user', 'phpillowUserView' );
        $user = phpillowManager::getView( 'user' );

        $this->assertTrue(
            $user instanceof phpillowUserView
        );
    }

    public function testGetUnknownView()
    {
        try
        {
            $user = phpillowManager::getView( 'unknown_view' );
            $this->fail( 'Expected phpillowNoSuchPropertyException.' );
        }
        catch ( phpillowNoSuchPropertyException $e )
        { /* Expected exception */ }
    }

    public function testSetUnknownViewClass()
    {
        phpillowManager::setViewClass( 'unknown_view', 'phpillowUserView' );
        $user = phpillowManager::getView( 'unknown_view' );

        $this->assertTrue(
            $user instanceof phpillowUserView
        );
    }

    public function testOverwriteViewClass()
    {
        phpillowManager::setViewClass( 'user', 'phpillowGroupView' );
        phpillowManager::setViewClass( 'user', 'phpillowUserView' );
        $user = phpillowManager::getView( 'user' );

        $this->assertTrue(
            $user instanceof phpillowUserView
        );
    }

    public function testCreateDocument()
    {
        phpillowManager::setDocumentClass( 'user', 'phpillowUserDocument' );
        $user = phpillowManager::createDocument( 'user' );

        $this->assertTrue(
            $user instanceof phpillowUserDocument
        );
    }

    public function testCreateUnknownDocument()
    {
        try
        {
            $user = phpillowManager::createDocument( 'unknown_document' );
            $this->fail( 'Expected phpillowNoSuchPropertyException.' );
        }
        catch ( phpillowNoSuchPropertyException $e )
        { /* Expected exception */ }
    }

    public function testFetchDocument()
    {
        phpillowManager::setDocumentClass( 'user', 'phpillowUserDocument' );
        phpillowConnection::createInstance();
        phpillowConnection::setDatabase( 'test' );

        // Create test database
        $db = phpillowConnection::getInstance();
        $db->put( '/test' );

        // Add coument to fetch
        $author = phpillowManager::createDocument( 'user' );
        $author->login = 'kore';
        $author->save();

        // Test fetch
        $author = phpillowManager::fetchDocument( 'user', 'user-kore' );

        $this->assertTrue(
            $author instanceof phpillowUserDocument
        );

        // Remove / clear test database
        $db = phpillowConnection::getInstance();
        $db->delete( '/test' );

        phpillowConnectionTestHelper::reset();
    }

    public function testDeleteDocument()
    {
        phpillowConnection::createInstance();
        phpillowConnection::setDatabase( 'test' );

        // Create test database
        $db = phpillowConnection::getInstance();
        $db->put( '/test' );

        // Add coument to fetch
        $author = phpillowManager::createDocument( 'user' );
        $author->login = 'kore';
        $author->save();

        // Test delete
        phpillowManager::deleteDocument( 'user', 'user-kore' );

        try
        {
            $user = phpillowManager::fetchDocument( 'user', 'user-kore' );
            $this->fail( 'Expected phpillowResponseNotFoundErrorException.' );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        { /* Expected exception */ }

        // Remove / clear test database
        $db = phpillowConnection::getInstance();
        $db->delete( '/test' );

        phpillowConnectionTestHelper::reset();
    }

    public function testDeleteDocumentMultipleRevisions()
    {
        phpillowConnection::createInstance();
        phpillowConnection::setDatabase( 'test' );

        // Create test database
        $db = phpillowConnection::getInstance();
        $db->put( '/test' );

        // Add coument to fetch
        $author = phpillowManager::createDocument( 'user' );
        $author->login = 'kore';
        $author->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $doc->name = 'Kore';
        $doc->save();

        // Test delete
        phpillowManager::deleteDocument( 'user', 'user-kore' );

        try
        {
            $user = phpillowManager::fetchDocument( 'user', 'user-kore' );
            $this->fail( 'Expected phpillowResponseNotFoundErrorException.' );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        { /* Expected exception */ }

        // Remove / clear test database
        $db = phpillowConnection::getInstance();
        $db->delete( '/test' );

        phpillowConnectionTestHelper::reset();
    }

    public function testFetchUnknownDocument()
    {
        try
        {
            $user = phpillowManager::fetchDocument( 'unknown_document', 'user-kore' );
            $this->fail( 'Expected phpillowNoSuchPropertyException.' );
        }
        catch ( phpillowNoSuchPropertyException $e )
        { /* Expected exception */ }
    }

    public function testSetUnknownDocumentClass()
    {
        phpillowManager::setDocumentClass( 'unknown_document', 'phpillowUserDocument' );
        $user = phpillowManager::createDocument( 'unknown_document' );

        $this->assertTrue(
            $user instanceof phpillowUserDocument
        );
    }

    public function testOverwriteDocumentClass()
    {
        phpillowManager::setDocumentClass( 'author', 'phpillowUserDocument' );
        $user = phpillowManager::createDocument( 'author' );

        $this->assertTrue(
            $user instanceof phpillowUserDocument
        );
    }
}
