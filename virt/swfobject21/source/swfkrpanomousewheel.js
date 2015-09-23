/*
	swfkrpanomousewheel.js (partially based on SWFMacMouseWheel)
	Version: 1.0
	for SWFObject: 2.x

	- mousewheel html scrolling fix
	- mousewheel fix for mac osx flashplayer

	Functions:
	- when a krpano flash object has the focus  -> do zooming in krpano and disable scrolling on the html site
	- when NO krpano flash object has the focus -> scroll normally in the html site

	Mac Flashplayer / Safari Windows bugfix:
	- send mousewheel events to flash

	NOTE: this replaces SWFMacMouseWheel, don't use both!!!

	tested sucessfully on:
	- Firefox 2 (Windows)
	- Firefox 3 (Windows)
	- IE6
	- IE7
	- Opera 9.5 (Windows)
	- Opera 9.5 (Mac)
	- Firefox 2 (Mac)
	- Firefox 3 (Mac)

	tested buggy on:
	- Safari 3.1.2 (Mac)     - problems with zooming/scrolling via touchpad, external mouse okay
	- Safari 3.1.2 (Windows) - no known bugfix to solve correct mousewheel handling!!!
	
	TODO: compress code
*/

var swfkrpanomousewheel = function()
{
	if( !swfobject ) 
	{
		alert("no swfobject");
		return null;
	}
	
	var isMac    = navigator.appVersion.toLowerCase().indexOf("mac")    != -1;
	var isSafari = navigator.appVersion.toLowerCase().indexOf("safari") != -1;

	var regObjArr = [];
	
	var overobj = null;
	var safari_pageXOffset = 0;
	var safari_pageYOffset = 0;
	
	
	var deltaDispatcher = function(event)
	{
		if (!event)
			event = window.event;
	     
		var delta = 0;
		
        if (event.wheelDelta)
        {
			delta = event.wheelDelta/120.0;
			
			if (window.opera) 
			{
				if (isMac == false)
				{
					delta = -delta;
				}
			}
        } 
        else if (event.detail) 
        {
            delta = -event.detail;
        }
        
        var stopevent = false;
		var obj;
		
		for(var i=0; i<regObjArr.length; i++ )
		{
			obj = swfobject.getObjectById(regObjArr[i]);
			
			if (isSafari && !isMac)
        	{
        		// windows safari
        		if( typeof( obj.externalMouseEvent ) == 'function' ) 
				{
					obj.externalMouseEvent( delta );
				}
        	}
        	else
        	{
        		if (isMac || isSafari)
        		{
        			// mac flashplayer mousewheel bugfix
        			if (overobj == obj.getAttribute('id'))
	       			{
						if( typeof( obj.externalMouseEvent ) == 'function' ) 
						{
							obj.externalMouseEvent( delta );
							
							document[ obj.getAttribute('id') ].focus();
							
							stopevent = true;
							break;
						}
					}
				}
					
				if ( obj.get("has_mousewheel_event()") == "true" )
				{
					stopevent = true;
					break;
				}
			}
		}
		
		if (overobj)
		{
			stopevent = true;
		}
		
		if (stopevent)
		{
			if (isSafari && isMac)
			{
				// mac safari touchpad scrolling bugfix, doesn't work perfectly
				window.scrollTo(safari_pageXOffset, safari_pageYOffset);
			}
		
			if (event.stopPropagation)
		   		event.stopPropagation();
	
			if (event.preventDefault)
				event.preventDefault();
	
			event.cancelBubble = true;
			event.cancel       = true;
			event.returnValue  = false;
		}
	}
	
	
	
	var SWFkrpanoMouseWheel_overEvent = function(event)
	{
		overobj = event.target.id;
	
		if (isSafari && isMac)
		{
			// mac safari touchpad scrolling bugfix
			safari_pageXOffset = window.pageXOffset;
			safari_pageYOffset = window.pageYOffset;
		}
	}
	
	var SWFkrpanoMouseWheel_outEvent = function(event)
	{
		overobj = null;
	}

		
	var SWFkrpanoMouseWheel_registerEvents = function()
	{
		var i = 0;
	   	var cnt = regObjArr.length;
	
		for (i=0; i<cnt; i++)
		{
			obj = swfobject.getObjectById(regObjArr[i]);
			
			if (obj)
			{
				if (!overobj)
				{
					// select 1. object on start
					overobj = objid;
				}
			
				if (isSafari && !isMac)
				{
					// no known bugfix for safari on windows at the moment
					//
					// this browser is too buggy:
					// - no mousewheel events are sent to flash objects
					// - no onmouseover/onmouseout events on flash objects to solve the first bug
					// - no focus events
					//
					// solution at the moment:
					// - sent mousewheel events to all flash objects
					// - that allows using the mousewheel with single flash objects
	
					if ( obj.enable_mousewheel_js_bugfix )
					{
						// needed krpano 1.0.6 or krpano 1.1 beta 1b
						obj.enable_mousewheel_js_bugfix();
					}
				}
				else
				{
					obj.onmouseover = SWFkrpanoMouseWheel_overEvent;
					obj.onmouseout  = SWFkrpanoMouseWheel_outEvent;
				}
			}
		}
	}	
	
	var SWFkrpanoMouseWheel_registerEvents_delayed = function()
	{
		setTimeout( SWFkrpanoMouseWheel_registerEvents, 1000);
	}

	
	if (window.addEventListener) 
	{
		window.addEventListener('DOMMouseScroll', deltaDispatcher, false);
	}
	
	if (window.opera)
	{
		window.attachEvent("onmousewheel", deltaDispatcher);
	}
	else
	{
		window.onmousewheel = document.onmousewheel = deltaDispatcher;
	}
	
	if (window.opera || isMac || isSafari)
	{
		// opera / mac / safari fixes

		var oldonload = window.onload;

		if (typeof window.onload != 'function')
		{
			window.onload = SWFkrpanoMouseWheel_registerEvents_delayed;
		}
		else
		{
			window.onload = function()
							{
								oldonload();
								SWFkrpanoMouseWheel_registerEvents_delayed();
							}
		}
	}	
			
	return	{
				registerObject: function(objectIdStr)
				{
					regObjArr[regObjArr.length] = objectIdStr;
				}
			};
}();




