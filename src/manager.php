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
 * @version $Revision: 505 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */

/**
 * Basic couch DB view and document manager / registry.
 *
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 505 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
final class arbitBackendCouchDbManager
{
    /**
     * Initial mapping of view types to view classes.
     *
     * @var array
     */
    protected static $views = array(
        'user'      => 'arbitBackendCouchDbUserView',
        'group'     => 'arbitBackendCouchDbGroupView',
    );

    /**
     * Initial mapping of document types to document classes.
     *
     * @var array
     */
    protected static $documents = array(
        'user'              => 'arbitBackendCouchDbUserDocument',
        'group'             => 'arbitBackendCouchDbGroupDocument',
        'project'           => 'arbitBackendCouchDbProjectDocument',
    );

    /**
     * Empty protected constructor
     *
     * We do not want this registry to be instanciated.
     * 
     * @ignore
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Set view class
     *
     * Set a view class for a view type.
     * 
     * @param string $name 
     * @param string $class 
     * @return void
     */
    public static function setViewClass( $name, $class )
    {
        self::$views[$name] = $class;
    }

    /**
     * Return view
     *
     * Get a view object for the given view type. Throws a
     * arbitBackendCouchDbNoSuchPropertyException if the view does not exist.
     * 
     * @param string $name 
     * @return arbitBackendCouchDbView
     */
    public static function getView( $name )
    {
        // Check if a view with the given name exists.
        if ( !isset( self::$views[$name] ) )
        {
            throw new arbitBackendCouchDbNoSuchPropertyException( $name );
        }

        // Instantiate and return view.
        $className = self::$views[$name];
        return new $className;
    }

    /**
     * Set document class
     *
     * Set a document class for a document type.
     * 
     * @param string $name 
     * @param string $class 
     * @return void
     */
    public static function setDocumentClass( $name, $class )
    {
        self::$documents[$name] = $class;
    }

    /**
     * Create new document
     *
     * Create a new document of the given type and return it. Throws a
     * arbitBackendCouchDbNoSuchPropertyException if the document does not exist.
     * 
     * @param string $name 
     * @return arbitBackendCouchDbDocument
     */
    public static function createDocument( $name )
    {
        // Check if a document with the given name exists.
        if ( !isset( self::$documents[$name] ) )
        {
            throw new arbitBackendCouchDbNoSuchPropertyException( $name );
        }

        // Instantiate and return document.
        $className = self::$documents[$name];
        return $className::createNew();
    }

    /**
     * Fetch document by ID
     *
     * Fetch the document of the given type with the given ID. Throws a
     * arbitBackendCouchDbNoSuchPropertyException if the document does not exist.
     * 
     * @param string $name 
     * @param string $id 
     * @return arbitBackendCouchDbDocument
     */
    public static function fetchDocument( $name, $id )
    {
        // Check if a document with the given name exists.
        if ( !isset( self::$documents[$name] ) )
        {
            throw new arbitBackendCouchDbNoSuchPropertyException( $name );
        }

        // Instantiate and return document.
        $className = self::$documents[$name];
        return $className::fetchById( $id );
    }

    /**
     * Delete document by ID
     *
     * Delete the document of the given type with the given ID. Throws a
     * arbitBackendCouchDbNoSuchPropertyException if the document does not exist.
     *
     * Deletion means, that all revisions, including the current one, are
     * removed.
     * 
     * @param string $name 
     * @param string $id 
     * @return void
     */
    public static function deleteDocument( $name, $id )
    {
        // Check if a document with the given name exists.
        if ( !isset( self::$documents[$name] ) )
        {
            throw new arbitBackendCouchDbNoSuchPropertyException( $name );
        }

        $db = arbitBackendCouchDbConnection::getInstance();
        $revisions = $db->get( $db->getDatabase() . $id . '?revs=true' );
        foreach ( $revisions->_revs as $revision )
        {
            try
            {
                $db->delete( $db->getDatabase() . $id . '?rev=' . $revision );
            }
            catch ( arbitBackendCouchDbResponseConflictErrorException $e )
            {
                // @TODO: Check with the CouchDB guys, if this is really the
                // desired behaviour and what may be a better way to wipe a
                // document completely out of the database.
            }
        }
    }
}

