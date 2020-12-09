(function ($) {
    function vosSimpleModal() {
        var modal = this;

        modal.element = $(".vos-simple-popup");

        modal.closeButton = modal.element.find(".close");

        modal.backdrop = modal.element.find(".vos-simple-popup__backdrop");

        modal.remove = function () {
            $(".vos-simple-popup").remove();
        };

        modal.closeButton.on("click", function () {
            modal.element.fadeOut();
        });

        modal.backdrop.on("click", function () {
            modal.element.fadeOut();
        });

        modal.show = function () {
            if (window.localStorage && window.sessionStorage) {
                if (js_data.storage === "local") {
                    localStorage.setItem("vos-simple-popup", new Date().toISOString());
                } else {
                    sessionStorage.setItem("vos-simple-popup", new Date().toISOString());
                }
            }

            modal.element.fadeIn();
        };
    }

    function getFromStorage() {
        if (window.localStorage && window.sessionStorage) {

            if (js_data.storage === "local") {
                return localStorage.getItem("vos-simple-popup");
            } else {
                console.log(js_data.storage);
                return sessionStorage.getItem("vos-simple-popup");
            }
        }
        return false;
    }

    var vosSimpleModalInstance = new vosSimpleModal();

    $(document).ready(function () {
        var dateString = getFromStorage();
        console.log(dateString);
        if (dateString) {
            var popupDate = new Date(dateString);

            if (((popupDate - new Date()) / (1000 * 60 * 60 * 24)) * -1 > 1) {
                vosSimpleModalInstance.show();
            }
        } else {
            vosSimpleModalInstance.show();
        }
    });
})(jQuery);
