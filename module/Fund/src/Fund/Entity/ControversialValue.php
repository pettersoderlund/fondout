<?php

namespace Fund\Entity;

/**
* ControversialValue
*/
class ControversialValue extends Entity
{

    /**
     * the fund id;
     * @var int
     */
    protected $fundId;

    /**
     * the controversial value
     * @var int
     */
    protected $value;

    public function __construct($fundId, $value)
    {
        $this->fundId = $fundId;
        $this->value = $value;
    }

    /**
     * Gets the the fund id;.
     *
     * @return int
     */
    public function getFundId()
    {
        return $this->fundId;
    }

    /**
     * Gets the the controversial value.
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }
}
