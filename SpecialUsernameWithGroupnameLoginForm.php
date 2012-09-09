<?php

/**
 * Overwrites the LoginForm to add a dropdown with groupnames on the login and signup form.
 *
 * @author Thomas Crepain <info@thomascrepain.be>
 */
class SpecialUsernameWithGroupnameLoginForm extends LoginForm {
	var $mUsergroup;

	/**
	 * Loader
	 */
	function load() {
		$request = $this->getRequest ();

		$this->mUsergroup = $request->getText ( 'wpUsergroup' );

		parent::load ();
	}

	public function execute($par) {
		if (session_id () == '') {
			wfSetupSession ();
		}

		$this->load ();
		$this->setHeaders ();

		if ($par == 'signup') { // Check for [[Special:Userlogin/signup]]
			$this->mType = 'signup';
		}

		if (! is_null ( $this->mCookieCheck )) {
			$this->onCookieRedirectCheck ( $this->mCookieCheck );
			return;
		} elseif ($this->mPosted) {
		    // trim spaces from the username
		    $this->mUsername = trim($this->mUsername);

		    // add groupname to username
		    global $wgCharsBetweenUsernameAndGroupname, $wgCharsAfterGroupname;
		    $this->mUsername = $this->mUsername . $wgCharsBetweenUsernameAndGroupname . $this->mUsergroup . $wgCharsAfterGroupname;

		    if ($this->mCreateaccount) {
			    return $this->addNewAccount ();
		    } elseif ($this->mCreateaccountMail) {
			    return $this->addNewAccountMailPassword ();
		    } elseif (('submitlogin' == $this->mAction) || $this->mLoginattempt) {
			    return $this->processLogin ();
		    }
		}
		$this->mainLoginForm ( '' );
	}

	/**
	 * @private
	 */
	function mainLoginForm($msg, $msgtype = 'error') {
		global $wgEnableEmail, $wgEnableUserEmail;
		global $wgHiddenPrefs, $wgLoginLanguageSelector;
		global $wgAuth, $wgEmailConfirmToEdit, $wgCookieExpiration;
		global $wgSecureLogin, $wgPasswordResetRoutes;

		$titleObj = $this->getTitle ();
		$user = $this->getUser ();

		if ($this->mType == 'signup') {
			// Block signup here if in readonly. Keeps user from
			// going through the process (filling out data, etc)
			// and being informed later.
			$permErrors = $titleObj->getUserPermissionsErrors ( 'createaccount', $user, true );
			if (count ( $permErrors )) {
				throw new PermissionsError ( 'createaccount', $permErrors );
			} elseif ($user->isBlockedFromCreateAccount ()) {
				$this->userBlockedMessage ( $user->isBlockedFromCreateAccount () );
				return;
			} elseif (wfReadOnly ()) {
				throw new ReadOnlyError ();
			}
		}

		if ($this->mUsername == '') {
			if ($user->isLoggedIn ()) {
				$this->mUsername = $user->getName ();
			} else {
				$this->mUsername = $this->getRequest ()->getCookie ( 'UserName' );
			}
		}

		if ($this->mType == 'signup') {
			$template = new UsernameWithGroupnameUsercreateTemplate ();
			$q = 'action=submitlogin&type=signup';
			$linkq = 'type=login';
			$linkmsg = 'gotaccount';
		} else {
			$template = new UsernameWithGroupnameUserloginTemplate ();
			$q = 'action=submitlogin&type=login';
			$linkq = 'type=signup';
			$linkmsg = 'nologin';
		}

		if (! empty ( $this->mReturnTo )) {
			$returnto = '&returnto=' . wfUrlencode ( $this->mReturnTo );
			if (! empty ( $this->mReturnToQuery )) {
				$returnto .= '&returntoquery=' . wfUrlencode ( $this->mReturnToQuery );
			}
			$q .= $returnto;
			$linkq .= $returnto;
		}

		// Don't show a "create account" link if the user can't
		if ($this->showCreateOrLoginLink ( $user )) {
			// Pass any language selection on to the mode switch link
			if ($wgLoginLanguageSelector && $this->mLanguage) {
				$linkq .= '&uselang=' . $this->mLanguage;
			}
			$link = Html::element ( 'a', array (
					'href' => $titleObj->getLocalURL ( $linkq )
			), $this->msg ( $linkmsg . 'link' )->text () ); // Calling either
			                                           // 'gotaccountlink' or
			                                           // 'nologinlink'

			$template->set ( 'link', $this->msg ( $linkmsg )->rawParams ( $link )->parse () );
		} else {
			$template->set ( 'link', '' );
		}

		$resetLink = $this->mType == 'signup' ? null : is_array ( $wgPasswordResetRoutes ) && in_array ( true, array_values ( $wgPasswordResetRoutes ) );

		$template->set ( 'header', '' );
		$template->set ( 'name', $this->mUsername );
		$template->set ( 'password', $this->mPassword );
		$template->set ( 'retype', $this->mRetype );
		$template->set ( 'email', $this->mEmail );
		$template->set ( 'realname', $this->mRealName );
		$template->set ( 'domain', $this->mDomain );
		$template->set ( 'reason', $this->mReason );

		$template->set ( 'action', $titleObj->getLocalURL ( $q ) );
		$template->set ( 'message', $msg );
		$template->set ( 'messagetype', $msgtype );
		$template->set ( 'createemail', $wgEnableEmail && $user->isLoggedIn () );
		$template->set ( 'userealname', ! in_array ( 'realname', $wgHiddenPrefs ) );
		$template->set ( 'useemail', $wgEnableEmail );
		$template->set ( 'emailrequired', $wgEmailConfirmToEdit );
		$template->set ( 'emailothers', $wgEnableUserEmail );
		$template->set ( 'canreset', $wgAuth->allowPasswordChange () );
		$template->set ( 'resetlink', $resetLink );
		$template->set ( 'canremember', ($wgCookieExpiration > 0) );
		$template->set ( 'usereason', $user->isLoggedIn () );
		$template->set ( 'remember', $user->getOption ( 'rememberpassword' ) || $this->mRemember );
		$template->set ( 'cansecurelogin', ($wgSecureLogin === true) );
		$template->set ( 'stickHTTPS', $this->mStickHTTPS );

		if ($this->mType == 'signup') {
			if (! self::getCreateaccountToken ()) {
				self::setCreateaccountToken ();
			}
			$template->set ( 'token', self::getCreateaccountToken () );
		} else {
			if (! self::getLoginToken ()) {
				self::setLoginToken ();
			}
			$template->set ( 'token', self::getLoginToken () );
		}

		// Prepare language selection links as needed
		if ($wgLoginLanguageSelector) {
			$template->set ( 'languages', $this->makeLanguageSelector () );
			if ($this->mLanguage) {
				$template->set ( 'uselang', $this->mLanguage );
			}
		}

		// Use loginend-https for HTTPS requests if it's not blank, loginend
		// otherwise
		// Ditto for signupend
		$usingHTTPS = WebRequest::detectProtocol () == 'https';
		$loginendHTTPS = $this->msg ( 'loginend-https' );
		$signupendHTTPS = $this->msg ( 'signupend-https' );
		if ($usingHTTPS && ! $loginendHTTPS->isBlank ()) {
			$template->set ( 'loginend', $loginendHTTPS->parse () );
		} else {
			$template->set ( 'loginend', $this->msg ( 'loginend' )->parse () );
		}
		if ($usingHTTPS && ! $signupendHTTPS->isBlank ()) {
			$template->set ( 'signupend', $signupendHTTPS->parse () );
		} else {
			$template->set ( 'signupend', $this->msg ( 'signupend' )->parse () );
		}

		// Give authentication and captcha plugins a chance to modify the form
		$wgAuth->modifyUITemplate ( $template, $this->mType );
		if ($this->mType == 'signup') {
			wfRunHooks ( 'UserCreateForm', array (
					&$template
			) );
		} else {
			wfRunHooks ( 'UserLoginForm', array (
					&$template
			) );
		}

		$out = $this->getOutput ();
		$out->disallowUserJs (); // just in case...
		$out->addTemplate ( $template );
	}
}

?>
