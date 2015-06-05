(function( $ ){
	$( document ).ready( function() {
		/* 
		 *add notice about changing in the settings page 
		 */
		$( '#mlq-mail input, #mlq-mail .mailplugins select' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#mlq-settings-notice' ).css( 'display', 'block' );
			};
		});



		/**
		 * calculte maximum number of sent mails and show confirm-window if user enter too large value
		 */
		var runTime      = $( '#mlq_mail_run_time' ),
			runTimeVal   = runTime.val(),
			sendCount    = $( '#mlq_mail_send_count' ),
			sendCountVal = sendCount.val(),
			number       = 0;
		runTime.change( function() {
			if ( parseInt( $( this ).val() ) < 1 || !( /^\s*(\+|-)?\d+\s*$/.test( $( this ).val() ) ) ) {
				$( this ).val( '1' ).text( '1' );
			}
			if ( parseInt( $( this ).val() ) > 360 ) {
				if( ! confirm( mlqScriptVars['toLongMessage'] ) ) {
					$( this ).val( runTimeVal ).text( runTimeVal );
				}
			}
			number = Math.floor( ( 60 / $( this ).val() )  * parseInt( sendCount.val() ) );
			$( '#mlq-calculate' ).text( '' ).text( number );
		});
		sendCount.change( function() {
			if ( parseInt( $( this ).val() ) < 1 || !( /^\s*(\+|-)?\d+\s*$/.test( $( this ).val() ) ) ) {
				$( this ).val( '1' ).text( '1' );
			}
			if ( parseInt( $( this ).val() ) > 50 ) {
				if( ! confirm( mlqScriptVars['toLongMessage'] ) ) {
					$( this ).val( sendCountVal ).text( sendCountVal );
				}
			}
			number = parseInt( ( 60 / runTime.val() ) * $( this ).val() );
			$( '#mlq-calculate' ).text( '' ).text( number );
		});

		/**
		 * Show/hide some blocks on plugin settings page
		 */
		var phpRadio    = $( '#mlq_wp_mail_radio, #mlq_php_mail_radio' ),
			smtpRadio   = $( '#mlq_smtp_mail_radio' ),
			smtpOptions = $( '.mlq_smtp_options' ),
			delCheck	= $( '#mlq_delete_old_mail' ),
			delOptions	= $( '.mlq_delete_old_mail_option' );
		$( '#mlq_change_options' ).click( function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.mlq_ad_opt' ).each( function() {
					if( $( this ).hasClass( 'mlq_smtp_options' ) ) {
						if ( smtpRadio.is( ':checked' ) ) {
							$( this ).show();
						}
					} else if( $( this ).hasClass( 'mlq_delete_old_mail_option' ) ) {
						if ( delCheck.is( ':checked' ) ) {
							$( this ).show();
						}
					} else {
						$( this ).show();
					}
				})
			} else {
				$( '.mlq_ad_opt' ).hide();
			}
		});
		phpRadio.click( function() {
			smtpOptions.hide();
		});
		smtpRadio.click( function() {
			smtpOptions.show();
		});
		delCheck.click( function() {
			if ( $( this ).is( ':checked' ) ) {
				delOptions.show();
			} else {
				delOptions.hide();	
			}
		});

		/**
		 * hide bottom line of mail receivers list with headings
		 * if the height of the screen is enough to display the whole list
		 */
		var userCnt		= $( '#mlq-total-users' ),
			userCntVal 	= userCnt.val();
		if( $(window).height() > 117 + 49 * userCntVal ) {
			$( '.mlq-receivers-list tfoot' ).hide();
		}
		
		/**
		 * scroll to receivers list table
		 */
		if( $( '.mlq-receivers-list' ).length ) {
			$( 'html, body' ).animate({
				scrollTop: $( '.mlq-receivers-list' ).offset().top - 30 + 'px'
			}, 500 );
		}
	});
})(jQuery);