# Installation
The sso connector is disabled by default for flexibility reasons.
But it's quite easy to enable it. Use our helper command, which guides you through the installation process:

## Helper Command
![oauth2 helper command](https://user-images.githubusercontent.com/700119/68659412-858a6080-0537-11ea-9b13-872e134939b1.png)

```bash
$ bin/console members:oauth:setup
```

If you don't want to use it, you need to check several steps which we're going to explain to you right now:

## Install Classes
You need an additional Class for the SSO Identity. Every provider (e.g. Google or Facebook) creates an `SsoIdentity` entity, which gets appended to a user object.
If you're using all the default Members Classes, you can simple re-run the command:

````bash
$ bin/console members:install:class -o
````

By adding the `-o` argument, this command will install the `SsoIdentity`. Already installed classes will be skipped.

If you want to use a different name, just create the class and import it from `src/MembersBundle/Resources/install/classes/class_SsoIdentity_export.json`.
Read more about changing the default class name [here](../20_CustomClassName.md).  

## Add SSO Identity Relation Field

**Important!** This step is only required if you're updating an existing installation! If you have installed Members from scratch via the class installer, this field is already available!

Add this to your `var/classes/definition_YOUR_USER_CLASS_NAME.php` (right after the `group` section):

```json
{
    "fieldtype": "manyToManyObjectRelation",
    "width": "",
    "height": "",
    "maxItems": "",
    "queryColumnType": "text",
    "phpdocType": "array",
    "relationType": true,
    "visibleFields": "key",
    "optimizedAdminLoading": false,
    "visibleFieldDefinitions": [],
    "lazyLoading": true,
    "classes": [
        {
            "classes": "SsoIdentity"
        }
    ],
    "pathFormatterClass": "",
    "name": "ssoIdentities",
    "title": "SSO Identities",
    "tooltip": "",
    "mandatory": false,
    "noteditable": false,
    "index": false,
    "locked": false,
    "style": "",
    "permissions": null,
    "datatype": "data",
    "invisible": false,
    "visibleGridView": false,
    "visibleSearch": false
}
```

## Add SSO IdentityAwareInterface
![image](https://user-images.githubusercontent.com/700119/68705556-90bcab00-058e-11ea-8c7f-483e7bee3c30.png)

You need to change the parent class of your existing user class to `\MembersBundle\Adapter\User\AbstractSsoAwareUser`.

## Install Dependencies
Install the [KnpUOAuth2ClientBundle](https://github.com/knpuniversity/oauth2-client-bundle):

```bash
$ composer require knpuniversity/oauth2-client-bundle:^1.0
```

You also need to add some providers. There is a [list of all available provider](https://github.com/knpuniversity/oauth2-client-bundle#step-1-download-the-client-library). 
In this example, we're going to install the google client:

```bash
$ composer require league/oauth2-google:^3.0
```

## Enable Feature
Read more about the `activation_type` [here](./11_IntegrationTypes.md).

```yaml
members:
    oauth:
        enabled: true
        activation_type: 'complete_profile' # choose between "complete_profile" and  "instant"
```

## Configure Client
Every provider comes with its own configuration. 
In this example, we're going to setup the google client:

**Attention:** Always use the `members_user_security_oauth_check` route in `redirect_route`.

> There is also a full list of all [configurations](https://github.com/knpuniversity/oauth2-client-bundle#configuration)

```yaml
knpu_oauth2_client:
    clients:
        google:
            type: google
            client_id: 'YOUR_CLIENT_ID'
            client_secret: 'YOUR_CLIENT_SECRET'
            redirect_route: members_user_security_oauth_check
            redirect_params: {}
```

## Configure Client Scope
If you need a special scope definition, you can add them in the Members configuration.
Just add your client (`google`in your example) to the `scopes` node. Value needs to be an `array`.

> If there is no configured scope, the oauth2 client will trigger `getDefaultScopes()` (see [documentation](https://github.com/thephpleague/oauth2-client/blob/master/docs/providers/implementing.md#implementing-a-provider). 
> Default scope values vary from client to client.

```yaml
members:
    oauth:
        scopes:
            google: ['email']
```