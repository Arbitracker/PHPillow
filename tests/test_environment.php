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
    $files = require __DIR__ . '/../src/classes/autoload.php';

    if ( !isset( $files[$class] ) )
    {
        return false;
    }
    else
    {
        return require __DIR__ . '/../src/' . $files[$class];
    }
}

require __DIR__ . '/helper/general.php';

/**
 * Fix error reporting settings for test runs
 */
error_reporting( E_ALL | E_STRICT | E_DEPRECATED );
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
        phpillowConnection::createInstance();
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
}

