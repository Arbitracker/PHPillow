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
 * @version $Revision: 358 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */

/**
 * Wrapper for user views
 *
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 358 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbUserView extends arbitBackendCouchDbView
{
    /**
     * View functions to be registered on the server
     *
     * @var array
     */
    protected $viewDefinitions = array(
        // Add plain view on all users
        'all' => 'function( doc )
{
    if ( doc.type == "user" )
    {
        map( null, doc._id );
    }
}',
        // Add view for all users indexed by their login name
        'user' => 'function( doc )
{
    if ( doc.type == "user" )
    {
        map( doc.login, doc._id );
    }
}',
        // Add view for unregistered users waiting for activation
        'unregistered' => 'function( doc )
{
    if ( doc.type == "user" &&
         doc.valid !== "0" &&
         doc.valid !== "1" )
    {
        map( doc.valid, doc._id );
    }
}',
    );

    /**
     * Get name of view
     * 
     * Get name of view
     * 
     * @return string
     */
    protected static function getViewName()
    {
        return 'users';
    }
}

