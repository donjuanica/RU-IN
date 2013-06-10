// $Id: dndmodule.js 5284 2006-11-22 19:04:21Z deploy $
/*
 * <pre>
 * Copyright (c) 2004-2006 by Zapatec, Inc.
 * http://www.zapatec.com
 * 1700 MLK Way, Berkeley, California,
 * 94709, U.S.A.
 * All rights reserved.
 * </pre>
 */
// ---------------------------------------- Additional Utils Functions Section -----------------------------------
/**
 * Moves elelement to the specified position. To be moved element should be positioned absolute 
 * and should be child of 'within' element.
 * @param el [HTMLElement] - the element to move
 * @param x [number] - the x coordinate to move to
 * @param y [number] - the y coordinate to move to
 * @return true if successful, otherwise false
 */
Zapatec.Utils.moveElementTo = function(el, x, y) {
	if (!el.within) {
		return false;
	} else {
		var within = el.within;
	}
	//if value of coordinate is "center" - let's calculate it.
	if (x == 'center' || y == 'center') {
		var br = {};//scrolling
		var size = {};//container size, for body its window
		if (within == document.body) {
			size = Zapatec.Utils.getWindowSize(); 
			br.y = Zapatec.Utils.getPageScrollY();
			br.x = Zapatec.Utils.getPageScrollX();
		} else {
			size.width = within.offsetWidth;
			size.height = within.offsetHeight;
			br.y = within.scrollTop;
			br.x = within.scrollLeft;
		}
	 	if (x == 'center') { 
	 		if (screen.width) { 
				x = Math.round((size.width -  el.offsetWidth) / 2 + br.x); 
	 		} 
	 	} 
	 	if (y == 'center') { 
	 		if (screen.height) { 
				y = Math.round((size.height - el.offsetHeight) / 2 + br.y); 
	 		} 
	 	}
	}
	//setting of coordinates
	if (x || x === 0) {
		el.style.left = x + "px";
	}
	if (y || y === 0) {
		el.style.top = y + "px";
	}
	
	return true;
};

/**
 * Prepares elelement to be moved by Zapatec.Utils.moveElementTo. To be moved element should be positioned 
 * absolute and should be direct child of the BODY, should have margin set to 0px.
 * @param el [HTMLElement] - the element to move
 * @param restorer [SRProp object] - the SRProp object you want to use to restore our 
 * elements properties, if you have already one.
 * @param within [HTML element] - if you want to move the element within some other element, 
 * pass the refference to it through this var (default is document.body)
 * @return true if successful, otherwise false
 */
Zapatec.Utils.prepareToMove = function(el, restorer, within) {
	//Checking if the element is of the right type
	if (!el || typeof el != "object" || (typeof el == "object" && !el.style)) {
		return false;
	}
	//TODO : don't make BODY "relative"!!!
	if (!within) {
		within = document.body;
	}
	if (el.within == within) {
		return true;
	}
	if (!restorer || restorer.obj != el) {
		restorer = new Zapatec.Utils.SRProp(el);
	} 
	//To make it work we need the 'within' to be positioned relatively
	if (within != document.body && within.style.position != "absolute") {
		within.style.position = "relative";
	}
	//This is needed for the element to be put into the right place.
	//Need to preserve elements sizes
	if (!(/\d+px/i).test(el.style.width)) {
		restorer.saveProp("style.width");
		var width = 0;
		el.style.width = (width = el.offsetWidth) + "px";
		//we need to set the right width
		if (width != el.offsetWidth) {
			var diff = (width - (el.offsetWidth - width));
			if (diff < 0) diff = 0;
			el.style.width = diff + "px";
		}
	}
	if (el.style.height != "" && !(/\d+px/i).test(el.style.height)) {
		restorer.saveProp("style.height");
		var height = 0;
		el.style.height = (height = el.offsetHeight) + "px";
		//we need to set the right height
		if (height != el.offsetHeight) {
			var diff = (height - (el.offsetHeight - height));
			if (diff < 0) diff = 0;
			el.style.height = diff + "px";
		}
	}
	//Need to preserve elements position after we prepare it for move
	if (within != document.body) {
		var pos1 = Zapatec.Utils.getAbsolutePos(within);
	} else {
		var pos1 = {x : 0, y : 0};
	}
	var pos2 = Zapatec.Utils.getAbsolutePos(el);
	var x = pos2.x - pos1.x;
	var y = pos2.y - pos1.y;
	restorer.saveProps("style.left", "style.top");
	el.style.left = x + "px";
	el.style.top = y + "px";
	//Checks if the element is positioned absolute.
	if (el.style.position != "absolute") {
		restorer.saveProp("style.position");
		el.style.position = "absolute";
	}
	//no other elments in the parent elements tree can not have any positioning,
	//except the 'within' ofcourse :)
	var parent = el.parentNode;
	while(parent != within && parent != document.body) {
		if (!parent || (parent.style && (parent.style.position == "absolute" || parent.style.position == "relative"))) {
			parent = null;
			break;
		}
		parent = parent.parentNode;
	}
	//withon should be direct parent of the draggable element
	if (el.parentNode != within) {
		restorer.saveProps("parentNode", "nextSibling");
		within.appendChild(el);
	}
	//Checks if the elements has margin 0px.
	if (!/^(\d+px )*\d+px$/.test(el.style.margin)) {
		restorer.saveProp("style.margin");
		el.style.margin = "0px";
	}
	
	el.within = within;
	restorer.saveProp("within.style.position");
	
	return true;
};

/**
 * Restores all the changes made in thr elelement by Zapatec.Utils.preapareToMove. 
 * absolute and should be direct child of the BODY, should have margin set to 0px.
 * @param el [HTMLElement] - the element to move
 * @return true if successful, otherwise false
 */
Zapatec.Utils.restoreOfMove = function(el) {
	if (!el.within) return false;
	//restoreing properies if everything is okay
	el.restorer.restoreProps(
		"style.position",
		"parentNode",
		"style.margin",
		"style.width",
		"style.height",
		"nextSibling",
		"within.style.position",
		"style.left",
		"style.top"
	);
	el.within = null;
	return true;
};

/** 
 * Gets the element position within another one. This function implementation
 * fully depends on three above functions and currently will be used for another method below.
 * To work this function has requirements to the element and its 'within' element:
 * element should be positioned absolutely, have margin 0px;
 * 'within' element should be positioned relatively;
 * @param el [HTML element] - we are getting the position of this element
 * @return object with x and y properties or false if failed.
 */
Zapatec.Utils.getElementPos = function(el) {
	if (!el.within) {
		var within = document.body;
	} else {
		var within = el.within;
	}
	if (!el.within || el.within != within) return false;
	var ret = {};
	ret.x = parseInt(el.style.left, 10);
	ret.y = parseInt(el.style.top, 10);
	
	return ret;
};

/**
 * Moves element by the given offset in the 'within' element.
 * Function is closely connected to above functions.
 * @param el [HTML element] - element to move
 * @param offsetX [number] - x offset to move for
 * @param offsetY [number] - y offset to move for
 * @return true if succeeds, otherwise false
 */
Zapatec.Utils.moveElementFor = function(el, offsetX, offsetY) {	
	if (!el.within) {
		return false;
	} else {
		var within = el.within;
	}
	var oldPos = Zapatec.Utils.getElementPos(el);
	if (oldPos) {
		return Zapatec.Utils.moveElementTo(el, oldPos.x + offsetX, oldPos.y + offsetY);
	} else {
		return false;
	}
};
/**
 * returns element Array which has attribute 'attr' with value 'val'
 * by giving 'el' you can finetune your search
 * @param attr [string] attribute to search
 * @param val [string OR number] searched attributes value, ignored if 0.
 * @param el [HTMLElement] reference to the element.
 * @param recursive [boolean] searches in childs.
 */
Zapatec.Utils.getElementsByAttribute = function(attr, val, el, recursive){
	if (!attr) return false;
	el = Zapatec.Utils.idOrObject(el);
	el || (el = window.document.body);
	var a = el.firstChild; retArray = [];
	while (a) {
		if (a[attr]) {
			if (val) {
				if (a[attr] == val) {
					retArray = retArray.concat([a]);
				}
			} else {
				retArray = retArray.concat([a]);
			}
		};
		if (recursive && a.hasChildNodes()) {
			retArray = retArray.concat(Zapatec.Utils.getElementsByAttribute(attr, val, a, recursive));
		}
		a = a.nextSibling
	};
	return retArray
};

/**
 * verifies is the given variable object or object id and returns the object or false if either
 * @param el [HTMLElement or string] reference to the element or element id
 * @param errorMessage [string] message shown in statusbar if el is not correct object
 * @param errorAction [function reference] called function if el is not correct object
 * @return the needed element
 */
Zapatec.Utils.idOrObject = function(el, errorMessage, errorAction){
	//no element no result :)
	if (!el) return false;
	// if its the id then getting it by it
	if (typeof(el) == 'string') {
		el = document.getElementById(el) || el;
	}
	//if still this isn't an HTMLElement firing the error, othewise returning it
	if (typeof(el) != 'object' || !el.tagName) {
		if (errorAction) errorAction(errorMessage);
		return false
	} else {
		return el
	}
};

/**
 * Replaces the image with DIV element, and sets the image as background-image.
 * this is not good for images with changed size, because you cant change the background-image size and it will tile.
 * @param el [HTMLElement] reference to the element
 * @return the needed element
 */
Zapatec.Utils.img2div = function(el){
	//FIX ME: maybe this can be changed to div/img from just div with background
	if (el.nodeName.match(/img/i)) {
		var div = document.createElement('div');
	    // Set div width and height when image is loaded
	    var objImage = new Image();
	    objImage.onload = function() {
	      div.style.width = this.width + 'px';
	      div.style.height = this.height + 'px';
	      div.style.fontSize = '0px';
	      this.onload = null;
	    };
	    objImage.src = el.src;
	    // Replace image with the div
	    div.style.backgroundImage = 'url(' + el.src + ')';
	    div.style.backgroundColor = 'transparent';
		var id = el.id;
		el.parentNode.replaceChild(div, el);
		div.id = id;
		return div
	} else {
		return el
	}
};

//This is an HTML element used as a cover to imitate Dragging correctly.
Zapatec.Utils.cover = Zapatec.Utils.createElement("div");
Zapatec.Utils.cover.style.overflow = "hidden";
//This is the workaround for IE who doesn't firest needed events if the element is empty
Zapatec.Utils.cover.style.backgroundImage = "url(" + Zapatec.zapatecPath + "zpempty.gif)";
Zapatec.Utils.cover.style.display = "none";
Zapatec.Utils.cover.id = "zpCoverControl";

/**
 * Shows the cover.
 * @param zIndex [number] - z-index of the cover.
 * @param cursor [string] - cursor to be assigned.
 * @param moveHandler [function] - handler function for mouse move
 * @param mouseUpHandler [function] - handler function for mouse up
 */
Zapatec.Utils.cover.show = function(zIndex, cursor, moveHandler, mouseUpHandler) {
	if (!this.parentNode) {
		document.body.appendChild(this);
	}
	if (this.style.display != "none") {
		this.hide();
	}
	this.style.display = "block";
	//formatting it to be over all the others(even our draggable element)
	Zapatec.Utils.prepareToMove(this, null, document.body);
	//initial position
	var x = 0;
	var y = 0;
	//need to correct it with window scrolling
	y += Zapatec.Utils.getPageScrollY();
	x += Zapatec.Utils.getPageScrollX();
	//moving cover
	Zapatec.Utils.moveElementTo(this, x, y);
	//setting sizes
	var dim = Zapatec.Utils.getWindowSize();
	this.style.width = dim.width + "px";
	this.style.height = dim.height + "px";
	//registering it to scroll
	Zapatec.ScrollWithWindow.stickiness = 1;
	Zapatec.ScrollWithWindow.register(this);
	this.style.zIndex = zIndex;
	this.style.cursor = cursor;
	Zapatec.Utils.addEvent(this, 'mousemove', moveHandler);
	Zapatec.Utils.addEvent(this, 'mouseup', mouseUpHandler);
	this.moveHandler = moveHandler;
	this.mouseUpHandler = mouseUpHandler;
};

/**
 * Hides the cover.
 */
Zapatec.Utils.cover.hide = function() {
	Zapatec.ScrollWithWindow.unregister(this);
	Zapatec.Utils.removeEvent(this, 'mousemove', this.moveHandler);
	Zapatec.Utils.removeEvent(this, 'mouseup', this.mouseUpHandler);
	this.moveHandler = null;
	this.mouseUpHandler = null;
	this.style.zIndex = "";
	this.style.cursor = "";
	this.style.display = "none";
};

// ---------------------------------------- SRProp Object Section -----------------------------------

/**
 * object for saving and restoring properties of the given object - SRProp (Save Restore Object Properties)
 * @param obj [object] - object to work with
 * this class adds methods to any given object which enable to save and restore properties using following
 * \code
 *   var object = {};
 *   ob = new Zapatec.Utils.SRProp(obj);
 *   object.prop = "";
 *   ob.saveProp("prop");
 *   object.prop = "ttt";
 *	 ob.restoreProp("prop");
 *   ob.restoreAll();
 * \endcode
 */
Zapatec.Utils.SRProp = function(obj) {
	//storeing of an object to inspect
	this.obj = obj;
	//array of stored properties
	this.savedProp = [];
	//giving the object itself the refference to SRProp obj
	Zapatec.Utils.createProperty(obj, "restorer", this);
}

/**
 * Saves the named property to savedProp array
 * @param propName [string] - name of the property to save - can be the followin "prop", "level1.level2" and so on.
 * @return true if successful, otherwise false
 */
Zapatec.Utils.SRProp.prototype.saveProp = function (propName) {
	//property name should definately be string, I think :)
	if (typeof propName != "string") {
		return false;
	}
	var self = this;
	//using eval we save needed property to our array
	try {
		eval("self.savedProp[propName] = self.obj." + propName + ";");
	} catch (e) {
		return false;
	}
	
	return true;
}

/**
 * Saves the named properties to savedProp array
 * @param can be any set of params, but only strings will be parsed.
 * @return true if successful, otherwise false
 */
Zapatec.Utils.SRProp.prototype.saveProps = function (propName) {
	var res = true;
	for(var i = 0; i < arguments.length; ++i) {
		this.saveProp(arguments[i]);
	}
}

/**
 * Restores the named property from savedProp array
 * @param propName [string] - name of the property to restore - can be the followin "prop", "level1.level2" and so on.
 * @return true if successful, otherwise false
 */
Zapatec.Utils.SRProp.prototype.restoreProp = function (propName) {
	//property name should definately be string, I think :)
	if (typeof propName != "string" || typeof this.savedProp[propName] == "undefined") {
		return false;
	}
	var self = this;
	try {
		//For HTMLElements it is impossible to just set such property as parentNode and nextSibling
		//so we first try to do appendChild of the parentNode element
		//if it isn't HTMLElement we just restreing it using the old good eval again :)
		if (propName.indexOf("parentNode") != -1 && this.savedProp[propName] && typeof this.savedProp[propName] == "object" && this.savedProp[propName].appendChild) {
			this.savedProp[propName].insertBefore(self.obj, this.savedProp[propName.replace(/parentNode/, "nextSibling")]);
		} else {
			eval("self.obj." + propName + " = self.savedProp[propName];");
		}
		//and now free the position in the array as the property was restored
		delete this.savedProp[propName];
	} catch (e) {
		return false;
	}
	
	return true;
}

/**
 * Restores the named properties from savedProp array
 * @param can be any set of params, but only strings will be parsed.
 * @return true if successful, otherwise false
 */
Zapatec.Utils.SRProp.prototype.restoreProps = function (propName) {
	var res = true;
	for(var i = 0; i < arguments.length; ++i) {
		this.restoreProp(arguments[i]);
	}
}

/**
 * Restores all properties from the savedProp array
 */
Zapatec.Utils.SRProp.prototype.restoreAll = function() {
	//just iterating through all saved proerties and restoreing them
	for(var i in this.savedProp) {
		this.restoreProp(i);
	}
}

/**
 * Gets property value by name from the savedProp array
 * @param propName [string] - name of the property to get - can be the followin "prop", "level1.level2" and so on.
 */
Zapatec.Utils.SRProp.prototype.getProp = function(propName) {
	//Should I expalin this? :)
	return this.savedProp[propName];
}

/**
 * Destroys an object.
 */
Zapatec.Utils.SRProp.prototype.destroy = function() {
	this.obj.restorer = null;
	for(var iProp in this) {
		this[iProp] = null;
	}
	
	return null;
};

// --------------------------------------- CommandEvent Object Section ---------------------------------
/**
 * This is an extension of EventDriven object to acheive the following
 * possibilities: if listener returns false chain stops
 * its execution, true - continues the execution, "re-execute" string
 * will force the re-execution of the event, "parent-re-execution" can
 * be used for re-executing the parent event (this should be implemented
 * in parent event listener).
 */
Zapatec.CommandEvent = function() {
}

Zapatec.inherit(Zapatec.CommandEvent, Zapatec.EventDriven);

/**
 * New fireEvent method to fit new functionality.
 * @param strEvent [string] - event name.
 */
Zapatec.CommandEvent.prototype.fireEvent = function(strEvent) {
	if (!this.events[strEvent]) {
		return;
	}
	var arrListeners = this.events[strEvent].listeners;
	for (var iListener = 0; iListener < arrListeners.length; iListener++) {
		// Remove first argument
		var arrArgs = [].slice.call(arguments, 1);
		// Call in scope of this object
		var result = arrListeners[iListener].apply(this, arrArgs);
		if (result === false) {
			return false;
		} else if (result == "re-execute") {
			iListener = -1;
		} else if (result == "parent-re-execute") {
			return result;
		}
	}
};

// ---------------------------------------- Draggable Object Section -----------------------------------

/**
 * Makes element draggable. 
 * @param el [HTMLElement] reference to the element.
 * @param objArgs [object] must be object with the following properties:
 * left [number] draggable area left edge in pixels according to the dragLayer or document.body.
 * top [number] draggable area top edge -''-.
 * right [number] draggable area right edge -''-.
 * bottom [number] draggable area bottom edge -''-.
 * direction [String 'horizontal'/'vertical'] enables dragging only described direction
 * followShape [boolean] draggable area controls the object size (not only the top left position).
 * handler [HTMLElement] reference to the handler element (fe. window titlebar).
 * dragCSS [string] className for dragstate (will be removed after release)
 * overwriteCSS [string] this will overwrite the current elements className and will be restored only after calling restorePos()
 * dragLayer [HTMLElement] reference to the element in which we are dragging the draggable element, default is el.parentNode.
 * method [string] cut, copy, dummy, slide
 * dropname [string] defines name of the droparea
 * stopEv [boolean] determines if our event handlers should stop the event propogation
 * onDragInit [function] the handler for dragStart called in the end of the event
 * onDragMove [function] the handler for dragMove called in the end of the event
 * onDragEnd [function] the handler for dragEnd called in the end of the event
 * beforeDragInit [function] the handler for dragStart called at the beginning of the event
 * beforeDragMove [function] the handler for dragMove called at the beginning of the event
 * beforeDragEnd [function] the handler for dragEnd called at the beginning of the event
 * eventCapture [boolean] I don't think this should be documented as currently this is used
 * as a hack to cacth events before DnD, I used it in Window script. So basicaly this is for
 * programmers :)
 */
Zapatec.Utils.Draggable = function(el, objArgs){
	//objArgs should be object in any case when passed to the init method
	if (!objArgs || typeof objArgs != "object") {
		objArgs = {};
	}
	//Init works only with its objArgs object so let's not bother it with new arguments :)
	objArgs.container = el;
	Zapatec.Utils.Draggable.SUPERconstructor.call(this);
	this.initialized = this.init(objArgs);
	this.disabled = false;
};

Zapatec.Utils.Draggable.id = "Zapatec.DnD";

Zapatec.inherit(Zapatec.Utils.Draggable, Zapatec.CommandEvent);

/**
 * Initializes object. To be able to inherit.
 *
 * @param objArgs [object] user configuration.
 * @return true if successful, otherwise false
 */
Zapatec.Utils.Draggable.prototype.init = function(objArgs) {
	//getting element
	var el = Zapatec.Utils.idOrObject(objArgs.container);
	objArgs.container = null;
	if (!el) {
		return false;
	}
	// Check arguments
	if (!objArgs || typeof objArgs != "object") {
		return false;
	}
	Zapatec.Utils.Draggable.SUPERclass.init.call(this);
	//check method
	switch (objArgs.method) {
		case "cut"   : 
		case "copy"  : 
		case "dummy" : break;
		//case "slide" //this method will be improved in the future
		default : objArgs.method = "cut";
	}
	//if it is image we need to change this as image can not be dragged itself
	el = Zapatec.Utils.img2div(el);
	//passing all the arguments to the element as an dragAtr object
	//through this object will be done all the configuration and behaviour of the element
	Zapatec.Utils.createProperty(el, "dragAtr", objArgs);
	if (typeof objArgs.beforeDragInit == "function") {
		this.addEventListener("beforeDragInit", objArgs.beforeDragInit);
	}
	if (typeof objArgs.beforeDragMove == "function") {
		this.addEventListener("beforeDragMove", objArgs.beforeDragMove);
	}
	if (typeof objArgs.beforeDragEnd == "function") {
		this.addEventListener("beforeDragEnd", objArgs.beforeDragEnd);
	}
	if (typeof objArgs.onDragInit == "function") {
		this.addEventListener("onDragInit", objArgs.onDragInit);
	}
	if (typeof objArgs.onDragMove == "function") {
		this.addEventListener("onDragMove", objArgs.onDragMove);
	}
	if (typeof objArgs.onDragEnd == "function") {
		this.addEventListener("onDragEnd", objArgs.onDragEnd);
	}
	//default for stopEv is true
	if (el.dragAtr.stopEv !== false) {
		el.dragAtr.stopEv = true;
	}
	//default for eventCapture is false
	if (el.dragAtr.eventCapture !== true) {
		el.dragAtr.eventCapture = false;
	}
	//Opera has strange behaviour - when you just click element starts draging,
	//although mouseup should have been called.
	if (Zapatec.is_opera) {
		el.dragAtr.eventCapture = true;
	}
	//need to have valid handler
	if (el.dragAtr.handler) {
		el.dragAtr.handler = Zapatec.Utils.idOrObject(el.dragAtr.handler, 'cannot find the handlerobject:' + el.dragAtr.handler);
	}
	if (el.dragAtr.handler) {
		el.dragAtr.handler = Zapatec.Utils.img2div(el.dragAtr.handler);
	};
	//getting dragLayer
	el.dragAtr.dragLayer = Zapatec.Utils.idOrObject(el.dragAtr.dragLayer, 'cannot find the dragLayer:' + el.dragAtr.dragLayer) || document.body;
	//dragLayer should be positioned relatively
	if (el.dragAtr.dragLayer != document.body) {
		el.dragAtr.dragLayer.style.position = "relative";
	}
	
	//lets store the refference to this element for our object
	this.draggable = this.origDraggable = el;
	//and vice versa :)
	Zapatec.Utils.createProperty(el, "dragObj", this);
	//the hook should be the element who is responsible for dragStart
	Zapatec.Utils.createProperty(this, "hook", el.dragAtr.handler || el);
	//initially we are really not dragging :)
	this.dragging = false;
	//adding mousedown event to the hook as it is responsible for dragStart.
	//The rest of event are tied to the div which covers the whole screen and has big z-index.
	//This way we can achieve many advantages, like giving the right cursor, 
	//reactiong only on our events, solving problem with iframes on the page, etc.
	var self = this;
	if (Zapatec.is_gecko) {
		this.hook.style.setProperty("-moz-user-select", "none", "");
	}
	Zapatec.Utils.addEvent(this.hook, 'mousedown', function(objEvent) {
		return self.dragStart(objEvent);
	});
	if (el.dragAtr.eventCapture) {
		Zapatec.Utils.addEvent(this.draggable, 'mousemove', function(ev) {
			if (self.dragging) {
				self.dragMove(ev);
			}
		});
		Zapatec.Utils.addEvent(this.draggable, 'mouseup', function(ev) {
			if (self.dragging) {
				self.dragEnd(ev);
			}
		});
	}
	//if the initialization was successfull need to save and restore some of the elements properties
	//What a nice surprice - we have a class for it!!! ;)
	this.restorer = new Zapatec.Utils.SRProp(this.draggable);
	//need to save some properties of the element which we need to restore (if we'll need)
	this.restorer.saveProp("style.left");
	this.restorer.saveProp("style.top");
	el = null;
	return true;
};

/**
 * searches elements with className=class and makes them draggable(useful to call it on document load)
 * @ param class [string] searcable element's CSSclassname.
 * @ param el [HTMLElement] reference to the element.
 * @ param recursive [boolean] searches in childs.
 */
Zapatec.Utils.initDragObjects = function(className, el, recursive, attribObject){
	if (!className) return;
	el = Zapatec.Utils.idOrObject(el);
	var changeArray = Zapatec.Utils.getElementsByAttribute('className', className, el, recursive);
	for (a in changeArray){
		a = new Zapatec.Utils.Draggable(changeArray[a], attribObject);
	}
}

/**
 * Drag start event.
 */
Zapatec.Utils.Draggable.prototype.dragStart = function(ev) {
	if (this.disabled === true) {
		return false;
	}
	ev = ev || window.event;
	// Check mouse button, only left one is valid
	var iButton = ev.button || ev.which;
	if (iButton > 1) {
		return false;
	}
	//now we started dragging!
	this.dragging = true;
	//to work with the draggable element is easier using just 'el' and not 'this.draggable' :)
	var el = this.getElementForDragging();

	//calling the handler
	this.fireEvent("beforeDragInit", el, ev);
	//calling global event handler
	Zapatec.Utils.Draggable.globalEvents.fireEvent("beforeDragInit", el);
	//Lets save the starting position of the mouse
	el.dragAtr.mouseX = ev.clientX + Zapatec.Utils.getPageScrollX() || 0;
	el.dragAtr.mouseY = ev.clientY + Zapatec.Utils.getPageScrollY() || 0;
	this.restorer.saveProp("style.zIndex");
	//to position correctly we need to call Zapatec.Utils.prepareToMove
	Zapatec.Utils.prepareToMove(el, this.restorer, el.dragAtr.dragLayer);
	el.style.zIndex = 1000000;
	if (el.dragAtr.overwriteCSS) {
		this.restorer.saveProp("className");
		el.className = el.dragAtr.overwriteCSS;
	}
	if (el.dragAtr.dragCSS) {
		Zapatec.Utils.addClass(el, el.dragAtr.dragCSS);
	}

	//Using the cover
	var self = this;
	Zapatec.Utils.cover.show(
		el.dragAtr.eventCapture ? 999999 : 1000001,
		"move",
		function(objEvent) {
			return self.dragMove(objEvent);
		},
		function(objEvent) {
			return self.dragEnd(objEvent);
		}
	);
	//adding the move cursor and storeing old one
	this.restorer.saveProp("style.cursor");
	if (el.dragAtr.eventCapture) {
		el.style.cursor = "move";
	}
	
	//calling the handler
	this.fireEvent("onDragInit", el, ev);

	//calling the global handler
	Zapatec.Utils.Draggable.globalEvents.fireEvent("onDragInit", el);
	
	if (el.dragAtr.stopEv) {
		return Zapatec.Utils.stopEvent(ev);
	} else {
		return true;
	}
};

/**
 * Drag move event.
 */
Zapatec.Utils.Draggable.prototype.dragMove = function(ev){
	// Must initialize dragging first
	if (!this.dragging) {
		return;
	}
	//to work with the draggable element is easier using just 'el' and not 'this.draggable' :)
	var el = this.draggable;
	Zapatec.Utils.cover.style.zIndex = 1000001;
	//calling the handler
	this.fireEvent("beforeDragMove", el, ev);
	//calling global event handler
	Zapatec.Utils.Draggable.globalEvents.fireEvent("beforeDragMove", el);
	//mouse position
	var x = ev.clientX + Zapatec.Utils.getPageScrollX() || 0;
	var y = ev.clientY + Zapatec.Utils.getPageScrollY() || 0;
	//new coordinates, if it is null - it woldn't be touched by moveElementTo
	var diffX = 0; var diffY = 0; var newX = null; var newY = null;
	//if it is not vertical direction than we can change X coordinate
	if (el.dragAtr.direction != "vertical") {
		//calculating new position relative mouse pos
		newX = x - el.dragAtr.mouseX;
		//correcting it to fit left and right restrictions
		if (el.dragAtr.left || el.dragAtr.left === 0) {
			diffX = el.dragAtr.left - (newX + Zapatec.Utils.getElementPos(el).x);
		} else {diffX = 0;}
		if (diffX > 0) {
			newX = el.dragAtr.left - Zapatec.Utils.getElementPos(el).x;
		} else {
			if (el.dragAtr.right || el.dragAtr.right === 0) {
				diffX = el.dragAtr.right - (el.dragAtr.followShape ? el.offsetWidth : 0) - (newX + Zapatec.Utils.getElementPos(el).x);
			} else {diffX = 0;}
			if (diffX < 0) {
				newX = el.dragAtr.right - (el.dragAtr.followShape ? el.offsetWidth : 0) - Zapatec.Utils.getElementPos(el).x;
			} else {diffX = 0;}
		}
	}
	//if it is not horizontal direction than we can change Y coordinate
	if (el.dragAtr.direction != "horizontal") {
		//calculating new position relative mouse pos
		newY = y - el.dragAtr.mouseY;
		//correcting it to fit top and bottom restrictions
		if (el.dragAtr.top || el.dragAtr.top === 0) {
			diffY = el.dragAtr.top - (newY + Zapatec.Utils.getElementPos(el).y);
		} else {diffX = 0;}
		if (diffY > 0) {
			newY = el.dragAtr.top - Zapatec.Utils.getElementPos(el).y;
		} else {
			if (el.dragAtr.bottom || el.dragAtr.bottom === 0) {
				diffY = el.dragAtr.bottom - (el.dragAtr.followShape ? el.offsetHeight : 0) - (newY + Zapatec.Utils.getElementPos(el).y);
			} else {diffX = 0;}
			if (diffY < 0) {
				newY = el.dragAtr.bottom - (el.dragAtr.followShape ? el.offsetHeight : 0) - Zapatec.Utils.getElementPos(el).y;
			} else {diffY = 0;}
		}
	}
	//moving element to new position
	Zapatec.Utils.moveElementFor(el, newX, newY);
	el.dragAtr.mouseX = x + diffX;
	el.dragAtr.mouseY = y + diffY;

	//calling the handler
	this.fireEvent("onDragMove", el, ev);

	//calling global event handler
	Zapatec.Utils.Draggable.globalEvents.fireEvent("onDragMove", el);

	// Stop event
	return Zapatec.Utils.stopEvent(ev);
};

/**
 * Drag end event.
 */
Zapatec.Utils.Draggable.prototype.dragEnd = function(ev){
	// Must initialize dragging first
	if (!this.dragging) {
		return;
	}

	//to work with the draggable element is easier using just 'el' and not 'this.draggable' :)
	var el = this.draggable;
	if (el.dragAtr.eventCapture) {
		this.restorer.restoreProp("style.cursor");
	}
	//calling the handler
	this.fireEvent("beforeDragEnd", el, ev);
	//calling global event handler
	Zapatec.Utils.Draggable.globalEvents.fireEvent("beforeDragEnd", el);

	//restoreing some properties
	this.restorer.restoreProp("style.zIndex");
	//removing the dragCSS class
	if (el.dragAtr.overwriteCSS) {
		this.restorer.restoreProp("className");
	}
	if (el.dragAtr.dragCSS) {
		Zapatec.Utils.removeClass(el, el.dragAtr.dragCSS);
	}

	//hiding the cover
	Zapatec.Utils.cover.hide();
	// Remove flag
	this.dragging = false;

	//calling the handler
	this.fireEvent("onDragEnd", el, ev);

	//calling global event handler
	Zapatec.Utils.Draggable.globalEvents.fireEvent("onDragEnd", el);

	if (el.dragAtr.method == "copy" || el.dragAtr.method == "dummy") {
		if (el.dragAtr.method == "dummy") {
			var parent = el.parentNode;
			while(!parent && parent.nodeType != 1) {
				parent = parent.parentNode;
			}
			
			parent.removeChild(el);
		}
		this.draggable = this.origDraggable;
	}
	
	// Stop event
	return Zapatec.Utils.stopEvent(ev);
};

/** 
 * returns element to its original place where it was taken from
 */
Zapatec.Utils.Draggable.prototype.restorePos = function(){
	this.restorer.restoreAll();
	this.draggable.within = null;
}

/**
 * Immitates drag action to the point specified by x and y.
 * @param x [number] - X coordinate.
 * @param y [number] - Y coordinate.
 */
Zapatec.Utils.Draggable.prototype.dragTo = function(x, y) {
	//calling the handler
	this.fireEvent("beforeDragInit", this.draggable, {});
	Zapatec.Utils.prepareToMove(this.draggable, this.restorer, this.draggable.dragAtr.dragLayer);
	//calling the handler
	this.fireEvent("onDragInit", this.draggable, {});
	//calling the handler
	this.fireEvent("beforeDragMove", this.draggable, {});
	//moving element to new position
	Zapatec.Utils.moveElementTo(this.draggable, x, y);
	//calling the handler
	this.fireEvent("onDragMove", this.draggable, {});
	//calling the handler
	this.fireEvent("beforeDragEnd", this.draggable, {});
	//calling the handler
	this.fireEvent("onDragEnd", this.draggable, {});
};

/** 
 * returns element/its copy what will be drags
 * It depends on method property of draggable object
 * Cut method - this is how the DnD currently works. You drag the element itself.
 *    By 'element' I mean the HTML element which is passed to the constructor.
 * Copy method - by this method element is not touched at all, 
 *    DnD creates its copy and drags it. When you stopped dragging the copy stays at the
 *    place it was dragged to and can not be moved anymore (this is for now, we'll change this later).
 * Dummy method - the DnD should behave similar to the copy method, but with one little 
 *    difference - it will destroy the copy after the dragging ended. 
 */
Zapatec.Utils.Draggable.prototype.getElementForDragging = function () {
	var el = this.draggable;
	if (el.dragAtr.method == "dummy" || el.dragAtr.method == "copy") {
		var copyOfEl = el.cloneNode(false);
		copyOfEl.dragAtr = el.dragAtr;
		
		var parent = el.parentNode;
		while(!parent && parent.nodeType != 1) {
			parent = parent.parentNode;
		}

		this.draggable = parent.insertBefore(copyOfEl, el);
		return this.draggable;
	} else {
		return el;
	}
}

/**
 * Destroys all refferences to HTML elements so they can be released.
 */
Zapatec.Utils.Draggable.prototype.destroy = function() {
	if (this.draggable) {
		if (this.draggable.dragAtr) {
			this.draggable.dragAtr = null;
		}
		if (this.draggable.dragObj) {
			this.draggable.dragObj = null;
		}
		if (this.draggable.restorer) {
			this.draggable.restorer = null;
		}
	}
	if (this.origDraggable) {
		if (this.origDraggable.dragAtr) {
			this.origDraggable.dragAtr = null;
		}
		if (this.origDraggable.dragObj) {
			this.origDraggable.dragObj = null;
		}
		if (this.origDraggable.restorer) {
			this.origDraggable.restorer = null;
		}
	}
	this.restorer = this.restorer.destroy();
	for(var iProp in this) {
		this[iProp] = null;
	}
	
	return null;	
};

// -------------------------- Global handlers for Draggable Object Section -------------------------

// CommandEvent object for global dragging events
Zapatec.Utils.Draggable.globalEvents = new Zapatec.CommandEvent();
Zapatec.Utils.Draggable.globalEvents.init();

// ------------------------------------ DropArea Object Section ------------------------------------

/** Defines the droparea element
 * @param el [HTMLElement or string] 
 * @param dropname [string] id for simple dnd
 * @param ondrop [function] fires on drop
 * @param ondraginit [function] fires on any dragging start
 * @param ondragover [function] fires on drag over the droparea
 * @param odragout [function] fires on drag out of the droparea
 * @param ondragend [function] fires on any dragging end
 */
Zapatec.Utils.DropArea = function(el, dropname, ondrop, ondraginit, ondragover, ondragout, ondragend) {
	//Retreiveing droparea HTMLElement
	el = Zapatec.Utils.idOrObject(el);
	if (!el) return;
	Zapatec.Utils.DropArea.SUPERconstructor.call(this);
	Zapatec.Utils.DropArea.SUPERclass.init.call(this);
	//pushing our object to the array of drop area
	Zapatec.Utils.DropAreas.push(this);
	//saving a refference to it
	this.dropArea = el;
	//a flag for the element which is currently over the droparea.
	//This is used to prevent endless calls to onDragOver and onDragOut.
	//Now this handlers are called only onced.
	this.elementOver = false;
	//saving all the given handlers
	el.dropAtr = {};
	if (dropname) el.dropAtr.dropName = dropname;
	if (typeof ondrop == "function") {
		this.addEventListener("onDrop", ondrop);
	}
	if (typeof ondraginit == "function") {
		this.addEventListener("onDragInit", ondraginit);
	}
	if (typeof ondragout == "function") {
		this.addEventListener("onDragOut", ondragout);
	}
	if (typeof ondragover == "function") {
		this.addEventListener("onDragOver", ondragover);
	}
	if (typeof ondragend == "function") {
		this.addEventListener("onDragEnd", ondragend);
	}
	//adding our listeners to the Draggable global events
	this.addListeners();
};

Zapatec.Utils.DropArea.id = "Zapatec.DnD";

Zapatec.inherit(Zapatec.Utils.DropArea, Zapatec.CommandEvent);

/**
 * Drag Init event
 */
Zapatec.Utils.DropArea.prototype.dragInit = function(el) {
	//if somehow it was not HTMLElement, we should not do anything
	if (!el || !el.tagName || el == this.dropArea) {
		return false;
	}
	//retreiving the mouse position, we don't have event object, but I think we don't need it :)
	var mousePos = {x : el.dragAtr.mouseX, y : el.dragAtr.mouseY};
	this.mousePos = mousePos;
	//retreiving the droparea position
	var dropPos = Zapatec.Utils.getAbsolutePos(this.dropArea, true);
	//if the mouse is over the droparea so lets point it in elementOver flag
	if ((mousePos.x > dropPos.x) && (mousePos.x < dropPos.x + this.dropArea.offsetWidth) && (mousePos.y > dropPos.y) && (mousePos.y < dropPos.y + this.dropArea.offsetHeight)) {
		this.elementOver = el;
	}
	//if we have dragInit handler fire it
	if (this.fireEvent("onDragInit", el, this) == "parent-re-execute") {
		return "re-execute";
	}
	//if we have dragOver handler and eventually the element was over the droparea on the start
	//lets fire it 
	if (this.elementOver) {
		if (this.fireEvent("onDragOver", el, this) == "parent-re-execute") {
			return "re-execute";
		}
	}
};

/**
 * Drag Move event
 */
Zapatec.Utils.DropArea.prototype.dragMove = function(el) {
	//if somehow it was not HTMLElement, we should not do anything
	if (!el || !el.tagName || el == this.dropArea) {
		return false;
	}
	//retreiving the mouse position, we don't have event object, but I think we don't need it :)
	var mousePos = {x : el.dragAtr.mouseX, y : el.dragAtr.mouseY};
	this.mousePos = mousePos;
	//retreiving the droparea position
	var dropPos = Zapatec.Utils.getAbsolutePos(this.dropArea, true);
	//if the mouse is over the droparea so lets point it in elementOver flag
	//otherwise lets give it value null
	if ((mousePos.x > dropPos.x) && (mousePos.x < dropPos.x + this.dropArea.offsetWidth) && (mousePos.y > dropPos.y) && (mousePos.y < dropPos.y + this.dropArea.offsetHeight)) {
		//if we have dragOver handler fire it
		if (!this.elementOver) {
			if (this.fireEvent("onDragOver", el, this) == "parent-re-execute") {
				return "re-execute";
			}
		}
		this.elementOver = el;
	} else {
		//if we have dragOut handler fire it
		if (this.elementOver) {
			if (this.fireEvent("onDragOut", el, this) == "parent-re-execute") {
				return "re-execute";
			}
		}
		this.elementOver = null;
	}
};

/**
 * Drag End event
 */
Zapatec.Utils.DropArea.prototype.dragEnd = function(el) {
	//if somehow it was not HTMLElement, we should not do anything
	if (!el || !el.tagName || el == this.dropArea) {
		return false;
	}
	//if we have drop handler fire it
	if (this.elementOver) {
		if (this.fireEvent("onDrop", el, this) == "parent-re-execute") {
			return "re-execute";
		}
	}
	//if we have dragEnd handler fire it
	if (this.fireEvent("onDragEnd", el, this) == "parent-re-execute") {
		return "re-execute";
	}
	//cleaning the values
	this.mousePos = null;
	this.elementOver = null;
};

/**
 * removes all drag listeners
 */
Zapatec.Utils.DropArea.prototype.removeListeners = function() {
	//removes all the handlers set by this droparea
	Zapatec.Utils.Draggable.globalEvents.removeEventListener("onDragInit", this.tmp.onDragInit);
	Zapatec.Utils.Draggable.globalEvents.removeEventListener("onDragMove", this.tmp.onDragMove);
	Zapatec.Utils.Draggable.globalEvents.removeEventListener("onDragEnd", this.tmp.onDragEnd);
}

/**
 * adds all drag listeners
 */
Zapatec.Utils.DropArea.prototype.addListeners = function() {
	//Sets Draggable global handlers
	var self = this;
	if (!this.tmp) {
		this.tmp = {};
	}
	this.tmp.onDragInit = function(el) {
		return self.dragInit(el);
	};
	this.tmp.onDragMove = function(el) {
		return self.dragMove(el);
	};
	this.tmp.onDragEnd = function(el) {
		return self.dragEnd(el);
	};
	Zapatec.Utils.Draggable.globalEvents.addEventListener("onDragInit", this.tmp.onDragInit);
	Zapatec.Utils.Draggable.globalEvents.addEventListener("onDragMove", this.tmp.onDragMove);
	Zapatec.Utils.Draggable.globalEvents.addEventListener("onDragEnd", this.tmp.onDragEnd);
}

// ---------------------------- Global array of DropAreas Objects Section --------------------------

// Array of all dropareas
Zapatec.Utils.DropAreas = [];
