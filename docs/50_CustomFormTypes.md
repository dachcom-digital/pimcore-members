# Custom Form Types

The default form types only have some few elements. Within a registration you may want to know some more information about the user like first name, street etc.

## Create a Custom Form Type

Just create your own form type and override the default value in your `config/config.yaml`:

```yaml
members:
    relations:
        login:
            form:
                type:               MembersBundle\Form\Type\LoginFormType
                name:               members_user_login_form
                validation_groups:  [Login, Default]
        profile:
            form:
                type:               MembersBundle\Form\Type\ProfileFormType
                name:               members_user_profile_form
                validation_groups:  [Profile, Default]
        change_password:
            form:
                type:               MembersBundle\Form\Type\ChangePasswordFormType
                name:               members_user_change_password_form
                validation_groups:  [ChangePassword, Default]
        registration:
            form:
                type:               App\Form\Type\RegistrationFormType
                name:               members_user_registration_form
                validation_groups:  [Registration, Default]
        resetting_request:
            form:
                type:               MembersBundle\Form\Type\ResettingRequestFormType
                name:               members_user_resetting_request_form
                validation_groups:  [ResetPassword, Default]
        resetting:
            token_ttl: 86400
            form:
                type:               MembersBundle\Form\Type\ResettingFormType
                name:               members_user_resetting_form
                validation_groups:  [ResetPassword, Default]
        delete_account:
            form:
                type:               MembersBundle\Form\Type\DeleteAccountFormType
                name:               members_user_delete_account_form
                validation_groups:  [DeleteAccount, Default]

```