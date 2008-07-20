<?php
/**
 * Test helper classes
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Helper class to reset singleton in phpillowConnection.
 */
class phpillowConnectionTestHelper extends phpillowConnection
{
    public static function reset()
    {
        self::$instance = null;
        self::$database = null;
    }
}

/**
 * Helper class to reset singleton in phpillowConnection.
 */
class phpillowDocumentAllPublic extends phpillowDocument
{
    public function __construct()
    {
        parent::__construct();
    }

    public function generateId()
    {
        return null;
    }

    public function stringToId( $string, $replace = '_' )
    {
        return parent::stringToId( $string, $replace );
    }
}

class phpillowUserTestDocument extends phpillowUserDocument
{
    public function generateId()
    {
        return parent::generateId();
    }
}

class phpillowGroupTestDocument extends phpillowGroupDocument
{
    public function generateId()
    {
        return parent::generateId();
    }
}

class phpillowTestNullIdDocument extends phpillowUserDocument
{
    protected $requiredProperties = array();

    protected function generateId()
    {
        return null;
    }
}

class phpillowViewTestPublic extends phpillowView
{
    public function buildViewQuery( array $options )
    {
        return parent::buildViewQuery( $options );
    }
}

