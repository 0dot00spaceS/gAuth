<?php

/**
 * Project: Phalcon 3 Api Skelenton
 * Copyright (c) 2016 Iulian Gafiu
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

use \Phalcon\Di;

/**
 * Class gAuth process the Authentification with tokens
 * Tokens are stored like encrypted cookies
 * @version 1.0.0
 * @license: The MIT License (MIT)
 * @author: Iulian Gafiu <weblysolutions@gmail.com>
 */
class gAuth
{

    /**
     * @var string $secret The secret key needed for
     * cookie encryption
     * @see gAuth->createToken
     */
    private $secret;
    /**
     * @var \Phalcon\Crypt mixed The Crypt Di
     * @see https://docs.phalconphp.com/en/latest/reference/crypt.html
     */
    private $crypt;
    /**
     * @var \Phalcon\Http\Response\Cookies mixed Cookies Di
     * @see https://docs.phalconphp.com/en/latest/reference/cookies.html
     */
    private $cookies;
    /**
     * @var \Phalcon\Http\Request mixed The Request Di
     * @see https://docs.phalconphp.com/en/latest/reference/request.html
     *
     */
    private $request;
    /**
     * @var StdClass stdClass The token object
     * @see http://php.net/manual/en/reserved.classes.php
     */
    public $token;
    /**
     * @var array $params The
     */
    public $params;


    /**
     * You must register the next 3 services in
     * your Di Manager or change the below names
     * @see var gAuth::$crypt
     * @see var gAuth::$cookies
     * @see var gAuth::$request
     */
    public function __construct()
    {
        $this->token = new stdClass();
        $this->crypt = Di::getDefault()->get('crypt');
        $this->cookies = Di::getDefault()->get('cookies');
        $this->request = Di::getDefault()->get('request');

        $this->secret = '&1qjeG[=?219~5^"4;/zmzcVerT3#7';
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function createToken($params)
    {
        $this->params = (object)$params;
        $this->token->hash = $this->crypt->encryptBase64(json_encode($params), $this->secret);
    }

    /**
     * @var $this ->param->exp is defined in the
     * createToken method
     * @see gAuth::createToken()
     */
    public function store()
    {
        if (!$this->cookies->has('_token'))
            $this->cookies->set('_token', $this->token->hash, $this->params->exp, '/')->send();
    }


    /**
     * @return string
     */
    public function getStoredToken()
    {
        return (string)$this->cookies->get('_token');
    }


    /**
     * @return mixed
     */
    public function getJsonToken()
    {
        return $this->crypt->decryptBase64($this->getStoredToken(), $this->secret);
    }


    /**
     * @return mixed
     */
    public function getObjectToken()
    {
        return json_decode($this->getJsonToken());
    }


    /**
     * @return mixed
     */
    public function getIss()
    {
        $token = $this->getObjectToken();

        return $token->iss;
    }

    /**
     * @return mixed
     */
    public function getIat()
    {
        $token = $this->getObjectToken();

        return $token->iat;
    }

    /**
     * @return mixed
     */
    public function getExp()
    {
        $token = $this->getObjectToken();

        return $token->exp;
    }

    /**
     * @return mixed
     */
    public function getNbf()
    {
        $token = $this->getObjectToken();

        return $token->nbf;
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    public function getSub($param)
    {
        $token = $this->getObjectToken();

        return $token->sub->$param;
    }


    /**
     * @return bool
     */
    public function validIss()
    {
        if ($this->getIss() == $this->request->getHeader('host'))
            return true;
    }

    /**
     * @return bool
     */
    public function validIat()
    {
        if ($this->getIat() < time())
            return true;
    }

    /**
     * @return bool
     */
    public function validExp()
    {
        if ($this->getExp() > time())
            return true;
    }

    /**
     * @return bool
     */
    public function validNbf()
    {
        $nbf = new DateTime($this->getNbf());
        $now = new DateTime(date('Y-m-d H:m:j'));
        $now->modify('+ 10 minutes');

        if ($now > $nbf)
            return true;

    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ($this->validIss() && $this->validIat() && $this->validExp() && $this->validNbf())
            return true;
    }


}