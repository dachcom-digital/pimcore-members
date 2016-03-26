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
     * constructor
     */
    initialize: function(doc) {

        this.layoutId = this.layoutId + "_" + doc.id;
        this.element = doc;

    },

    setup: function ()
    {
        var _self = this;

        if (!this.layout) {

            Ext.Ajax.request({

                url: '/plugin/Members/admin_Restriction/get-document-restriction-config',
                params: {
                    docId: _self.element.id
                },
                success: function(result){

                    _self.data = Ext.decode(result.responseText);
                    _self.renderLayout();

                }

            });

            var proxy = new Ext.data.HttpProxy({
                url : '/admin/user/role-tree-get-childs-by-id'
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
                            checked: this.data.isActive
                        },

                        {
                            xtype:"checkbox",
                            name: "membersDocumentInheritable",
                            fieldLabel: t("members_enable_document_inheritable"),
                            checked: this.data.isInheritable
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
            settings : settings
        };

        Ext.Ajax.request({

            url: '/plugin/Members/admin_Restriction/set-document-restriction-config',
            params: {
                data : Ext.encode(values)
            },
            success: function(result){

                console.log(result);

            }

        });

    }

});