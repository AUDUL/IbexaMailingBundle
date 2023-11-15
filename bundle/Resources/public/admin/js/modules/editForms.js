export const eZMailingEditFormModule = function () {
    function _init() {
        const form = document.querySelector('form[name="mailing"]');
        const saveButton = document.getElementById("ibexamailing_save-tab");
        const cancelButton = document.getElementById("ibexamailing_cancel-tab");

        if (cancelButton) {
            cancelButton.addEventListener('click', () => history.back());
        }

        if (saveButton) {
            saveButton.addEventListener('click', () => {
                if (form.querySelector('button[type="submit"]') !== null) {
                    form.querySelector('button[type="submit"]').click();
                } else {
                    form.submit();
                }
            });
        }
    }

    return {init: _init};
}();
