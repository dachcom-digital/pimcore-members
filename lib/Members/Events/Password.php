<?php

namespace Members\Events;

class Password
{
    /**
     * Default password reset validation.
     *
     * You can provide your own validation by attaching callback to
     * 'member.password.reset' event.
     *
     * @param \Zend_EventManager_Event $event
     * @return \Zend_Filter_Input
     */
    public static function reset(\Zend_EventManager_Event $event)
    {
        $data = $event->getParam('data');

        $input = new \Zend_Filter_Input([
            '*' => ['StringTrim', 'StripTags']
        ], [
            'password' => [
                new \Zend_Validate_StringLength(6),
                'PasswordStrength',
                'presence' => 'required',
            ],
            'password_confirm' => [
                new \Zend_Validate_Callback(function ($v) use ($data) {
                    return $v === $data['password'];
                }),
                'presence' => 'required',
                'messages' => 'Password do not match'
            ],
        ], $data, [
            \Zend_Filter_Input::VALIDATOR_NAMESPACE => 'Members_Validate',
        ]);

        return $input;
    }
}