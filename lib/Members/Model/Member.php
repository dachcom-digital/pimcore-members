<?php

namespace Members\Model;

use Pimcore\Model\Object\Concrete;

use Pimcore\Model\Object\Folder;
use Pimcore\Model\Document\Email;
use Members\Model\Configuration;

class Member extends Concrete {

    public function register(array $data)
    {
        $argv = compact('data');
        $argv['validateFor'] = 'create';

        $results = \Pimcore::getEventManager()->triggerUntil('members.register.validate',
            $this, $argv, function ($v) {
                return ($v instanceof \Zend_Filter_Input);
            });
        $input = $results->last();

        if (!$input instanceof \Zend_Filter_Input)
        {
            throw new \Exception('No validate listener attached to "members.register.validate" event');
        }

        if (!$input->isValid())
        {
            return $input;
        }

        try
        {
            $this->setValues($input->getUnescaped());

            //@fixme: which userGroup to registered User?
            //$this->getGroups( array() );

            $this->setKey(\Pimcore\File::getValidFilename($this->getEmail()));
            $this->setParent(Folder::getByPath('/' . ltrim(Configuration::get('auth.adapter.objectPath'), '/')));
            $this->save();
            \Pimcore::getEventManager()->trigger('members.register.post', $this, $argv);
        }
        catch (\Exception $e)
        {
            if ($this->getId())
            {
                $this->delete();
            }

            throw $e;
        }

        return $input;
    }

    public function updateProfile(array $data)
    {
        $argv = compact('data');
        $argv['validateFor'] = 'update';

        $results = \Pimcore::getEventManager()->triggerUntil('members.update.validate',
            $this, $argv, function ($v) {
                return ($v instanceof \Zend_Filter_Input);
            });
        $input = $results->last();

        if (!$input instanceof \Zend_Filter_Input)
        {
            throw new \Exception('No validate listener attached to "members.update.validate" event');
        }

        if (!$input->isValid())
        {
            return $input;
        }

        try
        {
            $this->setValues($input->getUnescaped());
            $this->save();
            \Pimcore::getEventManager()->trigger('members.update.post', $this, $argv);
        }
        catch (\Exception $e)
        {
            throw $e;
        }

        return $input;
    }

    public function createHash($algo = 'md5')
    {
        return hash($algo, $this->getId() . $this->getEmail() . mt_rand());
    }

    public function confirm()
    {
        //do not check mandatory fields because of conditional logic!
        $this->setOmitMandatoryCheck(true);
        $this->setPublished(true);
        $this->setConfirmHash(null);
        $this->save();
        return $this;
    }

    public function requestPasswordReset()
    {
        //do not check mandatory fields because of conditional logic!
        $this->setOmitMandatoryCheck(true);
        $this->setResetHash($this->createHash());
        $this->save();

        $doc = Email::getByPath( Configuration::getLocalizedPath('emails.passwordReset') );

        if (!$doc)
        {
            throw new \Exception('No password reset email template defined');
        }

        /** @var \Zend_Controller_Request_Http $request */
        $request = \Zend_Controller_Front::getInstance()->getRequest();
        $email = new \Pimcore\Mail();
        $email->addTo($this->getEmail());
        $email->setDocument($doc);
        $email->setParams([
            'host' => sprintf('%s://%s', $request->getScheme(), $request->getHttpHost()),
            'member_id' => $this->getId(),
        ]);

        $email->send();

        return $this;
    }

    public function resetPassword(array $data)
    {
        $argv = compact('data');
        $results = \Pimcore::getEventManager()->triggerUntil('members.password.reset',
            $this, $argv, function ($v) {
                return ($v instanceof \Zend_Filter_Input);
            });

        $input = $results->last();

        if (!$input instanceof \Zend_Filter_Input)
        {
            throw new \Exception('No validate listener attached to "members.password.reset" event');
        }

        if (!$input->isValid())
        {
            return $input;
        }

        //do not check mandatory fields because of conditional logic!
        $this->setOmitMandatoryCheck(true);
        $this->setPassword( $input->getUnescaped('password') );
        $this->setResetHash(null);
        $this->save();

        if (!$this->isPublished())
        {
            $this->confirm();
        }

        return $input;
    }

    public function changePassword(array $data)
    {
        $argv = compact('data');
        $results = \Pimcore::getEventManager()->triggerUntil('members.password.change',
            $this, $argv, function ($v) {
                return ($v instanceof \Zend_Filter_Input);
            });

        $input = $results->last();

        if (!$input instanceof \Zend_Filter_Input)
        {
            throw new \Exception('No validate listener attached to "members.password.change" event');
        }

        if (!$input->isValid())
        {
            return $input;
        }

        //do not check mandatory fields because of conditional logic!
        $this->setOmitMandatoryCheck(true);
        $this->setPassword( $input->getUnescaped('password') );
        $this->save();

        return $input;
    }
}