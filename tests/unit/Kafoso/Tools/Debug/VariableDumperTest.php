<?php
use Kafoso\Tools\Debug\VariableDumper;

class VariableDumperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider    dataProvider_testCast
     */
    public function testCast($expected, $value)
    {
        $this->assertSame($expected, VariableDumper::cast($value));
    }

    public function dataProvider_testCast()
    {
        return [
            ["true", true],
            ["false", false],
            ["42", 42],
            ["3.14", 3.14],
            ["foo", "foo"],
            ["(object \stdClass)", new \stdClass],
            ["Array(1) [[\"foo\"] => \"bar\"]", ["foo" => "bar"]],
        ];
    }

    public function testCastResource()
    {
        $resource = curl_init('foo');
        $expected = "/\(resource #\d+ \(type: curl\)\)/";
        $this->assertRegExp($expected, VariableDumper::cast($resource));
    }

    /**
     * @dataProvider    dataProvider_testFound
     */
    public function testFound($expected, $value)
    {
        $this->assertSame($expected, VariableDumper::found($value));
    }

    public function dataProvider_testFound()
    {
        return [
            ["(boolean) true", true],
            ["(boolean) false", false],
            ["(integer) 42", 42],
            ["(double) 3.14", 3.14],
            ["(string) foo", "foo"],
            ["(object) \stdClass", new \stdClass],
            ["(array) Array(1)", ["foo" => "bar"]],
        ];
    }

    public function testFoundResource()
    {
        $resource = curl_init('foo');
        $expected = "/\(resource\) #\d+ \(type: curl\)/";
        $this->assertRegExp($expected, VariableDumper::found($resource));
    }
}
