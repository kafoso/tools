<?php
use Kafoso\Tools\Debug\Dumper\JsonFormatter;

class JsonFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $jsonFormatter = new JsonFormatter(null, null, JSON_PRETTY_PRINT);
        $this->assertSame("null", $jsonFormatter->render());
    }

    public function testBoolean()
    {
        $jsonFormatter = new JsonFormatter(true, null, JSON_PRETTY_PRINT);
        $this->assertSame("true", $jsonFormatter->render());
    }

    public function testFloat()
    {
        $jsonFormatter = new JsonFormatter(3.14, null, JSON_PRETTY_PRINT);
        $this->assertSame("3.14", $jsonFormatter->render());
    }

    public function testInteger()
    {
        $jsonFormatter = new JsonFormatter(42, null, JSON_PRETTY_PRINT);
        $this->assertSame("42", $jsonFormatter->render());
    }

    public function testString()
    {
        $jsonFormatter = new JsonFormatter("foo", null, JSON_PRETTY_PRINT);
        $this->assertSame("\"foo\"", $jsonFormatter->render());
    }

    public function testArrayOneDimension()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        $jsonFormatter = new JsonFormatter(["foo" => "bar"], null, JSON_PRETTY_PRINT);
        $expected = $this->normalizeEOL(trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/JsonFormatterTest/testArrayOneDimension.expected.json")));
        $this->assertSame($expected, $jsonFormatter->render());
    }

    public function testResource()
    {
        $resource = curl_init('foo');
        $jsonFormatter = new JsonFormatter($resource, null, JSON_PRETTY_PRINT);
        $expected = '/Resource \#\d+ ' . preg_quote('(Type: curl)', '/') . '/';
        $this->assertRegExp($expected, $jsonFormatter->render());
    }

    public function testObjectOneDimension()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        require_once($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectOneDimension.source.php");
        $class = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectOneDimension_b0559f888359b081714fdea9d26c65b7;
        $class->foo = "bar";
        $jsonFormatter = new JsonFormatter($class, null, JSON_PRETTY_PRINT);
        $expected = $this->normalizeEOL(trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/JsonFormatterTest/testObjectOneDimension.expected.json")));
        $expected = preg_replace('/Object &[a-f0-9]+/', 'Object &', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $jsonFormatter->render();
        $found = preg_replace('/Object &[a-f0-9]+/', 'Object &', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }

    public function testArrayMultipleDimensions()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        $jsonFormatter = new JsonFormatter([
            "foo" => [
                "bar" => [
                    "baz" => 1,
                    "bim" => null
                ],
            ],
        ], null, JSON_PRETTY_PRINT);
        $expected = $this->normalizeEOL(trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/JsonFormatterTest/testArrayMultipleDimensions.expected.json")));
        $this->assertSame($expected, $jsonFormatter->render());
    }

    public function testObjectMultipleLevelsWithoutRecursion()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        require_once($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectMultipleLevelsWithoutRecursion.source.php");
        $classA = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classB = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classC = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classB->setParent($classA);
        $classC->setParent($classB);
        $jsonFormatter = new JsonFormatter($classC, null, JSON_PRETTY_PRINT);
        $expected = $this->normalizeEOL(trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/JsonFormatterTest/testObjectMultipleLevelsWithoutRecursion.expected.json")));
        $expected = preg_replace('/Object &[a-f0-9]+/', 'Object &', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $jsonFormatter->render();
        $found = preg_replace('/Object &[a-f0-9]+/', 'Object &', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }

    public function testObjectWithRecursion()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        require_once($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectWithRecursion.source.php");
        $classA = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classB = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classB->setParent($classA);
        $classA->setParent($classB);
        $jsonFormatter = new JsonFormatter($classA, null, JSON_PRETTY_PRINT);
        $expected = $this->normalizeEOL(trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/JsonFormatterTest/testObjectWithRecursion.expected.json")));
        $expected = preg_replace('/Object &[a-f0-9]+/', 'Object &', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $jsonFormatter->render();
        $found = preg_replace('/Object &[a-f0-9]+/', 'Object &', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }

    protected function normalizeEOL($str)
    {
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        return $str;
    }
}
