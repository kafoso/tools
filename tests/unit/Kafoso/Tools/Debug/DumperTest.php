<?php
use Kafoso\Tools\Debug\Dumper;

class DebugTest extends \PHPUnit_Framework_TestCase
{
    protected $dumperTestResourceDirectory;

    public function setUp()
    {
        $this->dumperTestResourceDirectory = realpath(
            __DIR__
            . str_repeat('/..', 4)
            . "/resources/unit/Kafoso/Tools/Debug/DumperTest"
        );
    }

    public function testEmptyObjectIsPreparable()
    {
        $classA = new stdClass;
        $expected = $this->normalizeEOL(trim($this->getResource("testEmptyObjectIsPreparable.txt")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepare($classA)));
        $this->assertSame($expected, $prepared);
    }

    public function testScalarTypesAndNullAreFormattedNicely()
    {
        $classA = new stdClass;
        $classA->aBoolean = true;
        $classA->aFloat = 42.2;
        $classA->anInteger = 42;
        $classA->aString = "foo";
        $classA->justNull = null;
        $expected = $this->normalizeEOL(trim($this->getResource("testScalarTypesAndNullAreFormattedNicely.txt")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepare($classA)));
        $this->assertSame($expected, $prepared);
    }

    public function testIndexedArraysAreFormattedNicely()
    {
        $anArray = [
            "a",
            "37" => "b",
            3
        ];
        $expected = $this->normalizeEOL(trim($this->getResource("testIndexedArraysAreFormattedNicely.txt")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepare($anArray)));
        $this->assertSame($expected, $prepared);
    }

    public function testAssociativeArraysAreFormattedNicely()
    {
        $classA = new stdClass;
        $classA->anArray = [
            "foo",
            "subArray" => [
                "a" => 1,
                "b" => 2.0,
                "c" => "3",
            ]
        ];
        $expected = $this->normalizeEOL(trim($this->getResource("testAssociativeArraysAreFormattedNicely.txt")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepare($classA)));
        $this->assertSame($expected, $prepared);
    }

    public function testDepthRestrictionWorks()
    {
        $classA = new stdClass;
        $classB = new stdClass;
        $classA->classB = $classB;
        $expected = $this->normalizeEOL(trim($this->getResource("testDepthRestrictionWorks.txt")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepare($classA, 1)));
        $this->assertSame($expected, $prepared);
    }

    public function testRecursionIndicatorIsApplied()
    {
        $classA = new stdClass;
        $classB = new stdClass;
        $classA->classB = $classB;
        $classB->classA = $classA;
        $expected = $this->normalizeEOL(trim($this->getResource("testRecursionIndicatorIsApplied.txt")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepare($classA)));
        $this->assertSame($expected, $prepared);
    }

    public function testALargeAndComplexObjectIsFormattedCorrectly()
    {
        $classA = new stdClass;
        $classA_1 = new stdClass;
        $classA_2 = new stdClass;
        $classA_2_1 = new stdClass;
        $classA_2_1_1 = new stdClass;
        $classA->anArray = [
            new stdClass,
            new stdClass
        ];
        $classA_1->classA_1 = $classA_1; // Recursion
        $classA_2_1->aBoolean = true;
        $classA_2_1->anArray = [
            0 => "a"
        ];
        $classA->classA = $classA; // Recursion
        $classA->classA_1 = $classA_1;
        $classA->classA_2 = $classA_2;
        $classA->classA_2->classA_2_1 = $classA_2_1;
        $classA->classA_2->classA_2_1->classA_2_1_1 = $classA_2_1_1;
        $classA->classA_2->classA_2_1->classA_2_1_1->classA = $classA; // Omitted
        $expected = $this->normalizeEOL(trim($this->getResource("testALargeAndComplexObjectIsFormattedCorrectly.txt")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepare($classA)));
        $this->assertSame($expected, $prepared);
    }

    public function testJsonCanDumpSimpleArray()
    {
        $array = [
            "foo" => "bar",
        ];
        $expected = $this->normalizeEOL(trim($this->getResource("testJsonCanDumpSimpleArray.json")));
        $prepared = trim(Dumper::prepareJson($array));
        $this->assertSame($expected, $prepared);
    }

    public function testJsonWillDumpAllObjectVariablesEvenPrivateAndProtected()
    {
        require_once("{$this->dumperTestResourceDirectory}/testJsonWillDumpAllObjectVariablesEvenPrivateAndProtected.php");
        $array = [
            "foo" => new testJsonWillDumpAllObjectVariablesEvenPrivateAndProtected,
        ];
        $expected = $this->normalizeEOL(trim($this->getResource("testJsonWillDumpAllObjectVariablesEvenPrivateAndProtected.json")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepareJson($array)));
        $this->assertSame($expected, $prepared);
    }

    public function testJsonObjectRecursionIsTruncated()
    {
        $classA = new stdClass;
        $classB = new stdClass;
        $classA->classB = $classB;
        $classB->classA = $classA;
        $expected = $this->normalizeEOL(trim($this->getResource("testJsonObjectRecursionIsTruncated.json")));
        $prepared = $this->replaceSplHashesWithGenericIdentifier(trim(Dumper::prepareJson($classA)));
        $this->assertSame($expected, $prepared);
    }

    public function testJsonDepthExceededWillOmitArrayAndObjectValues()
    {
        $classA = new \stdClass;
        $classA->arrayA = [
            "classB" => new \stdClass,
            "arrayB" => [
                "this should be omitted"
            ]
        ];
        $expected = $this->normalizeEOL(trim($this->getResource("testJsonDepthExceededWillOmitArrayAndObjectValues.json")));
        $prepared = $this->normalizeEOL($this->replaceSplHashesWithGenericIdentifier(Dumper::prepareJson($classA, 2)));
        $this->assertSame($expected, $prepared);
    }

    protected function normalizeEOL($str)
    {
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        return $str;
    }

    /**
     * Since object hashes change for every object instantion, the snowflake hashes are converted to a static value
     * so that asserting expectations becomes possible.
     */
    protected function replaceSplHashesWithGenericIdentifier($prepared)
    {
        return preg_replace("/ Object \&[0-9a-f]{32}/", " Object &SPL_OBJECT_HASH", $prepared);
    }

    protected function getResource($fileName)
    {
        return file_get_contents("{$this->dumperTestResourceDirectory}/{$fileName}");
    }
}
