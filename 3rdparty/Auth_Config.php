<?php

class Auth_Config
{
    /**
     * The Vehicle Identification Number
     * @var string $vin
     */
    protected $vin = '';

    /**
     * The username from your Connected Drive application
     * @var string $username
     */
    protected $username = '';

    /**
     * The password from your Connected Drive application
     * @var string $password
     */
    protected $password = '';
	
	 /**
     * The brand of the vehicle
     * @var string $brand
     */
    protected $brand = '';	

    /**
     * Populate the Config object with the 3 variables
     * @param $vin
     * @param $username
     * @param $password
	 * @param $brand
     */
    public function __construct($vin, $username, $password, $brand)
    {
        $this->vin = $vin;
        $this->username = $username;
        $this->password = $password;
		$this->brand = $brand;
    }

    /**
     * Get the VIN value
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * Set the new VIN value
     * @param $vin
     * @return $this
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
        return $this;
    }

    /**
     * Get the username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the username
     * @param $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get the password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the password
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
	
	 /**
     * Get the brand
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set the brand
     * @param $brand
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }
}

?>