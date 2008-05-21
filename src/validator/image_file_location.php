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
 * @version $Revision: 349 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */

/**
 * Validate given file as a valid image file
 *
 * @package Core
 * @subpackage CouchDbBackend
 * @version $Revision: 349 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */
class arbitBackendCouchDbImageFileLocationValidator extends arbitBackendCouchDbValidator
{
    /**
     * Array containing a list of supported image formats.
     * 
     * @var array
     */
    protected $supportedImageFormats = array(
        IMAGETYPE_GIF,
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
    );

    /**
     * Validate input as string
     * 
     * @param mixed $input 
     * @return string
     */
    public function validate( $input )
    {
        // Check if we got readaccess to the provided file name at all.
        if ( !is_file( $input ) || !is_readable( $input ) )
        {
            throw new arbitRuntimeException( 'Given image file not found: ' . $input );
        }

        // Use getimagesize to determine the filetype of the image, and compare
        // with whitelist of supported image formats.
        $imageData = getimagesize( $input );
        if ( ( $imageData === false ) ||
             ( !in_array( $imageData[2], $this->supportedImageFormats ) ) )
        {
            throw new arbitBackendCouchDbValidationException( 'Unsupported image format provided.', array() );
        }

        // If all checks passed, we assume that this is a proper image file.
        return $input;
    }
}

