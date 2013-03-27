<?php

namespace RcApi;

class ClubValidator
{
    public function isValid(ClubInterface $club)
    {
        $name = $club->getName();
        if (empty($name)) {
            return false;
        }
        $leagueId = $club->getLeagueId();
        if (empty($leagueId)) {
            return false;
        }
        return true;
    }
}
