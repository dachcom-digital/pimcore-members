# Pimcore Members Bundle
Add frontend user authentication and document restriction to pimcore.

[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Software License](https://img.shields.io/badge/license-DCL-white.svg?style=flat-square&color=%23ff5c5c)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/members.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/members)
[![Tests](https://img.shields.io/github/actions/workflow/status/dachcom-digital/pimcore-members/.github/workflows/codeception.yml?branch=master&style=flat-square&logo=github&label=codeception)](https://github.com/dachcom-digital/pimcore-members/actions?query=workflow%3ACodeception+branch%3Amaster)
[![PhpStan](https://img.shields.io/github/actions/workflow/status/dachcom-digital/pimcore-members/.github/workflows/php-stan.yml?branch=master&style=flat-square&logo=github&label=phpstan%20level%204)](https://github.com/dachcom-digital/pimcore-members/actions?query=workflow%3A"PHP+Stan"+branch%3Amaster)

### Release Plan

| Release | Supported Pimcore Versions        | Supported Symfony Versions | Release Date | Maintained     | Branch   |
|---------|-----------------------------------|----------------------------|--------------|----------------|----------|
| **5.x** | `11.0`                            | `6.2`                      | 28.09.2023   | Feature Branch | master   |
| **4.x** | `10.5 - 10.6`                     | `5.4`                      | 22.11.2021   | Unsupported    | 4.x      |
| **3.x** | `6.0` - `6.8`                     | `3.4`, `^4.4`              | 21.07.2019   | Unsupported    | 3.x      |
| **2.5** | `5.4`, `5.5`, `5.6`, `5.7`, `5.8` | `3.4`                      | 18.07.2019   | Unsupported    | 2.5      |
| **1.5** | `4.0`                             | --                         | 07.07.2017   | Unsupported    | pimcore4 |

## Features
* Create Members in backend
* Allow Members to register in frontend
* Restrict documents, objects and assets to specific user roles

***

## Installation
Please read the installation instructions before going deep with Members!

### Composer Installation
1. Add code below to your `composer.json`    

```json
"require" : {
    "dachcom-digital/members" : "~5.1.0"
}
```

Add Bundle to `bundles.php`:
```php
return [
    MembersBundle\MembersBundle::class => ['all' => true],
];
```

- Execute: `$ bin/console pimcore:bundle:install MembersBundle`

## Upgrading
- Execute: `$ bin/console doctrine:migrations:migrate --prefix 'MembersBundle\Migrations'`

#### Optional: Class Installation
> Read more about the required classes [below](./README.md#class-installation)).

```bash
bin/console members:install:class
```

### Security Installation
It is not possible to merge security configurations from multiple locations, including bundles. Instead, you have to move them to
one single config file, e.g. `config/packages/security.yaml`. Please adopt [security_auth_manager.yaml](./config/packages/security_auth_manager.yaml)
and merge your own firewall configuration into one single file.

### Route Installation
MembersBundle does not include any routes per default. Otherwise, it would be hard for you to change or override included routes. 

**Include all Routes**
```yaml
# config/routes.yaml
app:
    resource: '@MembersBundle/config/pimcore/routing/all.yaml'
```

**Just include some Routes**
```yaml
# config/routes.yaml
members_auth:
    resource: '@MembersBundle/config/pimcore/routing/auth.yaml'
    prefix: /{_locale}/members #change your prefix if you have to.
```

### Class Installation
Since Members should be the one and only frontend authentication bundle, we need to add the most flexibility as possible.
But no worries, it's still simple to integrate.

> There is also a class installer command. If you're not using any special class configuration, feel free to use this command: `$ bin/console members:install:class`
> Use the `-o` argument to also install the SsoIdentity Class 

You need two classes: User and Group. So let's create it:

*User*  
1. Create a class and call it `MembersUser`
2. Add parent class: `\MembersBundle\Adapter\User\AbstractUser`
3. Add fields:

| Name                | Field Type  | Comment                                                                                                         |
|---------------------|-------------|-----------------------------------------------------------------------------------------------------------------|
| userName            | Input       |                                                                                                                 |
| email               | Input       | **Note:** Do not add this field if you're using the [CMF](docs/20_ClassCustomization.md).                       |
| confirmationToken   | Input       | must set to it read only                                                                                        |
| lastLogin           | Date & Time | must set to it read only                                                                                        |
| password            | Password    | Hide it, if you want. **Note:** Do not add this field if you're using the [CMF](docs/20_ClassCustomization.md). |
| passwordRequestedAt | Date & Time | must set to it read only                                                                                        |
| groups              | User Group  | This field comes with Members                                                                                   |

> `membersUser` is the default name, you may want to change it. Read [here](docs/20_ClassCustomization.md) how to achieve that.

#### Customer Data Framework
If you want to use the [Customer Data Framework](https://github.com/pimcore/customer-data-framework) you need to do some further work. Read more about it [here](docs/300_CustomerDataFw.md).

#### SSO Login
You want to enable the SSO Feature in Members? Read more about it [here](./docs/SSO/20_Installation.md).

*Group*  
1. Create a class and call it `MembersGroup`
2. Add parent class: `\MembersBundle\Adapter\Group\AbstractGroup`
3. Add fields:

| Name  | Field Type     | Comment                                                                                                     |
|-------|----------------|-------------------------------------------------------------------------------------------------------------|
| name  | Input          |                                                                                                             |
| roles | Multiselection | Set "Options Provider Class or Service Name" to `@MembersBundle\CoreExtension\Provider\RoleOptionsProvider` |

> `membersGroup` is the default name, you may want to change it. Read [here](docs/20_ClassCustomization.md) how to achieve that.

Feel free to add additional fields since those are just the required ones. That's it. Members will use those classes to manage authentication and group management.

### Email Configuration
You're almost there, just check the [email configuration](docs/70_EmailConfiguration.md) and you're good to go.

***

### User Management: Further Information
- [Auth Identifier](docs/10_AuthIdentifier.md) Use `email` instead of `username` for authentication
- [Custom Class Names](docs/20_ClassCustomization.md)
- [Frontend Routes & Views](docs/30_FrontendRoutes.md)
- [Available Events](docs/40_Events.md)
- [Custom Form Types](docs/50_CustomFormTypes.md)
- [Registration Types](docs/60_RegistrationTypes.md)
- [Email Configuration](docs/70_EmailConfiguration.md)
- [Groups](docs/80_Groups.md)
- [Roles](docs/90_Roles.md)
- [Use the Pimcore Customer Framework with Members](docs/300_CustomerDataFw.md)

***

## Restrictions
Learn more about the Members Restriction feature:

- [Brief Overview](docs/200_Restrictions.md)
- [Restricted Navigation](docs/210_RestrictedNavigation.md)
- [Restricted Routing](docs/220_RestrictedRouting.md)
- [Restricted Listing](docs/230_RestrictListing.md)
- [Protected Asset Downloader](docs/240_AssetProtection.md)

***

## Single Sign On (SSO) with OAuth2
- [Overview](./docs/SSO/10_Overview.md)
- [Integration Types](./docs/SSO/11_IntegrationTypes.md)
- [Resource Mapping](./docs/SSO/12_ResourceMapping.md)
- [Installation](./docs/SSO/20_Installation.md)
- [Twig Extensions](./docs/SSO/30_TwigExtensions.md)

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)

## License
**DACHCOM.DIGITAL AG**, Löwenhofstrasse 15, 9424 Rheineck, Schweiz  
[dachcom.com](https://www.dachcom.com), dcdi@dachcom.ch  
Copyright © 2024 DACHCOM.DIGITAL. All rights reserved.  

For licensing details please visit [LICENSE.md](LICENSE.md)  
