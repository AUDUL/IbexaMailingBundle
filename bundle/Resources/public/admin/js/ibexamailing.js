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
    eZMailingEditFormModule.init();
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
    // Ajouter un écouteur d'événements à chaque élément li
    document.querySelectorAll("li:not(.c-list-item--is-root-item).c-list-item--has-sub-items").forEach((li) => {
        li.addEventListener('click',() => {
            li.classList.toggle('c-list-item--is-expanded');
        });
    });

    document.querySelectorAll(".toggle-button-menu").forEach((div) => {
        div.addEventListener('click', () => {
            const scrollableWrapper = div.closest('.toggle-wrapper').querySelector('.m-tree__scrollable-wrapper');
            const treeContainer = div.closest('.m-tree');

            if (!scrollableWrapper || !treeContainer) {
                return; // Arrêter l'exécution si les éléments nécessaires ne sont pas présents
            }

            scrollableWrapper.classList.toggle('d-none');

            // Appliquer le style à la div toggle-button-menu si d-none est active
            if (scrollableWrapper.classList.contains('d-none')) {
                div.style.width = 'fit-content';
                treeContainer.style.width = '70px';
                return; // Sortir de la fonction si d-none est active
            }

            // Réinitialiser la largeur si d-none n'est pas active
            div.style.width = '';
            treeContainer.style.width = '320px';
        });
    });



});
