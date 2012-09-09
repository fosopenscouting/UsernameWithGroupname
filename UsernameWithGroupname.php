<?php
/**
 * Allows the user to provide a group on registration, the groupname will be displayed after the username and has to be given at login.
 *
 * @author Thomas Crepain <info@thomascrepain.be>
 * @link http://www.mediawiki.org/wiki/Extension:UsernameWithGroupname Extension page on mediawiki.org
 * @version 1.0
 */

 $wgExtensionCredits['other'][] = array(
        'name'		=> 'UsernameWithGroupname',
        'version'	=> '1.0',
        'author'	=> 'Thomas Crepain',
		'path'		=> __FILE__,
        'url'		=> 'http://www.mediawiki.org/wiki/Extension:CustomUserCreateForm',
        'description'	=> 'Allows the user to provide a group on registration, the groupname will be displayed after the username and has to be given at login.'
);

// set path
$path = dirname( __FILE__ );
set_include_path( implode( PATH_SEPARATOR, array( $path ) ) . PATH_SEPARATOR . get_include_path() );
$UsernameWithGroupnameDirectory = $path . '/';
$templateFolder = 'templates';

// Internationalization
$wgExtensionMessagesFiles['UsernameWithGroupname'] = $UsernameWithGroupnameDirectory . 'UsernameWithGroupname.i18n.php';
$wgExtensionMessagesFiles['UsernameWithGroupnameAlias'] = $UsernameWithGroupnameDirectory . 'UsernameWithGroupname.alias.php';

// define classes autoload
$wgAutoloadClasses['UsernameWithGroupnameHooks'] = $UsernameWithGroupnameDirectory . 'UsernameWithGroupname.hooks.php';
$wgAutoloadClasses['SpecialUsernameWithGroupnameLoginForm'] = $UsernameWithGroupnameDirectory . 'SpecialUsernameWithGroupnameLoginForm.php';
$wgAutoloadClasses['UsernameWithGroupnameUserloginTemplate'] = $UsernameWithGroupnameDirectory . $templateFolder . '/UsernameWithGroupnameUserloginTemplate.php';
$wgAutoloadClasses['UsernameWithGroupnameUsercreateTemplate'] = $UsernameWithGroupnameDirectory . $templateFolder . '/UsernameWithGroupnameUsercreateTemplate.php';

// define hooks
$wgHooks['SpecialPage_initList'][] = 'UsernameWithGroupnameHooks::onSpecialPage_initList';

// define special pages
$wgSpecialPages['SpecialUsernameWithGroupnameLoginForm'] = 'SpecialUsernameWithGroupnameLoginForm';

// define configuration vars (these may be overwritten in LocalSettings.php)
/**
 * $wgUsergroups
 */
$wgUsergroups = array(
	"Human resources" => "Human resources",
    "Management" => "Management",
	"Marketing" => "Marketing",
	"Production" => "Production",
	"Sales" => "Sales",
);

/**
 *
 */
$wgCharsBetweenUsernameAndGroupname = " (";

/**
 *
 */
$wgCharsAfterGroupname = ")";