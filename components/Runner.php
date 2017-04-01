<?php

namespace spiritdead\yii2resque\components;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;

/**
 * Class Runner - a component for running console command in yii2 web applications
 *
 * This extensions is inspired by the project https://github.com/vova07/yii2-console-runner-extension
 *
 * Basic usage:
 * ```php
 * use toriphes\console\Runner;
 * $output = '';
 * $runner = new Runner();
 * $runner->run('controller/action param1 param2 ...', $output);
 * echo $output; //prints the command output
 * ```
 *
 * Application component usage:
 * ```php
 * //you config file
 * 'components' => [
 *     'consoleRunner' => [
 *         'class' => 'toriphes\console\Runner'
 *     ]
 * ]
 * ```
 * ```php
 * //some application file
 * $output = '';
 * Yii::$app->consoleRunner->run('controller/action param1 param2 ...', $output);
 * echo $output; //prints the command output
 * ```
 * @author Giulio Ganci <giulioganci@gmail.com>
 */
class Runner extends Component
{
    /**
     * @var string yii console application file that will be executed
     */
    public $yiiscript;

    /**
     * @var string path to php executable
     */
    public $phpexec;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        set_time_limit(0);

        if ($this->yiiscript === null) {
            $this->yiiscript = "@app/../yii";
        }
    }

    /**
     * Runs yii console command
     *
     * @param string $cmd
     * @throws NotSupportedException
     */
    public function runAsync($cmd)
    {
        if (!$this->isWindows()) {
            $command = $this->buildCommand($cmd, true);
            shell_exec($command);
        } else {
            throw new NotSupportedException('Not implemented in windows yet');
        }
    }

    /**
     * Runs yii console command
     *
     * @param string $cmd command with arguments
     * @param string $output filled with the command output
     * @return int termination status of the process that was run
     */
    public function run($cmd, &$output = '')
    {
        $handler = popen($this->buildCommand($cmd, false), 'r');
        while (!feof($handler)) {
            $output .= fgets($handler);
        }
        $output = trim($output);
        $status = pclose($handler);
        return $status;
    }

    /**
     * Builds the command string
     *
     * @param string $cmd Yii command
     * @return string full command to execute
     */
    protected function buildCommand($cmd, $async = false)
    {
        $cmd = $this->getPHPExecutable($async) . ' ' . Yii::getAlias($this->yiiscript) . ' ' . $cmd;
        if ($this->isWindows()) {
            if ($async) {
                return '';
            }
            return 'start /b ' . $cmd;

        } else {
            if ($async) {
                return 'screen -mdS taskset ' . $cmd;
            }
            return $cmd . ' 2>&1';
        }
    }

    /**
     * If property $phpexec is set it will be used as php executable
     *
     * @return string path to php executable
     */
    protected function getPHPExecutable($async)
    {
        if ($this->phpexec) {
            return $this->phpexec;
        }
        if ($async) {
            return PHP_BINDIR . '/php';
        }
        return PHP_BINDIR . '/php';
    }

    /**
     * Check operating system
     *
     * @return boolean true if it's Windows OS
     */
    protected function isWindows()
    {
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') {
            return true;
        } else {
            return false;
        }
    }
}