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
 * @version $Revision: 479 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Response factory to create response objects from JSON results
 *
 * @package Core
 * @version $Revision: 479 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */
class phpillowResponseFactory
{
    /**
     * Parse a server response
     *
     * Parses a server response depending on the response body and the HTTP
     * status code.
     *
     * The method will eith return a plain phpillowResponse object, when the
     * server returned a single document. If the server returned a set of
     * documents you will receive a phpillowResultSetResponse object, with a row
     * property to iterate over all documents returned by the server.
     *
     * For put and delete requests the server will just return a status,
     * wheather the request was successfull, which is represented by a
     * phpillowStatusResponse object.
     *
     * For all other cases most probably some error occured, which is
     * transformed into a phpillowResponseErrorException, which will be thrown
     * by the parse method.
     * 
     * @param int $status 
     * @param string $body 
     * @return phpillowResponse
     */
    public static function parse( $status, $body )
    {
        $response = json_decode( $body );

        // To detect the type of the response from the couch DB server we use
        // the response status which indicates the return type.
        switch ( $status )
        {
            case 200:
                // The HTTP status code 200 - OK indicates, that we got a document
                // or a set of documents as return value.
                //
                // To check wheather we received a set of documents or a single
                // document we can check for the document properties _id or
                // _rev, which are always available for documents and are only
                // available for documents.
                if ( isset( $response->_id ) )
                {
                    return new phpillowResponse( $response );
                }
                else
                {
                    return new phpillowResultSetResponse( $response );
                }

            case 201:
            case 202:
                // The following status codes are given for status responses
                // depending on the request type - which does not matter here any
                // more.
                return new phpillowStatusResponse( $response );

            case 404:
                // The 404 and 409 (412) errors are using custom exceptions
                // extending the base error exception, because they are often
                // requeired to be handled in a special way by the application.
                //
                // Feel free to extend this for other errors as well.
                throw new phpillowResponseNotFoundErrorException( $response );
            case 409: // Conflict
            case 412: // Precondition Failed - we just consider this as a conflict.
                throw new phpillowResponseConflictErrorException( $response );

            default:
                // All other unhandled HTTP codes are for now handled as an error.
                // This may not be true, as lots of other status code may be used
                // for valid repsonses.
                throw new phpillowResponseErrorException( $status, $response );
        }
    }
}

/**
 * Response factory to create response objects from JSON results
 *
 * @package Core
 * @version $Revision: 479 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */
class phpillowResponse
{
    /**
     * Array containing all response properties
     * 
     * @var array
     */
    protected $properties;

    /**
     * Construct response object from JSON result
     * 
     * @param StdClass $body 
     * @return void
     */
    public function __construct( StdClass $body )
    {
        // Set all properties as virtual readonly repsonse object properties.
        foreach ( $body as $property => $value )
        {
            // All direct descandents, which are objects (StdClass) should be
            // transformed to arrays.
            if ( is_object( $value ) )
            {
                $value = (array) $value;
            }

            $this->properties[$property] = $value;
        }
    }

    /**
     * Get available property
     *
     * Receive response object property, if available. If the property is not
     * available, the method will throw an exception.
     * 
     * @param string $property 
     * @return mixed
     */
    public function __get( $property )
    {
        // Check if such an property exists at all
        if ( !isset( $this->properties[$property] ) )
        {
            throw new phpillowNoSuchPropertyException( $property );
        }

        return $this->properties[$property];
    }

    /**
     * Check if property exists.
     * 
     * Check if property exists.
     * 
     * @param string $property 
     * @return bool
     */
    public function __isset( $property )
    {
        return isset( $this->properties[$property] );
    }

    /**
     * Silently ignore each write access on response object properties.
     * 
     * @param string $property 
     * @param mixed $value 
     * @return bool
     */
    public function __set( $property, $value )
    {
        return false;
    }
}

