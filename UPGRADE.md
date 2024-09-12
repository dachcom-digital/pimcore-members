# Upgrade Notes

### 5.0.2
- **[IMPROVEMENT]**: RoleOptionsProvider now shows the default role in UserGroup objects if no configuration was made [#201](https://github.com/dachcom-digital/pimcore-members/issues/201)
- **[BUGFIX]**: Fixed a bug that breaks the system when you have empty roles [#200](https://github.com/dachcom-digital/pimcore-members/pull/200)

### 5.0.1
- **[IMPROVEMENT]**: Introduce `OAUTH_RESOURCE_MAPPING_REFRESH` Event
- **[IMPROVEMENT]**: Configurable Firewall Name via container parameter `members.firewall_name`

## Migrating from Version 4.x to Version 5.0

### Global Changes
- Recommended folder structure by symfony adopted
- [ROUTE] Route include changed from `@MembersBundle/Resources/pimcore/routing/all.yml` to `@MembersBundle/config/pimcore/routing/all.yaml`
- All template folder are lowercase/underscore now (`templates/change_password`, `templates/delete_account`)
- `AbstractUser` and `AbstractSsoAwareUser` now implements the `PasswordAuthenticatedUserInterface` interface by default

### Deprecations
- Constant `MembersBundle\Security\RestrictionUri::PROTECTED_ASSET_FOLDER` has been removed
- Constant `MembersBundle\Security\RestrictionUri::MEMBERS_REQUEST_URL` has been removed

***

Members 4.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-members/blob/4.x/UPGRADE.md
