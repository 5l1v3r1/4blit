<?php

include "common_header.php";

?>
    <div class="jumbotron">
	<div class="container"><!-- CONTAINER -->
	    <h1><?php echo _("API"); ?></h1>
	    <p class="lead"><?php echo _("4bl.it API enables easy integration for broadcasting to your Telegram Channels using our platform."); ?></p>
	</div><!-- /CONTAINER -->
    </div><div class="container">
	<div class="row">
	    <div class="col-sm-3 col-md-2 sidebar">
		<div class="list-group panel">
		    <a href="#introduction" class="list-group-item">Introduction</a>
		    <a href="#api_register" class="list-group-item">Register method</a>
		    <a href="#api_verify"  class="list-group-item">Verify Method</a>
		    <a href="#api_publish"  class="list-group-item">Publish method</a>
		    <a href="#api_queue"  class="list-group-item">Queue method</a>
		    <a href="#wp_plugin"  class="list-group-item">4bl.it Wordpress plugin</a>
		</div>
	    </div>
    	    <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
		<a name="introduction"><h2><?php echo _("Introduction"); ?></h2></a>
		<p>
		    4bl.it API use REST interface to provide register, publish and verify methods. Just to provide some examples, we choose
		    to use <a href="http://unirest.io/">Unirest - Lightweight HTTP Request Client Libraries</a>.
		</p>
		<p>
		    All request should be sent to https://www.4bl.it/rest with proper headers and body, like:
		    <pre><code>
$headers = array('Accept' => 'application/json');
$data = array('key' => $api_key,
    [...]
);
$body = Unirest\Request\Body::multipart($data);
$result = Unirest\Request::post('https://www.4bl.it/rest/register', $headers, $body);
		    </code></pre>
		    You can test API interface using REST client like <a href='https://insomnia.rest/'>Insomnia</a>.

		</p>
		<hr>
		<a name="api_register"><h2>Register method</h2></a>
		<p>
		    You need to get an unique API key before publishing from your source. To get one, you can use <b>register</b> method like:
		    <pre><code>
$headers = array('Accept' => 'application/json');
$data = array('key' => '',
    'blog_name' => $blog_name,
    'blog_description' => $blog_description,
    'blog_admin_email' => $blog_admin_email,
    'blog_language' => $blog_language,
    'blog_url' => $blog_url,
);
$body = Unirest\Request\Body::multipart($data);

$result = Unirest\Request::post('https://www.4bl.it/rest/register', $headers, $body);

if($result->code == '200') {
    if(isset($result->body->apikey)) {
	$api_key = $result->body->apikey;
    } else die('Error');
} else die('Error');
		    </code></pre>
		    Mandatory fields are <i>blog_name</i>,<i>blog_admin_email</i> and <i>blog_url</i>. Please note that e-mail address specified in <i>blog_admin_email</i> 
		    will receive a confirmation link to unlock the API key.
		</p>
		<hr>
		<a name="api_verify"><h2>Verify method</h2></a>
		<p>
		    If you just want to verify if your API key is still valid, you should use <b>verify</b> method:
		    <pre><code>
$headers = array('Accept' => 'application/json');

$data = array('key' => $api_key);

$body = Unirest\Request\Body::multipart($data);

$result = Unirest\Request::post('https://www.4bl.it/rest/verify', $headers, $body);

if($result->code == '200') {
    echo("Connected !");
} else {
    echo("Error:" . $result->code);
}				
		    </code></pre>
		    In JSON format, system reply like:
		    <pre><code>
{
    "success": "verified",
    "message": "No errors detected"
}
		    </code></pre>
		    if API key was verified successfully or:
		    <pre><code>
{
    "error": {
        "code": 403,
        "message": "Wrong Api Key"
    }
}
		    </code></pre>
		    if not.
		</p>
		<hr>
		<a name="api_publish"><h2>Publish method</h2></a>
		<p>
		    To publish post to your channel, you need to use <b>publish</b> method:
		    <pre><code>
$headers = array('Accept' => 'application/json');

$data = array('key' => $api_key,
    'title' => $post_title,
    'excerpt' => $post_excerpt,
    'author' => $post_author,
    'date' => $post_date,
    'image_url' => $post_image_url,
    'tags' => $post_tags,
    'url' => $post_url,
);

$headers = array('Accept' => 'application/json');
$body = Unirest\Request\Body::multipart($data);

$result = Unirest\Request::post('https://www.4bl.it/rest/publish', $headers, $body);

if($result->code == '200') {
    echo("Published !");
} else {
    echo("Error:" . $result->code);
}				
		    </code></pre>
		    Please note that 4blit checks for duplicates (using hash checksum). $post_tags need to be comma-separated (like <i>first tag, second tag, ...</i>) and
		    $url is the unique URL for the post, the permalink.
		</p>
		<hr>
		<a name="api_queue"><h2>Queue method</h2></a>
		<p>
		    To see the publish queue of your channel, you can to use <b>queue</b> method:
		    <pre><code>
$headers = array('Accept' => 'application/json');
$data = array('key' => $api_key);
$body = Unirest\Request\Body::multipart($data);
$result = Unirest\Request::post('https://www.4bl.it/rest/queue', $headers, $body);
		    </code></pre>
		    Of course you need to specify the <i>api key</i> of the source you want to check. As return, in JSON format, the last 10 entries in publish queue:
		    <pre><code>
{
    "success": 10,
    "result": [
        {
            "id": "1559",
            "title": "Un divertente esperimento di reti neurali: Quick, Draw!",
            "added": "11:11:20 19-11-2016",
            "published": 
        },
        {
            "id": "1543",
            "title": "Canon EOS M10",
            "added": "07:11:11 18-11-2016",
            "published": "08:11:12 19-11-2016"
        },
        {
            "id": "1542",
            "title": "Monitoraggio del traffico VoIP con Homer ed OpenSIPS",
            "added": "07:11:46 18-11-2016",
            "published": "08:11:12 19-11-2016"
        },
        <i>[...]</i>
    ]
}
		</code></pre>
		Empty "published" field means that the post is waiting for publication on Telegram channel. "ID" is the unique id for this post.
		</p>
		<hr>
		<a name="wp_plugin"><h2>wp-4blit Wordpress plugin</h2></a>
		<p>
		    Code was taken from wp-4blit Wordpress plugin, available on <a href="https://github.com/michelep/wp-4blit">GitHub</a>
		</p>
	    </div>
	</div>
    </div>
<?php

include "common_footer.php";

?>
