if(app_version == undefined)
	app_version = '2.2';
	
//a class type object that will be used for scrolling 
function Scroller() {
	//update function that stores the current position of the facebook scrollbar
	this.update				= update;
	function update() {
		var updatedPos	= null;
		var thisObj		= this;
		FB.Canvas.getPageInfo(function(pageInfo) {
			updatedPos = pageInfo.scrollTop;
			thisObj.currentPosition = updatedPos;
			return false;
		});
	}
	//property that always stores the current position
	this.currentPosition	= 0;//default is the most current location


	this.scrollTo			= scrollTo;
	function scrollTo(position) {
		var speed = this.speed
		//thanks to http://stackoverflow.com/questions/7193425/how-do-you-animate-fb-canvas-scrollto
		FB.Canvas.getPageInfo(function(pageInfo) {
			$({y: pageInfo.scrollTop}).animate(
				{y: position},
				{duration: speed, step: function(offset) {
					FB.Canvas.scrollTo(0, offset);
				}
			});
		});
		update(); //update to where you are now
	};

	this.speed					= 500;
	this.updateSpeed			= updateSpeed;
	function updateSpeed(newSpeed) {
		this.speed			= newSpeed;
	};

	this.attachToModal			= attachToModal;
	function attachToModal(selector, function_opened, function_closed) {
		var scroller = this;
		var previous;
		$("#"+selector).bind('opened', function() {
			previous		= scroller.currentPosition; //save the stop you were at
			destination		= $(this).offset().top;		//get the spot you are going to
			scroller.scrollTo(destination);				//scroll to the destination
			if(typeof function_opened === "function"){
				function_opened();
			}
		});
		$("#"+selector).bind('closed', function() {
			scroller.scrollTo(previous);				//return to where you were at
			if(typeof function_closed === "function"){
				function_closed();
			}
		});
	}
}

//class type that can be used for counting money without worry about floating point values
function ObjectComputer(thingsToBeAltered) {
	//property that stores the total for this type of counter
	this.total			= 0;


	/**********MONEY**********/
	//function that adds to the total
	this.countMoneyObj	= countMoneyObj;
	function countMoneyObj(priceObj, thisObj) {
		var countObj = priceObj;

		//first decide what obj was placed in (could be a string)
		if(typeof countObj == "string"){
			countObj = parseFloat(countObj);
		}

		//multiply total by 100 and countObj by 100 and count as pennies
		var newTotal		= 100*thisObj.total + 100*countObj;
		thisObj.total		= (newTotal/100).toFixed(2);
	}

	//function that adds an array of things to be added in this instance's total
	this.countMoneyObjs		= countMoneyObjs;
	function countMoneyObjs() {
		var thisObj		= this;
		for(obj in thingsToBeAltered){
			countMoneyObj(thingsToBeAltered[obj], thisObj);
		}
		return this.total;
	}

	/**********TIME**********/
	//function that subtracts time
}

//class type for logging functions. should make it much easier!
function Log() {
	this.on 				= true;
	this.log 				= log;
	function log(thingToLog, message) {
		if(this.on) {
			//check if debugging on a windows machine
			if ( ! window.console ) {
				console = { 
					log: function(){}
				} 
			}
			
			if(message != null)
				console.log(message);
			else
				console.log("Logging something:");

			console.log(thingToLog);
		}
	}

	this.on			= on;
	function on() {
		this.on = true;
	}

	this.off		= off;
	function off() {
		this.on = false;
	}
}

//class for wrapping facebook API calls
function fbObj(appID, channelURL) {
	this.inited		= false;
	this.loggedIn	= false;
	this.appId		= appID;
	this.channelURL = channelURL;

	this.init 		= init;
	function init() {
		this.inited = true;
		FB.init({
			appId  : this.appID,
			status : true,					// check login status.
			cookie : true,					// enable cookies to allow the server to access the session
			xfbml  : true, 					// parse XFBML
			frictionlessRequests : true, 	// allows user option to always confirm app requests
			channelUrl:	this.channelURL,
			oauth  : true,
			version: 'v' + app_version
		});
	}//init

	this.getStatus 	= getStatus;
	function getStatus(perms_given_func, perms_denied_func, perms_required, selector) {
		//check if they have called facebook init; if they haven't throw a warning and then init
		if(!this.inited){
			//console.log("You are trying to call FB.init before FB.login. I just called it for you, though. HIGH FIVE");
			this.init();
		}

		FB.getLoginStatus(function(response) {
			//check if we need the selector
			if(selector == null || selector == undefined) {
				selector = response;
			}

			//check the users status before calling login to avoid the flash when asking for permissions that we already have
			if(response.status 			== 'connected') { 			//they are already logged in, no need to call FB.login
				this.loggedIn = true;
				perms_given_func(response);
			} else if(response.status 	== 'not_authorized') {		//they are logged in, but have not given us permissions.
				FB.login(function(response) {
					if (response.authResponse) {
						this.loggedIn = true;
						perms_given_func(response);
					} else { 										//they denied permissions to grab their friends
						this.loggedIn = false;
						perms_denied_func(selector);
					}
				}, {scope: perms_required});
			} else {												//they aren't logged in
				FB.login(function(response) {
					if (response.authResponse) {
						this.loggedIn = true;
						perms_given_func(response);
					} else {										//they denied permissions to grab their friends
						this.loggedIn = false;
						perms_denied_func(selector);
					}
				}, {scope: perms_required});
			}
		});
	} //getPerms


	this.removePerms	= removePerms;
	function removePerms() {
		this.api(
			"/me/permissions",		//path
			"DELETE",				//method
			null,					//params
			function(response) {},	//callback_func
			function(selector) {	//perms_denied_func
				alert('permissions were denied');
			},
			null,					//perms_required
			null					//selector
		);
	}//removePerms

	this.api 			= api;
	function api(path, method, params, callback_func, perms_denied_func, perms_required, selector) {
		this.getStatus(
			function() {			//perms_given_func
				FB.api(path, method, params, callback_func);
			}, 
			perms_denied_func,		//perms_denied_func
			perms_required,			//perms_required
			selector				//selector
		);
	}//api

}
