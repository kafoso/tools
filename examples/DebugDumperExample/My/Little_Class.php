<?php
namespace My;

class Little_Class
{
    private $id;
    protected $parent = null;
    protected $children = [];
    public static $list = [
        "foo",
        "bar",
        [
            "baz"
        ]
    ];
    public $html = '<b>foo</b>';

    public function getId()
    {
        return $this->id;
    }
}
