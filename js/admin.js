(function ($){
	function empty( v )
	{
		return typeof v != 'undefined' && /^\s*$/.test( v );
	};
	
	function clear()
	{
		// Clear
		$( '#cpts_screen_width' ).val( '' );
		$( '#cpts_predefined_screen option:first' ).prop( 'selected', true );
	};
	
	window[ 'cptsDisplayPreview' ] = function()
	{
		var t = $( '[name="cpts_theme"]:checked' ),
			w = $( '#cpts_screen_width' ).val(),
			p = $( '.cp-preview-container' );
		
		if( empty( w ) )
		{
			alert( 'The screen width is required' );
			return;
		}
		
		p.show().html( '' );
		
		$( '<iframe width="' + w + '" height="480px" src="' + cptsObj[ 'home' ] + '/?theme_switch_preview=1&theme_switch_stylesheet=' + escape( t.attr( 'stylesheet' ) ) + '"></iframe>' ).appendTo( p );
	};
	
	window[ 'cptsLoadScreenSizes' ] = function( e )
	{
		var o = $( e.options[ e.selectedIndex ] );
		if( o.attr( 'w' ) )
		{
			$( '#cpts_screen_width'  ).val( o.attr( 'w' ) );
		}
		
	};
})(jQuery);