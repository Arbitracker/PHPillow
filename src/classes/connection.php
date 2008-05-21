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
     * Configured host of couch server instance
     *
     * @var string
     */
    protected $host;

    /**
     * Configured port for couch server instance
     *
     * @var int
     */
    protected $port;

    /**
     * IP for host, to force caching of DNS response
     *
     * @var string
     */
    protected $ip;

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
        $this->host         = (string) $host;
        $this->port         = (int) $port;

        // @TODO: Implement this properly
        $this->ip           = '127.0.0.1';
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

        // Finally perform request and return the result from the server
        return $this->request( $method, $path, $data );
    }

    /**
     * Perform a request to the server and return the result
     *
     * Perform a request to the server and return the result
     *
     * @param string $method
     * @param string $path
     * @param string $data
     * @return phpillow...
     */
    protected function request( $method, $path, $data )
    {
        // Try establishing the connection to the server
        //
        // If the connection could not be established, fsockopen sadly does not
        // only return false (as document), but also always issues a warning.
        // This is converted to an exception, so we just catch this..
        try
        {
            if ( $this->connection === null )
            {
                if ( ( $this->connection = fsockopen( $this->ip, $this->port, $errno, $errstr ) ) === false )
                {
                    // This is a bit hackisch...
                    throw new phpillowPhpErrorException( 'Connection failed.' );
                }
            }
        }
        catch ( phpillowPhpErrorException $e )
        {
            throw new phpillowConnectionException(
                "Could not connect to server at %ip:%port: '%errno: %error'",
                array(
                    'ip'    => $this->ip,
                    'port'  => $this->port,
                    'error' => $errstr,
                    'errno' => $errno,
                )
            );
        }

        // Create basic request headers
        $request = "$method $path HTTP/1.0\r\nHost: {$this->host}\r\n";

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

        // Send the build request to the server
        fwrite( $this->connection, $request );

        // Read server response
        //
        // @TODO: Handle chunked and non-chunked connections here. For chunked
        // handling see patch available in the backend root directory. This is
        // easy to detect after reading the header and then works with "all"
        // versions of CouchDB.
        $response = "";
        while( !feof( $this->connection ) )
        {
            $response .= fgets( $this->connection );
        }

        // Split response into headers and response body
        if ( substr( $response, "\r\n\r\n" ) !== false )
        {
            list( $headers, $body ) = explode( "\r\n\r\n", $response, 2 );
        }
        else
        {
            $headers = $response;
            $body = null;
        }

        // Always reset the connection. We requested to close it.
        $this->connection = null;

        // Extract response status code from headers
        $status = 501;
        if ( preg_match( '(^HTTP\S+\s+(?P<status>\d+))', $headers, $match ) )
        {
            $status = (int) $match['status'];
        }

        // Create repsonse object from couch db response
        return phpillowResponseFactory::parse( $status, $body );
    }
}

