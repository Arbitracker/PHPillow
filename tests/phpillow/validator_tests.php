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
class phpillowValidatorTests extends PHPUnit_Framework_TestCase
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

    public function testStringValidator()
    {
        $validator = new phpillowStringValidator();
        
        $this->assertSame(
            'String',
            $validator->validate( 'String' )
        );

        $this->assertSame(
            '123',
            $validator->validate( 123 )
        );
    }

    public function testTextValidator()
    {
        $validator = new phpillowTextValidator();
        
        $this->assertSame(
            'Text',
            $validator->validate( 'Text' )
        );

        $this->assertSame(
            '123',
            $validator->validate( 123 )
        );
    }

    public function testArrayValidator()
    {
        $validator = new phpillowArrayValidator();

        $this->assertSame(
            array(),
            $validator->validate( array() )
        );

        $this->assertSame(
            array( 'foo', 'bar' ),
            $validator->validate( array( 'foo', 'bar' ) )
        );

        $this->assertSame(
            array( 1, 2, 3 ),
            $validator->validate( array( 1, 2, 3 ) )
        );

        try
        {
            $validator->validate( 'foo' );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Input is not an array.',
                $e->getText()
            );
        }
    }

    public function testDateFromNumeric()
    {
        $validator = new phpillowDateValidator();

        $this->assertSame(
            'Fri, 13 Feb 2009 23:31:30 +0000',
            $validator->validate( 1234567890 ),
            'Test from int.'
        );

        $this->assertSame(
            'Fri, 13 Feb 2009 23:31:30 +0000',
            $validator->validate( '1234567890' ),
            'Test from numeric string.'
        );

        $this->assertSame(
            'Fri, 13 Feb 2009 23:31:30 +0000',
            $validator->validate( 1234567890. ),
            'Test from float.'
        );
    }

    public function testDateFromString()
    {
        $validator = new phpillowDateValidator();

        $this->assertSame(
            'Wed, 15 Apr 1981 08:16:00 +0000',
            $validator->validate( '15 Apr 1981 8:16' )
        );

        $this->assertSame(
            'Wed, 15 Apr 1981 08:16:00 +0000',
            $validator->validate( '15.04.1981 8:16' )
        );

        $this->assertSame(
            'Wed, 15 Apr 1981 08:16:00 +0000',
            $validator->validate( '04/15/81 8:16' )
        );
    }

    public function testImageNotExisting()
    {
        $validator = new phpillowImageFileLocationValidator();

        try
        {
            $validator->validate( 'not_existing_file' );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowRuntimeException $e )
        {
            $this->assertSame(
                'Runtime exception: %message',
                $e->getText()
            );
        }
    }

    public function testImageUnhandledFormat()
    {
        $validator = new phpillowImageFileLocationValidator();

        try
        {
            $validator->validate( __DIR__ . '/data/image_bmp.bmp' );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Unsupported image format provided.',
                $e->getText()
            );
        }
    }

    public function testImageSupportedFormats()
    {
        $validator = new phpillowImageFileLocationValidator();

        $images = array(
            __DIR__ . '/data/image_png.png',
            __DIR__ . '/data/image_gif.gif',
            __DIR__ . '/data/image_jpg.jpg',
        );

        foreach ( $images as $image )
        {
            $this->assertSame(
                $image,
                $validator->validate( $image )
            );
        }
    }

    public function testRegexpValidator()
    {
        $validator = new phpillowRegexpValidator( '(^[a-z]+$)' );

        $this->assertSame(
            'abcdef',
            $validator->validate( 'abcdef' )
        );

        try
        {
            $validator->validate( '123' );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Input %input did not match regular expression %expression.',
                $e->getText()
            );
        }
    }

    public function testEmailValidator()
    {
        $validator = new phpillowEmailValidator( 10 );

        $this->assertSame(
            'kn@ez.no',
            $validator->validate( 'kn@ez.no' )
        );

        $this->assertSame(
            'kore@php.net',
            $validator->validate( 'kore@php.net' )
        );

        try
        {
            $validator->validate( 'foo' );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Invalid mail address provided: %email',
                $e->getText()
            );
        }
    }
}

