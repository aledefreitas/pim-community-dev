'use strict';
define([
    'underscore',
    'pim/mass-edit-form/product/operation',
    'acme/template/add-product-model',
], function (
    _,
    BaseOperation,
    template
) {
    return BaseOperation.extend({
        template: _.template(template),
        events: {
            'change .comment-field': 'updateModel'
        },

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.updateModel);

            return BaseOperation.prototype.configure.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            this.$el.html(this.template({
                readOnly: this.readOnly
            }));

            BaseOperation.prototype.render.apply(this, arguments);

            return this;
        },

        /**
         * Updates the model to store action
         *
         * @param {Object} formData
         */
        updateModel: function (formData) {
            if (this.getParent().getCurrentOperation() === this.getCode()) {
                formData.actions = [{
                    field: 'familyVariant',
                    value: formData.family_variant
                }];

                this.setData(formData, {silent: true});
            }
        },
    });
});
