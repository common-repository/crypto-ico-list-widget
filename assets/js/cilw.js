
jQuery(document).ready(function ($) {

   // For main page ICO list
   $('.cilw-container').on('click','li[role="presentation"] a',function(){        
        var tabpanel_id         = $(this).attr('href');
        var mainContainer_id    = $(this).parents('.cilw-container').attr('id');
        var mainContainer       = $('.cilw-container'+'#'+mainContainer_id);
        var tabs                = mainContainer.find('div.ico-tab-pane');

        if( tabs.hasClass('show') ){
            tabs.removeClass('show');
            $(tabpanel_id).addClass('show');
        }

        $('li[role="presentation"]').removeClass('active');
        $(this).parent('li[role="presentation"]').addClass('active');

    });

});
