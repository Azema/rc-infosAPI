<?php

namespace RcApi\Resource\League;

/**
 * Interface for club
 */
interface LeagueInterface
{
    public function setId($id);
    public function setName($name);
    public function setAddress($address);
    public function setpostCode($postCode);
    public function setCity($city);
    public function setEmail($email);
    public function setPhone($phone);
    public function setSiteWeb($url);
    public function setCreatedAt($date);
    public function setUpdatedAt($date);
    public function setClubs($clubs);

    public function getId();
    public function getName();
    public function getAddress();
    public function getpostCode();
    public function getCity();
    public function getEmail();
    public function getPhone();
    public function getSiteWeb();
    public function getCreatedAt();
    public function getUpdatedAt();
    public function getClubs();
}
