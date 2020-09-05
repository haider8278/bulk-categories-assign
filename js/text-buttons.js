(function() {
    tinymce.PluginManager.add('bca_tc_button', function( editor, url ) {
        editor.addButton( 'bca_tc_button', {
            text: 'BCA',
            icon: 'icon bca-icon',
			title: 'Click to add Gallery. add category ids (coma seperated for multiple) for specific category.',
            onclick: function() {
                editor.insertContent('[image-gallery cat=""]');
            }
        });
    });
})();