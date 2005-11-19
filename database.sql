<?xml version="1.0" encoding="utf-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">

<head>
<title>phpMyAdmin</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<base href="https://phpmyadmin.laber-land.de/" />
<script language="JavaScript" type="text/javascript">
<!--
    /*
     *       we need this for Backwards-Compatibility and resolving problems
     *       with non DOM browsers, which may have problems with css 2 (like NC 4)
     */
    var isDOM      = (typeof(document.getElementsByTagName) != 'undefined'
                      && typeof(document.createElement) != 'undefined')
                   ? 1 : 0;
    var isIE4      = (typeof(document.all) != 'undefined'
                      && parseInt(navigator.appVersion) >= 4)
                   ? 1 : 0;
    var isNS4      = (typeof(document.layers) != 'undefined')
                   ? 1 : 0;
    var capable    = (isDOM || isIE4 || isNS4)
                   ? 1 : 0;
    // Ugly fix for Opera and Konqueror 2.2 that are half DOM compliant
    if (capable) {
        if (typeof(window.opera) != 'undefined') {
            var browserName = ' ' + navigator.userAgent.toLowerCase();
            if ((browserName.indexOf('konqueror 7') == 0)) {
                capable = 0;
            }
        } else if (typeof(navigator.userAgent) != 'undefined') {
            var browserName = ' ' + navigator.userAgent.toLowerCase();
            if ((browserName.indexOf('konqueror') > 0) && (browserName.indexOf('konqueror/3') == 0)) {
                capable = 0;
            }
        } // end if... else if...
    } // end if
    document.writeln('<link rel="stylesheet" type="text/css" href="./css/phpmyadmin.css.php?lang=de-utf-8&amp;server=1&amp;collation_connection=utf8_general_ci&amp;js_frame=right&amp;js_isDOM=' + isDOM + '" />');
//-->
</script>
<noscript>
    <link rel="stylesheet" type="text/css" href="./css/phpmyadmin.css.php?lang=de-utf-8&amp;server=1&amp;collation_connection=utf8_general_ci&amp;js_frame=right" />
</noscript>
    <link rel="stylesheet" type="text/css" href="./css/print.css?lang=de-utf-8&amp;server=1&amp;collation_connection=utf8_general_ci" media="print" />
</head><body><p>export.php: Missing parameter: what <a href="./Documentation.html#faqmissingparameters" target="documentation"> (FAQ 2.8)</a><br /></p></body></html>