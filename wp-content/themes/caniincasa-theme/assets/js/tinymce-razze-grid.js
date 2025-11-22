(function() {
    'use strict';

    tinymce.PluginManager.add('razze_grid_button', function(editor, url) {
        // Add button
        editor.addButton('razze_grid_button', {
            title: 'Inserisci Griglia Razze',
            icon: 'dashicon dashicons-grid-view',
            onclick: function() {
                // Open the modal
                if (typeof window.openRazzeGridModal === 'function') {
                    window.openRazzeGridModal();
                }
            }
        });
    });
})();
