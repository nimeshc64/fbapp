<?php
######### edit details ##########
$appId              = '537180863122064'; //Facebook App ID
$appSecret          = 'f1f866dafe0fca19eba0e88945633a95'; // Facebook App Secret
$return_url         = 'https://dewz.esy.es/';  //return url (url to script)
$temp_folder        = 'tmp/'; //temp dir path to store images
$image_id_png       = 'assets/commen.png'; //  image template path
$font               = 'assets/fonts/DidactGothic.ttf'; //font used
#################################


// check if curl is enabled
if (!in_array  ('curl', get_loaded_extensions())){die('curl required!');} 

//include facebook SDK
include_once("inc/facebook.php"); 

//Facebook API
$facebook = new Facebook(array(
  'appId'  => $appId,
  'secret' => $appSecret,
));


if(isset($_GET["logout"]) && $_GET["logout"]==1)
{
    //Destroy the current session and logout user
    $facebook->destroySession();
    header('Location: '.$return_url);
}

//get facebook user
$fbuser = $facebook->getUser();


//check user session
if(!$fbuser) 
{
    //new users get to see this login button
    $loginUrl = $facebook->getLoginUrl(array('scope' => $fbPermissions,'redirect_uri'=>$return_url));
    echo '<div style="margin:20px;text-align:center;"><a href="'.$loginUrl.'"><img src="assets/facebook-login.png" /></a></div>';
}
else
{    
     //get user profile
     try {
        $user_profile = $facebook->api('/me');
        
        //list of user granted permissions
        $user_permissions = $facebook->api("/me/permissions"); 
      } catch (FacebookApiException $e) {
        echo $e;
        $fbuser = null;
      }
     
   
    //display logout url
    echo '<div>'.$user_profile["name"].' [<a href="?logout=1">Log Out</a>]</div>'; 
    ###### start generating ID ##########
    
    //copy user profile image from facebook in temp folder
    if(!copy('http://graph.facebook.com/'.$fbuser.'/picture?width=100&height=100',$temp_folder.$fbuser.'.jpg'))
    {
        die('Could not copy image!');
    }

    ##### start generating Facebook APP Image ########
    $dest = imagecreatefrompng($image_id_png); // source  image template
    $src = imagecreatefromjpeg($temp_folder.$fbuser.'.jpg'); //facebook user image stored in our temp folder
    
    imagealphablending($dest, false); 
    imagesavealpha($dest, true);
    
    //merge user picture with  image template
    //need to play with numbers here to get alignment right
    imagecopymerge($dest, $src, 320, 32, 0, 0, 100, 100, 100); 
    
    //colors we use for font
    $facebook_blue = imagecolorallocate($dest, 255, 255, 255); // Create white color
    $facebook_grey = imagecolorallocate($dest, 74, 74, 74); // Create grey color
    
    //Texts to embed into  image template
    $txt_user_id        = $fbuser;
    $txt_user_name      = isset($user_profile['name'])?$user_profile['name']:'No Name';
//    $txt_user_gender    = isset($user_profile['gender'])?$user_profile['gender']:'No gender';
    $txt_credit         = 'Developed by www.programmer.lk';
    
    $txtLuckyNo = substr($txt_user_id, -1);
    

    imagealphablending($dest, true); //bring back alpha blending for transperent font
    
    imagettftext($dest, 72, 0, 170, 190, $facebook_grey , $font, $txtLuckyNo); //Write Lucky NO 
    imagettftext($dest, 15, 0, 290, 165, $facebook_grey, $font, $txt_user_name); //Write name 
 //   imagettftext($dest, 15, 0, 25, 147, $facebook_grey, $font, $txt_user_gender); //Write gender 
    imagettftext($dest, 8, 0, 25, 270, $facebook_blue, $font, $txt_credit); //Write credit link 
        
    imagepng($dest, $temp_folder.'id_'.$fbuser.'.jpg'); //save  in temp folder

	//now we have generated , we can display or post it on facebook
    echo '<img src="tmp/id_'.$fbuser.'.jpg" >'; //display saved user 
    
	
    imagedestroy($dest);
    imagedestroy($src);
}
?>
