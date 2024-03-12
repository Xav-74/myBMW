<?php

class Auth_Token implements JsonSerializable
{
    /**
     * The auth token
     * @var string $token
     */
    protected $token = '';

    /**
     * The auth expires delay
     * @var int $expires
     */
    protected $expires = 0;

	/**
     * The auth refresh_token
     * @var string $refresh_token
     */
	protected $refresh_token = '';
	
	/**
     * The auth token_type
     * @var string $token_type
     */
	protected $token_type = 'Bearer';

	/**
     * The auth id_token
     * @var string $id_token
     */
	protected $id_token = '';

    /**
     * The gcid
     * @var string $gcid
     */
	protected $gcid = '';


    public function __construct($token, $expires, $refresh_token, $token_type, $id_token, $gcid)
    {
        $this->token = $token;
        $this->expires = $expires;
		$this->refresh_token = $refresh_token;
		$this->token_type = $token_type;
		$this->id_token = $id_token;
        $this->gcid = $gcid;
    }

    /**
     * Get the auth token
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the auth token
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the auth expires delay
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set the auth expires delay
     * @param $expires
     * @return $this
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    /**
     * Get the auth refresh_token
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * Set the auth refresh_token
     * @param $refresh_token
     * @return $this
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = $refresh_token;
        return $this;
    }

    /**
     * Get the auth token_type
     * @return string
     */
    public function getTokenType()
    {
        return $this->token_type;
    }

    /**
     * Set the auth token_type
     * @param $token_type
     * @return $this
     */
    public function setTokenType($token_type)
    {
        $this->token_type = $token_type;
        return $this;
    }

    /**
     * Get the auth id_token
     * @return string
     */
    public function getIdToken()
    {
        return $this->id_token;
    }

    /**
     * Set the auth id_token
     * @param $id_token
     * @return $this
     */
    public function setIdToken($id_token)
    {
        $this->id_token = $id_token;
        return $this;
    }

    /**
     * Get the gcid
     * @return string
     */
    public function getGcId()
    {
        return $this->gcid;
    }

    /**
     * Set the gcid
     * @param $gcid
     * @return $this
     */
    public function setGcId($gcid)
    {
        $this->gcid = $gcid;
        return $this;
    }


    /**
     * Used to be json encoded
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'token' => $this->getToken(),
            'expires' => $this->getExpires(),
			'refresh_token' => $this->getRefreshToken(),
			'token_type' => $this->getTokenType(),
			'id_token' => $this->getIdToken(),
            'gcid' => $this->getGcId()
        ];
    }
}

?>