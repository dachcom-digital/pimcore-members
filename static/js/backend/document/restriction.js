pimcore.registerNS('pimcore.plugin.members.document.restriction');
pimcore.plugin.members.document.restriction = Class.create({

    /**
     * @var string
     */
    layoutId: 'members_document_restriction_panel',

    /**
     *
     */
    layout : null,

    /**
     *
     */
    element : null,

    /**
     *
     */
    data : null,

    /**
     *
     */
    userRolesStore : null,

    /**
     * string 'object' or 'page'
     */
    cType : null,

    /**
     * constructor
     */
    initialize: function(doc) {

        this.layoutId = this.layoutId + '_' + doc.id;
        this.element = doc;

    },

    setup: function ( cType )
    {
        var _self = this;

        this.cType = cType;

        if (!this.layout) {

            Ext.Ajax.request({

                url: '/plugin/Members/admin_Restriction/get-document-restriction-config',
                params: {
                    docId: _self.element.id,
                    cType: _self.cType
                },
                success: function(result){

                    _self.data = Ext.decode(result.responseText);
                    _self.renderLayout();

                }

            });

            var proxy = new Ext.data.HttpProxy({
                url : '/plugin/Members/admin_Restriction/get-roles'
            });

            var reader = new Ext.data.JsonReader({}, [
                {name:'id'},
                {name:'name'}
            ]);

            this.userRolesStore = new Ext.data.Store({
                restful:    false,
                proxy:      proxy,
                reader:     reader,
                autoload:   true
            });
        }
    },

    renderLayout : function() {

        var _self = this,
            restrictionItems = [];

        if(this.data.isInherited === true) {

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
                            xtype:'fieldset',
                            border: false,
                            padding:'8px 0 8px 8px',
                            margin:'0 0 5px 0',
                            style: {
                                borderTop: '0 !important',
                                background: '#4e4e4e',
                                color: 'white'
                            },
                            items: [
                                {
                                    xtype: 'label',
                                    style: {
                                    },
                                    listeners: {
                                        beforerender: function() {

                                            var el = this;

                                            Ext.Ajax.request({

                                                url: '/plugin/Members/admin_Restriction/get-next-parent-restriction',
                                                params: {
                                                    docId: _self.element.id,
                                                    cType: _self.cType
                                                },
                                                success: function(result){
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
                            id: '',
                            name: 'membersDocumentInheritWarning',
                            text: t('members_unlock_inherit'),
                            style: 'margin:10px 0 10px 10px;',
                            handler: function (btn) {

                                var $a = Ext.getCmp(this.layoutId).getForm().findField('membersDocumentRestrict'),
                                    $b = Ext.getCmp(this.layoutId).getForm().findField('membersDocumentInheritable'),
                                    $c = Ext.getCmp(this.layoutId).getForm().findField('membersDocumentUserGroups');

                                if($a) { $a.enable(); }
                                if($b) { $b.enable(); }

                                if($c) {
                                    $c.setDisabled(false)
                                        .setDisabled(true)
                                        .setDisabled(false); //oh yea...
                                }

                                var fieldset = btn.up('fieldset');
                                fieldset.hide();

                            }.bind(this)
                        }
                    ]
                }
            );
        }

        restrictionItems.push(
            {
                xtype:'checkbox',
                name: 'membersDocumentRestrict',
                fieldLabel: t('members_enable_document_restriction'),
                checked: this.data.isActive,
                disabled: this.data.isInherited === true
            }
        );

        var showInheritElements = this.cType === 'page'
            || this.cType === 'object'
            || (this.cType === 'asset' && this.element.type === 'folder');

        if(showInheritElements) {

            restrictionItems.push({
                xtype:'checkbox',
                name: 'membersDocumentInheritable',
                fieldLabel: t('members_enable_document_inheritable'),
                checked: this.data.inherit || this.element.type === 'folder',
                readOnly: this.element.type === 'folder',
                disabled: this.data.isInherited === true,
                listeners: {

                    afterrender: function(e,b) {
                        var me = this;
                        if(_self.element.type === 'folder') {

                            Ext.create('Ext.tip.ToolTip', {
                                target: me.el,
                                title: 'Info',
                                width: 200,
                                showDelay:50,
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

        restrictionItems.push(
            Ext.create('Ext.ux.form.MultiSelect', {
                name: 'membersDocumentUserGroups',
                triggerAction: 'all',
                editable: false,
                fieldLabel: t('members_allowed_user_groups_description'),
                store: this.userRolesStore,
                disabled: this.data.isInherited === true,
                itemCls: 'object_field',
                width: 700,
                valueField: 'id',
                displayField: 'text',
                minHeight: 100,
                queryMode : 'local',
                value: this.data.userGroups,
                listeners : {
                    beforerender : function() {
                        if(!_self.userRolesStore.isLoaded() && !_self.userRolesStore.isLoading())
                            _self.userRolesStore.load();
                    }
                }
            })
        );

        this.layout = new Ext.FormPanel({

            id: this.layoutId,
            title: t('members_restriction'),
            iconCls: 'members_icon_document_restriction',
            border: false,
            autoScroll: true,
            bodyStyle:'padding:0 10px 0 10px;',
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

        this.element.tabbar.add( this.layout );
        this.element.members.restrictionTab = this;

    },

    save : function() {

        var _self = this,
            settings = this.layout.getForm().getValues();

        var values = {
            docId: _self.element.id,
            cType : _self.cType,
            settings : settings
        };

        Ext.Ajax.request({

            url: '/plugin/Members/admin_Restriction/set-document-restriction-config',
            params: {
                data : Ext.encode(values)
            }

        });

    },

    delete : function() {

        var _self = this,
            values = {
                docId: _self.element.id,
                cType : _self.cType
            };

        Ext.Ajax.request({

            url: '/plugin/Members/admin_Restriction/delete-document-restriction-config',
            params: {
                data : Ext.encode(values)
            }
        });
    }
});