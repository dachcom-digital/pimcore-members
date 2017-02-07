<?php
namespace Members\Auth\Storage;

use Pimcore\Tool\Session;

class Pimcore extends \Zend_Auth_Storage_Session
{
    /**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Members';

    /**
     * Pimcore constructor.
     *
     * @param string       $namespace
     * @param mixed|string $member
     */
    public function __construct($namespace = self::NAMESPACE_DEFAULT, $member = self::MEMBER_DEFAULT)
    {
        $this->_namespace = $namespace;
        $this->_member = $member;
        $this->_session = Session::get('Members');
    }
}