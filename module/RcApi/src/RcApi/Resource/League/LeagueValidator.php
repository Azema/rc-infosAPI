<?php

namespace RcApi\Resource\League;

class LeagueValidator
{
    public function isValid(LeagueInterface $league)
    {
        $name = $league->getName();
        if (empty($name)) {
            return false;
        }
        return true;
    }
}
