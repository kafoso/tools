<?php
require(__DIR__ . '/../vendor/autoload.php');

// Make e.g. json_encode work on various PHP versions and OS's
ini_set("precision", 14);
ini_set("serialize_precision", -1);

define('TESTS_RESOURCES_DIRECTORY', __DIR__ . "/resources");
define('TESTS_TESTS_DIRECTORY', __DIR__ . "/tests");
