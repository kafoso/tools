<?php
trait Trait_Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7
{
    protected $aTraitVariable;
}

interface Interface_Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7
{
    const AN_INHERITED_CONSTANT = "I'M AN INHERITED CONSTANT!";
}

abstract class Abstract_Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7
{
    protected $anInheritedVariable;
    protected $anOverriddenVariable;
    protected function aProtectedMethod(){}
    protected static function aProtectedStaticMethod(){}
    protected function anInheritedMethod(){}
    abstract public function anAbstractMethod();
    protected static function anInheritedStaticMethod(){}
}

final class Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7 extends Abstract_Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7 implements Interface_Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7
{
    use \Trait_Kafoso_Tools_Debug_Dumper_ObjectOneDimension_b0559f888359b081714fdea9d26c65b7;

    const A_CONSTANT = "I'M A CONSTANT DECLARED IN CLASS!";

    private static $aNull = null;
    protected $aBoolean = false;
    private $aString = "";
    private $aResource;
    protected $anOverriddenVariable = "foo";

    public function __construct()
    {
        $this->aString = "lorem";
        $this->aResource = curl_init("foo");
    }

    function justFunction(){} // Defaults to "public"

    private function aPrivateMethod(){}
    protected function aProtectedMethod(){}
    protected static function aProtectedStaticMethod(){}
    static public function aStaticMethod(){}
    final public function aFinalMethod(){}
    public function anAbstractMethod(){}

    public function forTestingArguments(
        $anEmpty,
        $aNull = null,
        $aBoolean = true,
        $aFloat = 3.14,
        $anInterger = 42,
        $aString = "lorem",
        array $anArray,
        \stdClass $aClass,
        callable $aCallable,
        $oneWithAConstant = PHP_VERSION,
        $oneWithAClassConstant = \DateTime::ATOM,
        &$oneByReference
    ){}
}
