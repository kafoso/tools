<?php
require(__DIR__ . "/bootstrap.php");
require(__DIR__ . "/DebugDumperExample/My/Little_Class.php");

$myLittleClass = new \My\Little_Class;
$b = new \My\Little_Class;

$reflectionA = new \ReflectionObject($myLittleClass);
$propertiesA = [];
foreach ($reflectionA->getProperties() as $property) {
    $propertiesA[$property->getName()] = $property;
}
$propertiesA["id"]->setAccessible(true);
$propertiesA["id"]->setValue($myLittleClass, 42);
$propertiesA["parent"]->setAccessible(true);
$propertiesA["parent"]->setValue($myLittleClass, $b);

$reflectionB = new \ReflectionObject($b);
$propertiesB = [];
foreach ($reflectionB->getProperties() as $property) {
    $propertiesB[$property->getName()] = $property;
}
$propertiesB["id"]->setAccessible(true);
$propertiesB["id"]->setValue($b, 41);
$propertiesB["children"]->setAccessible(true);
$propertiesB["children"]->setValue($b, [
    $myLittleClass
]);

echo "--- Plain text ------------------------------------" . PHP_EOL;
\Kafoso\Tools\Debug\Dumper::dump($myLittleClass) . PHP_EOL;
