<?php

function proto() {
	if (isset($_SERVER['REQUEST_SCHEME']))
		return $_SERVER['REQUEST_SCHEME'];

	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
		return 'https';

	return 'http';
}
// eof
