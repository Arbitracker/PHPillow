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
 * @version $Revision: 535 $
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */

class arbitTextUiResultPrinter extends PHPUnit_TextUI_ResultPrinter
{
    /**
     * Current column position in output
     * 
     * @var int
     */
    protected $column = 0;

    /**
     * Number of tests already finished
     * 
     * @var int
     */
    protected $testsRun = 0;

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     * @access protected
     */
    protected function printFooter(PHPUnit_Framework_TestResult $result)
    {
        if ($result->wasSuccessful() &&
            $result->allCompletlyImplemented() &&
            $result->noneSkipped())
        {
            $this->write(
              sprintf(
                "\n\033[1;32mOk\033[0m (%d test%s)\n",
                count($result),
                (count($result) == 1) ? '' : 's'
              )
            );
        }
        elseif ((!$result->allCompletlyImplemented() ||
                  !$result->noneSkipped())&&
                 $result->wasSuccessful())
        {
            $this->write(
              sprintf(
                "\n\033[1;32mOk\033[0m, but incomplete or skipped tests!\n" .
                "Tests: %d%s%s.\n",
                count($result),
                $this->getCountString($result->notImplementedCount(), 'Incomplete'),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );
        }
        else
        {
            $this->write(
              sprintf(
                "\n\033[1;31mFailures\033[0m\n" .
                "Tests: %d%s%s%s%s.\n",
                count($result),
                $this->getCountString($result->failureCount(), 'Failures'),
                $this->getCountString($result->errorCount(), 'Errors'),
                $this->getCountString($result->notImplementedCount(), 'Incomplete'),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );
        }
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgress( "\033[1;31m✘\033[0m" );
        $this->lastTestFailed = true;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     * @access public
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeProgress( "\033[0;31m✗\033[0m" );
        $this->lastTestFailed = true;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgress( "\033[0;33m◔\033[0m" );
        $this->lastTestFailed = true;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeProgress( "\033[0;34m➔\033[0m" );
        $this->lastTestFailed = true;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     * @access public
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if (!$this->lastTestFailed) {
            $this->writeProgress( "\033[1;32m✓\033[0m" );
        }

        $this->lastEvent = self::EVENT_TEST_END;
        $this->lastTestFailed = FALSE;
        $this->column++;
        $this->testsRun++;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $name = $suite->getName();

        if (empty($name)) {
            $name = 'Test Suite';
        }

        $name = preg_replace( '(^.*::(.*?)$)', '\\1', $name );

        $this->write(
          $title = sprintf(
            "%s%s• %s: ",
            "\n",
            // $this->lastEvent == self::EVENT_TESTSUITE_START || $this->lastEvent == self::EVENT_TEST_END ? "\n" : '',
            str_repeat( '  ', count( $this->testSuiteSize ) ),
            $name
          )
        );

        array_push($this->testSuiteSize, count($suite));

        $this->lastEvent = self::EVENT_TESTSUITE_START;
        $this->column = strlen( $title );
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ( $this->lastEvent === self::EVENT_TEST_END )
        {
            echo ( ( $this->column < 72 ) ? str_repeat( ' ', 73 - $this->column ) : ' ' );
            printf( "\033[0;37m[%3d%%]\033[0m", $this->testsRun / $this->testSuiteSize[0] * 100 );
        }

        array_pop($this->numberOfTests);
        array_pop($this->testSuiteSize);

        $this->lastEvent = self::EVENT_TESTSUITE_END;
    }

    /**
     * @param  string $progress
     * @access protected
     */
    protected function writeProgress($progress)
    {
        $this->write($progress);
    }
}

