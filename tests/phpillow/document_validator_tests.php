<?php


class phpillowDocumentValidatorTests extends PHPUnit_Framework_TestCase
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

    public function testDocumentValidatorNotSaved()
    {
        $validator = new phpillowDocumentValidator();

        // Create new publisher to validate
        $doc = phpillowGroupDocument::createNew();
        $doc->name = 'Maintainer';
        
        try
        {
            $validator->validate( $doc );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Invalid document type provided.',
                $e->getText()
            );
        }
    }

    public function testDocumentValidatorNoDocument()
    {
        $validator = new phpillowDocumentValidator();

        try
        {
            $validator->validate( false );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Invalid document type provided.',
                $e->getText()
            );
        }
    }

    public function testDocumentValidatorValid()
    {
        $validator = new phpillowDocumentValidator();

        // Create new publisher to validate
        $doc = phpillowGroupDocument::createNew();
        $doc->name = 'Maintainer';
        $doc->description = 'In line 0!';
        $doc->save();

        $this->assertSame(
            'group-maintainer',
            $validator->validate( $doc )
        );
    }

    public function testDocumentValidatorSpecifiedInvalid()
    {
        $validator = new phpillowDocumentValidator( 'phpillowUserDocument' );

        // Create new publisher to validate
        $doc = phpillowGroupDocument::createNew();
        $doc->name = 'Maintainer';
        $doc->save();

        try
        {
            $validator->validate( $doc );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Invalid document type provided.',
                $e->getText()
            );
        }
    }

    public function testDocumentValidatorSpecifiedValid()
    {
        $validator = new phpillowDocumentValidator( 'phpillowGroupDocument' );

        // Create new publisher to validate
        $doc = phpillowGroupDocument::createNew();
        $doc->name = 'Maintainer';
        $doc->save();

        $this->assertSame(
            'group-maintainer',
            $validator->validate( $doc )
        );
    }

    public function testDocumentArrayValidatorNoArray()
    {
        $validator = new phpillowDocumentValidator();

        try
        {
            $validator->validate( false );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Invalid document type provided.',
                $e->getText()
            );
        }
    }

    public function testDocumentArrayValidatorValid()
    {
        $validator = new phpillowDocumentArrayValidator();

        $doc1 = phpillowGroupDocument::createNew();
        $doc1->name = 'Maintainer';
        $doc1->save();

        $doc2 = phpillowUserDocument::createNew();
        $doc2->login = 'kore';
        $doc2->save();

        $this->assertSame(
            array(
                'group-maintainer',
                'user-kore',
            ),
            $validator->validate( array( 
                $doc1,
                $doc2,
            ) )
        );
    }

    public function testDocumentArrayValidatorSpecifiedInvalid()
    {
        $validator = new phpillowDocumentArrayValidator( 'phpillowUserDocument' );

        $doc1 = phpillowGroupDocument::createNew();
        $doc1->name = 'Maintainer';
        $doc1->save();

        $doc2 = phpillowUserDocument::createNew();
        $doc2->login = 'kore';
        $doc2->save();

        try
        {
            $validator->validate( array( 
                $doc1,
                $doc2,
            ) );
            $this->fail( 'Expected phpillowValidationException.' );
        }
        catch ( phpillowValidationException $e )
        {
            $this->assertSame(
                'Invalid document type provided.',
                $e->getText()
            );
        }
    }

    public function testDocumentArrayValidatorSpecifiedValid()
    {
        $validator = new phpillowDocumentArrayValidator( 'phpillowGroupDocument' );

        $doc1 = phpillowGroupDocument::createNew();
        $doc1->name = 'Maintainer';
        $doc1->save();

        $doc2 = phpillowGroupDocument::createNew();
        $doc2->name = 'Maintainers';
        $doc2->description = 'The project leaders.';
        $doc2->save();

        $this->assertSame(
            array(
                'group-maintainer',
                'group-maintainers',
            ),
            $validator->validate( array( 
                $doc1,
                $doc2,
            ) )
        );
    }
}

