const path = require('path');
const fs = require('fs');

let subItemsModule = path.resolve(__dirname, '../../../../../../vendor/ibexa/admin-ui/src/bundle/ui-dev/src/modules/sub-items/sub.items.module.js');
if (fs.existsSync(subItemsModule)) {
} else {
    //todo
}
module.exports = (Encore) => {
    Encore
        .autoProvidejQuery()
        .addEntry('ibexamailing', [
            path.resolve(__dirname, '../public/admin/css/ibexamailing.scss'),
        path.resolve(__dirname, '../public/admin/css/tree.scss'),
        path.resolve(__dirname, '../public/admin/js/jquery.autocomplete.min.js'),
        path.resolve(__dirname, '../public/admin/js/jquery.peity.min.js'),
        path.resolve(__dirname, '../public/admin/js/Chart.min.js'),
            path.resolve(__dirname, '../public/admin/js/ibexamailing.js'),
        subItemsModule
    ]);
};
