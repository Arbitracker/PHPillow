<?php
/**
 * phpillow CouchDB backend
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
 * Basic couch DB connection handling class
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */
class phpillowConnection
{
    /**
     * CouchDB connection options
     * 
     * @var array
     */
    protected $options = array(
        'host'       => 'localhost',
        'port'       => 5984,
        'ip'         => '127.0.0.1',
        'keep-alive' => true,
        'http-log'   => false,
    );

    /**
     * Currently used database
     *
     * @var string
     */
    protected static $database = null;

    /**
     * Instance of phpillowConnection for singleton implementation.
     *
     * @var phpillowConnection
     */
    protected static $instance = null;

    /**
     * Connection pointer for connections, once keep alive is working on the
     * CouchDb side.
     * 
     * @var resource
     */
    protected $connection;

    /**
     * Array containing the list of allowed HTTP methods to interact with couch
     * server.
     *
     * @var array
     */
    protected static $allowedMethods = array(
        'DELETE'    => true,
        'GET'       => true,
        'POST'      => true,
        'PUT'       => true,
    );

    /**
     * Construct a couch DB connection
     *
     * Construct a couch DB connection from basic connection parameters for one
     * given database. Method is protected and should not be called directly.
     * For initializing a connection use the static method createInstance().
     *
     * @param string $host
     * @param int $port
     * @return phpillowConnection
     */
    protected function __construct( $host, $port )
    {
        $this->options['host'] = (string) $host;
        $this->options['port'] = (int) $port;

        // @TODO: Implement this properly
        $this->options['ip']   = '127.0.0.1';
    }

    /**
     * Set option value
     *
     * Set the value for an connection option. Throws an
     * phpillowOptionException for unknown options.
     * 
     * @param string $option 
     * @param mixed $value 
     * @return void
     */
    public function setOption( $option, $value )
    {
        switch ( $option )
        {
            case 'keep-alive':
                $this->options[$option] = (bool) $value;
                break;

            case 'http-log':
                $this->options[$option] = $value;
                break;

            default:
                throw new phpillowOptionException( $option );
        }
    }

    /**
     * Create a new couch DB connection instance.
     *
     * Static method to create a new couch DB connection instance. This method
     * should be used to configure the connection for later use.
     *
     * The host and its port default to localhost:5984.
     *
     * @param string $host
     * @param int $port
     * @return void
     */
    public static function createInstance( $host = 'localhost', $port = 5984 )
    {
        // Prevent from reestablishing connection during one run, without
        // explicit cleanup before.
        if ( self::$instance !== null )
        {
            throw new phpillowConnectionException(
                'Connection already established.',
                array()
            );
        }

        // Create connection and store it in static property to be accessible
        // by static getInstance() method.
        self::$instance = new phpillowConnection( $host, $port );
    }

    /**
     * Set database to use
     *
     * Set the name of database to use. You do not need to provide this as a
     * path, but only its name.
     * 
     * @param string $database 
     * @return void
     */
    public static function setDatabase( $database )
    {
        self::$database = '/' . $database . '/';
    }

    /**
     * Return name of the currently used database
     * 
     * Return name of the currently used database
     * 
     * @return string
     */
    public static function getDatabase()
    {
        if ( self::$database === null )
        {
            throw new phpillowNoDatabaseException();
        }

        return self::$database;
    }

    /**
     * Get configured couch DB connection instance
     *
     * Get configured couch DB connection instance
     *
     * @return phpillowConnection
     */
    public static function getInstance()
    {
        // Check if connection has been properly confugured, and bail out
        // otherwise.
        if ( self::$instance === null )
        {
            throw new phpillowConnectionException(
                'No connection to database configured.',
                array()
            );
        }

        // If a connection has been configured properly, jsut return it
        return self::$instance;
    }

    /**
     * HTTP method request wrapper
     *
     * Wraps the HTTP method requests to interact with teh couch server. The
     * supported methods are:
     *  - GET
     *  - DELETE
     *  - POST
     *  - PUT
     *
     * Each request takes the request path as the first parameter and
     * optionally data as the second parameter. The requests will return a
     * object wrapping the server response.
     *
     * @param string $method
     * @param array $params
     * @return phpillow...
     */
    public function __call( $method, $params )
    {
        // Check if request method is an allowed HTTP request method.
        $method = strtoupper( $method );
        if ( !isset( self::$allowedMethods[$method] ) )
        {
            throw new phpillowInvalidRequestException(
                'Unsupported request method: %method',
                array(
                    'method' => $method,
                )
            );
        }

        // Check if required parameter containing the path is set and valid.
        if ( !isset( $params[0] ) ||
             !is_string( $params[0] ) ||
             ( $params[0][0] !== '/' ) )
        {
            throw new phpillowInvalidRequestException(
                'Absolute path required as first parameter for the request.',
                array()
            );
        }
        $path = $params[0];

        // Check if data has been provided
        $data = ( ( isset( $params[1] ) ) ? (string) $params[1] : null );
        $raw  = ( ( isset( $params[2] ) ) ? (bool) $params[2] : false );

        // Finally perform request and return the result from the server
        return $this->request( $method, $path, $data, $raw );
    }

    /**
     * Check for server connection
     *
     * Checks if the connection already has been established, or tries to
     * establish the connection, if not done yet.
     * 
     * @return void
     */
    protected function checkConnection()
    {
        // If the connection could not be established, fsockopen sadly does not
        // only return false (as documented), but also always issues a warning.
        if ( ( $this->connection === null ) &&
             ( ( $this->connection = fsockopen( $this->options['ip'], $this->options['port'], $errno, $errstr ) ) === false ) )
        {
            // This is a bit hackisch...
            throw new phpillowConnectionException(
                "Could not connect to server at %ip:%port: '%errno: %error'",
                array(
                    'ip'    => $this->options['ip'],
                    'port'  => $this->options['port'],
                    'error' => $errstr,
                    'errno' => $errno,
                )
            );
        }
    }

    /**
     * Build a HTTP 1.1 request
     *
     * Build the HTTP 1.1 request headers from the gicven input.
     * 
     * @param string $method
     * @param string $path
     * @param string $data
     * @return string
     */
    protected function buildRequest( $method, $path, $data )
    {
        // Create basic request headers
        $request = "$method $path HTTP/1.1\r\nHost: {$this->options['host']}\r\n";

        // Set keep-alive header, which helps to keep to connection
        // initilization costs low, especially when the database server is not
        // available in the locale net.
        $request .= "Connection: " . ( $this->options['keep-alive'] ? 'Keep-Alive' : 'Close' ) . "\r\n";

        // Also add headers and request body if data should be sent to the
        // server. Otherwise just add the closing mark for the header section
        // of the request.
        if ( $data !== null )
        {
            $request .= "Content-Length: " . strlen( $data ) . "\r\n\r\n";
            $request .= "$data\r\n";
        }
        else
        {
            $request .= "\r\n";
        }

        return $request;
    }

    /**
     * Perform a request to the server and return the result
     *
     * Perform a request to the server and return the result converted into a
     * phpillowResponse object. If you do not expect a JSON structure, which
     * could be converted in such a response object, set the forth parameter to
     * true, and you get a response object retuerned, containing the raw body.
     *
     * @param string $method
     * @param string $path
     * @param string $data
     * @return phpillowResponse
     */
    protected function request( $method, $path, $data, $raw = false )
    {
        // Try establishing the connection to the server
        $this->checkConnection();

        // Send the build request to the server
        fwrite( $this->connection, $request = $this->buildRequest( $method, $path, $data ) );

        // If requested log request information to http log
        if ( $this->options['http-log'] !== false )
        {
            $fp = fopen( $this->options['http-log'], 'a' );
            fwrite( $fp, $request );
        }

        // Read server response headers
        $rawHeaders = '';
        $headers = array(
            'connection' => ( $this->options['keep-alive'] ? 'Keep-Alive' : 'Close' ),
        );

        while ( ( ( $line = rtrim( fgets( $this->connection ) ) ) !== '' ) ||
                ( $headers === array() ) ) 
        {
            // Also store raw headers for later logging
            $rawHeaders .= $line . "\n";

            // Extract header values
            if ( preg_match( '(^HTTP/(?P<version>\d+\.\d+)\s+(?P<status>\d+))', $line, $match ) )
            {
                $headers['version'] = $match['version'];
                $headers['status']  = (int) $match['status'];
            }
            else
            {
                list( $key, $value ) = explode( ':', $line, 2 );
                $headers[strtolower( $key )] = ltrim( $value );
            }
        }

        // Read response body
        $body = '';
        if ( !isset( $headers['transfer-encoding'] ) ||
             ( $headers['transfer-encoding'] !== 'chunked' ) )
        {
            // HTTP 1.1 supports chunked transfer encoding, if the according
            // header is not set, just read the specified amount of bytes.
            //
            // @TODO: Maybe also handle missing content-length header.
            $bytesToRead = (int) $headers['content-length'];

            // Read body only as specified by chunk sizes, everything else
            // are just footnotes, which are not relevant for us.
            while ( $bytesToRead > 0 )
            {
                $body .= $read = fgets( $this->connection, $bytesToRead + 1 );
                $bytesToRead -= strlen( $read );
            }
        }
        else
        {
            // When transfer-encoding=chunked has been specified in the
            // response headers, read all chunks and sum them up to the body,
            // until the server has finished. Ignore all additional HTTP
            // options after that.
            do {
                $line = rtrim( fgets( $this->connection ) );

                // Get bytes to read, with option appending comment
                if ( preg_match( '(^([0-9a-f]+)(?:;.*)?$)', $line, $match ) )
                {
                    $bytesToRead = hexdec( $match[1] );

                    // Read body only as specified by chunk sizes, everything else
                    // are just footnotes, which are not relevant for us.
                    while ( $bytesToRead > 0 )
                    {
                        $body .= $read = fgets( $this->connection, $bytesToRead + 3 );
                        $bytesToRead -= strlen( $read );
                    }
                }
            } while ( $line !== '' );
        }

        // Reset the connection if the server asks for it.
        if ( $headers['connection'] !== 'Keep-Alive' )
        {
            fclose( $this->connection );
            $this->connection = null;
        }

        // If requested log response information to http log
        if ( $this->options['http-log'] !== false )
        {
            fwrite( $fp, "\n" . $rawHeaders . "\n" . $body . "\n" );
            fclose( $fp );
        }

        // Create repsonse object from couch db response
        return phpillowResponseFactory::parse( $headers['status'], $body, $raw );
    }
}

