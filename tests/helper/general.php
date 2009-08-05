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

    protected function request( $method, $path, $data, $raw = false )
    {
        return null;
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

    protected function getType()
    {
        return self::$type;
    }

    public function stringToId( $string, $replace = '_' )
    {
        return parent::stringToId( $string, $replace );
    }

    public static function createNew( $docType = null )
    {
        return parent::createNew( $docType === null ? __CLASS__ : $docType );
    }
}

class phpillowUserTestDocument extends phpillowUserDocument
{
    public static function createNew($docType = null)
    {
        return parent::createNew( $docType === null ? __CLASS__ : $docType );
    }

    protected function getType()
    {
        return 'user';
    }

    public function generateId()
    {
        return parent::generateId();
    }
}

class phpillowGroupTestDocument extends phpillowGroupDocument
{
    public static function createNew($docType = null)
    {
        return parent::createNew( $docType === null ? __CLASS__ : $docType );
    }

    protected function getType()
    {
        return 'group';
    }

    public function generateId()
    {
        return parent::generateId();
    }
}

class phpillowTestNullIdDocument extends phpillowUserDocument
{
    protected $requiredProperties = array();

    public static function createNew( $docType = null )
    {
        return parent::createNew( $docType === null ? __CLASS__ : $docType );
    }

    protected function getType()
    {
        return 'null';
    }

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

class phpillowTestTool extends phpillowTool {
    public function getConnectionInformation()
    {
        $this->parseConnectionInformation();
        return $this->connectionInfo;
    }
}

