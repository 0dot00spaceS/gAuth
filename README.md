# JWT Phalcon 3.x - Gauth

Auth library for Phalcon 3.x based on JWT standard that can be used on traditional websites or API based applications. It is recommended to upgrade your PHP installation to 5.6. because in Phalcon 3.0.0 the support for PHP 5.4 has been deprecated and in PHP 5.5 some unsafe functions has been also deprecated.

###Requirements
 - Phalcon 3.x
 - PHP 5.6


###Installation
- cd to library folder
- git clone https://github.com/infobuscador/gAuth.git
- In your Di Manager call services: 


```php
   use Phalcon\Http\Response\Cookies;
   use Phalcon\Http\Response;
   use Phalcon\Http\Request;
   use Phalcon\Crypt;
  /*
   * Load Phalcon Crypt Service
   * \Phalcon\Crypt
   * @see https://docs.phalconphp.com/en/latest/reference/crypt.html
   * */
  $di->setShared('crypt', function(){
      $crypt = new Crypt();
      return $crypt;
  });

   /*
    * Load Phalcon Cookies Service
    * \Phalcon\Http\Response\Cookies
    * @see https://docs.phalconphp.com/en/latest/reference/cookies.html
    * */
    $di->setShared('cookies', function(){
        $cookies = new Cookies();
        $cookies->useEncryption(false);
        return $cookies;
    });

   /*
    * Load Phalcon Request Service
    * Phalcon\Http\Request
    * @see https://docs.phalconphp.com/en/latest/reference/request.html
     * */
    $di->setShared('request', function(){
       $request = new Request();
       return $request;
    });

   /*
    * Load gAuth Service
    * */
    $di->setShared('gauth', function(){
       $gauth = new gAuth();
        return $gauth;
    });
```

###Creation Example:
```php
   /*
    * Simple example via Router File
    * In this library we only use the Payload because
    * encryption is handled by Phalcon Crypt
    * */
    $app->get("/login", function(){

       /*
        * Method is called after success login.
        * @return string. Token is returned as HTTP response 
        * */
        $this->gauth->createToken([
                'iss' => 'api.yourapp.com',
                'iat' => time(),
                'exp' => time() + 3600,
                'nbf' => date('Y-m-d H:m:j'),
                'sub' => [
                   'id' => 23, // after check 
                    'role' => 'admin'
                    ]
                ]);

       /*
        * You need to call the store method if you want
        * to save the token as cookie.
        * @return void
        * */
        $this->gauth->store();

    });
```

###Check Example
```php

   /*
    * Token can be checked by calling isValid()
    * If any param doesn't match the condition the token will
    * be marked as invalid.
    * @see Manual Validations below
    * */
    $app->get("/private", function(){
        if(!$this->gauth->isValid()){
            throw new \Phalcon\Exception('Validation not passed');
        }
    });
```


### Manual Validations
```php

    /*
     * Iss validation checks if the stored Iss
     * match with the current host by comparing
     * the current host $this->request->getHeader('host')
     * */
    if(!$this->gauth->validIss()){
        throw new \Phalcon\Exception('Iss not valid');
    }


    /*
     * Iat validation checks if the Issued at time
     * is not greater that current time.
     * */
    if(!$this->gauth->validIat()){
        throw new \Phalcon\Exception('Iat not valid' );
    }


    /*
     * Exp validation checks if current time is
     * greatter that stored time. If not then throw exception
     * */
    if(!$this->gauth->validExp()){
        throw new \Phalcon\Exception('Token was expired' );
    }


    /*
     * Nbf validation check if the creation date is
     * greatter that stored NBF. If not then throw exception
     * */
    if(!$this->gauth->validNbf()){
        throw new \Phalcon\Exception('Nbf cannot be greatter that token creation date' );
    }
```
###Getters
```php
    $app->get("/getters", function(){

        // Returns the stored hash
        $this->gauth->getStoredToken();

        // Returns the stored payload as Object
        $this->gauth->getObjectToken();

        // Returns the stored payload as JSON string
        $this->gauth->getJsonToken();


        // Returns token ISS as string
        $this->gauth->getIss();

        // Returns token IAT as string
        $this->gauth->getIat();

        // Returns token EXP as string
        $this->gauth->getExp();

        // Returns token NBF as string
        $this->gauth->getNbf();
        
        //Returns token SUB as array
        $this->gauth->getSub()
        
        //Returns SUB value by key
        $this->gauth->getSub('email')
        $this->gauth->getSub('id')
        
        

    });
```
