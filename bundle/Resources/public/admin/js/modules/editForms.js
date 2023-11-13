export const eZMailingEditFormModule = function () {
    function _init($, $app) {
        var $form = $('form[ibexamailing]', $app);
        var $saveButton = $("#ibexamailing_save-tab", $app);
        var $cancelButton = $("#ibexamailing_cancel-tab", $app);

        $cancelButton.click(function () {
            history.back();
        });
        $saveButton.click(function () {
            if (document.querySelector('form[ibexamailing]').querySelector('button[type="submit"]') !== null) {
                $('button[type="submit"]', $form).click();
            } else {
                $form.submit();
            }
        });
    }

    return {init: _init};
}();
