<?php namespace Assets;

class Bar
{
    public $baz;

    /**
     * @param Assets\Baz $baz
     */
    public function __construct(BazInterface $baz)
    {
        $this->baz = $baz;
    }
}