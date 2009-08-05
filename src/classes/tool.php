<?php
/**
 * phpillow tool
 *
 * This file is part of phpillow.
 *
 * phpillow is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * phpillow is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with phpillow; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Basic tool handling in- end exports of CouchDB dumps.
 *
 * API and format should be compatible with couchdb-python [1].
 *
 * [1] http://code.google.com/p/couchdb-python/
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */
class phpillowTool
{
    /**
     * Data source name for the CouchDB connection
     * 
     * @var string
     */
    protected $dsn;

    /**
     * CLI tool options
     * 
     * @var array
     */
    protected $options;

    /**
     * Parsed connection information
     * 
     * @var array
     */
    protected $connectionInfo = array(
        'host' => 'localhost',
        'port' => '5984',
        'user' => null,
        'pass' => null,
        'path' => '/',
    );

    /**
     * Construct tool
     *
     * Construct tool from database DSN (Data-Source-Name, the URL defining the
     * databases location) and an optional set of options.
     * 
     * @param mixed $dsn 
     * @param array $options 
     * @return void
     */
    public function __construct( $dsn, array $options = array() )
    {
        $this->dsn     = $dsn;
        $this->options = $options;

        if ( !array_search( 'string', stream_get_wrappers() ) )
        {
            stream_wrapper_register( 'string', 'phpillowToolStringStream' );
        }
    }

    /**
     * Print version
     *
     * Print version of the tool, if the version flag has been set.
     * 
     * @return bool
     */
    protected function printVersion()
    {
        if ( !isset( $this->options['version'] ) )
        {
            return false;
        }

        $version = '$Revision$';
        if ( preg_match( '(\\$Revision:\\s+(?P<revision>\\d+)\\s*\\$)', $version, $match ) )
        {
            $version = 'svn-' . $match['revision'];
        }

        echo "PHPillow backup tool - version: ", $version, "\n";
        return true;
    }

    /**
     * Parse the provided connection information
     *
     * Returns false,if the conenction information could not be parser
     * properly.
     * 
     * @return bool
     */
    protected function parseConnectionInformation()
    {
        if ( ( $info = @parse_url( $this->dsn ) ) === false )
        {
            echo "Could not parse provided DSN.\n";
            return false;
        }

        foreach ( $info as $key => $value )
        {
            if ( array_key_exists( $key, $this->connectionInfo ) )
            {
                $this->connectionInfo[$key] = $value;
            }
        }

        if ( isset( $this->options['username'] ) )
        {
            $this->connectionInfo['user'] = $this->options['username'];
        }

        if ( isset( $this->options['password'] ) )
        {
            $this->connectionInfo['pass'] = $this->options['password'];
        }

        return true;
    }

    /**
     * Execute dump command
     *
     * Returns a proper status code indicating successful execution of the
     * command.
     *
     * @return int
     */
    public function dump()
    {
        if ( $this->printVersion() )
        {
            return 0;
        }

        if ( !$this->parseConnectionInformation() )
        {
            return 1;
        }

        return 0;
    }

    /**
     * Clean up document definition
     *
     * Returns the cleaned up document body as a result.
     * 
     * @param array $document 
     * @return string
     */
    protected function getDocumentBody( array $document )
    {
        if ( strpos( $document['Content-Type'], 'application/json' ) === 0 )
        {
            $source = json_decode( $document['body'], true );
            unset( $source['_rev'] );
            return json_encode( $source );
        }

        if ( is_array( $document['body'] ) )
        {
            $main   = array_shift( $document['body'] );
            $source = json_decode( $main['body'], true );
            unset( $source['_rev'] );

            $source['_attachments'] = array();
            foreach ( $document['body'] as $attachment )
            {
                $source['_attachments'][$attachment['Content-ID']] = array(
                    'content_type' => $attachment['Content-Type'],
                    'data'         => base64_encode( $attachment['body'] ),
                );
            }

            return json_encode( $source );
        }

        throw new Exception( "Invalid document: " . var_export( $document, true ) );
    }

    /**
     * Execute load command
     *
     * Returns a proper status code indicating successful execution of the
     * command.
     *
     * @return int
     */
    public function load()
    {
        if ( $this->printVersion() )
        {
            return 0;
        }

        if ( !$this->parseConnectionInformation() )
        {
            return 1;
        }

        // Open input stream to read contents from
        $stream = isset( $options['input'] ) ? fopen( $options['input'] ) : STDIN;
        $multipartParser = new phpillowToolMultipartParser( $stream );

        phpillowConnection::createInstance(
            $this->connectionInfo['host'],
            $this->connectionInfo['port'],
            $this->connectionInfo['user'],
            $this->connectionInfo['pass']
        );
        $db = phpillowConnection::getInstance();

        while ( ( $document = $multipartParser->getDocument() ) !== false )
        {
            // @TODO: Check hash
            // @TODO: Add error handling

            $path = $this->connectionInfo['path'] . '/' . $document['Content-ID'];
            $db->put( $path, $this->getDocumentBody( $document ) );
        }

        return 0;
    }
}

