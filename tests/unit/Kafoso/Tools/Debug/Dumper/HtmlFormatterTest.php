<?php
use Kafoso\Tools\Debug\Dumper\HtmlFormatter;

class HtmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $htmlFormatter = new HtmlFormatter(null);
        $expected = '<span style="color:#d19a66;">null</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testBoolean()
    {
        $htmlFormatter = new HtmlFormatter(true);
        $expected = '<span style="color:#d19a66;">true</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testFloat()
    {
        $htmlFormatter = new HtmlFormatter(3.14);
        $expected = '<span style="color:#d19a66;">3.14</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testInteger()
    {
        $htmlFormatter = new HtmlFormatter(42);
        $expected = '<span style="color:#d19a66;">42</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testString()
    {
        $htmlFormatter = new HtmlFormatter("foo");
        $expected = '<span style="color:#98c379;">&quot;foo&quot;</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testArrayOneDimension()
    {
        $htmlFormatter = new HtmlFormatter(["foo" => "bar"]);
        $expected = '['
            . PHP_EOL
            . '    <span style="color:#98c379;">&quot;foo&quot;</span>'
            . ' => '
            . '<span style="color:#98c379;">&quot;bar&quot;</span>'
            . PHP_EOL
            . ']';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testResource()
    {
        $resource = curl_init('foo');
        $htmlFormatter = new HtmlFormatter($resource);
        $expected = '/Resource \#\d+ ' . preg_quote('<span style="color:#5c6370;">(type: curl)</span>', '/') . '/';
        $this->assertRegExp($expected, $htmlFormatter->renderInner());
    }

    public function testObjectOneDimension()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        require_once($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testObjectOneDimension.source.php");
        $class = new Kafoso_Tools_Debug_Dumper_HtmlFormatterTest_testObjectOneDimension_a7bfddaf1078c5fb4341961839326645;
        $class->foo = "bar";
        $htmlFormatter = new HtmlFormatter($class);
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testObjectOneDimension.expected.html"));
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testRender()
    {
        $htmlFormatter = new HtmlFormatter("foo");
        $expected = '<div style="background-color:rgb(40,44,52);color:#abb2bf;font-family:Menlo,Consolas,\'DejaVu Sans Mono\',monospace;overflow:hidden;padding:10px;position:relative;">'
            . '<span style="color:#98c379;">&quot;foo&quot;</span>'
            . '</div>';
        $this->assertSame($expected, $htmlFormatter->render());
    }
}
