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
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.txt"));
        $this->assertSame($expected, $plainTextFormatter->render());
    }

    public function testObjectOneDimension()
    {
        $this->_requireResource("Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7.php");
        $class = new \Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7;
        $class->foo = "bar";
        $plainTextFormatter = new PlainTextFormatter($class);
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.txt"));
        $found = $plainTextFormatter->render();
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($found)
        );
    }

    public function testObjectMultipleLevelsWithoutRecursion()
    {
        $this->_requireResource("Kafoso_Tools_Debug_Dumper_ObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730.php");
        $classA = new \Kafoso_Tools_Debug_Dumper_ObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classB = new \Kafoso_Tools_Debug_Dumper_ObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classC = new \Kafoso_Tools_Debug_Dumper_ObjectMultipleLevelsWithoutRecursion_e01540c6d67623eed60a8f0c3ceeb730;
        $classB->setParent($classA);
        $classC->setParent($classB);
        $plainTextFormatter = new PlainTextFormatter($classC);
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.txt"));
        $found = $plainTextFormatter->render();
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($found)
        );
    }

    public function testObjectWithRecursion()
    {
        $this->_requireResource("Kafoso_Tools_Debug_Dumper_ObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d.php");
        $classA = new \Kafoso_Tools_Debug_Dumper_ObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB = new \Kafoso_Tools_Debug_Dumper_ObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB->setParent($classA);
        $classA->setParent($classB);
        $plainTextFormatter = new PlainTextFormatter($classA);
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.txt"));
        $found = $plainTextFormatter->render();
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($found)
        );
    }

    private function _requireResource($name)
    {
        require_once(TESTS_RESOURCES_DIRECTORY . "/classes/" . $name);
    }

    private function _getResourceContents($name)
    {
        return file_get_contents(TESTS_RESOURCES_DIRECTORY. "/tests/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/" . $name);
    }
}
