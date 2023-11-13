export const eZMailingSearchModule = function () {
    function _init($, $app) {
        var $searchIbexaMailing = $('.ibexamailing-search > input[type="search"]');
        if ($searchIbexaMailing.length > 0) {
            $searchIbexaMailing.devbridgeAutocomplete({
                serviceUrl: $app.data('search-endpoint'),
                minChars: 3,
                onSelect: function (suggestion) {
                    location.href = suggestion.data;
                }
            });
        }
    }

    return {init: _init};
}();
