<?php
interface Interface_Abstract_Kafoso_Tools_Debug_Dumper_HtmlFormatterTest_testObjectOneDimension_a7bfddaf1078c5fb4341961839326645
{
    const A_CONSTANT = "I'M A CONSTANT!";
}

abstract class Abstract_Kafoso_Tools_Debug_Dumper_HtmlFormatterTest_testObjectOneDimension_a7bfddaf1078c5fb4341961839326645 implements Interface_Abstract_Kafoso_Tools_Debug_Dumper_HtmlFormatterTest_testObjectOneDimension_a7bfddaf1078c5fb4341961839326645
{
    protected $anInheritedVariable;
    abstract public function anAbstractMethod();
}

final class Kafoso_Tools_Debug_Dumper_HtmlFormatterTest_testObjectOneDimension_a7bfddaf1078c5fb4341961839326645 extends Abstract_Kafoso_Tools_Debug_Dumper_HtmlFormatterTest_testObjectOneDimension_a7bfddaf1078c5fb4341961839326645
{
    private static $aNull = null;
    protected $aBoolean = false;
    private $aString = "";

    private $parent = null;

    public function __construct()
    {
        $this->aString = "lorem";
    }

    function justFunction(){} // Defaults to "public"

    private function aPrivateMethod(){}
    protected function aProtectedMethod(){}
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
        \stdClass $aClass,
        $oneWithAConstant = PHP_VERSION,
        $oneWithAClassConstant = \DateTime::ATOM,
        &$oneByReference
    ){}

    public function setParent(Kafoso_Tools_Debug_Dumper_HtmlFormatterTest_testObjectOneDimension_a7bfddaf1078c5fb4341961839326645 $parent)
    {
        $this->parent = $parent;
    }
}
