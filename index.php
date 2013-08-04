<?php

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));

$user_id = $facebook->getUser();
if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }

  

  $home = idx($facebook->api('/me/home?limit=100'), 'data', array());

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());

$app_name = idx($app_info, 'name', '');


function assignFriend($status){
  $daturl = "https://api.sentigem.com/external/get-sentiment?api-key=75bb2830195e0ef2af7714e30bd337df7D-3dzCLGWprRax85XusgTYAJwVH1Bb0&text=";
  
  $total = $daturl . urlencode($status);
  
  $string = get_data($total);
  $json_a = json_decode($string,true);
  
  $sentiment = $json_a['polarity'];

  return $sentiment;
}

/* gets the data from a URL */
function get_data($url) {
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt ($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}



?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>


    <script src="js/vendor/custom.modernizr.js"></script>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title>faceMood</title>
    <!--<link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />
    -->
    <link rel="stylesheet" href="css/foundation.css">
    <link rel="stylesheet" href="css/normalize.css">
    


    <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="faceMood" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
    <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
    <meta property="og:description" content="My first app" />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>

    

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
<script>
    $( document ).ready(function() {
    
        $("#positiveRefresh").click(function() {
            FB.api('/me/home?limit=10', function(response) {
              jQuery.each(response.post, function(){
                  if(document.getElementById("post" + this.id) == null){
                      $("#positive .friends").prepend("<div class=\"panel\"><a href=\"https:www.facebook.com/" + this.from.id + "target="_top"><img src=\"https://graph.facebook.com/" + this.from.id + "/picture?type=square\" alt=\""  + this.from.name . "\"> +  this.from.name + "</a><br><br>" +  this.message + "<hr> <div class=\"row collapse\"> <input id=\"" + this.id + "\" type=\"text\" placeholder=\"Comment on their mood...\">  <a href=\"#\"  class=\"button prefix\">Post</a></div> </div>");
                  }
              });
            });
        });

    });
</script>
  </head>
  <body>

    <div id="fb-root"></div>
    <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>



    
      <?php if (isset($basic)) { ?>
    <div class="fixed"> 
      <nav class="top-bar">
        <ul class="title-area">
           <!-- Title Area -->
           <li class="name">
             <h1>FaceMood</h1>
           </li>
        </ul>

        <section class="top-bar-section">
          <!-- Left Nav Section -->
          <ul class="right">
            <li>
              <a href="#" class="button success" onclick="fbLogout()">Logout</a>
            </li>
          </ul>
        </section>
      </nav>
     </div>
      
      <?php } else { ?>
      <header class="clearfix">
        <div>
          <style>
          div.frontpage {
            text-align: center;
          }
          </style>
          <h1>Welcome to</h1>
          <img id="frontpage" src="logo.png" alt="FaceMood">
          <div id="frontpage" class="fb-login-button" data-scope="user_likes,user_photos,read_stream"></div>
        </div>
      </header>
      <?php } ?>
      


   <?php
      if ($user_id) {
    ?>
  <script>
    function fbLogout() {
        FB.logout(function (response) {
            //Do what ever you want here when logged out like reloading the page
            window.location.reload();
        });
    }
  </script>


   <style type="text/css">
       
    /* Start of Column CSS */
    #container3 {
      clear:left;
      float:left;
      width:100%;
      overflow:hidden;
      background:#89ffa2; /* column 3 background colour */
    }
    #container2 {
      clear:left;
      float:left;
      width:100%;
      position:relative;
      right:33.333%;
      background:#fff689; /* column 2 background colour */
    }
    #container1 {
      float:left;
      width:100%;
      position:relative;
      right:33.33%;
      background:#ffa7a7; /* column 1 background colour */
    }
    #negative {
      float:left;
      width:29.33%;
      position:relative;
      left:68.67%;
      overflow:hidden;
    }
    #neutral {
      float:left;
      width:29.33%;
      position:relative;
      left:72.67%;
      overflow:hidden;
    }
    #positive {
      float:left;
      width:29.33%;
      position:relative;
      left:76.67%;
      overflow:hidden;
    }
  </style>        
  <div id="container3">
  <div id="container2">   
  <div class="container" id="container1">  
    <div class="small-2 large-4 columns" id="negative" >
          <br>
          <a href="#" id="negativeRefresh" class="button">Refresh</a>
          <br>
          <div class="friends">
           
          </div>
         
    </div>
    
    <div class="small-4 large-4 columns" id="neutral" >
          <br>
          <a href="#" id="neutralRefresh" class="button">Refresh</a>
          <br>
          <div class="friends">
              
            </div>
    </div>
    
    <div class="small-6 large-4 columns" id="positive" >
            <br>
            <a href="#" id="positiveRefresh" class="button">Refresh</a>
            <br>
            <div class="friends">
      
            </div>

    </div>
  </div> </div></div>
    

  <!-- Logic for Sentiment Analysis and jQuery Sorting -->
  <?php
  
    foreach ($home as $status) {
                // Extract the pieces of info we need from the requests above
                $message = idx($status, 'message');
        if(strlen($message) > 6) {        //filter out short messages
          $from = idx($status, 'from');
          if(idx($from, 'category') == null) { //filter out Facebook Pages
            $id = idx($from, 'id');
            $name = idx($from, 'name');
            
            if(idx($status, 'link') == null){
                $post_id = idx($status, 'id');
                $returnHTML = '<div id="post' . he($post_id) . '" class="panel"><a href="https://www.facebook.com/' . he($id) . '" target="_top"><img src="https://graph.facebook.com/' . he($id) . '/picture?type=square" alt=" ' . he($name) . '"> ' .  he($name) . '</a><br><br>' . he($message) . '<hr> <div class="row collapse"> <input id="' . he($post_id) . '" type="text" placeholder="Comment on their mood...">  <a href="#"  class="button prefix">Post</a></div> </div>';
                          

            }else{
                $post_id = idx($status, 'id');
                $url = idx($status, 'link');                     
                $returnHTML = '<div class="panel"><a href="https://www.facebook.com/' . he($id) . '" target="_top"><img src="https://graph.facebook.com/' . he($id) . '/picture?type=square" alt=" ' . he($name) . '"> ' . he($name) . '</a><br><br><a href="' . he($url) . '" target="_blank">' . he($message) . '</a><hr> <div class="row collapse"> <input type="text" id="' . he($post_id) . '" placeholder="Comment on their mood..."> <a href="#" class="button prefix">Post</a>  </div></div>';

            }
            
            $datResult = assignFriend($message);
            
            if($datResult == "positive" ){  ?>
              <script>
                $('#positive .friends').append('<?php echo $returnHTML; ?>');
              </script>
             <?php } elseif($datResult == "neutral"){ ?>
              <script>
                $('#neutral .friends').append('<?php echo $returnHTML; ?>');
              </script>
            <?php } elseif($datResult == "negative"){ ?>
              <script>
                $('#negative .friends').append('<?php echo $returnHTML; ?>');
              </script>
            <?php } 
            }
        }
  }
        ?>
  

  <script>
  document.write('<script src=js/vendor/' +
  ('__proto__' in {} ? 'zepto' : 'jquery') +
  '.js><\/script>')
  </script>


  <script src="js/foundation.min.js"></script>
  <script>
    $(document).foundation();
  </script>
  <!-- End Footer -->

  <?php }?>



  </body>
</html>