/**
 * Multi Image Upload for WPForms — frontend.
 *
 * Pre-uploads images to a temp dir via admin-ajax on selection and
 * stores returned tokens in hidden inputs, so the field works with
 * WPForms AJAX submission on or off.
 */
( function () {
	'use strict';

	if ( typeof window.miuwpfVars === 'undefined' ) {
		return;
	}

	var vars = window.miuwpfVars;

	function sprintf( str, val ) {
		return str.replace( /%[ds]/, val );
	}

	function initWrap( wrap ) {
		var input   = wrap.querySelector( '.miuwpf-input' );
		var btn     = wrap.querySelector( '.miuwpf-btn' );
		var list    = wrap.querySelector( '.miuwpf-list' );
		var tokens  = wrap.querySelector( '.miuwpf-tokens' );
		var max     = parseInt( wrap.dataset.max, 10 ) || 5;
		var maxSize = parseInt( wrap.dataset.size, 10 ) || 5242880;
		var exts    = ( wrap.dataset.exts || '' ).toLowerCase().split( ',' );
		var errorEl = null;

		if ( ! input || ! btn || ! list || ! tokens ) {
			return;
		}

		// Unwrap JCF-style fake UI if a theme wrapped our input.
		// Such libraries init on DOM-ready, possibly after this script,
		// so check now, on ready, on load, and once more after a delay.
		function unjcf() {
			var w = input.closest( '.jcf-file' );
			if ( w ) {
				w.parentNode.insertBefore( input, w );
				w.remove();
				input.className = 'miuwpf-input jcf-ignore';
				input.hidden = true;
				input.removeAttribute( 'style' );
			}
		}
		unjcf();
		document.addEventListener( 'DOMContentLoaded', unjcf );
		window.addEventListener( 'load', unjcf );
		setTimeout( unjcf, 800 );

		function showError( msg ) {
			clearError();
			errorEl = document.createElement( 'div' );
			errorEl.className = 'miuwpf-error';
			errorEl.textContent = msg;
			wrap.appendChild( errorEl );
			setTimeout( clearError, 5000 );
		}

		function clearError() {
			if ( errorEl ) {
				errorEl.remove();
				errorEl = null;
			}
		}

		function count() {
			return list.children.length;
		}

		function removeItem( item ) {
			var i;
			var inputs;
			if ( item.dataset.token ) {
				inputs = tokens.querySelectorAll( 'input' );
				for ( i = 0; i < inputs.length; i++ ) {
					if ( inputs[ i ].value === item.dataset.token ) {
						inputs[ i ].remove();
						break;
					}
				}
			}
			item.remove();
		}

		function upload( file, item, status ) {
			var fd = new FormData();
			fd.append( 'action', 'miuwpf_upload' );
			fd.append( 'nonce', vars.nonce );
			fd.append( 'file', file );

			window.fetch( vars.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' } )
				.then( function ( r ) {
					return r.json();
				} )
				.then( function ( res ) {
					if ( res && res.success && res.data && res.data.token ) {
						var h = document.createElement( 'input' );
						h.type  = 'hidden';
						h.name  = wrap.dataset.name;
						h.value = res.data.token;
						tokens.appendChild( h );
						item.dataset.token = res.data.token;
						item.classList.remove( 'is-loading' );
						status.textContent = '';
					} else {
						removeItem( item );
						showError( ( res && res.data && res.data.message ) || vars.i18n.uploadFailed );
					}
				} )
				.catch( function () {
					removeItem( item );
					showError( vars.i18n.network );
				} );
		}

		function addFiles( files ) {
			clearError();
			Array.prototype.forEach.call( files, function ( file ) {

				if ( count() >= max ) {
					showError( sprintf( vars.i18n.maxFiles, max ) );
					return;
				}

				var ext = ( file.name.split( '.' ).pop() || '' ).toLowerCase();

				if ( file.type.indexOf( 'image/' ) !== 0 || exts.indexOf( ext ) === -1 ) {
					showError( sprintf( vars.i18n.badType, file.name ) );
					return;
				}

				if ( file.size > maxSize ) {
					showError( sprintf( vars.i18n.tooLarge, file.name ) );
					return;
				}

				var item = document.createElement( 'div' );
				item.className = 'miuwpf-item is-loading';

				var name = document.createElement( 'span' );
				name.className = 'miuwpf-name';
				name.textContent = file.name;

				var status = document.createElement( 'span' );
				status.className = 'miuwpf-status';
				status.textContent = vars.i18n.uploading;

				var x = document.createElement( 'button' );
				x.type = 'button';
				x.className = 'miuwpf-remove';
				x.setAttribute( 'aria-label', vars.i18n.remove );
				x.textContent = '\u00d7';
				x.addEventListener( 'click', function ( e ) {
					e.stopPropagation();
					removeItem( item );
				} );

				item.appendChild( x );
				item.appendChild( name );
				item.appendChild( status );
				list.appendChild( item );

				upload( file, item, status );
			} );
		}

		btn.addEventListener( 'click', function () {
			input.click();
		} );

		input.addEventListener( 'change', function () {
			addFiles( input.files );
			input.value = '';
		} );

		function isFileDrag( dataTransfer ) {
			return !! dataTransfer && Array.prototype.indexOf.call( dataTransfer.types || [], 'Files' ) !== -1;
		}

		// Counts nested enter/leave pairs so the highlight doesn't flicker
		// as the drag moves across the button, hint, and list children.
		var dragDepth = 0;

		wrap.addEventListener( 'dragenter', function ( e ) {
			if ( ! isFileDrag( e.dataTransfer ) ) {
				return;
			}
			e.preventDefault();
			dragDepth++;
			wrap.classList.add( 'is-dragover' );
		} );

		wrap.addEventListener( 'dragover', function ( e ) {
			if ( ! isFileDrag( e.dataTransfer ) ) {
				return;
			}
			e.preventDefault();
			e.dataTransfer.dropEffect = 'copy';
		} );

		wrap.addEventListener( 'dragleave', function ( e ) {
			if ( ! isFileDrag( e.dataTransfer ) ) {
				return;
			}
			dragDepth = Math.max( 0, dragDepth - 1 );
			if ( dragDepth === 0 ) {
				wrap.classList.remove( 'is-dragover' );
			}
		} );

		wrap.addEventListener( 'drop', function ( e ) {
			if ( ! isFileDrag( e.dataTransfer ) ) {
				return;
			}
			e.preventDefault();
			dragDepth = 0;
			wrap.classList.remove( 'is-dragover' );
			addFiles( e.dataTransfer.files );
		} );
	}

	function init() {
		document.querySelectorAll( '.miuwpf-wrap' ).forEach( initWrap );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
