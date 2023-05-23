# Upgrade Notes

### 4.1.0
- **[FEATURE]**: PIMCORE 10.5 support only
- **[ENHANCEMENT]**: Add public asset path protection, read more about it [here](./docs/200_Restrictions.md#public-assets-path-protection)
- **[ENHANCEMENT]**: Respect flysystem stream in asset/zip download, read more about it [here](./docs/240_AssetProtection.md#package) | [#174|@aarongerig](https://github.com/dachcom-digital/pimcore-members/pull/174)

### 4.0.4
- **[BUGFIX]**: assert non list array after filtering user roles [@dasraab](https://github.com/dachcom-digital/pimcore-members/pull/169)

### 4.0.3
- **[BUGFIX]**: Fix join behaviour, query-building and `$queryIdentifier` on RestrictionQuery [@scrummer](https://github.com/dachcom-digital/pimcore-members/pull/167)
- **[BUGFIX]**: Remove scheme and host from `_target_path` in ForbiddenRouteListener [@lukas-schnieper](https://github.com/dachcom-digital/pimcore-members/pull/166)
- **[DEPRECATION]**: Constants `RestrictionUri::PROTECTED_ASSET_FOLDER` and `RestrictionUri::MEMBERS_REQUEST_URL` have been marked as deprecated and will be removed in 5.0

### 4.0.2
- **[BUGFIX]**: [MembersLogin] Inject correct Translator service [@aarongerig](https://github.com/dachcom-digital/pimcore-members/pull/165)

### 4.0.1
- **[BUGFIX]**: fix inherited group check [#162](https://github.com/dachcom-digital/pimcore-members/issues/162)
- **[ENHANCEMENT]**: return proper HTTP response codes on form submit [@aarongerig](https://github.com/dachcom-digital/pimcore-members/pull/161)

## Migrating from Version 3.x to Version 4.0

### Breaking Changes

- It is no longer possible to merge security configurations from multiple locations, including bundles. Instead, you have to move
  them to one single config file, e.g. config/packages/security.yaml. Please
  adopt `MembersBundle/Resources/config/packages/security.yaml` and merge your own firewall configuration into one single file.
- `LuceneSearchBundle` support removed
- All Folders in `views` are lowercase/dashed now (`views/areas`, `views/auth`, `views/backend`, ...)
- PHP8 return type declarations added: you may have to adjust your extensions accordingly
- Twig Helper `members_build_nav()` legacy navigation building support removed
- `UserTrait::hasGroup` has been removed, use either `UserTrait::hasGroupId` or `UserTrait::hasGroupName` instead
- PIMCORE removed the `Placeholder` feature, so we need to pass all the variables to the twig template:
    - If you're using a custom email controller, make sure to pass all email variables to the view template and also check your
      email templates and replace all the deprecated variables (e.g. `%DataObject(user,{\"method\" : \"getUsername\"});` with `{{ user.username }}`)`
    - Area Snippets: If any snippet contains placeholder elements, you need to pass the values from your snippet controller to the
      view template: `return $this->render('default/snippet.html.twig', array_filter($request->attributes->all(), static function ($parameterKey) { return !str_starts_with($parameterKey, '_'); }, ARRAY_FILTER_USE_KEY));`
      and also replace the deprecated variables like described in email templates above
- [SSO] ⚠️ You need to set `framework.session.cookie_samesite` to `lax`, otherwise the oAUthConnect won't work properly
- [SSO] Make sure you have a valid key for `pimcore.encryption.secret`, defined via env variable `PIMCORE_ENCRYPTION_SECRET` (You can generate a defuse key by executing the `vendor/bin/generate-defuse-key` command)
- [SECURITY] `setSalt` method removed from `UserTrait.php` (deprecated in symfony 5.3)
- [SECURITY] `MembersBundle\Security\EmailUserProvider` has been removed. Use [`auth_identifier: 'email'`](./docs/10_AuthIdentifier.md) instead.
- `MembersBundle\Security\RestrictionUri::getAssetUrlInformation()` => `restrictionGroups` always returns array type

### Misc
- Check your email templates (controller and template definition)

### New Features
- You're able to switch the [auth_identifier](./docs/10_AuthIdentifier.md) (Use `email` instead of `username` for authentication)
- `addRestrictionInjection()` comes with an optional `$aliasFrom` argument
- Your able to pass an instance of `\MembersBundle\Validation\ValidationGroupResolverInterface` to each `validation_groups` property instead of array

***

Members 3.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-members/blob/3.x/UPGRADE.md
