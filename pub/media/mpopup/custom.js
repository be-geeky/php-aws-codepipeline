	////SCRIPT FOR MODAL POPUP WINDOW

jQuery(document).ready(function(){	
	jQuery('#popup-container a.close').click(function(){
			jQuery('#popup-container').fadeOut();
			jQuery('#active-popup').fadeOut();
			jQuery('body').removeClass('mpopup');
	});
	var visits = jQuery.cookie('visits') || 0;
	visits++;
	
    var date = new Date();
    date.setTime(date.getTime() + (4.32e+7));
    //$.cookie('username', username, { expires: date });
	jQuery.cookie('visits', visits, { expires: date, path: '/' });
		
	console.debug(jQuery.cookie('visits'));
		
	if ( jQuery.cookie('visits') > 1 ) {
		jQuery('#active-popup').hide();
		jQuery('#popup-container').hide();
		jQuery('body').removeClass('mpopup');
	} else {
		setTimeout( function(){ 
		   var pageHeight = jQuery(document).height();
					jQuery('<div id="active-popup"></div>').insertBefore('body');
					jQuery('#active-popup').css("height", pageHeight);
					jQuery('#popup-container').show();
					jQuery('body').addClass('mpopup');
		}  , 2000 );
			
			
	}

	if (jQuery.cookie('noShowWelcome')) { jQuery('#popup-container').hide(); jQuery('#active-popup').hide(); jQuery('body').removeClass('mpopup');}
});	

jQuery(document).mouseup(function(e){
	var container = jQuery('#popup-container');
	
	if( !container.is(e.target)&& container.has(e.target).length === 0)
	{
		container.fadeOut();
		jQuery('#active-popup').fadeOut();
	}

});
