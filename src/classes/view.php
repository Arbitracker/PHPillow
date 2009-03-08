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
 * Wrapper base for views in the database
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */
abstract class phpillowView extends phpillowDocument
{
    /**
     * List of required properties. For each required property, which is not
     * set, a validation exception will be thrown on save.
     * 
     * @var array
     */
    protected $requiredProperties = array(
        'language',
        'views'
    );

    /**
     * Document type, may be a string matching the regular expression:
     *  (^[a-zA-Z0-9_]+$)
     * 
     * @var string
     */
    protected static $type = '_view';

    /**
     * View functions to be registered on the server
     *
     * @var array
     */
    protected $viewDefinitions = array();

    /**
     * Reduce function for a view function.
     *
     * A reduce function may be used to aggregate / reduce the results
     * calculated by a view function. See the CouchDB documentation for more
     * results: @TODO: Not yet documented.
     *
     * Each view reduce function MUST have a view definition with the same
     * name, otherwise there is nothing to reduce.
     * 
     * @var array
     */
    protected $viewReduces = array();

    /**
     * Construct new document
     * 
     * Construct new document
     * 
     * @return void
     */
    public function __construct()
    {
        $this->properties = array(
            'language'  => new phpillowRegexpValidator( '(^(?:javascript)$)' ),
            'views'     => new phpillowArrayValidator(),
        );

        parent::__construct();

        $this->language = 'javascript';
        $this->views = $this->viewDefinitions;
    }

    /**
     * Get name of view
     * 
     * Get name of view
     * 
     * @return string
     */
    protected static function getViewName()
    {
        throw new phpillowRuntimeException(
            'This method should be considerd abstract, but PHP does not allow this.'
        );
    }

    /**
     * Get document ID from object ID
     *
     * Composes the document ID out of the document type and the generated ID
     * for the current document.
     * 
     * @param string $type 
     * @param string $id 
     * @return string
     */
    protected static function getDocumentId( $type, $id )
    {
        return '_design/' . $id;
    }

    /**
     * Get ID from document
     *
     * The ID normally should be calculated on some meaningful / unique
     * property for the current ttype of documents. The returned string should
     * not be too long and should not contain multibyte characters.
     * 
     * @return string
     */
    protected function generateId()
    {
        return $this->stringToId( static::getViewName() );
    }

    /**
     * Wrapper for more convenient view queries
     *
     * Wrap all static calls to a extended view class, instantiate it and then
     * call query method on the view object, reusing the called method name to
     * query the view.
     * 
     * @param string $method 
     * @param array $parameters 
     * @return phpillowResultArray
     */
    public static function __callStatic( $method, $parameters )
    {
        $view = new static();

        // Check if options were set
        $options = ( isset( $parameters[0] ) ? $parameters[0] : array() );

        // Execute query in normal manner
        return $view->query( $method, $options );
    }

    /**
     * Build view query string from options
     *
     * Validates and transformed paased options to limit the view data, to fit
     * the specifications in the HTTP view API, documented at:
     * http://www.couchdbwiki.com/index.php?title=HTTP_View_API#Querying_Options
     * 
     * @param array $options 
     * @return string
     */
    protected function buildViewQuery( array $options )
    {
        // Return empty query string, if no options has been passed
        if ( $options === array() )
        {
            return '';
        }

        $queryString = '?';
        foreach ( $options as $key => $value )
        {
            switch ( $key )
            {
                case 'key':
                case 'startkey':
                case 'endkey':
                    // These values has to be valid JSON encoded strings, so we
                    // just encode the passed data, whatever it is, as CouchDB
                    // may use complex datatypes as a key, like arrays or
                    // objects.
                    $queryString .= $key . '=' . urlencode( json_encode( $value ) );
                    break;

                case 'startkey_docid':
                    // The docidstartkey is handled differntly then the other
                    // keys and is just passed as a string, because it always
                    // is and can only be a string.
                    $queryString .= $key . '=' . urlencode( (string) $value );
                    break;

                case 'group':
                case 'update':
                case 'descending':
                    // These two values may only contain boolean values, passed
                    // as "true" or "false". We just perform a typical PHP
                    // boolean typecast to transform the values.
                    $queryString .= $key . '=' . ( $value ? 'true' : 'false' );
                    break;

                case 'group_level':
                    // Theses options accept integers defining the limits of
                    // the query. We try to typecast to int.
                    $queryString .= $key . '=' . ( (int) $value );
                    break;

                case 'skip':
                case 'offset':
                    // Theses options accept integers defining the limits of
                    // the query. We try to typecast to int.
                    $queryString .= 'offset=' . ( (int) $value );
                    break;

                case 'count':
                case 'limit':
                    // Theses options accept integers defining the limits of
                    // the query. We try to typecast to int.
                    $queryString .= 'limit=' . ( (int) $value );
                    break;

                default:
                    throw new phpillowNoSuchPropertyException( $key );
            }

            $queryString .= '&';
        }

        // Return query string, but remove appended '&' first.
        return substr( $queryString, 0, -1 );
    }

    /**
     * Query a view
     *
     * Query the specified view to get a set of results. You may optionally use
     * the view query options as additional paramters to limit the returns
     * values, specified at:
     * http://www.couchdbwiki.com/index.php?title=HTTP_View_API#Querying_Options
     * 
     * @param string $view 
     * @param array $options 
     * @return phpillowResultArray
     */
    public function query( $view, array $options = array() )
    {
        // Build query string, just as a normal HTTP GET query string
        $url = phpillowConnection::getDatabase() . 
            '_view/' . $this->getViewName() . '/' . $view;
        $url .= $this->buildViewQuery( $options );

        // Get database connection, because we directly execute a query here.
        $db = phpillowConnection::getInstance();

        try
        {
            // Try to execute query, a failure most probably means, the view
            // has not been added, yet.
            $response = $db->get( $url );
        }
        catch ( phpillowResponseErrorException $e )
        {
            // Ensure view has been created properly and then try to execute
            // the query again. If it still fails, there is most probably a
            // real problem.
            $this->verifyView();
            $response = $db->get( $url );
        }

        return $response;
    }

    /**
     * Verify stored views
     *
     * Check if the views stored in the database equal the view definitions
     * specified by the vew classes. If the implmentation differs update to the
     * view specifications in the class.
     * 
     * @return void
     */
    public function verifyView()
    {
        // Fetch view definition from database
        try
        {
            $view = static::fetchById( '_design/' . static::getViewName() );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        {
            // If the view does not exist yet, recreate it
            $view = static::createNew();
        }
        
        // Force setting of view definitions
        $views = array();
        foreach ( $this->viewDefinitions as $name => $function )
        {
            $views[$name]['map'] = $function;

            // Check if there is also a reduce function for the given view
            // function.
            if ( isset( $this->viewReduces[$name] ) )
            {
                $views[$name]['reduce'] = $this->viewReduces[$name];
            }
        }

        $view->views = $views;
        $view->save();
    }
}

