<?php
 require_once('php-captcha.inc.php');
 $root = $_SERVER['DOCUMENT_ROOT'];
 $fontsPath = dirname(__FILE__).'/ttf/';
 $aFonts = array($fontsPath.'VeraBd.ttf', $fontsPath.'VeraIt.ttf', $fontsPath.'Vera.ttf');
 $oVisualCaptcha = new PhpCaptcha($aFonts, 120, 41);
 $oVisualCaptcha->SetCharSet('0-9');
 $oVisualCaptcha->Create();