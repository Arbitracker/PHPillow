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
        
        $doc = phpillowUserDocument::fetchById( 'user-kore' );
        $this->assertEquals(
            array(),
            $doc->_attachments
        );
    }

    public function testAddFileAsAttachment()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( __DIR__ . '/data/image_png.png' );
        $doc->save();
        
        $attachment = new StdClass();
        $attachment->stub = true;
        $attachment->content_type = 'application/octet-stream';
        $attachment->length = 4484;

        $doc = phpillowUserDocument::fetchById( 'user-kore' );
        $this->assertEquals(
            array(
                'image_png.png' => $attachment,
            ),
            $doc->_attachments
        );
    }

    public function testGetAttachementFromBackend()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->attachFile( $file = __DIR__ . '/data/image_png.png' );
        $doc->save();
        
        $doc = phpillowUserDocument::fetchById( 'user-kore' );

        $this->assertSame(
            file_get_contents( $file ),
            $doc->getFile( 'image_png.png' )
        );
    }
}
