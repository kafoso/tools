<?php
namespace Kafoso\Tools\Tests\Unit\Debug\Dumper;

use Kafoso\Tools\Debug\Dumper\JsonFormatter;
use Kafoso\Tools\Traits\File\EOLNormalizer;

class JsonFormatterTest extends \PHPUnit_Framework_TestCase
{
    use EOLNormalizer;

    public function testNull()
    {
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $this->assertSame("null", $jsonFormatter->render(null));
    }

    public function testBoolean()
    {
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $this->assertSame("true", $jsonFormatter->render(true));
    }

    public function testFloat()
    {
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $found = $jsonFormatter->render(3.14, 2);
        $this->assertSame("3.14", number_format($found, 2));
    }

    public function testInteger()
    {
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $this->assertSame("42", $jsonFormatter->render(42));
    }

    public function testString()
    {
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $this->assertSame("\"foo\"", $jsonFormatter->render("foo"));
    }

    public function testArrayOneDimension()
    {
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.json"));
        $found = $jsonFormatter->render(["foo" => "bar"]);
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($found)
        );
    }

    public function testResource()
    {
        $resource = curl_init('foo');
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $expected = '/^"Resource \#\d+ ' . preg_quote('(Type: curl)', '/') . '"$/';
        $found = $jsonFormatter->render($resource);
        $this->assertRegExp(
            $expected,
            $this->normalizeEOL($found)
        );
    }

    public function testArrayMultipleDimensions()
    {
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.json"));
        $found = $jsonFormatter->render([
            "foo" => [
                "bar" => [
                    "baz" => 1,
                    "bim" => null
                ],
            ],
        ]);
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($found)
        );
    }

    public function testObjectOneDimension()
    {
        $this->_requireResource("Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7.php");
        $class = new \Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7;
        $class->foo = "bar";
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.json"));
        $found = $jsonFormatter->render($class);
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
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.json"));
        $found = $jsonFormatter->render($classC);
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
        $jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.json"));
        $found = $jsonFormatter->render($classA);
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
        return file_get_contents(TESTS_RESOURCES_DIRECTORY. "/tests/unit/Kafoso/Tools/Debug/Dumper/JsonFormatterTest/" . $name);
    }
}
