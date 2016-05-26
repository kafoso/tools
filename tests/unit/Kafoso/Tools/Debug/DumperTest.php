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
        $expected = $this->getResource("testEmptyObjectIsPreparable.txt");
        $prepared = $this->replaceSqlHashesWithGenericIdentifier(Dumper::prepare($classA));
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
        $expected = $this->getResource("testScalarTypesAndNullAreFormattedNicely.txt");
        $prepared = $this->replaceSqlHashesWithGenericIdentifier(Dumper::prepare($classA));
        $this->assertSame($expected, $prepared);
    }

    public function testIndexedArraysAreFormattedNicely()
    {
        $anArray = [
            "a",
            "37" => "b",
            3
        ];
        $expected = $this->getResource("testIndexedArraysAreFormattedNicely.txt");
        $prepared = $this->replaceSqlHashesWithGenericIdentifier(Dumper::prepare($anArray));
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
        $expected = $this->getResource("testAssociativeArraysAreFormattedNicely.txt");
        $prepared = $this->replaceSqlHashesWithGenericIdentifier(Dumper::prepare($classA));
        $this->assertSame($expected, $prepared);
    }

    public function testDepthRestrictionWorks()
    {
        $classA = new stdClass;
        $classB = new stdClass;
        $classA->classB = $classB;
        $prepared = $this->replaceSqlHashesWithGenericIdentifier(Dumper::prepare($classA, 1));
        $expected = $this->getResource("testDepthRestrictionWorks.txt");
        $this->assertSame($expected, $prepared);
    }

    public function testRecursionIndicatorIsApplied()
    {
        $classA = new stdClass;
        $classB = new stdClass;
        $classA->classB = $classB;
        $classB->classA = $classA;
        $prepared = $this->replaceSqlHashesWithGenericIdentifier(Dumper::prepare($classA));
        $expected = $this->getResource("testRecursionIndicatorIsApplied.txt");
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
        $prepared = $this->replaceSqlHashesWithGenericIdentifier(Dumper::prepare($classA));
        $expected = $this->getResource("testALargeAndComplexObjectIsFormattedCorrectly.txt");
        $this->assertSame($expected, $prepared);
    }

    /**
     * Since object hashes change for every object instantion, the snowflake hashes are converted to a static value
     * so that asserting expectations becomes possible.
     */
    protected function replaceSqlHashesWithGenericIdentifier($prepared)
    {
        return preg_replace("/ Object \&[0-9a-f]{32}/", " Object &SPL_OBJECT_HASH", $prepared);
    }

    protected function getResource($fileName)
    {
        return file_get_contents("{$this->dumperTestResourceDirectory}/{$fileName}");
    }
}
