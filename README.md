# Pimcore Members
Add frontend user authentication and document restriction to pimcore.

[![Join the chat at https://gitter.im/pimcore/pimcore](https://img.shields.io/gitter/room/pimcore/pimcore.svg?style=flat-square)](https://gitter.im/pimcore/pimcore)
[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/members.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/members)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/dachcom-digital/pimcore-members.svg?style=flat-square)](https://www.scrutinizer-ci.com/g/dachcom-digital/pimcore-members/)
[![Travis](https://img.shields.io/travis/com/dachcom-digital/pimcore-members/master.svg?style=flat-square)](https://travis-ci.com/dachcom-digital/pimcore-members)
[![PhpStan](https://img.shields.io/badge/PHPStan-level%202-brightgreen.svg?style=flat-square)](#)

### Release Plan

| Release | Supported Pimcore Versions        | Supported Symfony Versions | Release Date | Maintained     | Branch     |
|---------|-----------------------------------|----------------------------|--------------|----------------|------------|
| **3.0** | `6.0`                             | `3.4`, `^4.0`              | 21.07.2019   | Feature Branch | dev-master |
| **2.5** | `5.4`, `5.5`, `5.6`, `5.7`, `5.8` | `3.4`                      | 18.07.2019   | Bugfix only    | 2.5        |
| **1.5** | `4.0`                             | --                         | 07.07.2017   | Unsupported    | pimcore4   |

## Features
* Create Members in Backend
* Allow Members to register in frontend
* Restrict Documents, Objects and Assets to specific User Roles

* * *

## Installation
Please read the installation instructions before going deep with Members!

### Composer Installation
1. Add code below to your `composer.json`    

```json
"require" : {
    "dachcom-digital/members" : "~3.0.0"
}
```

### Installation via Extension Manager
After you have installed the Members Bundle via composer, open pimcore backend and go to `Tools` => `Extension`:
- Click the green `+` Button in `Enable / Disable` row
- Click the green `+` Button in `Install/Uninstall` row

### Installation via CommandLine
After you have installed the Members Bundle via composer:
- Execute: `$ bin/console pimcore:bundle:enable MembersBundle`
- Execute: `$ bin/console pimcore:bundle:install MembersBundle`

#### Optional: Class Installation

> Read more about the required classes [below](./README.md#class-installation)).

```bash
bin/console members:install:class
```

### Route Installation
Members does not include any routes per default. Otherwise it would be hard for you to change or override included routes. 

**Include all Routes**
```yaml
# app/config/routing.yml
app:
    resource: '@MembersBundle/Resources/config/pimcore/routing/all.yml'
```

**Just include some Routes**
```yaml
# app/config/routing.yml
members_auth:
    resource: '@MembersBundle/Resources/config/pimcore/routing/auth.yml'
    prefix: /{_locale}/members #change your prefix if you have to.
```

### Class Installation
Unlike members1, this bundle does not install any classes for you any more.
Since Members should be the one and only frontend authentication Bundle, we need to add the most flexibility as possible.
But no worries, it's still simple to integrate.

> There is also a class installer command. If your not using any special class configuration, feel free to use this command: `$ bin/console members:install:class`

You need two classes: User and Group. So let's create it:

*User*  
1. Create a class and call it `MembersUser`
2. Add parent class: `\MembersBundle\Adapter\User\AbstractUser`
3. Add fields:

| Name | Field Type | Comment |
|---------------------|-------------|-------------------------------|
| userName | Input |  |
| email | Input |  **Note:** Do not add this field if you're using the [CMF](docs/20_CustomClassName.md). |
| confirmationToken | Input | must set to it read only |
| lastLogin | Date & Time | must set to it read only |
| password | Password | Hide it, if you want. **Note:** Do not add this field if you're using the [CMF](docs/20_CustomClassName.md). |
| passwordRequestedAt | Date & Time | must set to it read only |
| groups | User Group | This field comes with Members |

> `membersUser` is the default name, you may want to change it. Read [here](docs/20_CustomClassName.md) how to achieve that.

#### Customer Data Framework
If you want to use the [Customer Data Framework](https://github.com/pimcore/customer-data-framework) you need to do some further work. Read more about it [here](docs/300_CustomerDataFw.md).

*Group*  
1. Create a class and call it `MembersGroup`
2. Add parent class: `\MembersBundle\Adapter\Group\AbstractGroup`
3. Add fields:

| Name | Field Type | Comment |
|---------------------|-------------|-------------------------------|
| name | Input |  |
| roles | Multiselection | Set "Options Provider Class or Service Name" to `@MembersBundle\CoreExtension\Provider\RoleOptionsProvider` |

> `membersGroup` is the default name, you may want to change it. Read [here](docs/20_CustomClassName.md) how to achieve that.

Feel free to add additional fields since those are just the required ones. That's it. Members will use those classes to manage authentication and group management.

### Email Configuration
You're almost there, just check the [email configuration](docs/70_EmailConfiguration.md) and you're good to go.

* * *

### User Management: Further Information
- [Custom Class Names](docs/20_CustomClassName.md)
- [Frontend Routes & Views](docs/30_FrontendRoutes.md)
- [Available Events](docs/40_Events.md)
- [Custom Form Types](docs/50_CustomFormTypes.md)
- [Registration Types](docs/60_RegistrationTypes.md)
- [Email Configuration](docs/70_EmailConfiguration.md)
- [Groups](docs/80_Groups.md)
- [Roles](docs/90_Roles.md)
- [Use LuceneSearch with Members](docs/100_LuceneSearch.md)
- [Use the Pimcore Customer Framework with Members](docs/300_CustomerDataFw.md)

* * *

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

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  
