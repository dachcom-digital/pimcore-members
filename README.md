# Pimcore Members
Add frontend user authentication and document restriction to pimcore 5.0.

#### Requirements
* Pimcore 5

#### Features
* Create Members in Backend
* Allow Members to register in frontend
* Restrict Documents, Objects and Assets to specific User Roles

### Installation

#### Composer
1. Add code below to your `composer.json`    
2. Activate & install it through backend
3. Clear Cache

```json
"require" : {
    "dachcom-digital/members" : "2.0.0",
}
```

#### Basics
Unlike members1, this bundle does not install any classes for you any more.
Since Members should be the one and only frontend authentication Bundle, we need to add the most flexibility as possible.
But no worries, it's still simple to integrate.

#### Class Installation
You need two classes: User and Group. So let's create it:

*User*  
1. Create a class and call it `membersUser`
2. Add parent class: `\MembersBundle\Adapter\User\AbstractUser`
3. Add fields:

| Name | Field Type | Comment |
|---------------------|-------------|-------------------------------|
| userName | Input |  |
| email | Input |  |
| confirmationToken | Input | set to it read only |
| lastLogin | Date & Time | set to it read only |
| password | Password |  |
| passwordRequestedAt | Date & Time | set to it read only |
| groups | User Group | This field comes with Members |

> `membersUser` is the default name, you may want to change it. Read [here](docs/20_CustomClassName.md) how to achieve that.

*Group*  
1. Create a class and call it `membersGroup`
2. Add parent class: `\MembersBundle\Adapter\Group\AbstractGroup`
3. Add fields:

| Name | Field Type | Comment |
|---------------------|-------------|-------------------------------|
| name | Input |  |

> `membersGroup` is the default name, you may want to change it. Read [here](docs/20_CustomClassName.md) how to achieve that.

Feel free to add additional fields since those are just the required ones.

That's it. Members will use those classes to manage authentication and group management.

### Further Information
- [Custom Class Names](docs/20_CustomClassName.md)
- [Frontend Routes & Views](docs/30_FrontendRoutes.md)
- [Available Events](docs/40_Events.md)
- [Custom Form Types](docs/50_CustomFormTypes.md)
- [Registration Types](docs/60_RegistrationTypes.md)

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  