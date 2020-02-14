

require(["jquery", "Magento_Ui/js/modal/alert", "Magento_Ui/js/modal/confirm"], function ($, alert, confirm) {
    $(function () {
        $(document).ready(function () {
            var CMcssRules = null;
            var CMjsTemplate = null;
            $(document).on('click', "#es_test_servers", function () {
                $.ajax({
                    url: $("#es_test_servers").attr("callback_url"),
                    data: {
                        servers: $("#catalog_search_elasticsearch_servers").val()
                    },
                    type: 'POST',
                    showLoader: true,
                    success: function (data) {
                        var html = "";
                        data.each(function (host_data) {
                            html += "<h3>" + host_data.host + "</h3>";
                            if (host_data.error != undefined) {
                                html += "<span class='error'>ERROR</span><br/><br/>" + host_data.error;
                            } else {
                                html += "<span class='success'>SUCCESS</span><br/><br/>";
                                html += "<b>Name</b> : " + host_data.data.name + "<br/>";
                                html += "<b>Cluster name</b> : " + host_data.data.cluster_name + "<br/>";
                                html += "<b>Elasticsearch version</b> : " + host_data.data.version.number + "<br/>";
                            }
                            html += "<br/><br/>";
                        });
                        alert({
                            title: "",
                            content: html
                        });
                    }
                });
            });

            $(document).on('click', ".load-template", function () {
                var path = $(this).attr('path');
                $.ajax({
                    url: $(this).attr("load_url"),
                    data: {path: path},
                    type: 'POST',
                    showLoader: true,
                    success: function (data) {
                        CMcssRules.setValue(data.css);
                        CMjsTemplate.setValue(data.template);
                    }
                });
            });

            $(document).on('click', "#elasticsearch_autocomplete_advanced-head", function () {
                CMcssRules.refresh();
                CMjsTemplate.refresh();
            });

            $(document).on('click', "#catalog_search_elasticsearch-head", function () {
                CMIndexSettings.refresh();
            });

            while(typeof CodeMirror == "undefined") {
                setTimeout(function() {}, 300);
            }

            if ($("#catalog_search_elasticsearch_index_settings").length === 1) {
                CMIndexSettings = CodeMirror.fromTextArea(document.getElementById('catalog_search_elasticsearch_index_settings'), {
                    matchBrackets: true,
                    mode: "text/javascript",
                    readOnly: false,
                    indentUnit: 2,
                    indentWithTabs: false,
                    lineNumbers: true,
                    styleActiveLine: true,
                    theme: 'mdn-like',
                    extraKeys: {
                        "F11": function (cm) {
                            cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                        },
                        "Esc": function (cm) {
                            if (cm.getOption("fullScreen"))
                                cm.setOption("fullScreen", false);
                        }
                    }

                });
                CMIndexSettings.refresh();
            }

            if ($("#elasticsearch_autocomplete_advanced_css_rules").length === 1) {

                CMcssRules = CodeMirror.fromTextArea(document.getElementById('elasticsearch_autocomplete_advanced_css_rules'), {
                    matchBrackets: true,
                    mode: "text/css",
                    readOnly: false,
                    indentUnit: 2,
                    indentWithTabs: false,
                    lineNumbers: true,
                    styleActiveLine: true,
                    theme: 'mdn-like',
                    extraKeys: {
                        "F11": function (cm) {
                            cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                        },
                        "Esc": function (cm) {
                            if (cm.getOption("fullScreen"))
                                cm.setOption("fullScreen", false);
                        }
                    }
                });
                CMcssRules.refresh();

                CMjsTemplate = CodeMirror.fromTextArea(document.getElementById('elasticsearch_autocomplete_advanced_js_template'), {
                    matchBrackets: true,
                    mode: "application/x-ejs",
                    readOnly: false,
                    indentUnit: 2,
                    indentWithTabs: false,
                    lineNumbers: true,
                    styleActiveLine: true,
                    theme: 'mdn-like',
                    extraKeys: {
                        "F11": function (cm) {
                            cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                        },
                        "Esc": function (cm) {
                            if (cm.getOption("fullScreen"))
                                cm.setOption("fullScreen", false);
                        }
                    }
                });
                CMjsTemplate.refresh();
            }
        });
    });
});