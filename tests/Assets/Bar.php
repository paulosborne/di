<?php namespace Assets;

class Bar
{
    public $baz;

    /**
     * @param Assets\Baz $baz
     */
    public function __construct(BazInterface $baz = null)
    {
        $this->baz = $baz;
    }

    public function setBaz(BazInterface $baz)
    {
        $this->baz = $baz;
    }
}