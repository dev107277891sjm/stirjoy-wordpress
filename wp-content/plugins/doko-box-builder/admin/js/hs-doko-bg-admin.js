(function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			var file_frame, image_data;

			if ( isLodash() ) {
				_.noConflict();
			}

			$( document ).on(
				"click",
				"#doko-steps-background-image",
				function (e) {
					e.preventDefault();

					/**
					 * If an instance of file_frame already exists, then we can open it
					 * rather than creating a new instance.
					 */
					if ( undefined !== file_frame ) {

						file_frame.open();
						return;

					}

					/**
					 * If we're this far, then an instance does not exist, so we need to
					 * create our own.
					 *
					 * Here, use the wp.media library to define the settings of the Media
					 * Uploader implementation by setting the title and the upload button
					 * text. We're also not allowing the user to select more than one image.
					 */
					file_frame = wp.media.frames.file_frame = wp.media(
						{
							title:    "Insert Background Image",    // For production, this needs i18n.
							button:   {
								text: "Upload Background Image"     // For production, this needs i18n.
							},
							multiple: false
						}
					);

					/**
					 * Setup an event handler for what to do when an image has been
					 * selected.
					 */
					file_frame.on(
						'select',
						function () {

							image_data = file_frame.state().get( 'selection' ).first().toJSON();

							$( "p.doko-steps-background-image" ).empty().append( "<img src='" + image_data.url + "' alt='doko_steps_background_image'/>" );
							$( "p.doko-steps-background-image" ).append( "<input type='hidden' name='doko[steps-background-image]' value='" + image_data.id + "' />" );
						}
					);

					// Now display the actual file_frame
					file_frame.open();
				}
			);

			if ( $( 'textarea.doko-custom-css' ).is(':visible') ) {
				wp.codeEditor.initialize( $( 'textarea.doko-custom-css' ), cm_settings );
			}

	
			$( document ).on(
				"click",
				"#doko-remove-background-image",
				function (e) {
					e.preventDefault();
					$( "p.doko-steps-background-image" ).empty();

				}
			);

		}
	);

})( jQuery );
