<?php

/**
 * The functions which are hooked on events in UsernameWithGroupname.php
 *
 * @author Thomas Crepain <info@thomascrepain.be>
 */
class UsernameWithGroupnameHooks {
	/**
	 * Replaces the SpecialLogin page by our own page.
	 *
	 * @param array $list
	 * @return boolean
	 */
	public static function onSpecialPage_initList (&$list) {
		// replace the standard userLogin and createUser forms by our own
		$list['Userlogin'] = 'SpecialUsernameWithGroupnameLoginForm';
		$list['CreateAccount'] = 'SpecialUsernameWithGroupnameLoginForm';

		// The hook has operated successfully, let the system continue
		return true;
	}
}


?>
