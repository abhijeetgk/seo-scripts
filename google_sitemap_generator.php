<?php

$url="http://example.com";

//Fetch the page using the CURL Library
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
$store = curl_exec ($ch);
curl_close ($ch);

//Print website information (optional)
print <<<END
<h1>Sitemap links Information for a given website</h1>
Website: $url<br /><br />
END;


//Strip the Host name from the Url and echo it (used later)
preg_match('@^(?:http://)?([^/]+)@i',$url, $matches);
$host = $matches[1];
echo '<br />Host: '.$host;

//Take out the directory name of your url
if(strrpos($url, "/") > 10)
$root= substr($url,0,strrpos($url, "/"));
else $root= $url;
echo '<br />Root: '.$root;

//Create an array to save urls
$links=array();

//Strip all links from the page
preg_match_all('/<a href="(.*)"/U',$store, $matches, PREG_SET_ORDER);

//Loop inside the links and rebuild the corresponding full urls.
foreach ($matches as $val)
{

	if(strpos($val[1],'#') === FALSE && strpos($val[1],'http://') === FALSE && strpos($val[1],'@') === FALSE)
	if(!in_array(trim($val[1]),$links))
	if(strpos(trim($val[1]),'/') == 0 && strpos(trim($val[1]),'/') !== FALSE)
	$links[]='http://'.$host.trim($val[1]);
	else
	$links[]='http://'.$root.'/'.trim($val[1]);

}

$date=date('Y-m-d');


//Print all results inside a textarea box:
echo '<textarea rows="40" cols="120">';
echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
';

//Loop inside all links and add them to the sitemap using a default priority as 0.9 and a default changefreq as daily.
foreach ($links as $val)
{

print "<url>
<loc>$val</loc>
<lastmod>$date</lastmod>
<changefreq>daily</changefreq>
<priority>0.9</priority>
</url>
";
}

print '</urlset>';
echo '</textarea>';
?>