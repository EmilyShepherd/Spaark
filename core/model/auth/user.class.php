<?php namespace Spaark\Core\Model\Auth;
/**
 */

use \Spaark\Core\Model\Vars\Password;

class User extends \Spaark\Core\Model\Base\DefaultModel
{
    public static function _fromSingle()
    {
        return Session::get('runAs');
    }

    public function runAs()
    {
        $this->session->runAs = $this;
    }

    public function login()
    {return;
        $this->session->currentUser = $this;
        $this->session->runAs       = $this;
    }

    public function checkPassword(Password $pass)
    {
        return $pass->checkHash($this->password);
    }
}

?>