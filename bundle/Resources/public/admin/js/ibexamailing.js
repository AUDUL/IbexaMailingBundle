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
            const titreNavToggleElement = div.querySelector('.titre-nav-toggle');
            const toggleSvg = div.querySelector('.ibexa-icon');

            if (!scrollableWrapper || !treeContainer || !titreNavToggleElement || !toggleSvg) {
                return; // Arrêter l'exécution si les éléments nécessaires ne sont pas présents
            }

            scrollableWrapper.classList.toggle('d-none');

            // Appliquer le style à la div toggle-button-menu si d-none est active
            if (scrollableWrapper.classList.contains('d-none')) {
                div.style.width = 'fit-content';
                treeContainer.style.width = '70px';

                // Ajouter la classe d-none à l'élément avec la classe "titre-nav-toggle"
                titreNavToggleElement.classList.add('d-none');

                // Faire tourner le SVG de 180 degrés avec une transition de 0.3s
                toggleSvg.style.transition = 'transform 0.3s';
                toggleSvg.style.transform = 'rotate(180deg)';

                return; // Sortir de la fonction si d-none est active
            }

            // Réinitialiser la largeur si d-none n'est pas active
            div.style.width = '';
            treeContainer.style.width = '320px';

            // Supprimer la classe d-none de l'élément avec la classe "titre-nav-toggle"
            titreNavToggleElement.classList.remove('d-none');

            // Réinitialiser la rotation du SVG avec une transition de 0.3s
            toggleSvg.style.transition = 'transform 0.3s';
            toggleSvg.style.transform = '';
        });
    });
});
