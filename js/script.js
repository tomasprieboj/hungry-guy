
var HungryGuy = (function(){
	'use strict';

	var menuData;
	var templates;
	var $body;

	var init = function(){
		
		_domCache();
		_bindEvents();
		_getMenuJSon();
	}

	var _domCache = function(){

		$body = $("body");
	}

	var _bindEvents = function(){

		_bindLoadingAnimation();
	}

	var _bindLoadingAnimation = function(){

		$(document).on({
			ajaxStart: function() { 
				console.log("start");
				$body.addClass("loading");
			},
			ajaxStop: function() { 
				console.log("stop");
				$body.removeClass("loading"); 
			}    
		});
	}

	var _getMenuJSon = function(){

		$.ajax({
			type:'GET',
			url:'getMenuValues.php',
			dataType: 'json',
			success: function( data ){
				menuData = data;
				_loadTemplate();
			},
			error: function(/*jqXHR, exception"*/ts){
				console.log( "Error send" + ts.responseText );
			}		
		});
	}

	var _loadTemplate = function(){

		$.get( 'js/templates/templates.mst', function( templ ) {
			templates = templ;
			_render();
		} );

	}
	
	var _render = function(  ){
		
		var keys = Object.keys( menuData );
		keys.forEach( function( key ) {
			_renderSpecific( key );
		}, this );
		
	}

	var _renderSpecific = function( name ){

		var template = $( templates ).filter( '#' + name + "Tmplt" ).html();
		var templateData = {};
		templateData[ name ] = menuData[ name ];
	
		var render = Mustache.render(template, templateData);
		$('#' + name).append(render);

	}
	
	return {
		init: init
	}
})();


$( document ).ready( function(){
	HungryGuy.init();
} );

