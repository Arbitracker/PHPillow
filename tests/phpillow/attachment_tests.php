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
class phpillowDocumentAttachmentTests extends PHPUnit_Framework_TestCase
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

    public function testInitalAttachementList()
    {
        $doc = phpillowUserDocument::createNew();
        
        $this->assertEquals(
            array(),
            $doc->_attachments
        );
    }

    public function testUnmodifiedAttachementList()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->save();
        
        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $this->assertEquals(
            array(),
            $doc->_attachments
        );
    }

    public function testAddFileAsAttachment()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( dirname( __FILE__ ) . '/data/image_png.png' );
        $doc->save();
        
        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $this->assertEquals(
            array(
                'image_png.png' => array(
                    'stub'         => true,
                    'content_type' => 'application/octet-stream',
                    'length'       => 4484,
                    'revpos'       => 1,
                ),
            ),
            $doc->_attachments
        );
    }

    public function testGetAttachementFromBackend()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( $file = dirname( __FILE__ ) . '/data/image_png.png' );
        $doc->save();
        
        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $response = $doc->getFile( 'image_png.png' );

        $this->assertSame(
            file_get_contents( $file ),
            $response->data
        );

        $this->assertSame(
            'application/octet-stream',
            $response->contentType
        );
    }

    public function testGetAttachementFromBackendReadAfter()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( $file = dirname( __FILE__ ) . '/data/image_png.png' );
        $doc->save();
        
        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $response = $doc->getFile( 'image_png.png' );

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $this->assertSame(
            'kore',
            $doc->login
        );
    }

    public function testOverwriteAttachment()
    {
        $db = phpillowConnection::getInstance();

        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( dirname( __FILE__ ) . '/data/image_png.png' );
        $doc->save();
        
        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $doc->attachFile( $file = dirname( __FILE__ ) . '/data/image_jpg.jpg', 'image_png.png' );
        $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $response = $doc->getFile( 'image_png.png' );

        $this->assertSame(
            file_get_contents( $file ),
            $response->data
        );

        $this->assertSame(
            'application/octet-stream',
            $response->contentType
        );
    }

    public function testGetAttachementMultipleFiles()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( dirname( __FILE__ ) . '/data/image_png.png' );
        $doc->attachFile( dirname( __FILE__ ) . '/data/image_jpg.jpg' );
        $doc->save();
        
        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $this->assertEquals(
            array(
                'image_png.png' => array(
                    'stub'         => true,
                    'content_type' => 'application/octet-stream',
                    'length'       => 4484,
                    'revpos'       => 1,
                ),
                'image_jpg.jpg' => array(
                    'stub'         => true,
                    'content_type' => 'application/octet-stream',
                    'length'       => 3146,
                    'revpos'       => 1,
                ),
            ),
            $doc->_attachments
        );
    }

    public function testAddFileWithMimeType()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( dirname( __FILE__ ) . '/data/image_png.png', 'image_png.png', 'image/png' );
        $doc->save();
        
        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $this->assertEquals(
            array(
                'image_png.png' => array(
                    'stub'         => true,
                    'content_type' => 'image/png',
                    'length'       => 4484,
                    'revpos'       => 1,
                ),
            ),
            $doc->_attachments
        );
    }

    public function testManuallySetContentType()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( $file = dirname( __FILE__ ) . '/data/image_png.png', 'image_png.png', 'image/png' );
        $doc->save();
        
        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $response = $doc->getFile( 'image_png.png' );

        $this->assertSame(
            file_get_contents( $file ),
            $response->data
        );

        $this->assertSame(
            'image/png',
            $response->contentType
        );
    }
}
