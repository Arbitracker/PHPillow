<?php
/**
 * arbit CouchDB backend
 *
 * This file is part of arbit.
 *
 * arbit is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License.
 *
 * arbit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with arbit; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */

/**
 * Basic CouchDB backend exception
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
abstract class arbitBackendCouchDbException extends arbitException
{
}

/**
 * Exception thrown, when connection could not be established or
 * configured.
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbConnectionException extends arbitBackendCouchDbException
{
}

/**
 * Exception thrown, when no database has been configured.
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchNoDatabaseException extends arbitBackendCouchDbException
{
    /**
     * Create exception
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct( 
            "No database has been configured.",
            array(
            )
        );
    }
}

/**
 * Exception thrown, when a request could not be build out of the given
 * parameters
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbInvalidRequestException extends arbitBackendCouchDbException
{
}

/**
 * Exception thrown, when a property requested from an response object is
 * not available.
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbNoSuchPropertyException extends arbitBackendCouchDbException
{
    /**
     * Create exception from property name
     * 
     * @param string $property 
     * @return void
     */
    public function __construct( $property )
    {
        parent::__construct( 
            "Property '%property' is not available.",
            array(
                'property' => $property,
            )
        );
    }
}

/**
 * Exception thrown, when a document property could not be validated by the
 * validator.
 *
 * The exception contains an identifier for the error type, if the error should
 * be presented to the user.
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbValidationException extends arbitBackendCouchDbException
{
}

/**
 * Exception thrown if the server could not properly response a request.
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbResponseErrorException extends arbitBackendCouchDbException
{
    /**
     * Actual parsed server response
     * 
     * @var StdClass
     */
    protected $response;

    /**
     * Construct exception out of given response
     * 
     * @param int $status 
     * @param StdClass $response 
     * @return arbitBackendCouchDbResponseErrorException
     */
    public function __construct( $status, $response )
    {
        $this->response = $response;

        parent::__construct(
            "Error (%status) in request: %error (%reason).",
            array(
                'status'    => $status,
                'error'     => $response !== null ? $response->error : 'Unknown',
                'reason'    => $response !== null ? $response->reason : 'Unknown',
            )
        );
    }

    /**
     * Return response
     * 
     * Return response to check the actual response which cause the error,
     * or receive details about the server error.
     * 
     * @return StdClass
     */
    public function getResponse()
    {
        return $this->response;
    }
}

/**
 * Exception thrown if the server could not find a requested document.
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbResponseNotFoundErrorException extends arbitBackendCouchDbResponseErrorException
{
    /**
     * Construct parent from response
     * 
     * @param StdClass $response
     * @return void
     */
    public function __construct( $response )
    {
        parent::__construct( 404, $response );
    }
}

/**
 * Exception thrown if the server detected a conflict while processing a
 * request.
 * 
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 478 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbResponseConflictErrorException extends arbitBackendCouchDbResponseErrorException
{
    /**
     * Construct parent from response
     * 
     * @param StdClass $response
     * @return void
     */
    public function __construct( $response )
    {
        parent::__construct( 409, $response );
    }
}

