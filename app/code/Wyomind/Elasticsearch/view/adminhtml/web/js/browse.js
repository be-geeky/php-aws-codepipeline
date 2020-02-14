
var Elasticsearch = {};

require(["jquery", "jquery/ui", "Magento_Ui/js/modal/modal", "Wyomind_Elasticsearch_Jsonview"], function (jQuery) {
    jQuery(function () {
        Elasticsearch = {
            raw: function (url) {
                jQuery('#raw').modal({
                    'type': 'slide',
                    'title': 'Raw data',
                    'modalClass': 'mage-new-category-dialog form-inline',
                    buttons: []
                });

                jQuery('#raw').html("");
                jQuery('#raw').modal('openModal');

                jQuery.ajax({
                    url: url,
                    data: {},
                    type: 'GET',
                    showLoader: true,
                    success: function (data) {
                        jQuery('#raw').html(JSON.stringify(data));
                        jQuery('#raw').JSONView(data);
                    },
                    error: function (data) {
                        jQuery('#raw').html("<hr style='border:1px solid #e3e3e3'/><br/>" + data.responseText);
                    }
                });
            }
        };
    });
});