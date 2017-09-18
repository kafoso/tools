<?php
namespace Kafoso\Tools\Tests\Unit\Debug\Dumper;

use Kafoso\Tools\Debug\Dumper\PlainTextFormatter;
use Kafoso\Tools\Traits\File\EOLNormalizer;

class PlainTextFormatterTest extends \PHPUnit_Framework_TestCase
{
    use EOLNormalizer;

    public function testNull()
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $this->assertSame("null", $plainTextFormatter->render(null));
    }

    public function testBoolean()
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $this->assertSame("bool(true)", $plainTextFormatter->render(true));
    }

    public function testFloat()
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $this->assertSame("float(3.14)", $plainTextFormatter->render(3.14));
    }

    public function testInteger()
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $this->assertSame("int(42)", $plainTextFormatter->render(42));
    }

    public function testString()
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $this->assertSame("string(3) \"foo\"", $plainTextFormatter->render("foo"));
    }

    public function testArrayOneDimension()
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $expected = 'array(1) {'
            . PHP_EOL
            . '  ["foo"] => string(3) "bar",'
            . PHP_EOL
            . '}';
        $this->assertSame($expected, $plainTextFormatter->render(["foo" => "bar"]));
    }

    public function testResource()
    {
        $resource = curl_init('foo');
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $expected = '/^Resource \#\d+ \(Type: curl\)$/';
        $this->assertRegExp($expected, $plainTextFormatter->render($resource));
    }

    public function testRender()
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $expected = 'string(3) "foo"';
        $this->assertSame($expected, $plainTextFormatter->render("foo"));
    }

    public function testArrayMultipleDimensions()
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.txt"));
        $found = $plainTextFormatter->render([
            "foo" => [
                "bar" => [
                    "baz" => 1,
                    "bim" => null
                ],
            ],
        ]);
        $this->assertSame($expected, $found);
    }

    public function testObjectOneDimension()
    {
        $this->_requireResource("Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7.php");
        $class = new \Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7;
        $class->foo = "bar";
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.txt"));
        $found = $plainTextFormatter->render($class);
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
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.txt"));
        $found = $plainTextFormatter->render($classC);
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
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.txt"));
        $found = $plainTextFormatter->render($classA);
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
