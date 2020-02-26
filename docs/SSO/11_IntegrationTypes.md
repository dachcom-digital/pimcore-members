# Integration Types
There are two basic integration types:

## I. Login
Triggers, if a new user want's to connect. That said, there are two ways to complete users registration:

### I.I Instant Login
A successfully connected users gets logged in instantly. 

```yaml
members:
    oauth:
        enabled: true
        activation_type: 'instant'
```

#### Delayed Profile Completion
![image](https://user-images.githubusercontent.com/700119/75279916-c2d7dd00-580c-11ea-9f19-5d326a1d69b0.png)

It's possible to complete this "unknown" users profile in later step which uses a dedicated form type (`Form/Type/Sso/CompleteProfileFormType.php`).
By default, this completion is possible, as long the instant created SSO identity has no given password.
If you want to change that, use the [status event](./32_IdentityStatusListener.md#OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION).

### I.II Login via Profile Completion
After the request was successful, the user gets redirected to the registration form. 
There is no need to enter a password. Some fields may already be pre-filled (Read more about it [here](./12_ResourceMapping.md).

> **Attention**: The `validation_group` of `RegistrationFormType` changes to `SSO` while registration is in `complete_profile` mode.

```yaml
members:
    oauth:
        enabled: true
        activation_type: 'complete_profile'
```

***

## II. Connect
Triggers, if an existing user want's to connect a profile with a given provider (e.g. Google).
A list of all possible connectors are available in user's profile (There are some [twig extensions](./30_TwigExtensions.md)).