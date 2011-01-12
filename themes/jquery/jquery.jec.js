/**
 * jQuery jEC (jQuery Editable Combobox) 1.2.3
 * http://code.google.com/p/jquery-jec
 *
 * Copyright (c) 2008-2009 Lukasz Rajchel (lukasz@rajchel.pl | http://lukasz.rajchel.pl)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Documentation	:	http://code.google.com/p/jquery-jec/wiki/Documentation
 * Changelog		:	http://code.google.com/p/jquery-jec/wiki/Changelog
 */

/*jslint white: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, 
bitwise: true, regexp: true, strict: true, newcap: true, immed: true, maxerr: 50, indent: 4*/
/*global Array, Math, String, clearInterval, document, jQuery, setInterval*/
/*members ":", Handle, Remove, Set, acceptedKeys, addClass, all, append, appendTo, attr, before, 
bind, blinkingCursor, blinkingCursorInterval, blur, browser, ceil, change, charCode, children, 
classes, createElement, css, data, destroy, disable, each, editable, empty, enable, eq, expr, 
extend, filter, floor, fn, focus, focusOnNewOption, fromCharCode, get, getId, handleCursor, 
hasClass, hasOwnProperty, ignoredKeys, inArray, init, initJS, int, isArray, jEC, jECTimer, jec, 
jecKill, jecOff, jecOn, jecPref, jecValue, keyCode, keyDown, keyPress, keyRange, length, max, min, 
msie, optionClasses, optionStyles, position, pref, random, remove, removeAttr, removeClass, 
removeData, setEditableOption, styles, substring, text, unbind, uneditable, useExistingOptions, 
val, value*/
"use strict";
(function ($) {

	$.jEC = (function () {
		var pluginClass = 'jecEditableOption', cursorClass = 'hasCursor', options = {}, 
			values = {}, lastKeyCode, defaults, Validators, EventHandlers, Combobox,
			activeCombobox;
		
		defaults = {
			position				: 0,
			classes					: [],
			styles					: {},
			optionClasses			: [],
			optionStyles			: {},
			focusOnNewOption		: false,
			useExistingOptions		: false,
			blinkingCursor			: false,
			blinkingCursorInterval	: 1000,
			ignoredKeys				: [],
			acceptedKeys			: [{min: 32, max: 126}, {min: 191, max: 382}]
		};
		
		Validators = (function () {
			return {
				int: function (value) {
					return typeof value === 'number' && Math.ceil(value) === Math.floor(value);
				},
				empty: function (value) {
					if (value === undefined || value === null) {
						return true;
					} else if ($.isArray(value)) {
						return value.length === 0;
					} else if (typeof value === 'object') {
						for (var key in value) {
							if (value.hasOwnProperty(key)) {
								return false;
							}
						}
					}
					return false;
				},
				keyRange: function (value) {
					if (typeof value === 'object' && !$.isArray(value)) {
						var min = value.min, max = value.max;
						return Validators.int(min) && Validators.int(max) && min <= max;
					}
					return false;
				}
			};
		}());
		
		EventHandlers = (function () {
			var getKeyCode, clearCursor;
			
			getKeyCode = function (event) {
				var charCode = event.charCode;
				if (charCode !== undefined && charCode !== 0) {
					return charCode;
				} else {
					return event.keyCode;
				}
			};
			
			clearCursor = function (elem) {
				$(elem).children('option.' + cursorClass).each(function () {
					var text = $(this).text();
					$(this).removeClass(cursorClass).text(text.substring(0, text.length - 1));
				});
			};
			
			return {
				// focus event handler
				// enabled blinking cursor
				focus: function (event) {
					var opt = options[Combobox.getId($(this))];
					if (opt.blinkingCursor && $.jECTimer === undefined && !$.browser.msie) {
						activeCombobox = $(this);
						$.jECTimer = setInterval($.jEC.handleCursor, opt.blinkingCursorInterval);
					}
				},
				// blur event handler
				// disables blinking cursor
				blur: function (event) {
					if ($.jECTimer !== undefined && !$.browser.msie) {
						clearInterval($.jECTimer);
						$.jECTimer = undefined;
						activeCombobox = undefined;
						clearCursor($(this));
					}
				},
				// keydown event handler
				// handles keys pressed on select (backspace and delete must be handled
				// in keydown event in order to work in IE)
				keyDown: function (event) {
					var keyCode = getKeyCode(event), option, value;
					
					lastKeyCode = keyCode;
					
					switch (keyCode) {
					case 8:	// backspace
					case 46: // delete
						option = $(this).children('option.' + pluginClass);
						if (option.val().length >= 1) {
							value = option.text().substring(0, option.text().length - 1);
							option.val(value).text(value).attr('selected', 'selected');
						}
						return (keyCode !== 8);
					default:
						break;
					}
				},
				// keypress event handler
				// handles the rest of the keys (keypress event gives more informations
				// about pressed keys)
				keyPress: function (event) {
					var keyCode = getKeyCode(event), opt = options[Combobox.getId($(this))], i, 
						option, value, specialKeys;
					
					clearCursor($(this));
					if (keyCode !== 9) {
						// special keys codes
						specialKeys = [37, 38, 39, 40, 46];
						// handle special keys
						for (i = 0; i < specialKeys.length; i += 1) {
							if (keyCode === specialKeys[i] && keyCode === lastKeyCode) {
								return false;
							}
						}
						
						// don't handle ignored keys
						if ($.inArray(keyCode, opt.ignoredKeys) === -1) {
							// remove selection from all options
							$(this).children(':selected').removeAttr('selected');
							
							if ($.inArray(keyCode, opt.acceptedKeys) !== -1) {
								option = $(this).children('option.' + pluginClass);
								value = option.text() + String.fromCharCode(keyCode);
								option.val(value).text(value).attr('selected', 'selected');
							}
						}
						
						return false;
					}
				},
				// change event handler
				change: function () {
					clearCursor($(this));
					var opt = options[Combobox.getId($(this))];
					if (opt.useExistingOptions) {
						Combobox.setEditableOption($(this));
					}
				}
			};
		}());
		
		// Combobox
		Combobox = (function () {
			var Parameters, EditableOption, generateId, setup;
			
			// validates and set combobox parameters
			Parameters = (function () {
				var Set, Remove, Handle;
					
				(Set = function () {
					var parseKeys = function (value) {
						var i, j, keys = [];
						if ($.isArray(value)) {
							for (i = 0; i < value.length; i += 1) {
								// min,max tuple
								if (Validators.keyRange(value[i])) {
									for (j = value[i].min; j <= value[i].max; j += 1) {
										keys[keys.length] = j;
									}
								// number
								} else if (typeof value[i] === 'number' && 
								Validators.int(value[i])) {
									keys[keys.length] = value[i];
								}
							}
						}
						return keys;
					};
					
					return {
						position: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id], optionsCount;
							if (opt !== undefined && Validators.int(value)) {
								optionsCount = 
									elem.children('option:not(.' + pluginClass + ')').length;
								if (value > optionsCount) {
									value = optionsCount;
								}
								opt.position = value;
							}
						},
						classes: function (elem, value) {
							if (typeof value === 'string') {
								value = [value];
							}
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && $.isArray(value)) {
								opt.classes = value;
							}
						},
						optionClasses: function (elem, value) {
							if (typeof value === 'string') {
								value = [value];
							}
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && $.isArray(value)) {
								opt.optionClasses = value;
							}
						},
						styles: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && typeof value === 'object' && 
								!$.isArray(value)) {
								opt.styles = value;
							}
						},
						optionStyles: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && typeof value === 'object' &&
								!$.isArray(value)) {
								opt.optionStyles = value;
							}
						},
						focusOnNewOption: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && typeof value === 'boolean') {
								opt.focusOnNewOption = value;
							}
						},
						useExistingOptions: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && typeof value === 'boolean') {
								opt.useExistingOptions = value;
							}
						},
						blinkingCursor: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && typeof value === 'boolean') {
								opt.blinkingCursor = value;
							}
						},
						blinkingCursorInterval: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && Validators.int(value)) {
								opt.blinkingCursorInterval = value;
							}
						},
						ignoredKeys: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && $.isArray(value)) {
								opt.ignoredKeys = parseKeys(value);
							}
						},
						acceptedKeys: function (elem, value) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && $.isArray(value)) {
								opt.acceptedKeys = parseKeys(value);
							}
						}
					};
				}());
				
				Remove = (function () {
					var removeClasses, removeStyles;
					
					removeClasses = function (elem, classes) {
						for (var i = 0; i < classes.length; i += 1) {
							elem.removeClass(classes[i]);
						}
					};
					
					removeStyles = function (elem, styles) {
						for (var style in styles) {
							if (!Validators.empty(styles[style])) {
								elem.css(style, '');
							}
						}
					};
					
					return {
						classes: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined) {
								removeClasses(elem, opt.classes);
							}
						},
						optionClasses: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined) {
								removeClasses(elem.children('option.' + pluginClass), 
									opt.optionClasses);
							}
						},
						styles: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined) {
								removeStyles(elem, opt.styles);
							}
						},
						optionStyles: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined) {
								removeStyles(elem.children('option.' + pluginClass),
									opt.optionStyles);
							}
						},
						all: function (elem) {
							Remove.classes(elem);
							Remove.optionClasses(elem);
							Remove.styles(elem);
							Remove.optionStyles(elem);
						}
					};
				}());
				
				Handle = (function () {
					var setClasses, setStyles;
					
					setClasses = function (elem, classes) {
						for (var i = 0; i < classes.length; i += 1) {
							elem.addClass(classes[i]);
						}
					};
					
					setStyles = function (elem, styles) {
						for (var style in styles) {
							if (!Validators.empty(styles[style])) {
								elem.css(style, styles[style]);
							}
						}
					};
					
					return {
						position: function (elem) {
							var id = Combobox.getId(elem), opt = options[id], option, 
								uneditableOptions;
							if (opt !== undefined) {
								option = elem.children('option.' + pluginClass);
								uneditableOptions = 
									elem.children('option:not(.' + pluginClass + ')');
								if (opt.position < uneditableOptions.length) {
									uneditableOptions.eq(opt.position).before(option);
								} else {
									elem.append(option);
								}
							}
						},
						classes: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined) {
								setClasses(elem, opt.classes);
							}
						},
						optionClasses: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined) {
								setClasses(elem.children('option.' + pluginClass), 
									opt.optionClasses);
							}
						},
						styles: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined) {
								setStyles(elem, opt.styles);
							}
						},
						optionStyles: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined) {
								setStyles(elem.children('option.' + pluginClass),
									opt.optionStyles);
							}
						},
						focusOnNewOption: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && opt.focusOnNewOption) {
								elem.children('option.' + pluginClass).
									attr('selected', 'selected');
							}
						},
						useExistingOptions: function (elem) {
							var id = Combobox.getId(elem), opt = options[id];
							if (opt !== undefined && opt.useExistingOptions) {
								Combobox.setEditableOption(elem);
							}
						},
						all: function (elem) {
							Handle.position(elem);
							Handle.classes(elem);
							Handle.optionClasses(elem);
							Handle.styles(elem);
							Handle.optionStyles(elem);
							Handle.focusOnNewOption(elem);
							Handle.useExistingOptions(elem);
						}
					};
				}());
				
				return {
					Set		: Set,
					Remove	: Remove,
					Handle	: Handle
				};
			}());
			
			EditableOption = (function () {
				return {
					init: function (elem) {
						var editableOption = $(document.createElement('option'));
						
						editableOption.addClass(pluginClass);
						
						elem.append(editableOption);
						elem.bind('keydown', EventHandlers.keyDown);
						elem.bind('keypress', EventHandlers.keyPress);
						elem.bind('change', EventHandlers.change);
						elem.bind('focus', EventHandlers.focus);
						elem.bind('blur', EventHandlers.blur);
					},
					destroy: function (elem) {
						elem.children('option.' + pluginClass).remove();
						elem.children('option:first').attr('selected', 'selected');
						elem.unbind('keydown', EventHandlers.keyDown);
						elem.unbind('keypress', EventHandlers.keyPress);
						elem.unbind('change', EventHandlers.change);
					}
				};
			}());
			
			// find unique identifier
			generateId = function () {
				while (true) {
					var random = Math.floor(Math.random() * 100000);
					
					if (options[random] === undefined) {
						return random;
					}
				}
			};
			
			// sets combobox
			setup = function (elem) {
				EditableOption.init(elem);
				Parameters.Handle.all(elem);
			};
			
			// Combobox public members
			return {
				// create editable combobox
				init: function (settings) {
					return $(this).filter(':uneditable').each(function () {
						var id = generateId(), key;
						
						$.data($(this).get(0), "jecId", id);
						
						// override passed default options
						options[id] = $.extend(true, {}, defaults);
						
						// parse keys
						Parameters.Set.ignoredKeys($(this), options[id].ignoredKeys);
						Parameters.Set.acceptedKeys($(this), options[id].acceptedKeys);
						
						if (typeof settings === 'object' && !$.isArray(settings)) {
							for (key in settings) {
								if (settings[key] !== undefined) {
									switch (key) {
									case 'position':
										Parameters.Set.position($(this), settings[key]);
										break;
									case 'classes':
										Parameters.Set.classes($(this), settings[key]);
										break;
									case 'optionClasses':
										Parameters.Set.optionClasses($(this), settings[key]);
										break;
									case 'styles':
										Parameters.Set.styles($(this), settings[key]);
										break;
									case 'optionStyles':
										Parameters.Set.optionStyles($(this), settings[key]);
										break;
									case 'focusOnNewOption':
										Parameters.Set.focusOnNewOption($(this), settings[key]);
										break;
									case 'useExistingOptions':
										Parameters.Set.useExistingOptions($(this), settings[key]);
										break;
									case 'blinkingCursor':
										Parameters.Set.blinkingCursor($(this), settings[key]);
										break;
									case 'blinkingCursorInterval':
										Parameters.Set.blinkingCursorInterval($(this), 
											settings[key]);
										break;
									case 'ignoredKeys':
										Parameters.Set.ignoredKeys($(this), settings[key]);
										break;
									case 'acceptedKeys':
										Parameters.Set.acceptedKeys($(this), settings[key]);
										break;
									}
								}
							}
						}
						
						setup($(this));
					});
				},
				// creates editable combobox without using existing select elements
				initJS: function (options, settings) {
					var select, i, key;
					
					select = $('<select>');
					
					if ($.isArray(options)) {
						for (i = 0; i < options.length; i += 1) {
							if (typeof options[i] === 'object' && !$.isArray(options[i])) {
								for (key in options[i]) {
									if (typeof options[i][key]  === 'number' ||
										typeof options[i][key] === 'string') {
										$('<option>').text(options[i][key]).attr('value', key).
											appendTo(select);
									}
								}
							} else if (typeof options[i] === 'string' ||
								typeof options[i] === 'number') {
								$('<option>').text(options[i]).attr('value', options[i]).
									appendTo(select);
							}
						}
					}
					
					return select.jec(settings);
				},
				// destroys editable combobox
				destroy: function () {
					return $(this).filter(':editable').each(function () {
						$(this).jecOff();
						$.removeData($(this).get(0), 'jecId');
					});
				},
				// enable editablecombobox
				enable: function () {
					return $(this).filter(':editable').each(function () {
						var id = Combobox.getId($(this)), value = values[id];
						
						setup($(this));
						
						if (value !== undefined) {
							$(this).jecValue(value);
						}
					});
				},
				// disable editable combobox
				disable: function () {
					return $(this).filter(':editable').each(function () {
						values[Combobox.getId($(this))] = $(this).
							children('option.' + pluginClass).val();
						Parameters.Remove.all($(this));
						EditableOption.destroy($(this));
					});
				},
				// gets or sets editable option's value
				value: function (value, setFocus) {
					if ($(this).filter(':editable').length > 0) {
						if (Validators.empty(value)) {
							// get value
							return $(this).filter('select').children('option.' + pluginClass).
								val();
						} else if (typeof value === 'string' || typeof value === 'number') {
							// set value
							return $(this).filter(':editable').each(function () {
								var option = $(this).children('option.' + pluginClass);
								option.val(value).text(value);
								if (typeof setFocus !== 'boolean' || setFocus) {
									option.attr('selected', 'selected');
								}
							});
						}
					}
				},
				// gets or sets editable option's preference
				pref: function (name, value) {
					if ($(this).filter(':editable').length > 0) {
						if (!Validators.empty(name)) {
							if (Validators.empty(value)) {
								// get preference
								return options[Combobox.getId($(this))][name];
							} else {
								// set preference
								return $(this).filter(':editable').each(function () {
									switch (name) {
									case 'position':
										Parameters.Set.position($(this), value);
										Parameters.Handle.position($(this));
										break;
									case 'classes':
										Parameters.Remove.classes($(this));
										Parameters.Set.classes($(this), value);
										Parameters.Handle.position($(this));
										break;
									case 'optionClasses':
										Parameters.Remove.optionClasses($(this));
										Parameters.Set.optionClasses($(this), value);
										Parameters.Set.optionClasses($(this));
										break;
									case 'styles':
										Parameters.Remove.styles($(this));
										Parameters.Set.styles($(this), value);
										Parameters.Set.styles($(this));
										break;
									case 'optionStyles':
										Parameters.Remove.optionStyles($(this));
										Parameters.Set.optionStyles($(this), value);
										Parameters.Handle.optionStyles($(this));
										break;
									case 'focusOnNewOption':
										Parameters.Set.focusOnNewOption($(this), value);
										Parameters.Handle.focusOnNewOption($(this));
										break;
									case 'useExistingOptions':
										Parameters.Set.useExistingOptions($(this), value);
										Parameters.Handle.useExistingOptions($(this));
										break;
									case 'blinkingCursor':
										Parameters.Set.blinkingCursor($(this), value);
										break;
									case 'blinkingCursorInterval':
										Parameters.Set.blinkingCursorInterval($(this), value);
										break;
									case 'ignoredKeys':
										Parameters.Set.ignoredKeys($(this), value);
										break;
									case 'acceptedKeys':
										Parameters.Set.acceptedKeys($(this), value);
										break;
									}
								});
							}
						}
					}
				},
				// sets editable option to the value of currently selected option
				setEditableOption: function (elem) {
					var value = elem.children('option:selected').text();
					elem.children('option.' + pluginClass).attr('value', elem.val()).
						text(value).attr('selected', 'selected');
				},
				// get combobox id
				getId: function (elem) {
					return $.data(elem.get(0), "jecId");
				},
				//handles editable cursor
				handleCursor: function () {
					if (activeCombobox !== undefined && activeCombobox !== null) {
						var elem = activeCombobox.children('option:selected'), text = elem.text();
						if (elem.hasClass(cursorClass)) {
							elem.removeClass(cursorClass).text(text.substring(0, text.length - 1));
						} else {
							elem.addClass(cursorClass).text(text + '|');
						}
					}
				}
			};
		}());
		
		// jEC public members
		return {
			init			: Combobox.init,
			enable			: Combobox.enable,
			disable			: Combobox.disable,
			destroy			: Combobox.destroy,
			value			: Combobox.value,
			pref			: Combobox.pref,
			initJS			: Combobox.initJS,
			handleCursor	: Combobox.handleCursor
		};
	}());

 	// register functions
	$.fn.extend({
		jec			: $.jEC.init,
		jecOn		: $.jEC.enable,
		jecOff		: $.jEC.disable,
		jecKill		: $.jEC.destroy,
		jecValue	: $.jEC.value,
		jecPref		: $.jEC.pref
	});
	
	$.extend({
		jec: $.jEC.initJS
	});
	
	// register selectors
	$.extend($.expr[':'], {
		editable	: function (a) {
			return $.data($(a).get(0), 'jecId') !== undefined;
		},
		uneditable	: function (a) {
			return $.data($(a).get(0), 'jecId') === undefined;
		}
	});

}(jQuery));