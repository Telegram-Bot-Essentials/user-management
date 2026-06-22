<?php

if (! defined('CANVAS_WORKING_PATH')) {
    $workingPath = is_string(getenv('TESTBENCH_WORKING_PATH'))
        ? getenv('TESTBENCH_WORKING_PATH')
        : getcwd();

    if (is_string($workingPath) && file_exists($workingPath.'/canvas.yaml')) {
        define('CANVAS_WORKING_PATH', $workingPath);
    }
}
