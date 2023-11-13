import {eZMailingApprobationModule} from "./modules/approbation"
import {eZMailingSearchModule} from "./modules/search"
import {eZMailingChartsModule} from "./modules/charts"
import {eZMainlingNormalizeModule} from "./modules/doc"
import {eZMailingEditFormModule} from "./modules/editForms"
import {eZMailingContentSelectionModule} from "./modules/contentSelection"

jQuery(function () {
    "use strict";
    var $ = jQuery;
    var $app = $(".ibexamailing-app:first");
    eZMailingApprobationModule.init(jQuery, $app);
    eZMailingSearchModule.init(jQuery, $app);
    eZMailingChartsModule.init(jQuery, $app);
    eZMainlingNormalizeModule.init(jQuery, $app);
    //eZMailingSubItemsModule.init(jQuery, $app);
    eZMailingEditFormModule.init(jQuery, $app);
    eZMailingContentSelectionModule.init(jQuery, $app);

    $('.campaigns > ul > li > label').click(function () {
        window.location = $(this).parent().find('ul > li.subscriptions > a').attr('href');
        return false;
    });
    $('.campaigns > ul > li.current_ancestor').addClass('expand');
    $('.campaigns > ul > li > input').click(function () {
        if ($(this).prop('checked')) {
            $(this).parent().addClass('expand');
        } else {
            $(this).parent().removeClass('expand');
        }
    });

    $('.ibexa-tabs > ul > li').click(function () {
        $(this).parent().find('.ibexa-tabs__tab--active').removeClass('ibexa-tabs__tab--active')
        $(this).addClass('ibexa-tabs__tab--active');
    });
});
