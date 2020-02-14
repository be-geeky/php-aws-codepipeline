/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function(targetModule){

        var reloadPrice = targetModule.prototype._reloadPrice;
        var reloadPriceWrapper = wrapper.wrap(reloadPrice, function(original){
            //do extra stuff

            //call original method
            var result = original();

            //do extra stuff
            var simpleSku = this.options.spConfig.skus[this.simpleProduct];
            var simpleBarcode = this.options.spConfig.barcodes[this.simpleProduct];
			//var simpleBarcode = "";
			//alert(simpleSku + simpleBarcode);
            if(simpleSku != '' && typeof simpleSku != 'undefined') {
				console.log("skuswitch.js");
				console.log(simpleSku);
                $('div.product-info-main .sku .value').html(simpleSku);
				$("#distributor-main-container .owl-item").each(function(){					
					var searchSring = $(this).find("span").html();
					searchSring = searchSring.replace("{{sku}}",simpleSku);
					//console.log(searchSring);
					$(this).find("a").attr('href',searchSring);
				});
				$("#reseller-main-container .owl-item").each(function(){
					var searchSring = $(this).find("span").html();
					searchSring = searchSring.replace("{{sku}}",simpleSku);				
					//console.log(searchSring);	
					$(this).find("a").attr('href',searchSring);
				});				
				$("#part-no").html(simpleSku);
            }
			if(simpleBarcode != '') {
				$('div.product-info-main .barcode .value').html(simpleBarcode);
				$("#barcode").html(simpleBarcode);
			}

            //return original value
            return result;
        });

        targetModule.prototype._reloadPrice = reloadPriceWrapper;
        return targetModule;
    };
});