
var pubSub = ( function(){
    'use strict';

    var events;
    var hOP;

    var init = function(){
        events = {};
        hOP = events.hasOwnProperty;
    }

    var subscribe = function( event, callback ){
        /**
         * if there is no such event create one
         */
        if( !hOP.call( events, event ) )
            events[ event ] = [];
        /**
         * add callpackt to specific event and get its index in array
         */
        var index = events[ event ].push( callback ) - 1;
        /**
         * handler for removing topics
         */
        return {
            remove: function(){
                delete events[ event ][ index ];
            }
        }
    }


    var publish = function( event, info ){
        /**
         * if there is no such event then return
         */
        if( !hOP.call( events, event ) )
            return;
        /**
         * fire all callbacks on specific event
         */
        events[ event ].forEach( function( callback ){
            //callback( info != undefined ? info : {} );
            callback( info );            
        } );
    }

    return {
        init: init,
        subscribe: subscribe,
        publish: publish
    };

} )();

$( document ).ready( function(){
    pubSub.init();
} );