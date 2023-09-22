pimcore.registerNS('pimcore.plugin.members.document.restriction');
pimcore.plugin.members.document.restriction = Class.create({

    layout: null,
    element: null,
    data: null,
    userGroupsStore: null,
    cType: null,

    initialize: function (doc) {
        this.element = doc;
    },

    setup: function (cType) {
        var _self = this;

        this.cType = cType;

        if (!this.layout) {

            Ext.Ajax.request({
                url: '/admin/members/restriction/get-document-restriction-config',
                params: {
                    docId: _self.element.id,
                    cType: _self.cType
                },
                success: function (result) {
                    _self.data = Ext.decode(result.responseText);
                    _self.renderLayout();
                }
            });

            this.userGroupsStore = new Ext.data.Store({
                proxy: new Ext.data.HttpProxy({
                    url: '/admin/members/restriction/get-groups'
                }),
                reader: new Ext.data.JsonReader({}, [
                    {name: 'id'},
                    {name: 'name'}
                ]),
                autoload: true
            });
        }
    },

    renderLayout: function () {

        var _self = this,
            restrictionItems = [];

        if (this.data.isInherited === true) {

            restrictionItems.push(
                {
                    xtype: 'fieldset',
                    name: 'membersDocumentUnlockFieldset',
                    padding: 0,
                    margin: '0 0 10px 0',
                    style: {
                        background: 'rgba(128, 160, 171, 0.28)'
                    },
                    items: [
                        {
                            xtype: 'fieldset',
                            border: false,
                            padding: '8px 0 8px 8px',
                            margin: '0 0 5px 0',
                            style: {
                                borderTop: '0 !important',
                                background: '#4e4e4e',
                                color: 'white'
                            },
                            items: [
                                {
                                    xtype: 'label',
                                    style: {},
                                    listeners: {
                                        beforerender: function () {

                                            var el = this;

                                            Ext.Ajax.request({

                                                url: '/admin/members/restriction/get-next-parent-restriction',
                                                params: {
                                                    docId: _self.element.id,
                                                    cType: _self.cType
                                                },
                                                success: function (result) {
                                                    var data = Ext.decode(result.responseText),
                                                        str = data.key === null ? '--' : '<em>"' + data.key + '"</em> (' + data.path + ')';
                                                    el.setHtml(t('members_restriction_inherited_from') + ' ' + str);
                                                }
                                            });
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'displayfield',
                            submitValue: false,
                            value: t('members_unlock_inheritable_description'),
                            style: 'margin:10px 0 0 10px;'
                        },
                        {
                            xtype: 'button',
                            name: 'membersDocumentInheritWarning',
                            text: t('members_unlock_inherit'),
                            style: 'margin:10px 0 10px 10px;',
                            handler: function (btn) {

                                var fieldset,
                                    $a = this.layout.getForm().findField('membersDocumentRestrict'),
                                    $b = this.layout.getForm().findField('membersDocumentInheritable'),
                                    $c = this.layout.getForm().findField('membersDocumentUserGroups');

                                if ($a) {
                                    $a.enable();
                                }

                                if ($b) {
                                    $b.enable();
                                }

                                if ($c) {
                                    $c
                                        .setDisabled(false)
                                        .setDisabled(true)
                                        .setDisabled(false); //oh yea...
                                }

                                fieldset = btn.up('fieldset');
                                fieldset.hide();

                            }.bind(this)
                        }
                    ]
                }
            );
        }

        restrictionItems.push(
            Ext.create('Ext.ux.form.MultiSelect', {
                name: 'membersDocumentUserGroups',
                triggerAction: 'all',
                editable: false,
                fieldLabel: t('members_allowed_user_groups_description'),
                store: this.userGroupsStore,
                disabled: this.data.isInherited === true,
                itemCls: 'object_field',
                width: 700,
                valueField: 'id',
                displayField: 'name',
                minHeight: 100,
                queryMode: 'local',
                value: this.data.userGroups,
                listeners: {
                    beforerender: function () {
                        if (!_self.userGroupsStore.isLoaded() && !_self.userGroupsStore.isLoading())
                            _self.userGroupsStore.load();
                    }
                }
            })
        );

        var showInheritElements = this.cType === 'page'
            || this.cType === 'object'
            || (this.cType === 'asset' && this.element.type === 'folder');

        if (showInheritElements) {

            restrictionItems.push({
                xtype: 'checkbox',
                name: 'membersDocumentInheritable',
                fieldLabel: t('members_enable_document_inheritable'),
                checked: this.data.inherit || this.element.type === 'folder',
                readOnly: this.element.type === 'folder',
                disabled: this.data.isInherited === true,
                listeners: {
                    afterrender: function (e, b) {
                        var me = this;
                        if (_self.element.type === 'folder') {

                            Ext.create('Ext.tip.ToolTip', {
                                target: me.el,
                                title: 'Info',
                                width: 200,
                                showDelay: 50,
                                html: t('members_enable_document_inheritable_locked'),
                                listeners: {
                                    scope: me
                                }
                            });

                            me.setStyle('opacity', 0.5);
                        }
                    }
                }
            });
        }

        this.layout = new Ext.FormPanel({
            title: t('members_restriction'),
            iconCls: 'pimcore_material_icon members_icon_document_restriction',
            border: false,
            autoScroll: true,
            bodyStyle: 'padding:0 10px 0 10px;',
            items: [
                {
                    xtype: 'fieldset',
                    title: t('members_restriction'),
                    collapsible: true,
                    autoHeight: true,
                    defaults: {
                        labelWidth: 200
                    },
                    items: restrictionItems
                }
            ]
        });

        this.element.tabbar.add(this.layout);
        this.element.members.restrictionTab = this;

    },

    save: function () {

        var _self = this,
            settings = this.layout.getForm().getValues();

        var values = {
            docId: _self.element.id,
            cType: _self.cType,
            settings: settings
        };

        Ext.Ajax.request({
            url: '/admin/members/restriction/set-document-restriction-config',
            params: {
                data: Ext.encode(values)
            }
        });

    }
});