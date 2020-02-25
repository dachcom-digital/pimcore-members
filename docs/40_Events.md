# Events

### members.restriction.staticroute

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:RESTRICTION_CHECK_STATICROUTE` |
| **name** | `members.restriction.staticroute` |
| **class** | `\MembersBundle\Event\StaticRouteEvent` |
| **description** | The RESTRICTION_CHECK_STATICROUTE event occurs when a frontend request is in a staticroute context |

***

### members.change_password.edit.initialize

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:CHANGE_PASSWORD_INITIALIZE` |
| **name** | `members.change_password.edit.initialize` |
| **class** | `\MembersBundle\Event\GetResponseUserEvent` |
| **description** | The CHANGE_PASSWORD_INITIALIZE event occurs when the change password process is initialized. This event allows you to modify the default values of the user before binding the form. |

### members.change_password.edit.success

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:CHANGE_PASSWORD_SUCCESS` |
| **name** | `members.change_password.edit.success` |
| **class** | `\MembersBundle\Event\FormEvent` |
| **description** | The CHANGE_PASSWORD_SUCCESS event occurs when the change password form is submitted successfully. This event allows you to set the response instead of using the default one. |

### members.change_password.edit.completed

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:CHANGE_PASSWORD_COMPLETED` |
| **name** | `members.change_password.edit.completed` |
| **class** | `\MembersBundle\Event\FilterUserResponseEvent` |
| **description** | The CHANGE_PASSWORD_COMPLETED event occurs after saving the user in the change password process. This event allows you to access the response which will be sent. |

***

### members.profile.edit.initialize

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:PROFILE_EDIT_INITIALIZE` |
| **name** | `members.change_password.edit.initialize` |
| **class** | `\MembersBundle\Event\GetResponseUserEvent` |
| **description** | The PROFILE_EDIT_INITIALIZE event occurs when the profile editing process is initialized. This event allows you to modify the default values of the user before binding the form. |

### members.profile.edit.success

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:PROFILE_EDIT_SUCCESS` |
| **name** | `members.change_password.edit.success` |
| **class** | `\MembersBundle\Event\FormEvent` |
| **description** | The PROFILE_EDIT_SUCCESS event occurs when the profile edit form is submitted successfully. This event allows you to set the response instead of using the default one. |

### members.profile.edit.completed

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:PROFILE_EDIT_COMPLETED` |
| **name** | `members.profile.edit.completed` |
| **class** | `\MembersBundle\Event\FilterUserResponseEvent` |
| **description** | The PROFILE_EDIT_COMPLETED event occurs after saving the user in the profile edit process. This event allows you to access the response which will be sent. |

***

### members.registration.initialize

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:REGISTRATION_INITIALIZE` |
| **name** | `members.registration.initialize` |
| **class** | `\MembersBundle\Event\UserEvent` |
| **description** | The REGISTRATION_INITIALIZE event occurs when the registration process is initialized. This event allows you to modify the default values of the user before binding the form. |

### members.registration.success

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:REGISTRATION_SUCCESS` |
| **name** | `members.registration.success` |
| **class** | `\MembersBundle\Event\FormEvent` |
| **description** | The REGISTRATION_SUCCESS event occurs when the registration form is submitted successfully. This event allows you to set the response instead of using the default one. |

### members.registration.failure

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:REGISTRATION_FAILURE` |
| **name** | `members.registration.failure` |
| **class** | `\MembersBundle\Event\FormEvent` |
| **description** | The REGISTRATION_FAILURE event occurs when the registration form is not valid. This event allows you to set the response instead of using the default one. The event listener method receives a MembersBundle\Event\FormEvent instance. |

### members.registration.completed

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:REGISTRATION_COMPLETED` |
| **name** | `members.registration.completed` |
| **class** | `\MembersBundle\Event\FilterUserResponseEvent` |
| **description** | The REGISTRATION_COMPLETED event occurs after saving the user in the registration process. This event allows you to access the response which will be sent. |

### members.registration.confirm

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:REGISTRATION_CONFIRM` |
| **name** | `members.registration.confirm` |
| **class** | `\MembersBundle\Event\GetResponseUserEvent` |
| **description** | The REGISTRATION_CONFIRM event occurs just before confirming the account. This event allows you to access the user which will be confirmed. |

### members.registration.confirmed

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:REGISTRATION_CONFIRMED` |
| **name** | `members.registration.confirmed` |
| **class** | `\MembersBundle\Event\FilterUserResponseEvent` |
| **description** | The REGISTRATION_CONFIRMED event occurs after confirming the account. This event allows you to access the response which will be sent. |
 
***

### members.resetting.reset.request

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:RESETTING_RESET_REQUEST` |
| **name** | `members.resetting.reset.request` |
| **class** | `\MembersBundle\Event\GetResponseUserEvent` |
| **description** | The RESETTING_RESET_REQUEST event occurs when a user requests a password reset of the account. This event allows you to check if a user is locked out before requesting a password. The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance. |

### members.resetting.reset.initialize

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:RESETTING_RESET_INITIALIZE` |
| **name** | `members.resetting.reset.initialize` |
| **class** | `\MembersBundle\Event\GetResponseUserEvent` |
| **description** | The RESETTING_RESET_INITIALIZE event occurs when the resetting process is initialized. This event allows you to set the response to bypass the processing. |

### members.resetting.reset.success

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:RESETTING_RESET_SUCCESS` |
| **name** | `members.resetting.reset.success` |
| **class** | `\MembersBundle\Event\FormEvent` |
| **description** | The RESETTING_RESET_SUCCESS event occurs when the resetting form is submitted successfully. This event allows you to set the response instead of using the default one. |

### members.resetting.reset.completed

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:RESETTING_RESET_COMPLETED` |
| **name** | `members.resetting.reset.completed` |
| **class** | `\MembersBundle\Event\FilterUserResponseEvent` |
| **description** | The RESETTING_RESET_COMPLETED event occurs after saving the user in the resetting process. This event allows you to access the response which will be sent. |

### members.resetting.send_email.initialize

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:RESETTING_SEND_EMAIL_INITIALIZE` |
| **name** | `members.resetting.send_email.initialize` |
| **class** | `\MembersBundle\Event\GetResponseNullableUserEvent` |
| **description** | The RESETTING_SEND_EMAIL_INITIALIZE event occurs when the send email process is initialized. This event allows you to set the response to bypass the email confirmation processing. The event listener method receives a MembersBundle\Event\GetResponseNullableUserEvent instance. |

### members.resetting.send_email.confirm

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:RESETTING_SEND_EMAIL_CONFIRM` |
| **name** | `members.resetting.send_email.confirm` |
| **class** | `\MembersBundle\Event\GetResponseUserEvent` |
| **description** | The RESETTING_SEND_EMAIL_CONFIRM event occurs when all prerequisites to send email are confirmed and before the mail is sent. This event allows you to set the response to bypass the email sending. The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance. |

### members.resetting.send_email.completed

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:RESETTING_SEND_EMAIL_COMPLETED` |
| **name** | `members.resetting.send_email.completed` |
| **class** | `\MembersBundle\Event\GetResponseUserEvent` |
| **description** | The RESETTING_SEND_EMAIL_COMPLETED event occurs after the email is sent. This event allows you to set the response to bypass the the redirection after the email is sent. The event listener method receives a MembersBundle\Event\GetResponseUserEvent instance. |

***

### members.security.implicit_login

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:SECURITY_IMPLICIT_LOGIN` |
| **name** | `members.security.implicit_login` |
| **class** | `\MembersBundle\Event\UserEvent` |
| **description** | The SECURITY_IMPLICIT_LOGIN event occurs when the user is logged in programmatically. This event allows you to access the response which will be sent. |

***

### members.delete_account.confirm.initialize

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:DELETE_ACCOUNT_INITIALIZE` |
| **name** | `members.delete_account.confirm.initialize` |
| **class** | `\MembersBundle\Event\GetResponseUserEvent` |
| **description** | The DELETE_ACCOUNT_INITIALIZE event occurs when the delete account process is initialized. This event allows you to set the response to bypass the processing. |

### members.delete_account.confirm.success

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:DELETE_ACCOUNT_SUCCESS` |
| **name** | `members.delete_account.confirm.success` |
| **class** | `\MembersBundle\Event\FormEvent` |
| **description** | The DELETE_ACCOUNT_SUCCESS event occurs when the delete account form is submitted successfully. This event allows you to delete additional data objects from the system. |

### members.delete_account.confirm.completed

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:DELETE_ACCOUNT_COMPLETED` |
| **name** | `members.delete_account.confirm.completed` |
| **class** | `\MembersBundle\Event\FilterUserResponseEvent` |
| **description** | The DELETE_ACCOUNT_COMPLETED event occurs after deleting the user object in the delete account process. This event allows you to access the response which will be sent. |

***

### members.oauth.sso_instance.complete_profile.success

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_SUCCESS` |
| **name** | `members.oauth.sso_instance.complete_profile.success` |
| **class** | `\MembersBundle\Event\FormEvent` |
| **description** | The OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_SUCCESS event occurs when the complete-profile form is submitted successfully. This event allows you to set the response instead of using the default one. |

### members.oauth.sso_instance.complete_profile.completed

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_COMPLETED` |
| **name** | `members.oauth.sso_instance.complete_profile.completed` |
| **class** | `\MembersBundle\Event\FilterUserResponseEvent` |
| **description** | The OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_COMPLETED event occurs after saving the user in the complete-profile process. This event allows you to access the response which will be sent. |

### members.oauth.sso_instance.complete_profile.failure

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_FAILURE` |
| **name** | `members.oauth.sso_instance.complete_profile.failure` |
| **class** | `\MembersBundle\Event\FormEvent` |
| **description** | The OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_FAILURE event occurs when the complete-profile form is not valid. This event allows you to set the response instead of using the default one. |

***

### members.oauth.connection.success

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:OAUTH_PROFILE_CONNECTION_SUCCESS` |
| **name** | `members.oauth.connection.success` |
| **class** | `\MembersBundle\Event\OAuth\OAuthResponseEvent` |
| **description** | The OAUTH_PROFILE_CONNECTION_SUCCESS event occurs after a existing user has been successfully connected to a provider. This event allows you to access the oauth response. |

### members.oauth.resource_mapping.profile

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:OAUTH_RESOURCE_MAPPING_PROFILE` |
| **name** | `members.oauth.connection.success` |
| **class** | `\MembersBundle\Event\OAuth\OAuthResourceEvent` |
| **description** | The OAUTH_RESOURCE_MAPPING_PROFILE event occurs before a sso identity gets assigned to given user profile. This event allows you to map resource data (e.g. google) to your user identity. |

### members.oauth.resource_mapping.registration

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:OAUTH_RESOURCE_MAPPING_REGISTRATION` |
| **name** | `members.oauth.connection.success` |
| **class** | `\MembersBundle\Event\OAuth\OAuthResourceEvent` |
| **description** | The OAUTH_RESOURCE_MAPPING_REGISTRATION event occurs before the registration form gets rendered. This event allows you to map resource data (e.g. google) to your registration form. |

***

### members.oauth.identity_status.profile_completion

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION` |
| **name** | `members.oauth.identity_status.profile_completion` |
| **class** | `\MembersBundle\Event\OAuth\OAuthIdentityEvent` |
| **description** | The OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION event occurs before a user enters the profile completion step. This event allows you to overrule the termination if a user is able to complete is profile or not. |

### members.oauth.identity_status.deletion

| Type | Reference |
|:--- |:--- |
| **const** | `\MembersEvent:OAUTH_IDENTITY_STATUS_DELETION` |
| **name** | `members.oauth.identity_status.deletion` |
| **class** | `\MembersBundle\Event\OAuth\OAuthIdentityEvent` |
| **description** | The OAUTH_IDENTITY_STATUS_DELETION event occurs before a identity gets deleted. This event allows you to overrule the termination if a identity can be deleted (after sso identity has been deleted). |
