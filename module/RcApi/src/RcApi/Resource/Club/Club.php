<?php

/**
 *
 */
namespace RcApi\Resource\Club;

use DateTime;

/**
 * Clubs
 */
class Club implements ClubInterface
{
    protected $id;
    protected $name;
    protected $address;
    protected $postCode;
    protected $city;
    protected $email;
    protected $phone;
    protected $siteWeb;
    protected $createdAt;
    protected $updatedAt;
    protected $tracks;
    protected $leagueId;

    public function setId($id)
    {
        if (!preg_match('/^[0-9]{1,11}$/', $id)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Identifier provided, "%s", does not appear to be a valid',
                $id
            ));
        }
        $this->id = (int)$id;
    }

    public function setName($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'name provided, "%s", does not appear to be a valid',
                $name
            ));
        }
        $this->name = $name;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function setpostCode($postCode)
    {
        if (!empty($postCode) && !preg_match('/^[0-9]{5,6}$/', $postCode)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'postCode provided, "%s", does not appear to be valid',
                $postCode
            ));
        }
        $this->postCode = $postCode;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function setEmail($email)
    {
        if (null !== $email
            && !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Email must be valid; "%s" fails validation',
                $email
            ));
        }
        $this->email = $email;
    }

    public function setPhone($phone)
    {
        if (null !== $phone) {
            $phone = preg_replace('/[^\+0-9]*/', '', $phone);
            if (!preg_match('/^(\+33|0)[0-9]{9}$/', $phone)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Phone must be valid; "%s" fails validation',
                    $phone
                ));
            }
        }
        $this->phone = $phone;
    }

    public function setSiteWeb($url)
    {
        if (null !== $url
            && !filter_var($url, FILTER_VALIDATE_URL)
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Site Web URL must be valid; "%s" fails validation',
                $url
            ));
        }
        $this->siteWeb = $url;
    }

    public function setCreatedAt($date)
    {
        $this->createdAt = $date;
    }

    public function setUpdatedAt($date)
    {
        $this->updatedAt = $date;
    }

    public function setTracks($tracks)
    {
        $this->tracks = $tracks;
    }

    public function setLeagueId($leagueId)
    {
       $this->leagueId = $leagueId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPostCode()
    {
        return $this->postCode;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getSiteWeb()
    {
        return $this->siteWeb;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getTracks()
    {
        return $this->tracks;
    }

    public function getLeagueId()
    {
       return $this->leagueId;
    }
}
