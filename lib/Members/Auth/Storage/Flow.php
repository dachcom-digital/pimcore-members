<?php
namespace Members\Auth\Storage;

class Flow implements \Zend_Auth_Storage_Interface
{
    /**
     * @var \stdClass
     */
    protected $storage;

    /**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Zend_Auth';

    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'storage';

    /**
     * Session namespace
     *
     * @var mixed
     */
    protected $_namespace;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $_member;

    /**
     * Stream constructor.
     *
     * @param string $namespace
     * @param string $member
     */
    public function __construct($namespace = self::NAMESPACE_DEFAULT, $member = self::MEMBER_DEFAULT)
    {
        $this->storage = new \stdClass;
        $this->_namespace = $namespace;
        $this->_member = $member;
    }

    /**
     * Returns the session namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Returns the name of the session object member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->_member;
    }

    /**
     * Returns true if and only if storage is empty.
     *
     * @return boolean
     * @throws \Zend_Auth_Storage_Exception If it is impossible to determine whether storage is empty
     */
    public function isEmpty()
    {
        return !isset($this->storage->{$this->_member});
    }

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws \Zend_Auth_Storage_Exception If reading contents from storage is impossible
     * @return mixed
     */
    public function read()
    {
        return $this->storage->{$this->_member};
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws \Zend_Auth_Storage_Exception If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents)
    {
        $this->storage->{$this->_member} = $contents;
    }

    /**
     * Clears contents from storage
     *
     * @throws \Zend_Auth_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clear()
    {
        unset($this->storage->{$this->_member});
    }
}