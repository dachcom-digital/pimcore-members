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

> There is also a class installer command. If your not using any special class configuration, feel free to use this command: `$ bin/console members:install:class`

You need two classes: User and Group. So let's create it:

*User*  
1. Create a class and call it `MembersUser`
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
1. Create a class and call it `MembersGroup`
2. Add parent class: `\MembersBundle\Adapter\Group\AbstractGroup`
3. Add fields:

| Name | Field Type | Comment |
|---------------------|-------------|-------------------------------|
| name | Input |  |
| roles | Multiselection | Set "Options Provider Class or Service Name" to `MembersBundle\CoreExtension\Provider\RoleOptionsProvider` |

> `membersGroup` is the default name, you may want to change it. Read [here](docs/20_CustomClassName.md) how to achieve that.

Feel free to add additional fields since those are just the required ones. That's it. Members will use those classes to manage authentication and group management.

#### Frontend Implementation

**Navigation:** Do **not** use the default nav builder extension (`pimcore_build_nav`). Just use the `members_build_nav` to build secure menus. 
Otherwise your restricted pages will show up. This twig extension will also handel your navigation cache strategy.

**Usage**  
```twig
{% set nav = members_build_nav(currentDoc, documentRootDoc, null, true) %}
{{ pimcore_render_nav(nav, 'menu', 'renderMenu', { maxDepth: 2 }) }}
```

#### Email Implementation
You're almost there, just check the [email configuration](docs/70_EmailConfiguration.md) and you're good to go.

### Further Information
- [Custom Class Names](docs/20_CustomClassName.md)
- [Frontend Routes & Views](docs/30_FrontendRoutes.md)
- [Available Events](docs/40_Events.md)
- [Custom Form Types](docs/50_CustomFormTypes.md)
- [Registration Types](docs/60_RegistrationTypes.md)
- [Email Configuration](docs/70_EmailConfiguration.md)
- [Roles](docs/80_Roles.md)

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  