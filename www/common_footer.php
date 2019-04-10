    <footer class="footer">
        <div class="container">
	    <div class="row justify-content-md-center">
    	        <div class="text-center">
            	    <h4>
			<strong>4bl.it</strong>
        	    </h4>
        	    <p>Made with <i class="fa fa-heart fa-fw"></i> in Siena, Tuscany, Italy</p>
        	    <br>
            	    <ul class="list-inline">
                	<li class="list-inline-item">
                    	    <a href="https://www.facebook.com/4bl.it"><i class="fa fa-facebook fa-fw fa-2x"></i></a>
                	</li>
                	<li class="list-inline-item">
                    	    <a href="https://twitter.com/4bl_it"><i class="fa fa-twitter fa-fw fa-2x"></i></a>
                	</li>
                	<li class="list-inline-item">
        		    <a href="https://t.me/fourblit"><i class="fa fa-paper-plane-o fa-2x"></i></a>
                	</li>
            	    </ul>
            	    <hr class="small">
            	    <p class="text-muted">Copyright &copy; 4bl.it</p>
        	</div>
            </div>
        </div>
    </footer>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js"></script>

    <script>window.jQuery</script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.2.0/js/tether.min.js" integrity="sha384-Plbmg8JY28KFelvJVai01l8WyZzrYWG825m+cZ0eDDS1f7d/js6ikvy1+X+guPIB" crossorigin="anonymous"></script>

    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/moderniz.2.8.1.js"></script>
    <script src="/js/ie10-viewport-bug-workaround.js"></script>
    <script src="/js/jquery.validationEngine.js"></script>
    <script src="/js/jquery.validationEngine-it.js"></script>
    <script src="/js/jquery.noty.packaged.min.js"></script>
    <script src="/js/common.js"></script>

<?php
if(isset($gbJsScripts)) {
    foreach($gbJsScripts as $jsScript) {
	echo "<script src=\"/js/$jsScript\"></script>";
    }
}
?>
<?php
$local_js = '00-'.basename($_SERVER['SCRIPT_FILENAME'],".php").".js";

if(file_exists("./js/".$local_js)) {
    echo "\t<!-- local JS -->\n\t<script src=\"/js/".$local_js."\"></script>\n";
}

$result = doQuery("SELECT ID,Type,Message FROM SessionMessages WHERE sessionId='$mySession->ID' ORDER BY addDate DESC;");
if(mysqli_num_rows($result) > 0) {
?>
	<script type="text/javascript">
	    function printNotice(text,type) {
		var n = noty({
        	    text        : text,
        	    type        : type,
        	    dismissQueue: true,
        	    layout      : 'top',
        	    theme       : 'defaultTheme',
		    maxvisible	: 10,
    		    closeWith	: ['click'],
        	    animation	: {
            		open: 'animated bounceInDown',
            		close: 'animated bounceOutRight', 
            		easing: 'swing', 
            		speed: 500 
        	    }
    		});
	    }
	$(document).ready(function () {
<?php

    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	$id = $row["ID"];
	$type = $row["Type"];
	$message = $row["Message"];
	echo "printNotice(\"$message\",\"$type\");\n";

	doQuery("DELETE FROM SessionMessages WHERE ID='$id';");
    }
    echo "});</script>";
}

if(isset($gbTailCode)) {
    echo $gbTailCode;
}

?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-7661472-21"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-7661472-21');
    </script>
    </body>
</html>
<?php

?>