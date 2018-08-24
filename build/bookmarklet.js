/**
 * This file is part of the composer package typo3-auto-login
 *
 * It‘s used to build the bookmarklet code for the Readme.md
 *
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

//javascript:
(q=>{
	let n=window.Notification,
		s='_typo3-auto-login',
		d='disable',
		p='TYPO3 auto login',
		w=document,
		a=w.cookie.split(';').some(x=>x.trim()===`${s}=${d}`);
	w.cookie=`${s}=${a?';expires='+new Date(0).toUTCString():d};path=/;`;
	n&&n.permission!=='denied'&&n.requestPermission().then(q=>new n(p,{body:`(${a?'✓':'✗'}) ${p} is now ${a ? 'enabled' : 'disabled'}. Cookie »${s}« has been ${a?'removed':'set'}.`,icon:'https://extensions.typo3.org/fileadmin/user_upload/ext_icon.png'}));
})();
