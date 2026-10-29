/*
 * Image picker for the old Blade admin pages. A button with data-ckfinder-field="<input id>" opens the CKFinder media
 * library (the same one as the React admin's «کتابخانه رسانه», behind the admin login) and puts the chosen image's
 * address ("/upload/…") in that input. Replaces the old Responsive FileManager popup.
 */
(function () {
    var script = document.currentScript;
    var connector = (script && script.getAttribute('data-connector')) || '/admin/ckfinder/connector';
    var loading = null;

    function load() {
        if (window.CKFinder) return Promise.resolve(window.CKFinder);
        if (!loading) {
            loading = new Promise(function (resolve, reject) {
                var s = document.createElement('script');
                s.src = '/js/ckfinder/ckfinder.js';
                s.onload = function () { window.CKFinder ? resolve(window.CKFinder) : reject(); };
                s.onerror = function () { loading = null; reject(); }; // let the next click try again
                document.head.appendChild(s);
            });
        }
        return loading;
    }

    function fill(id, url) {
        var input = document.getElementById(id);
        if (!input || !url) return;
        if (window.jQuery) {
            window.jQuery(input).val(url).trigger('change');
        } else {
            input.value = url;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        // The picture beside the field (gallery_image_input_1 → gallery_image_input_per_1) shows the new image at once.
        var preview = document.getElementById(id.replace('_input_', '_input_per_'));
        if (preview && preview.tagName === 'IMG') preview.src = url;
    }

    // Delegated, so rows the pages add later (plans, products) work too.
    document.addEventListener('click', function (e) {
        var button = e.target.closest && e.target.closest('[data-ckfinder-field]');
        if (!button) return;
        e.preventDefault();
        var field = button.getAttribute('data-ckfinder-field');
        load().then(function (CKFinder) {
            CKFinder.modal({
                connectorPath: connector,
                language: 'fa',
                resourceType: 'Images',
                chooseFiles: true,
                width: Math.min(1100, window.innerWidth - 32),
                height: Math.min(700, window.innerHeight - 32),
                onInit: function (finder) {
                    finder.on('files:choose', function (evt) { fill(field, evt.data.files.first().getUrl()); });
                    finder.on('file:choose:resizedImage', function (evt) { fill(field, evt.data.resizedUrl); });
                }
            });
        }, function () {
            alert('کتابخانه رسانه بارگذاری نشد. اتصال را بررسی کنید و دوباره امتحان کنید.');
        });
    });
})();
