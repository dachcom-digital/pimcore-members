<?php

namespace MembersBundle;

final class MembersEvents
{
    /**
     * The RESTRICTION_CHECK_STATICROUTE event occurs when a frontend request is in a staticroute context.
     *
     * @Event("MembersBundle\Event\StaticRouteEvent")
     */
    public const RESTRICTION_CHECK_STATICROUTE = 'members.restriction.staticroute';

    /**
     * The ENTITY_CREATE_RESTRICTION event occurs after a restriction has been created
     *
     * @Event("MembersBundle\Event\RestrictionEvent")
     */
    public const ENTITY_CREATE_RESTRICTION = 'members.entity.restriction.create';

    /**
     * The ENTITY_UPDATE_RESTRICTION event occurs after a restriction has been updated
     *
     * @Event("MembersBundle\Event\RestrictionEvent")
     */
    public const ENTITY_UPDATE_RESTRICTION = 'members.entity.restriction.update';

    /**
     * The ENTITY_DELETE_RESTRICTION event occurs after a restriction has been deleted
     *
     * @Event("MembersBundle\Event\RestrictionEvent")
     */
    public const ENTITY_DELETE_RESTRICTION = 'members.entity.restriction.delete';

    /**
     * The CHANGE_PASSWORD_INITIALIZE event occurs when the change password process is initialized.
     *
     * This event allows you to modify the default values of the user before binding the form.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    public const CHANGE_PASSWORD_INITIALIZE = 'members.change_password.edit.initialize';

    /**
     * The CHANGE_PASSWORD_SUCCESS event occurs when the change password form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    public const CHANGE_PASSWORD_SUCCESS = 'members.change_password.edit.success';

    /**
     * The CHANGE_PASSWORD_COMPLETED event occurs after saving the user in the change password process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    public const CHANGE_PASSWORD_COMPLETED = 'members.change_password.edit.completed';

    /**
     * The PROFILE_EDIT_INITIALIZE event occurs when the profile editing process is initialized.
     *
     * This event allows you to modify the default values of the user before binding the form.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    public const PROFILE_EDIT_INITIALIZE = 'members.profile.edit.initialize';

    /**
     * The PROFILE_EDIT_SUCCESS event occurs when the profile edit form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    public const PROFILE_EDIT_SUCCESS = 'members.profile.edit.success';

    /**
     * The PROFILE_EDIT_COMPLETED event occurs after saving the user in the profile edit process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    public const PROFILE_EDIT_COMPLETED = 'members.profile.edit.completed';

    /**
     * The REGISTRATION_INITIALIZE event occurs when the registration process is initialized.
     *
     * This event allows you to modify the default values of the user before binding the form.
     *
     * @Event("MembersBundle\Event\UserEvent")
     */
    public const REGISTRATION_INITIALIZE = 'members.registration.initialize';

    /**
     * The REGISTRATION_SUCCESS event occurs when the registration form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    public const REGISTRATION_SUCCESS = 'members.registration.success';

    /**
     * The REGISTRATION_FAILURE event occurs when the registration form is not valid.
     *
     * This event allows you to set the response instead of using the default one.
     * The event listener method receives a MembersBundle\Event\FormEvent instance.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    public const REGISTRATION_FAILURE = 'members.registration.failure';

    /**
     * The REGISTRATION_COMPLETED event occurs after saving the user in the registration process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    public const REGISTRATION_COMPLETED = 'members.registration.completed';

    /**
     * The REGISTRATION_CONFIRM event occurs just before confirming the account.
     *
     * This event allows you to access the user which will be confirmed.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    public const REGISTRATION_CONFIRM = 'members.registration.confirm';

    /**
     * The REGISTRATION_CONFIRMED event occurs after confirming the account.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    public const REGISTRATION_CONFIRMED = 'members.registration.confirmed';

    /**
     * The RESETTING_RESET_REQUEST event occurs when a user requests a password reset of the account.
     *
     * This event allows you to check if a user is locked out before requesting a password.
     * The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    public const RESETTING_RESET_REQUEST = 'members.resetting.reset.request';

    /**
     * The RESETTING_RESET_INITIALIZE event occurs when the resetting process is initialized.
     *
     * This event allows you to set the response to bypass the processing.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    public const RESETTING_RESET_INITIALIZE = 'members.resetting.reset.initialize';

    /**
     * The RESETTING_RESET_SUCCESS event occurs when the resetting form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent ")
     */
    public const RESETTING_RESET_SUCCESS = 'members.resetting.reset.success';

    /**
     * The RESETTING_RESET_COMPLETED event occurs after saving the user in the resetting process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    public const RESETTING_RESET_COMPLETED = 'members.resetting.reset.completed';

    /**
     * The SECURITY_IMPLICIT_LOGIN event occurs when the user is logged in programmatically.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\UserEvent")
     */
    public const SECURITY_IMPLICIT_LOGIN = 'members.security.implicit_login';

    /**
     * The RESETTING_SEND_EMAIL_INITIALIZE event occurs when the send email process is initialized.
     *
     * This event allows you to set the response to bypass the email confirmation processing.
     * The event listener method receives a MembersBundle\Event\GetResponseNullableUserEvent instance.
     *
     * @Event("MembersBundle\Event\GetResponseNullableUserEvent")
     */
    public const RESETTING_SEND_EMAIL_INITIALIZE = 'members.resetting.send_email.initialize';

    /**
     * The RESETTING_SEND_EMAIL_CONFIRM event occurs when all prerequisites to send email are
     * confirmed and before the mail is sent.
     *
     * This event allows you to set the response to bypass the email sending.
     * The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    public const RESETTING_SEND_EMAIL_CONFIRM = 'members.resetting.send_email.confirm';

    /**
     * The RESETTING_SEND_EMAIL_COMPLETED event occurs after the email is sent.
     *
     * This event allows you to set the response to bypass the the redirection after the email is sent.
     * The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    public const RESETTING_SEND_EMAIL_COMPLETED = 'members.resetting.send_email.completed';

    /**
     * The DELETE_ACCOUNT_INITIALIZE event occurs when the delete account process is initialized.
     *
     * This event allows you to set the response to bypass the processing.
     *
     * @Event("MembersBundle\Event\GetResponseUserEvent")
     */
    public const DELETE_ACCOUNT_INITIALIZE = 'members.delete_account.confirm.initialize';

    /**
     * The DELETE_ACCOUNT_SUCCESS event occurs when the delete account form is submitted successfully.
     *
     * This event allows you to delete additional data objects from the system.
     *
     * @Event("MembersBundle\Event\FormEvent ")
     */
    public const DELETE_ACCOUNT_SUCCESS = 'members.delete_account.confirm.success';

    /**
     * The DELETE_ACCOUNT_COMPLETED event occurs after deleting the user object in the delete account process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    public const DELETE_ACCOUNT_COMPLETED = 'members.delete_account.confirm.completed';

    /**
     * The OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_SUCCESS event occurs when the complete-profile form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    public const OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_SUCCESS = 'members.oauth.sso_instance.complete_profile.success';

    /**
     * The OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_COMPLETED event occurs after saving the user in the complete-profile process.
     *
     * This event allows you to access the response which will be sent.
     *
     * @Event("MembersBundle\Event\FilterUserResponseEvent")
     */
    public const OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_COMPLETED = 'members.oauth.sso_instance.complete_profile.completed';

    /**
     * The OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_FAILURE event occurs when the complete-profile form is not valid.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("MembersBundle\Event\FormEvent")
     */
    public const OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_FAILURE = 'members.oauth.sso_instance.complete_profile.failure';

    /**
     * The OAUTH_PROFILE_CONNECTION_SUCCESS event occurs after a existing user has been successfully connected to a provider.
     *
     * This event allows you to access the oauth response.
     *
     * @Event("MembersBundle\Event\OAuth\OAuthResponseEvent")
     */
    public const OAUTH_PROFILE_CONNECTION_SUCCESS = 'members.oauth.connection.success';

    /**
     * The OAUTH_RESOURCE_MAPPING_PROFILE event occurs before a sso identity gets assigned to given user profile.
     *
     * This event allows you to map resource data (e.g. google) to your user identity.
     *
     * @Event("MembersBundle\Event\OAuth\OAuthResourceEvent")
     */
    public const OAUTH_RESOURCE_MAPPING_PROFILE = 'members.oauth.resource_mapping.profile';

    /**
     * The OAUTH_RESOURCE_MAPPING_REGISTRATION event occurs before the registration form gets rendered.
     *
     * This event allows you to map resource data (e.g. google) to your registration form.
     *
     * @Event("MembersBundle\Event\OAuth\OAuthResourceEvent")
     */
    public const OAUTH_RESOURCE_MAPPING_REGISTRATION = 'members.oauth.resource_mapping.registration';

    /**
     * The OAUTH_RESOURCE_MAPPING_REFRESH event occurs after an existing sso identity has been found.
     *
     * This event allows you to map resource data (e.g. google) to your existing user identity.
     *
     * @Event("MembersBundle\Event\OAuth\OAuthResourceRefreshEvent")
     */
    public const OAUTH_RESOURCE_MAPPING_REFRESH = 'members.oauth.resource_mapping.refresh';

    /**
     * The OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION event occurs before a user enters the profile completion step.
     *
     * This event allows you to overrule the termination if a user is able to complete is profile or not
     *
     * @Event("MembersBundle\Event\OAuth\OAuthIdentityEvent")
     */
    public const OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION = 'members.oauth.identity_status.profile_completion';

    /**
     * The OAUTH_IDENTITY_STATUS_DELETION event occurs before a identity gets deleted.
     *
     * This event allows you to overrule the termination if a identity can be deleted (after sso identity has been deleted).
     *
     * @Event("MembersBundle\Event\OAuth\OAuthIdentityEvent")
     */
    public const OAUTH_IDENTITY_STATUS_DELETION = 'members.oauth.identity_status.deletion';
}
