
var netWageCalculator = (function(){
	'use strict';

	var $myBtn;
	var $workHoursInput;
	var $eurPerHourInput;
	var $resultArea;
	var netWage;
	var wageCalculated;
	
	

	var init = function(){

		_domCache();
		_bindEvents();
		_makeSubscription();
		
	}

	var _makeSubscription = function(){
		wageCalculated = pubSub.subscribe('wageCalculated', function( obj ){
			
			var x = obj.x;
			var y = obj.y;
			var multiplier = obj.multiplier;

			console.log( (x + y) * multiplier );
		})
	}
	
	var _domCache = function(){
		
		$myBtn = $('.myBtn');
		$workHoursInput = $('#workHour');
		$eurPerHourInput = $('#eurPerHour');
		$resultArea = $('#result');
		
	}
	
	var _bindEvents = function(){
		
		$myBtn.click( function(){
			
			_calculateNetWage();
			
		} );


		$( document ).keypress( function( e ){
			/**
			*ENTER button
			*/
			if( e.which == 13 ){
				$myBtn.trigger('click');
			}

		} )
		
	}
	
	var _render = function(){
		
		$resultArea.text( netWage );
		pubSub.publish('wageCalculated', {
			x: 5,
			y: 10,
			multiplier: 10
		});

		pubSub.publish('wageCalculated', {
			x: 5,
			y: 5,
			multiplier: 10
		});
		
	}
	
	var _calculateNetWage = function(){
		
		var hours = $workHoursInput.val();
		var eurPerHour = $eurPerHourInput.val();
		var grossWage;
		var levies;
		var tax;
		
		if( hours < 0 || eurPerHour < 0 )
			return;
		
		grossWage = hours * eurPerHour;
		levies = ( grossWage - 200 ) * 0.07;
		
		if( levies < 0 )
			levies = 0;
			
		tax = ( grossWage - 316.94 - levies ) * 0.19;
		
		if( tax < 0 )
			tax = 0;
		/**
		* 2 decimal places
		*/
		netWage = (grossWage - levies - tax).toFixed( 2 );
		
		_render();
	}
	
	return{
		init: init
	}
	
})();


$( document ).ready( function(){
	netWageCalculator.init();
} );

