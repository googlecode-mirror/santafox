<?php
    require '../../ini.php';
    require '../../include/kernel.class.php';
    require '../../include/pub_interface.class.php';
    require '../../modules/auth/auth.class.php';


    if (!function_exists('mime_content_type'))
    {
	    function mime_content_type($filename)
	    {
		$idx = strtolower(end( explode( '.', $filename )) );
		$mimet = array(	'ai' =>'application/postscript',
			'aif' =>'audio/x-aiff',
			'aifc' =>'audio/x-aiff',
			'aiff' =>'audio/x-aiff',
			'asc' =>'text/plain',
			'atom' =>'application/atom+xml',
			'avi' =>'video/x-msvideo',
			'bcpio' =>'application/x-bcpio',
			'bmp' =>'image/bmp',
			'cdf' =>'application/x-netcdf',
			'cgm' =>'image/cgm',
			'cpio' =>'application/x-cpio',
			'cpt' =>'application/mac-compactpro',
			'crl' =>'application/x-pkcs7-crl',
			'crt' =>'application/x-x509-ca-cert',
			'csh' =>'application/x-csh',
			'css' =>'text/css',
			'dcr' =>'application/x-director',
			'dir' =>'application/x-director',
			'djv' =>'image/vnd.djvu',
			'djvu' =>'image/vnd.djvu',
			'doc' =>'application/msword',
			'dtd' =>'application/xml-dtd',
			'dvi' =>'application/x-dvi',
			'dxr' =>'application/x-director',
			'eps' =>'application/postscript',
			'etx' =>'text/x-setext',
			'ez' =>'application/andrew-inset',
			'gif' =>'image/gif',
			'gram' =>'application/srgs',
			'grxml' =>'application/srgs+xml',
			'gtar' =>'application/x-gtar',
			'hdf' =>'application/x-hdf',
			'hqx' =>'application/mac-binhex40',
			'html' =>'text/html',
			'html' =>'text/html',
			'ice' =>'x-conference/x-cooltalk',
			'ico' =>'image/x-icon',
			'ics' =>'text/calendar',
			'ief' =>'image/ief',
			'ifb' =>'text/calendar',
			'iges' =>'model/iges',
			'igs' =>'model/iges',
			'jpe' =>'image/jpeg',
			'jpeg' =>'image/jpeg',
			'jpg' =>'image/jpeg',
			'js' =>'application/x-javascript',
			'kar' =>'audio/midi',
			'latex' =>'application/x-latex',
			'm3u' =>'audio/x-mpegurl',
			'man' =>'application/x-troff-man',
			'mathml' =>'application/mathml+xml',
			'me' =>'application/x-troff-me',
			'mesh' =>'model/mesh',
			'mid' =>'audio/midi',
			'midi' =>'audio/midi',
			'mif' =>'application/vnd.mif',
			'mov' =>'video/quicktime',
			'movie' =>'video/x-sgi-movie',
			'mp2' =>'audio/mpeg',
			'mp3' =>'audio/mpeg',
			'mpe' =>'video/mpeg',
			'mpeg' =>'video/mpeg',
			'mpg' =>'video/mpeg',
			'mpga' =>'audio/mpeg',
			'ms' =>'application/x-troff-ms',
			'msh' =>'model/mesh',
			'mxu m4u' =>'video/vnd.mpegurl',
			'nc' =>'application/x-netcdf',
			'oda' =>'application/oda',
			'ogg' =>'application/ogg',
			'pbm' =>'image/x-portable-bitmap',
			'pdb' =>'chemical/x-pdb',
			'pdf' =>'application/pdf',
			'pgm' =>'image/x-portable-graymap',
			'pgn' =>'application/x-chess-pgn',
			'php' =>'application/x-httpd-php',
			'php4' =>'application/x-httpd-php',
			'php3' =>'application/x-httpd-php',
			'phtml' =>'application/x-httpd-php',
			'phps' =>'application/x-httpd-php-source',
			'png' =>'image/png',
			'pnm' =>'image/x-portable-anymap',
			'ppm' =>'image/x-portable-pixmap',
			'ppt' =>'application/vnd.ms-powerpoint',
			'ps' =>'application/postscript',
			'qt' =>'video/quicktime',
			'ra' =>'audio/x-pn-realaudio',
			'ram' =>'audio/x-pn-realaudio',
			'ras' =>'image/x-cmu-raster',
			'rdf' =>'application/rdf+xml',
			'rgb' =>'image/x-rgb',
			'rm' =>'application/vnd.rn-realmedia',
			'roff' =>'application/x-troff',
			'rtf' =>'text/rtf',
			'rtx' =>'text/richtext',
			'sgm' =>'text/sgml',
			'sgml' =>'text/sgml',
			'sh' =>'application/x-sh',
			'shar' =>'application/x-shar',
			'shtml' =>'text/html',
			'silo' =>'model/mesh',
			'sit' =>'application/x-stuffit',
			'skd' =>'application/x-koan',
			'skm' =>'application/x-koan',
			'skp' =>'application/x-koan',
			'skt' =>'application/x-koan',
			'smi' =>'application/smil',
			'smil' =>'application/smil',
			'snd' =>'audio/basic',
			'spl' =>'application/x-futuresplash',
			'src' =>'application/x-wais-source',
			'sv4cpio' =>'application/x-sv4cpio',
			'sv4crc' =>'application/x-sv4crc',
			'svg' =>'image/svg+xml',
			'swf' =>'application/x-shockwave-flash',
			't' =>'application/x-troff',
			'tar' =>'application/x-tar',
			'tcl' =>'application/x-tcl',
			'tex' =>'application/x-tex',
			'texi' =>'application/x-texinfo',
			'texinfo' =>'application/x-texinfo',
			'tgz' =>'application/x-tar',
			'tif' =>'image/tiff',
			'tiff' =>'image/tiff',
			'tr' =>'application/x-troff',
			'tsv' =>'text/tab-separated-values',
			'txt' =>'text/plain',
			'ustar' =>'application/x-ustar',
			'vcd' =>'application/x-cdlink',
			'vrml' =>'model/vrml',
			'vxml' =>'application/voicexml+xml',
			'wav' =>'audio/x-wav',
			'wbmp' =>'image/vnd.wap.wbmp',
			'wbxml' =>'application/vnd.wap.wbxml',
			'wml' =>'text/vnd.wap.wml',
			'wmlc' =>'application/vnd.wap.wmlc',
			'wmlc' =>'application/vnd.wap.wmlc',
			'wmls' =>'text/vnd.wap.wmlscript',
			'wmlsc' =>'application/vnd.wap.wmlscriptc',
			'wmlsc' =>'application/vnd.wap.wmlscriptc',
			'wrl' =>'model/vrml',
			'xbm' =>'image/x-xbitmap',
			'xht' =>'application/xhtml+xml',
			'xhtml' =>'application/xhtml+xml',
			'xls' =>'application/vnd.ms-excel',
			'xml xsl' =>'application/xml',
			'xpm' =>'image/x-xpixmap',
			'xslt' =>'application/xslt+xml',
			'xul' =>'application/vnd.mozilla.xul+xml',
			'xwd' =>'image/x-xwindowdump',
			'xyz' =>'chemical/x-xyz',
			'zip' =>'application/zip'
		);

		if (isset( $mimet[$idx] ))
			return $mimet[$idx];
		else
			return 'application/octet-stream';
	    }
    }

    // Храним сессию неделю
    session_cache_expire(60*60*24*7);
    session_start();


    $requestedUri = $_SERVER['REQUEST_URI'];
    $kernel = new kernel(PREFIX);


    if (!$kernel->pub_user_is_registred())
    {//пользователь не авторизирован
        $auth = new auth();
        print $auth->pub_show_authorize(null,null,null,$requestedUri);
    }
    else
    {
        $path2file = $kernel->pub_site_root_get().$requestedUri;
        if (!file_exists($path2file))
            frontoffice_manager::throw_404_error();
        else
        {
            header("Content-Type: ".mime_content_type($path2file));
            header('Content-Length: '.filesize($path2file));
            set_time_limit(0);
            @readfile($path2file);
            die();
        }
    }

?>