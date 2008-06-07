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
 * Wrapper for group views
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */
class phpillowGroupView extends phpillowView
{
    /**
     * View functions to be registered on the server
     *
     * @var array
     */
    protected $viewDefinitions = array(
        // Add view for all groups indexed by their name
        'group' => 'function( doc )
{
    if ( doc.type == "group" )
    {
        emit( doc.name, doc._id );
    }
}',
        // Fetch all rights of one user, which is defined by the groups a user
        // belongs to.
        'user_permissions' => 'function( doc )
{
    if ( doc.type == "group" )
    {
        for ( var i = 0; i < doc.users.length; ++i )
        {
            for ( var j = 0; j < doc.permissions.length; ++j )
            {
                emit( doc.users[i], doc.permissions[j] );
            }
        }
    }
}',
        // Fetch all rights of one user, which is defined by the groups a user
        // belongs to.
        'user_permissions_reduced' => 'function( doc )
{
    if ( doc.type == "group" )
    {
        for ( var i = 0; i < doc.users.length; ++i )
        {
            for ( var j = 0; j < doc.permissions.length; ++j )
            {
                emit( doc.users[i], doc.permissions[j] );
            }
        }
    }
}',
    );

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
    protected $viewReduces = array(
        'user_permissions_reduced' => 'function( keys, values )
{
    var reduced = {};
    for ( var i = 0; i < keys.length; ++i )
    {
        if ( !reduced[keys[i][0]] )
        {
            reduced[keys[i][0]] = {};
        }

        reduced[keys[i][0]][values[i]] = true;
    }
    return reduced;
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
        return 'groups';
    }
}

