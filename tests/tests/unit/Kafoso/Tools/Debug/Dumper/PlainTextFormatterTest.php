<?php
namespace Kafoso\Tools\Tests\Unit\Debug\Dumper;

use Kafoso\Tools\Debug\Dumper\PlainTextFormatter;
use Kafoso\Tools\Traits\File\EOLNormalizer;

class PlainTextFormatterTest extends \PHPUnit_Framework_TestCase
{
    use EOLNormalizer;

    public function testNull()
    {
        $plainTextFormatter = new PlainTextFormatter(null);
        $this->assertSame("null", $plainTextFormatter->render());
    }

    public function testBoolean()
    {
        $plainTextFormatter = new PlainTextFormatter(true);
        $this->assertSame("bool(true)", $plainTextFormatter->render());
    }

    public function testFloat()
    {
        $plainTextFormatter = new PlainTextFormatter(3.14);
        $this->assertSame("float(3.14)", $plainTextFormatter->render());
    }

    public function testInteger()
    {
        $plainTextFormatter = new PlainTextFormatter(42);
        $this->assertSame("int(42)", $plainTextFormatter->render());
    }

    public function testString()
    {
        $plainTextFormatter = new PlainTextFormatter("foo");
        $this->assertSame("string(3) \"foo\"", $plainTextFormatter->render());
    }

    public function testArrayOneDimension()
    {
        $plainTextFormatter = new PlainTextFormatter(["foo" => "bar"]);
        $expected = 'array(1) {'
            . PHP_EOL
            . '  ["foo"] => string(3) "bar",'
            . PHP_EOL
            . '}';
        $this->assertSame($expected, $plainTextFormatter->render());
    }

    public function testResource()
    {
        $resource = curl_init('foo');
        $plainTextFormatter = new PlainTextFormatter($resource);
        $expected = '/^Resource \#\d+ \(Type: curl\)$/';
        $this->assertRegExp($expected, $plainTextFormatter->render());
    }

    public function testRender()
    {
        $plainTextFormatter = new PlainTextFormatter("foo");
        $expected = 'string(3) "foo"';
        $this->assertSame($expected, $plainTextFormatter->render());
    }

    public function testObjectOneDimension()
    {
        require_once(TESTS_RESOURCES_DIRECTORY . "/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectOneDimension.source.php");
        $class = new \Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectOneDimension_b0559f888359b081714fdea9d26c65b7;
        $class->foo = "bar";
        $plainTextFormatter = new PlainTextFormatter($class);
        $expected = trim(file_get_contents(TESTS_RESOURCES_DIRECTORY . "/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectOneDimension.expected.txt"));
        $expected = preg_replace('/Object &[a-f0-9]+/', 'Object &', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $plainTextFormatter->render();
        $found = preg_replace('/Object &[a-f0-9]+/', 'Object &', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }

    public function testArrayMultipleDimensions()
    {
        $plainTextFormatter = new PlainTextFormatter([
            "foo" => [
                "bar" => [
                    "baz" => 1,
                    "bim" => null
                ],
            ],
        ]);
        $expected = trim(file_get_contents(TESTS_RESOURCES_DIRECTORY . "/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testArrayMultipleDimensions.expected.txt"));
        $this->assertSame($expected, $plainTextFormatter->render());
    }

    public function testObjectMultipleLevelsWithoutRecursion()
    {
        require_once(TESTS_RESOURCES_DIRECTORY . "/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectMultipleLevelsWithoutRecursion.source.php");
        $classA = new \Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classB = new \Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classC = new \Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classB->setParent($classA);
        $classC->setParent($classB);
        $plainTextFormatter = new PlainTextFormatter($classC);
        $expected = trim(file_get_contents(TESTS_RESOURCES_DIRECTORY . "/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectMultipleLevelsWithoutRecursion.expected.txt"));
        $expected = preg_replace('/Object &[a-f0-9]+/', 'Object &', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $plainTextFormatter->render();
        $found = preg_replace('/Object &[a-f0-9]+/', 'Object &', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }

    public function testObjectWithRecursion()
    {
        require_once(TESTS_RESOURCES_DIRECTORY . "/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectWithRecursion.source.php");
        $classA = new \Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB = new \Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB->setParent($classA);
        $classA->setParent($classB);
        $plainTextFormatter = new PlainTextFormatter($classA);
        $expected = trim(file_get_contents(TESTS_RESOURCES_DIRECTORY . "/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectWithRecursion.expected.txt"));
        $expected = preg_replace('/Object &[a-f0-9]+/', 'Object &', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $plainTextFormatter->render();
        $found = preg_replace('/Object &[a-f0-9]+/', 'Object &', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }
}
