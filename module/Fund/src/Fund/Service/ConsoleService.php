<?php

namespace Fund\Service;

/**
* ConsoleService
*/
class ConsoleService extends FundService
{
    protected $entityManager;

    public function getEM ()
    {
        return $this->getEntityManager();
    }
}
