# Upgrade Notes

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
