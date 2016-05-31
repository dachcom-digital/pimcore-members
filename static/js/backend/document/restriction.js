pimcore.registerNS("pimcore.plugin.members.document.restriction");
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
     * string "object" or "page"
     */
    cType : null,

    /**
     * constructor
     */
    initialize: function(doc) {

        this.layoutId = this.layoutId + "_" + doc.id;
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

        var _self = this;

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

                    items: [

                        {
                            xtype:"checkbox",
                            name: "membersDocumentRestrict",
                            fieldLabel: t("members_enable_document_restriction"),
                            checked: this.data.isActive,
                            disabled: this.data.isInherited == true
                        },
                        {
                            xtype:"checkbox",
                            name: "membersDocumentInheritable",
                            fieldLabel: t("members_enable_document_inheritable"),
                            checked: this.data.inherit,
                            disabled: this.data.isInherited == true
                        },

                        {
                            xtype:"fieldset",
                            name: "membersDocumentUnlockFieldset",
                            hidden: this.data.isInherited == false,
                            items: [
                                {
                                    xtype: "label",
                                    text: t("members_unlock_inheritable_description"),
                                    width: 185,
                                    style:"float:left;margin-right:5px;"
                                },
                                {
                                    xtype:"button",
                                    id:"",
                                    name: "membersDocumentInheritWarning",
                                    text: t("members_unlock_inherit"),

                                    handler: function (btn) {
                                        Ext.getCmp(this.layoutId).getForm().findField("membersDocumentRestrict").enable();
                                        Ext.getCmp(this.layoutId).getForm().findField("membersDocumentInheritable").enable();
                                        var fieldset = btn.up('fieldset');
                                        fieldset.hide();

                                    }.bind(this)
                                }
                            ]
                        },

                        Ext.create('Ext.ux.form.MultiSelect', {

                            name: 'membersDocumentUserGroups',
                            triggerAction: "all",
                            editable: false,
                            fieldLabel: t('members_allowed_user_groups_description'),
                            store: this.userRolesStore,
                            itemCls: "object_field",
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

                    ]

                }

            ]

        });

        this.element.tabbar.add( this.layout );
        this.element.members.restrictionTab = this;

    },

    save : function() {

        var _self = this,
            settings = this.layout.getForm().getFieldValues();

        var values = {
            docId: _self.element.id,
            cType : _self.cType,
            settings : settings
        };

        Ext.Ajax.request({

            url: '/plugin/Members/admin_Restriction/set-document-restriction-config',
            params: {
                data : Ext.encode(values)
            },
            success: function(result){ }

        });

    },

    delete : function() {

        var _self = this;

        var values = {
            docId: _self.element.id,
            cType : _self.cType
        };

        Ext.Ajax.request({

            url: '/plugin/Members/admin_Restriction/delete-document-restriction-config',
            params: {
                data : Ext.encode(values)
            },
            success: function(result){ }

        });

    }

});