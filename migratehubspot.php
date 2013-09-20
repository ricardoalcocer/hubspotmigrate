<?php
	// php setup
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
	date_default_timezone_set('America/Los_Angeles');
	//

	// grab input data
	if (isset($_GET['max'])){
		$max=$_GET['max'];
	}else{
		$max=10;
	}
	
	if(isset($_GET['offset'])){
		$offset=$_GET['offset'];
	}else{
		$offset=0;
	}

	// this argument is not being used.  Is there in case I find time/motivation to 
	// add other formats
	if(isset($_GET['format'])){
		$format=$_GET['offset'];
	}else{
		$format='wp';
	}

	// api configuration
	$blogapiendpoint='https://api.hubapi.com/blog/';
	$blogapicall='posts.json';
	$commentsapiendpoint='https://api.hubapi.com/blog/v1/posts/';
	$commentsapicall='comments.json';
	$apiversion='v1';

	$blogguid='##YOUR_BLOG_GUID##';
	$accesstoken='##YOUR_HUBSPOT_ACCESS_TOKEN##';
	//

	

	// Export templates
	$feedheader= <<<EOD
	<rss version="2.0"
		xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:wfw="http://wellformedweb.org/CommentAPI/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:wp="http://wordpress.org/export/1.2/"
	>
	<channel>
	<generator>Alco did this</generator>
	<wp:wxr_version>1.2</wp:wxr_version>
EOD;

	$posttemplate=<<<EOD
	
	<item>
			<title>^title^</title>
			<pubDate>^pubdate^</pubDate>
			<dc:creator>^author^</dc:creator>
			<description></description>
			<content:encoded><![CDATA[^body^]]></content:encoded>
			<excerpt:encoded><![CDATA[]]></excerpt:encoded>
			
			<wp:post_date>^postdate^</wp:post_date>
			<wp:post_date_gmt>^gmtpostdate^</wp:post_date_gmt>
			<wp:comment_status>open</wp:comment_status>
			<wp:ping_status>open</wp:ping_status>
			<wp:post_name>^title^</wp:post_name>
			<wp:status>publish</wp:status>
			<wp:post_parent>0</wp:post_parent>
			<wp:menu_order>0</wp:menu_order>
			<wp:post_type>post</wp:post_type>
			<wp:post_password></wp:post_password>
			<wp:is_sticky>0</wp:is_sticky>
			<wp:postmeta>
				<wp:meta_key>_edit_last</wp:meta_key>
				<wp:meta_value><![CDATA[87]]></wp:meta_value>
			</wp:postmeta>

			^insertcomments^
EOD;

	$commenttemplate=<<<EOD
		<wp:comment>
			<wp:comment_id>^id^</wp:comment_id>
			<wp:comment_author><![CDATA[^anonyName^]]></wp:comment_author>
			<wp:comment_author_email>^anonyEmail^</wp:comment_author_email>
			<wp:comment_author_IP>^userEmail^</wp:comment_author_IP>
			<wp:comment_date>^approvedTimestamp^</wp:comment_date>
			<wp:comment_date_gmt>^gmtapprovedTimestamp^</wp:comment_date_gmt>
			<wp:comment_content><![CDATA[^comment^]]></wp:comment_content>
			<wp:comment_approved>1</wp:comment_approved>
			<wp:comment_type></wp:comment_type>
			<wp:comment_parent>0</wp:comment_parent>
			<wp:comment_user_id>0</wp:comment_user_id>
		</wp:comment>


EOD;

	$feedfooter=<<<EOD
		</channel>
	</rss>
EOD;

	// get posts
	function getPosts($blogguid){
		global $blogapiendpoint,$apiversion,$blogapicall,$accesstoken,$max,$offset,$posttemplate,$feedheader,$feedfooter;

		$url="$blogapiendpoint$apiversion/$blogguid/$blogapicall?access_token=$accesstoken&Max=$max&Offset=$offset";
		$data=file_get_contents($url);
		$data2=json_decode($data,true);
		$output='';
		$tmppost='';

		foreach($data2 as $post){
			$tmppost=$posttemplate;
			$tmppost=str_replace('^title^', $post['title'], $tmppost);
			$tmppost=str_replace('^pubdate^', date('r', $post['publishTimestamp']/1000),$tmppost);
			$tmppost=str_replace('^postdate^', date('Y-m-d H:i:s', $post['publishTimestamp']/1000),$tmppost);
			$tmppost=str_replace('^gmtpostdate^', gmdate('Y-m-d H:i:s', $post['publishTimestamp']/1000),$tmppost);
			$tmppost=str_replace('^author^', $post['authorDisplayName'],$tmppost);
			$tmppost=str_replace('^body^', $post['body'],$tmppost);
			$tmppost=str_replace('^title^', $post['title'],$tmppost);
			$tmppost=str_replace('^insertcomments^',getComments($post['guid']),$tmppost);
			$output.=$tmppost.'</item>'.PHP_EOL;
		}	
		return $feedheader.$output.$feedfooter;	
	}

	// comments function
	function getComments($postguid){
		global $commentsapiendpoint,$commentsapicall,$accesstoken,$commenttemplate;

		$id=1;
		$url="$commentsapiendpoint$postguid/$commentsapicall?access_token=$accesstoken&Max=50";
		$data=file_get_contents($url);
		$data2=json_decode($data,true);
		$output='';
		$tmpcomment='';

		foreach($data2 as $comment){
			$tmpcomment=$commenttemplate;
			$tmpcomment=str_replace('^id^', $id++, $tmpcomment);
			$tmpcomment=str_replace('^anonyName^', $comment['anonyName'], $tmpcomment);
			$tmpcomment=str_replace('^anonyEmail^', $comment['anonyEmail'], $tmpcomment);
			$tmpcomment=str_replace('^userEmail^', $comment['userEmail'], $tmpcomment);
			$tmpcomment=str_replace('^approvedTimestamp^', date('Y-m-d H:i:s', $comment['approvedTimestamp']/1000), $tmpcomment);
			$tmpcomment=str_replace('^gmtapprovedTimestamp^', gmdate('Y-m-d H:i:s', $comment['approvedTimestamp']/1000), $tmpcomment);
			$tmpcomment=str_replace('^comment^', $comment['comment'], $tmpcomment);
			$output.=$tmpcomment;
		}
		return $output;
	}


	// **********************************************************************

	echo getPosts($blogguid);

	// **********************************************************************