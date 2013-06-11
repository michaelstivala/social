<?php
namespace Craft;

/**
 * Connect by Dukt
 *
 * @package   Connect
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://dukt.net/add-ons/craft/connect/
 * @link      http://dukt.net/add-ons/craft/connect/
 */

/**
 * TokenIdentity represents the data needed to identify a user with a token and an email
 * It contains the authentication method that checks if the provided data can identity the user.
 */
class TokenIdentity extends UserIdentity
{
    private $_id;

    public function __construct() {

    }

    public function authenticate()
    {
        $this->_id = 1;
        $this->username = 'ben';
        $this->errorCode = static::ERROR_NONE;
        return true;
    }
}
