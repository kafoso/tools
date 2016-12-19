<?php
use Kafoso\Tools\Debug\Dumper\HtmlFormatter;

class HtmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $htmlFormatter = new HtmlFormatter(null);
        $expected = '<span class="constant">null</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testBoolean()
    {
        $htmlFormatter = new HtmlFormatter(true);
        $expected = '<span class="constant">true</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testFloat()
    {
        $htmlFormatter = new HtmlFormatter(3.14);
        $expected = '<span class="constant numeric">3.14</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testInteger()
    {
        $htmlFormatter = new HtmlFormatter(42);
        $expected = '<span class="constant numeric">42</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testString()
    {
        $htmlFormatter = new HtmlFormatter("foo");
        $expected = '<span class="string">&quot;foo&quot;</span>';
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testArrayOneDimension()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        $htmlFormatter = new HtmlFormatter(["foo" => "bar"]);
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testArrayOneDimension.expected.html"));
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testResource()
    {
        $resource = curl_init('foo');
        $htmlFormatter = new HtmlFormatter($resource);
        $expected = '/Resource \#\d+; ' . preg_quote('<span class="comment line double-slash php">// Type: curl</span>', '/') . '/';
        $this->assertRegExp($expected, $htmlFormatter->renderInner());
    }

    public function testRender()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        $htmlFormatter = new HtmlFormatter("foo");
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testRender.expected.html"));
        $expected = preg_replace('/Kafoso_Tools_Debug_Dumper_[0-9a-f_]+/', 'Kafoso_Tools_Debug_Dumper_', $expected);
        $found = $htmlFormatter->render();
        $found = preg_replace('/Kafoso_Tools_Debug_Dumper_[0-9a-f_]+/', 'Kafoso_Tools_Debug_Dumper_', $found);
        $this->assertSame($expected, $found);
    }

    public function testObjectOneDimension()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        require_once($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectOneDimension.source.php");
        $class = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectOneDimension_b0559f888359b081714fdea9d26c65b7;
        $class->foo = "bar";
        $htmlFormatter = new HtmlFormatter($class);
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testObjectOneDimension.expected.html"));
        $expected = preg_replace('/Object #[a-f0-9]+/', 'Object #', $expected);
        $expected = preg_replace('/Object_[a-f0-9]+/', 'Object_', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $htmlFormatter->renderInner();
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/Object_[a-f0-9]+/', 'Object_', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }

    public function testArrayMultipleDimensions()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        $htmlFormatter = new HtmlFormatter([
            "foo" => [
                "bar" => [
                    "baz" => 1,
                    "bim" => null
                ],
            ],
        ]);
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testArrayMultipleDimensions.expected.html"));
        $this->assertSame($expected, $htmlFormatter->renderInner());
    }

    public function testArrayMultipleDimensionsOmitted()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        $htmlFormatter = new HtmlFormatter([
            "foo" => [
                "bar" => [
                    "baz" => 1,
                    "bim" => null
                ],
            ],
        ], 2);
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testArrayMultipleDimensionsOmitted.expected.html"));
        $this->assertSame($expected, $htmlFormatter->renderInner());
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
        $htmlFormatter = new HtmlFormatter($classC);
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testObjectMultipleLevelsWithoutRecursion.expected.html"));
        $expected = preg_replace('/Object #[a-f0-9]+/', 'Object #', $expected);
        $expected = preg_replace('/Object_[a-f0-9]+/', 'Object_', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $htmlFormatter->renderInner();
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/Object_[a-f0-9]+/', 'Object_', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }

    public function testObjectWithRecursion()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        require_once($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectWithRecursion.source.php");
        $classA = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB->setParent($classA);
        $classA->setParent($classB);
        $htmlFormatter = new HtmlFormatter($classA);
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testObjectWithRecursion.expected.html"));
        $expected = preg_replace('/Object #[a-f0-9]+/', 'Object #', $expected);
        $expected = preg_replace('/Object_[a-f0-9]+/', 'Object_', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $htmlFormatter->renderInner();
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/Object_[a-f0-9]+/', 'Object_', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }

    public function testObjectOmitted()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat('/..', 5));
        require_once($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/PlainTextFormatterTest/testObjectWithRecursion.source.php");
        $classA = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classC = new Kafoso_Tools_Debug_Dumper_PlainTextFormatterTest_testObjectWithRecursion_298813df09b29eda5ff52f85f788ed5d;
        $classB->setParent($classA);
        $classC->setParent($classB);
        $htmlFormatter = new HtmlFormatter($classC, 2);
        $expected = trim(file_get_contents($baseDirectory . "/resources/unit/Kafoso/Tools/Debug/Dumper/HtmlFormatterTest/testObjectOmitted.expected.html"));
        $expected = preg_replace('/Object #[a-f0-9]+/', 'Object #', $expected);
        $expected = preg_replace('/Object_[a-f0-9]+/', 'Object_', $expected);
        $expected = preg_replace('/Resource #\d+/', 'Resource #1', $expected);
        $found = $htmlFormatter->renderInner();
        $found = preg_replace('/Object #[a-f0-9]+/', 'Object #', $found);
        $found = preg_replace('/Object_[a-f0-9]+/', 'Object_', $found);
        $found = preg_replace('/Resource #\d+/', 'Resource #1', $found);
        $this->assertSame($expected, $found);
    }
}
