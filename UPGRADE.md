# Upgrade Notes

## Migrating from Version 3.x to Version 4.0
- Firewall settings are not included by default anymore. Copy `MembersBundle/Resources/config/packages/security.yaml` to `config/packages/security.yaml`
- `LuceneSearchBundle` support removed
- All Folders in `views` are lowercase/dashed now (`views/areas`, `views/auth`, `views/backend`, ...)
- PHP8 return type declarations added: you may have to adjust your extensions accordingly
- TWIG Helper `members_build_nav()` legacy navigation building support removed
- `UserTrait::hasGroup` has been removed, use either `UserTrait::hasGroupId` or `UserTrait::hasGroupName` instead
- Check your email templates (controller and template definition)
***

Members 3.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-members/blob/3.x/UPGRADE.md
