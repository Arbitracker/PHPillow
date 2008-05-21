<?php
/**
 * phpillow CouchDB backend
 *
 * This file is part of phpillow.
 *
 * phpillow is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License.
 *
 * phpillow is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phpillow; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 358 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */

/**
 * Document representing the users
 *
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 358 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class phpillowBackendCouchDbUserDocument extends phpillowBackendCouchDbDocument
{
    /**
     * Document type, may be a string matching the regular expression:
     *  (^[a-zA-Z0-9_]+$)
     * 
     * @var string
     */
    protected static $type = 'user';

    /**
     * List of required properties. For each required property, which is not
     * set, a validation exception will be thrown on save.
     * 
     * @var array
     */
    protected $requiredProperties = array(
        'login',
    );

    /**
     * Construct new book document
     * 
     * Construct new book document and set its property validators.
     * 
     * @return void
     */
    protected function __construct()
    {
        $this->properties = array(
            'login'         => new phpillowBackendCouchDbRegexpValidator( '(^[\x21-\x7e]+$)i' ),
            'email'         => new phpillowBackendCouchDbEmailValidator(),
            'name'          => new phpillowBackendCouchDbStringValidator(),
            'valid'         => new phpillowBackendCouchDbRegexpValidator( '(^0|1|[a-f0-9]{32}$)' ),
            'auth_type'     => new phpillowBackendCouchDbStringValidator(),
            'auth_infos'    => new phpillowBackendCouchDbNoValidator(),
        );

        parent::__construct();
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
        return $this->stringToId( $this->storage->login );
    }
}

