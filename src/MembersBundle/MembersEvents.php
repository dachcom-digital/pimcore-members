<?php

namespace MembersBundle;

final class MembersEvents
{
    /**
     * The RESTRICTION_CHECK_STATICROUTE event occurs when a frontend request is in a staticroute context.
     *
     * @Event("MembersBundle\Event\StaticRouteEvent")
     */
    const RESTRICTION_CHECK_STATICROUTE = 'members.restriction.staticroute';

    /**
     * The CHANGE_PASSWORD_INITIALIZE event occurs when the change password process is initialized.
     *
     * This event allows you to modify the default values of the user before binding the form.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    const CHANGE_PASSWORD_INITIALIZE = 'members.change_password.edit.initialize';

    /**
     * The CHANGE_PASSWORD_SUCCESS event occurs when the change password form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    const CHANGE_PASSWORD_SUCCESS = 'members.change_password.edit.success';

    /**
     * The CHANGE_PASSWORD_COMPLETED event occurs after saving the user in the change password process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    const CHANGE_PASSWORD_COMPLETED = 'members.change_password.edit.completed';

    /**
     * The PROFILE_EDIT_INITIALIZE event occurs when the profile editing process is initialized.
     *
     * This event allows you to modify the default values of the user before binding the form.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    const PROFILE_EDIT_INITIALIZE = 'members.profile.edit.initialize';

    /**
     * The PROFILE_EDIT_SUCCESS event occurs when the profile edit form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    const PROFILE_EDIT_SUCCESS = 'members.profile.edit.success';

    /**
     * The PROFILE_EDIT_COMPLETED event occurs after saving the user in the profile edit process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    const PROFILE_EDIT_COMPLETED = 'members.profile.edit.completed';

    /**
     * The REGISTRATION_INITIALIZE event occurs when the registration process is initialized.
     *
     * This event allows you to modify the default values of the user before binding the form.
     *
     * @Event("MembersBundle\Event\UserEvent")
     */
    const REGISTRATION_INITIALIZE = 'members.registration.initialize';

    /**
     * The REGISTRATION_SUCCESS event occurs when the registration form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    const REGISTRATION_SUCCESS = 'members.registration.success';

    /**
     * The REGISTRATION_FAILURE event occurs when the registration form is not valid.
     *
     * This event allows you to set the response instead of using the default one.
     * The event listener method receives a MembersBundle\Event\FormEvent instance.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    const REGISTRATION_FAILURE = 'members.registration.failure';

    /**
     * The REGISTRATION_COMPLETED event occurs after saving the user in the registration process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    const REGISTRATION_COMPLETED = 'members.registration.completed';

    /**
     * The REGISTRATION_CONFIRM event occurs just before confirming the account.
     *
     * This event allows you to access the user which will be confirmed.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    const REGISTRATION_CONFIRM = 'members.registration.confirm';

    /**
     * The REGISTRATION_CONFIRMED event occurs after confirming the account.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    const REGISTRATION_CONFIRMED = 'members.registration.confirmed';

    /**
     * The RESETTING_RESET_REQUEST event occurs when a user requests a password reset of the account.
     *
     * This event allows you to check if a user is locked out before requesting a password.
     * The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    const RESETTING_RESET_REQUEST = 'members.resetting.reset.request';

    /**
     * The RESETTING_RESET_INITIALIZE event occurs when the resetting process is initialized.
     *
     * This event allows you to set the response to bypass the processing.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    const RESETTING_RESET_INITIALIZE = 'members.resetting.reset.initialize';

    /**
     * The RESETTING_RESET_SUCCESS event occurs when the resetting form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent ")
     */
    const RESETTING_RESET_SUCCESS = 'members.resetting.reset.success';

    /**
     * The RESETTING_RESET_COMPLETED event occurs after saving the user in the resetting process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    const RESETTING_RESET_COMPLETED = 'members.resetting.reset.completed';

    /**
     * The SECURITY_IMPLICIT_LOGIN event occurs when the user is logged in programmatically.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\UserEvent")
     */
    const SECURITY_IMPLICIT_LOGIN = 'members.security.implicit_login';

    /**
     * The RESETTING_SEND_EMAIL_INITIALIZE event occurs when the send email process is initialized.
     *
     * This event allows you to set the response to bypass the email confirmation processing.
     * The event listener method receives a MembersBundle\Event\GetResponseNullableUserEvent instance.
     *
     * @Event("MembersBundle\Event\GetResponseNullableUserEvent")
     */
    const RESETTING_SEND_EMAIL_INITIALIZE = 'members.resetting.send_email.initialize';

    /**
     * The RESETTING_SEND_EMAIL_CONFIRM event occurs when all prerequisites to send email are
     * confirmed and before the mail is sent.
     *
     * This event allows you to set the response to bypass the email sending.
     * The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    const RESETTING_SEND_EMAIL_CONFIRM = 'members.resetting.send_email.confirm';

    /**
     * The RESETTING_SEND_EMAIL_COMPLETED event occurs after the email is sent.
     *
     * This event allows you to set the response to bypass the the redirection after the email is sent.
     * The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    const RESETTING_SEND_EMAIL_COMPLETED = 'members.resetting.send_email.completed';

    /**
     * The DELETE_ACCOUNT_INITIALIZE event occurs when the delete account process is initialized.
     *
     * This event allows you to set the response to bypass the processing.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    const DELETE_ACCOUNT_INITIALIZE = 'members.delete_account.confirm.initialize';

    /**
     * The DELETE_ACCOUNT_SUCCESS event occurs when the delete account form is submitted successfully.
     *
     * This event allows you to delete additional data objects from the system.
     *
     * @Event("MembersBundle\Event\FormEvent ")
     */
    const DELETE_ACCOUNT_SUCCESS = 'members.delete_account.confirm.success';

    /**
     * The DELETE_ACCOUNT_COMPLETED event occurs after deleting the user object in the delete account process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    const DELETE_ACCOUNT_COMPLETED = 'members.delete_account.confirm.completed';

    /**
     * The OAUTH_PROFILE_CONNECTION_SUCCESS event occurs after a existing user has been successfully connected to a provider.
     *
     * This event allows you to access the oauth response.
     *
     * @Event("MembersBundle\Event\OAuth\OAuthResponseEvent")
     */
    const OAUTH_PROFILE_CONNECTION_SUCCESS = 'members.oauth.connection.success';

    /**
     * The OAUTH_RESOURCE_MAPPING_PROFILE event occurs before a sso identity gets assigned to given user profile.
     *
     * This event allows you to map resource data (e.g. google) to your user identity.
     *
     * @Event("MembersBundle\Event\OAuth\OAuthResourceEvent")
     */
    const OAUTH_RESOURCE_MAPPING_PROFILE = 'members.oauth.resource_mapping.profile';

    /**
     * The OAUTH_RESOURCE_MAPPING_REGISTRATION event occurs before the registration form gets rendered.
     *
     * This event allows you to map resource data (e.g. google) to your registration form.
     *
     * @Event("MembersBundle\Event\OAuth\OAuthResourceEvent")
     */
    const OAUTH_RESOURCE_MAPPING_REGISTRATION = 'members.oauth.resource_mapping.registration';
}
