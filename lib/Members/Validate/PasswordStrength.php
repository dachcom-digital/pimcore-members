<?php
namespace Members\Validate;

use Zend_Validate_Exception;

class PasswordStrength extends \Zend_Validate_Abstract
{
    const NO_NUMBER = 'passwordNoNumber';

    const NO_LOWER_CASE_LETTER = 'passwordNoLowerCaseLetter';

    const NO_UPPER_CASE_LETTER = 'passwordNoUpperCaseLetter';

    const NO_SPECIAL_CHARACTER = 'passwordNoSpecialCharacter';

    /**
     * Validation failure message template definitions
     * @var array
     */
    protected $_messageTemplates = [
        self::NO_NUMBER            => "Password must contain at least one number",
        self::NO_LOWER_CASE_LETTER => "Password must contain at least one lower case letter",
        self::NO_UPPER_CASE_LETTER => "Password must contain at least one upper case letter",
        self::NO_SPECIAL_CHARACTER => "Password must contain at least one special character",
    ];

    protected $number = TRUE;

    protected $upperCaseLetter = TRUE;

    protected $lowerCaseLetter = TRUE;

    protected $specialCharacter = FALSE;

    /**
     * Sets validator options
     *
     * @param array|\Zend_Config $options
     */
    public function __construct($options = [])
    {
        if ($options instanceof \Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp['number'] = array_shift($options);
            if (!empty($options)) {
                $temp['upperCaseLetter'] = array_shift($options);
            }
            if (!empty($options)) {
                $temp['lowerCaseLetter'] = array_shift($options);
            }
            if (!empty($options)) {
                $temp['specialCharacter'] = array_shift($options);
            }
            $options = $temp;
        }
        if (array_key_exists('number', $options)) {
            $this->setNumber($options['number']);
        }
        if (array_key_exists('upperCaseLetter', $options)) {
            $this->setUpperCaseLetter($options['upperCaseLetter']);
        }
        if (array_key_exists('lowerCaseLetter', $options)) {
            $this->setLowerCaseLetter($options['lowerCaseLetter']);
        }
        if (array_key_exists('specialCharacter', $options)) {
            $this->setSpecialCharacter($options['specialCharacter']);
        }
    }

    /**
     * @return boolean
     */
    public function isNumber()
    {
        return $this->number;
    }

    /**
     * @param boolean $number
     *
     * @return Password
     */
    public function setNumber($number)
    {
        $this->number = (bool)$number;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUpperCaseLetter()
    {
        return $this->upperCaseLetter;
    }

    /**
     * @param boolean $upperCaseLetter
     *
     * @return Password
     */
    public function setUpperCaseLetter($upperCaseLetter)
    {
        $this->upperCaseLetter = (bool)$upperCaseLetter;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isLowerCaseLetter()
    {
        return $this->lowerCaseLetter;
    }

    /**
     * @param boolean $lowerCaseLetter
     *
     * @return Password
     */
    public function setLowerCaseLetter($lowerCaseLetter)
    {
        $this->lowerCaseLetter = (bool)$lowerCaseLetter;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSpecialCharacter()
    {
        return $this->specialCharacter;
    }

    /**
     * @param boolean $specialCharacter
     *
     * @return Password
     */
    public function setSpecialCharacter($specialCharacter)
    {
        $this->specialCharacter = (bool)$specialCharacter;
        return $this;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     *
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        $this->_setValue($value);
        if ($this->isNumber() && preg_match('/[0-9]/', $value) !== 1) {
            $this->_error(self::NO_NUMBER);

            return FALSE;
        }
        if ($this->isLowerCaseLetter() && preg_match('/[a-z]/', $value) !== 1) {
            $this->_error(self::NO_LOWER_CASE_LETTER);

            return FALSE;
        }
        if ($this->isUpperCaseLetter() && preg_match('/[A-Z]/', $value) !== 1) {
            $this->_error(self::NO_UPPER_CASE_LETTER);

            return FALSE;
        }
        if ($this->isSpecialCharacter() && preg_match('/[^a-zA-Z0-9]/', $value) !== 1) {
            $this->_error(self::NO_SPECIAL_CHARACTER);

            return FALSE;
        }

        return TRUE;
    }
}

// unfortunately we need this alias here, since ZF plugin loader isn't able to handle namespaces correctly
class_alias("Members\\Validate\\PasswordStrength", "Members_Validate_PasswordStrength");