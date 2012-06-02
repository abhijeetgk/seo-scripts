<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<?php
/*Simple URL checking*/
$_POST['url']="http://www.example.com";
if(!isset($_POST['url']) || $_POST['url'] == '' || $_POST['url'] == 'http://')
{
	echo 'Please enter an url.';
}
else
{


$url=strip_tags($_POST['url']);

/*we first retrieve the contente of the page using the Curl library*/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_FILETIME, 1);
curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 2);

$store = curl_exec ($ch);


/*Error handling*/
if (curl_errno($ch))
{
print curl_error($ch);
}
else
{
	$info = curl_getinfo($ch);
	curl_close($ch);

print <<<END
<h1>Description for a given website</h1>
Website: $url<br />
END;

$ok=1;
$title='';
$description='';
$keywords='';



	/*We extract the Title from the head tags:*/
	preg_match("/<head.*>(.*)<\/head>/smUi",$store, $headers);
	if(count($headers) > 0)
	{
		/*Fetch the charset of the page*/
		if(preg_match("/<meta[^>]*http-equiv[^>]*charset=(.*)(\"|')>/Ui",$headers[1], $results))
		$charset= $results[1];
		else $charset='None';

		if(preg_match("/<title>(.*)<\/title>/Ui",$headers[1], $titles))
		{
			if(count($titles) > 0)
			{
				/*If the charset information has been extracted, we convert it to UTF-8 - Otherwise we assume it's already UTF-8*/
				if($charset == 'None')
				$title=trim(strip_tags($titles[1]));
				else
				$title=trim(strip_tags(iconv($charset, "UTF-8", $titles[1])));

			}
			else
			{
				/*If there is no title given we take the url as a title*/
				if(strlen($url) > 30)
				$title=trim(substr($url,30)).'...';
				else $title= trim($url);
			}
		}
		else
		{
			/*If there is no title given we take the url as a title*/
			if(strlen($url) > 30)
			$title=trim(substr($url,30)).'...';
			else $title= trim($url);

		}
	}
	else
	{
		$ok=0;
		echo 'No HEAD - That might not be an HTML page!';
	}



	/*Let's fetch the META description or give a description is there is not description available*/
	preg_match("|<meta[^>]*description[^>]*content=\"([^>]+)\"[^>]*>|Ui",$headers[1], $matches);
	if(count($matches) > 0)
	{
		if($charset != 'None')
		$description= trim(strip_tags(iconv($charset, "UTF-8",$matches[1])));
		else
		$description= trim(strip_tags($matches[1]));

	}
	else
	{
		preg_match("/<body.*>(.*)<\/body>/smUi",$store, $matches);
		if(count($matches) > 0)
		{
			if($charset != 'None')
			$description= trim(substr(trim(strip_tags(iconv($charset, "UTF-8",$matches[1]))),0,150));
			else
			$description= trim(substr(trim(strip_tags($matches[1])),0,150));

		}
		else
		{
			if($charset != 'None')
			$description= trim(substr(trim(strip_tags(iconv($charset, "UTF-8",$store))),0,150));
			else
			$description= trim(substr(trim(strip_tags($store)),0,150));
		}


	}

	/*Now the META keywords or some keywords which we extract from the description*/
	preg_match("|<meta[^>]*keywords[^>]*content=\"([^>]+)\"[^>]*>|Ui",$headers[1], $matches);
	if(count($matches) > 0)
	{
		if($charset != 'None')
		$keywords= trim(strip_tags(iconv($charset, "UTF-8",$matches[1])));
		else
		$keywords= trim(strip_tags($matches[1]));

	}
	else
	{
		/*We shall avoid the stopwords from the keywords*/
		$stopwords= array(' the ',' in ',' a ',' and ',' an ',' of ',' about ',' are ',' as ',' at ',' be ',' by ',' com ',' de ',' en ',' for ',' from ',' how ',' in ',' is ',' it ',' la ',' on ',' or ',' that ',' this ',' to ',' was ',' what ',' when ',' where ',' who ',' will ',' with ',' und ',' www ',' you ',' your ',' our ');

		$keywords=str_replace($stopwords," ",strtolower($description));
		$keywords=str_replace(" ",",",$keywords);

	}


	/*We print out the results*/
	if($ok)
	echo '<hr><p><u>Title</u>: '.$title.'<p><u>Description</u>:'.$description.'<p><u>Keywords</u>:'.$keywords;
	else
	{
		echo 'No title/description...';
	}

}
}
?>
</body>
</html>