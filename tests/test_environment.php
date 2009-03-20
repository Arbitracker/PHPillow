<?php

/**
 * Test environment constants
 */

/**
 * Set timezone to get clearly defined dates
 */
date_default_timezone_set( 'UTC' );

function __autoload( $class )
{
    $files = require dirname(__FILE__) . '/../src/classes/autoload.php';

    if ( !isset( $files[$class] ) )
    {
        return false;
    }
    else
    {
        return require dirname(__FILE__) . '/../src/' . $files[$class];
    }
}

require dirname(__FILE__) . '/helper/general.php';

/**
 * Fix error reporting settings for test runs
 */
error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', true );
if ( function_exists( 'xdebug_enable' ) )
{
    xdebug_enable( true );
}

/**
 * Set up mocks and fakes for the current test environment
 *
 * @package Tests
 * @version $Revision$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class phpillowTestEnvironmentSetup
{
    /**
     * Reset database depending on provided options 
     * 
     * @param array $options 
     * @return void
     */
    public static function resetDatabase( array $options = array() )
    {
        phpillowConnectionTestHelper::reset();

        // Initilize wanted connection handler
        $handler = isset( $options['handler'] ) ? $options['handler'] : 'phpillowConnection';
        call_user_func(array($handler, 'createInstance'));
        $db = phpillowConnection::getInstance();

        try
        {
            $db->delete( '/test' );
        } catch ( Exception $e )
        { /* Ignored */ }

        if ( isset( $options['database'] ) )
        {
            // Create test database
            phpillowConnection::setDatabase( $options['database'] );
            $db = phpillowConnection::getInstance();
            $db->put( '/' . $options['database'] );
        }
        else
        {
            // Reset all connection settings in the end
            phpillowConnectionTestHelper::reset();
        }
    }

    /**
     * Remove all temporary files from test directory
     * 
     * @return void
     */
    public static function resetTmpDir()
    {
        foreach( glob( dirname(__FILE__) . '/temp/*' ) as $file )
        {
            unlink( $file );
        }
    }
}

