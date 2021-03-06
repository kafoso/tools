<?php
namespace Kafoso\Tools\Tests\Unit\Debug\Dumper;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;
use Kafoso\Tools\Generic\HTML;
use Kafoso\Tools\Traits\File\EOLNormalizer;

class HtmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    use EOLNormalizer;

    public function testNull()
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = '<span class="syntax--language syntax--constant">null</span>;';
        $this->assertSame($expected, $htmlFormatter->renderInner(null));
    }

    public function testBoolean()
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = '<span class="syntax--language syntax--constant">true</span>;';
        $this->assertSame($expected, $htmlFormatter->renderInner(true));
    }

    public function testFloat()
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = '<span class="syntax--language syntax--constant syntax--numeric">3.14</span>;';
        $this->assertSame($expected, $htmlFormatter->renderInner(3.14));
    }

    public function testInteger()
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = '<span class="syntax--language syntax--constant syntax--numeric">42</span>;';
        $this->assertSame($expected, $htmlFormatter->renderInner(42));
    }

    public function testString()
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = '<span class="syntax--language syntax--string">&quot;foo&quot;</span>;';
        $this->assertSame($expected, $htmlFormatter->renderInner("foo"));
    }

    /**
     * @dataProvider    dataProvider_testStringIsEscapedProperly
     */
    public function testStringIsEscapedProperly($string, $expected)
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $stringEncoded = HTML::encode($string);
        $expected = '<span class="syntax--language syntax--string">&quot;' . $expected . '&quot;</span>;';
        $this->assertSame($expected, trim($htmlFormatter->renderInner($string)));
    }

    public function dataProvider_testStringIsEscapedProperly()
    {
        return [
            ["foo\"bar", 'foo<span class="syntax--constant syntax--character syntax--escape">\\&quot;</span>bar'],
            ["foo\\bar", 'foo<span class="syntax--constant syntax--character syntax--escape">\\\\</span>bar'],
            ["foo\$bar", 'foo<span class="syntax--constant syntax--character syntax--escape">\\$</span>bar'],
            ["foo\ebar", 'foo<span class="syntax--constant syntax--character syntax--escape">\\e</span>bar'],
            ["foo\nbar", 'foo<span class="syntax--constant syntax--character syntax--escape">\\n</span>bar'],
            ["foo\rbar", 'foo<span class="syntax--constant syntax--character syntax--escape">\\r</span>bar'],
            ["foo\tbar", 'foo<span class="syntax--constant syntax--character syntax--escape">\\t</span>bar'],
            ["foo\x4 bar", 'foo<span class="syntax--constant syntax--numeric syntax--octal">\\x04</span> bar'],
            ["foo\x4bar", 'fooKar'], // ASCII character "K"
            ["foo\4bar", 'foo<span class="syntax--constant syntax--numeric syntax--octal">\\x04</span>bar'],
        ];
    }

    public function testArrayOneDimension()
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.html"));
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($htmlFormatter->renderInner(["foo" => "bar"]))
        );
    }

    public function testArrayMultipleDimensions()
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.html"));
        $found = $htmlFormatter->renderInner([
            "foo" => [
                "bar" => [
                    "baz" => 1,
                    "bim" => null
                ],
            ],
        ], 3);
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($found)
        );
    }

    public function testArrayMultipleDimensionsOmitted()
    {
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.html"));
        $found = $htmlFormatter->renderInner([
            "foo" => [
                "bar" => [
                    "baz" => 1,
                    "bim" => null
                ],
            ],
        ], 2);
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
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.html"));
        $found = trim($htmlFormatter->renderInner($class));
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/data-object="[a-f0-9]+"/', 'data-object=""', $found);
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
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.html"));
        $found = $htmlFormatter->renderInner($classC, 2);
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/data-object="[a-f0-9]+"/', 'data-object=""', $found);
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
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.html"));
        $found = $htmlFormatter->renderInner($classA);
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/data-object="[a-f0-9]+"/', 'data-object=""', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($found)
        );
    }

    public function testObjectOmittedDueToDepthReached()
    {
        $this->_requireResource("Kafoso_Tools_Debug_Dumper_ObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d.php");
        $classA = new \Kafoso_Tools_Debug_Dumper_ObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB = new \Kafoso_Tools_Debug_Dumper_ObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classC = new \Kafoso_Tools_Debug_Dumper_ObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB->setParent($classA);
        $classC->setParent($classB);
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = trim($this->_getResourceContents(__FUNCTION__ . ".expected.html"));
        $found = $htmlFormatter->renderInner($classC, 2);
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/data-object="[a-f0-9]+"/', 'data-object=""', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame(
            $this->normalizeEOL($expected),
            $this->normalizeEOL($found)
        );
    }

    public function testResource()
    {
        $resource = curl_init('foo');
        $htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createDefault());
        $expected = '/^Resource \#\d+; ' . preg_quote('<span class="syntax--comment syntax--line syntax--double-slash">// Type: curl</span>', '/') . '$/';
        $this->assertRegExp(
            $expected,
            $this->normalizeEOL($htmlFormatter->renderInner($resource))
        );
    }

    private function _requireResource($name)
    {
        require_once(TESTS_RESOURCES_DIRECTORY . "/classes/" . $name);
    }

    private function _getResourceContents($name)
    {
        return file_get_contents(TESTS_RESOURCES_DIRECTORY. "/tests/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/" . $name);
    }
}
