# Upgrade Notes

## 5.1.2
- **[BUGFIX]**: Fix interface compatibility [#216](https://github.com/dachcom-digital/pimcore-members/issues/216)
- **[IMPROVEMENT]**: Account check before password reset
- **[IMPROVEMENT]**: [Restriction] Use AssetService to stream response by URI 

## 5.1.1
- **[BUGFIX]**: Fix chunked serving of protected video assets [#214](https://github.com/dachcom-digital/pimcore-members/pull/214)

## 5.1.0
- [NEW FEATURE] "Preview confirmation" workflow added to avoid registration confirmation by prefetching email processes. 
  Read more about it [here](./docs/60_RegistrationTypes.md#email-prefetching). 
  This also comes with a new template: `@Members/registration/confirm_preview.html.twig` and also two new translations (
  `members.registration.confirm_preview`, `members.registration.complete_confirmation`)
- [LICENSE] Dual-License with GPL and Dachcom Commercial License (DCL) added

### 5.0.3
- **[BUGFIX]**: Do not initialize `memberStorageId` in UserManager::constructor to prevent early db connection 

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
