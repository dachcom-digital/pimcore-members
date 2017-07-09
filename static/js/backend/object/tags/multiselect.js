pimcore.registerNS('pimcore.plugin.members.object.tags.multiselect');
pimcore.plugin.members.object.tags.multiselect = Class.create(pimcore.object.tags.multiselect, {

    getLayoutEdit: function () {

        var proxy = new Ext.data.HttpProxy({
            url : '/plugin/Members/admin_Restriction/get-roles'
        });

        var fields = [
            {name:'id'},
            {name:'name'}
        ];

        var reader = new Ext.data.JsonReader({}, fields);

        var store = new Ext.data.Store({
            restful:    false,
            proxy:      proxy,
            reader:     reader,
            autoload:   true
        });

        var options = {
            name: this.fieldConfig.name,
            triggerAction: 'all',
            editable: false,
            fieldLabel: this.fieldConfig.title,
            store: store,
            itemCls: 'object_field',
            valueField: 'id',
            displayField: 'text',
            width: 400,
            minHeight: 150,
            maxHeight : 400,
            queryMode : 'local',
            listeners : {
                beforerender : function() {
                    if(!store.isLoaded() && !store.isLoading())
                        store.load();
                }
            }
        };

        if (this.fieldConfig.width) {
            options.width = this.fieldConfig.width;
        }
        if (this.fieldConfig.height) {
            options.height = this.fieldConfig.height;
        }

        if (typeof this.data === 'string' || typeof this.data === 'number') {
            options.value = this.data;
        }

        this.component = new Ext.ux.form.MultiSelect(options);

        return this.component;
    }

});