<?php namespace ProcessWire;

/**
 * ProcessHello.info.php
 * 
 * Return information about this module.
 *
 * If you prefer to keep everything in the main module file, you can move this
 * to a static getModuleInfo() method in the ProcessHello.module.php file, which
 * would return the same array as below.
 * 
 * Note that if you change any of these properties for an already installed 
 * module, you will need to do a Modules > Refresh before you see them. 
 *
 */

$info = array(

	// Your module's title
	'title' => 'AltTextGpt',

	// A 1 sentence description of what your module does
	'summary' => 'A module for generating alt text for images in your site using the OpenAI API.',

	// Module version number (integer)
	'version' => 2, 

	// Name of person who created this module (change to your name)
	'author' => 'Max Fowler',

	// Icon to accompany this module (optional), uses font-awesome icon names, minus the "fa-" part
	'icon' => 'message',

	// Indicate any requirements as CSV string or array containing [RequiredModuleName][Operator][Version]
	'requires' => 'ProcessWire>=3.0.0',

	// URL to more info: change to your full modules.processwire.com URL (if available), or something else if you prefer
	'href' => 'https://processwire.com/modules/alt-text-gpt/',

	// name of permission required of users to execute this Process (optional)
	'permission' => 'alt-text-gpt',

	// permissions that you want automatically installed/uninstalled with this module (name => description)
	'permissions' => array(
		'alt-text-gpt' => 'Run the AltTextGpt module'
	), 
	
	// page that you want created to execute this module
	'page' => array(
		'name' => 'alt-text-gpt',
		'parent' => 'setup', 
		'title' => 'AltTextGpt',
		'icon' => 'message',
	),

	// for more options that you may specify here, see the file: /wire/core/Process.php
	// and the file: /wire/core/Module.php

);
