<?php
/**
 * arbit test runner
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
 * @version $Revision: 528 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */

define('PHPUnit_MAIN_METHOD', 'arbitTextUiCommand::main');
require 'PHPUnit/TextUI/Command.php';

// Custom printer
require __DIR__ . '/printer.php';

class arbitTextUiCommand extends PHPUnit_TextUI_Command
{
    /**
     * Text UI command main method
     * 
     * Copied from PHPUnit_TextUI_Command, used with a different test runner,
     * to use our own output formatting.
     *
     * We should try to keep this method with the original main() method in
     * sync.
     *
     * @return void
     */
    public static function main()
    {
        $arguments = self::handleArguments();
        $runner    = new PHPUnit_TextUI_TestRunner;

        // Changed for arbit
        $runner->setPrinter( new arbitTextUiResultPrinter() );
        // End of change

        if (is_object($arguments['test']) && $arguments['test'] instanceof PHPUnit_Framework_Test) {
            $suite = $arguments['test'];
        } else {
            $suite = $runner->getTest(
              $arguments['test'],
              $arguments['testFile'],
              $arguments['syntaxCheck']
            );
        }

        if ($suite->testAt(0) instanceof PHPUnit_Framework_Warning &&
            strpos($suite->testAt(0)->getMessage(), 'No tests found in class') !== FALSE) {
            $skeleton = new PHPUnit_Util_Skeleton(
                $arguments['test'],
                $arguments['testFile']
            );

            $result = $skeleton->generate(TRUE);

            if (!$result['incomplete']) {
                eval(str_replace(array('<?php', '?>'), '', $result['code']));
                $suite = new PHPUnit_Framework_TestSuite($arguments['test'] . 'Test');
            }
        }

        try {
            $result = $runner->doRun(
              $suite,
              $arguments
            );
        }

        catch (Exception $e) {
            throw new RuntimeException(
              'Could not create and run test suite: ' . $e->getMessage()
            );
        }

        if ($result->wasSuccessful()) {
            exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
        }

        else if($result->errorCount() > 0) {
            exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
        }

        else {
            exit(PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
        }
    }
}

