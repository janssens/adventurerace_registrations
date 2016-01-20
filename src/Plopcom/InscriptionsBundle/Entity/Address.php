<?php
// src/Plopcom/InscriptionsBundle/Entity/Address.php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="addresse")
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $line_1;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $line_2;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $line_3;

    /**
     * @ORM\Column(type="string")
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $county_province;

    /**
     * @ORM\Column(type="string")
     */
    protected $zip_or_postcode;

    /**
     * @ORM\Column(type="string")
     */
    protected $country;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set line1
     *
     * @param string $line1
     *
     * @return Address
     */
    public function setLine1($line1)
    {
        $this->line_1 = $line1;

        return $this;
    }

    /**
     * Get line1
     *
     * @return string
     */
    public function getLine1()
    {
        return $this->line_1;
    }

    /**
     * Set line2
     *
     * @param string $line2
     *
     * @return Address
     */
    public function setLine2($line2)
    {
        $this->line_2 = $line2;

        return $this;
    }

    /**
     * Get line2
     *
     * @return string
     */
    public function getLine2()
    {
        return $this->line_2;
    }

    /**
     * Set line3
     *
     * @param string $line3
     *
     * @return Address
     */
    public function setLine3($line3)
    {
        $this->line_3 = $line3;

        return $this;
    }

    /**
     * Get line3
     *
     * @return string
     */
    public function getLine3()
    {
        return $this->line_3;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set countyProvince
     *
     * @param string $countyProvince
     *
     * @return Address
     */
    public function setCountyProvince($countyProvince)
    {
        $this->county_province = $countyProvince;

        return $this;
    }

    /**
     * Get countyProvince
     *
     * @return string
     */
    public function getCountyProvince()
    {
        return $this->county_province;
    }

    /**
     * Set zipOrPostcode
     *
     * @param string $zipOrPostcode
     *
     * @return Address
     */
    public function setZipOrPostcode($zipOrPostcode)
    {
        $this->zip_or_postcode = $zipOrPostcode;

        return $this;
    }

    /**
     * Get zipOrPostcode
     *
     * @return string
     */
    public function getZipOrPostcode()
    {
        return $this->zip_or_postcode;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

}
