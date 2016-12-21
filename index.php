<?php
use Kafoso\Tools\Debug\Dumper;

require(__DIR__ . "/vendor/autoload.php");
require(__DIR__ . "/tests/resources/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectOneDimension.source.php");
require_once(__DIR__ . "/tests/resources/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectWithRecursion.source.php");
$classA = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
$classB = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
$classC = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
$classB->setParent($classA);
$classC->setParent($classB);

$var = $classC;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>k</title>
</head>
<body>
    <?=Dumper::dumpHtml($var);?>
</body>
</html>
