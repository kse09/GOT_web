<?php
session_start();
ob_start();
ini_set('display_errors', 1);
date_default_timezone_set("asia/beirut");
if(isSet($_GET["log_me_out_please"])&&$_GET["log_me_out_please"]==="true"):
session_destroy();

header("Location: index");

endif;

if(isset($_GET["get_members"])&&$_GET["get_members"]=="true"):


$query="select team, COUNT(*) from users GROUP BY team";

try{
	//in my laptop mysql at port 3307 you may need to change this
$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$rows = $db->query($query);

header("content-type: application/json");
?>
{
	"members":  [
<?php $order=1;
$count=$rows->rowcount(); 
 foreach($rows as $row):
if($order===$count): ?>
{"team": "<?= $row[0]; ?>", "count": "<?= $row[1]; ?>"}
<?php else:?>
{"team": "<?= $row[0]; ?>", "count": "<?= $row[1]; ?>"},
<?php endif; $order++; endforeach; ?>
]
}
<?php
}catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}
endif;
if(isset($_GET["characters"])):

if($_GET["characters"]=="all"):
$query="select * from characters";

else:
$char=$_GET["characters"];
$query="select * from characters where name='$char'";
endif;
try{
	//in my laptop mysql at port 3307 you may need to change this
$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$rows = $db->query($query);
header("content-type: application/json");
?>
{
	"characters":  [
<?php
$count = $rows->rowcount();
if ($count > 0) :
$order=1;
foreach ($rows as $row) :
if($order==$count):?>
{"name": "<?= $row[0]; ?>", "house": "<?= $row[1]; ?>","story": "<?=$row[2]; ?>" ,"state": "<?=$row[3]; ?>"}
 <?php else:?>
{"name": "<?= $row[0]; ?>", "house": "<?= $row[1]; ?>","story": "<?=$row[2]; ?>" ,"state": "<?=$row[3]; ?>"},
<?php endif;$order++;
 endforeach; 
  endif;  	?>]
}
<?php
}catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}
endif;
if(isset($_POST["title"])&&isset($_POST["text"])):

if(!isset($_SESSION["logged_in_name"])):die("u need to log in first");
endif;
$files="";
$dir="./temp";
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
	/*	if (($file = readdir($dh)) !== false) {    
		   if(preg_match("/^(.)*\.([Jj][Pp][Gg]|[Pp][Nn][Gg])$/",$file)):
		   
		   $files=$file;
		 rename($dir."/".$file,"discussion_files/".$file);
		   endif;
		}
	*/
        while (($file = readdir($dh)) !== false) {    
		   if(preg_match("/^(.)*\.([Jj][Pp][Gg]|[Pp][Nn][Gg])$/",$file)):
		   
		   $files=$file;//$files.'|'.$file;
		 rename($dir."/".$file,"discussion_files/".$file);
		   endif;
        }
        closedir($dh);
		
    }
}
$title=$_POST["title"];
$text=$_POST["text"];
$date =date("y-m-d h:i:s");
$user = $_SESSION["logged_in_name"] ; 
$query="insert into discussions(title,content,files,user,upvotes,downvotes,time_posted) values('$title','$text','$files','$user',0,0,'$date')";
try{

$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$db->exec($query);
header("Location: discussions.php");

}catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}
endif;

if(isset($_GET["save_pic_temporarly"])&&$_GET["save_pic_temporarly"]=='true'):
$output_dir = "temp/";
if(isset($_FILES["file"]))
{
	$ret = array();
	$error =$_FILES["file"]["error"];

	if(!is_array($_FILES["file"]["name"])) //single file
	{
 	 	$filename = $_FILES["file"]["name"];
 		move_uploaded_file($_FILES["file"]["tmp_name"],$output_dir.$filename);
    	
	}
	else  //multiple files, file[]
	{
	  $filecount = count($_FILES["file"]["name"]);
	  for($i=0; $i < $filecount; $i++)
	  {
	  	$filename = $_FILES["file"]["name"][$i];
		move_uploaded_file($_FILES["file"]["tmp_name"][$i],$output_dir.$filename);
	  
	  }
	
	}
 }
 endif;
 if(isset($_POST["login_name"])&&isset($_POST["login_password"])):
			$name=$_POST["login_name"];
			$pass=$_POST["login_password"];
$query="select name,password from users where name='$name'";
try{
	//in my laptop mysql at port 3307 you may need to change this
$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$user_result = $db->query($query);	
$user_result = $user_result->fetch(); 
if($user_result[0]==$name&&$user_result[1]==$pass):;
$_SESSION["logged_in_name"] = $name;

$db=null;#closedb?>
<h1>you have logged in successfully</h1>
<?php
#session_write_close();
header("location: index.php");
#exit();
endif;	
}catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}		
endif;	
// sending discussions
 if(isset($_GET["discussions"])):
 $check_for_votes=isSet($_SESSION["logged_in_name"]);
if($_GET["discussions"]==="all"):
 #this is a very complicated query that GET the discussion and the first 3 comments (if moore than 3 available)
 
 if($check_for_votes):
 $username = $_SESSION["logged_in_name"];
  $query="select distinct d.title, d.time_posted, d.upvotes,d.downvotes,d.content, d.files,c1.user_commented,c1.com_time,c1.comment_text,c2.user_commented,c2.com_time,c2.comment_text,c3.user_commented,c3.com_time,c3.comment_text,d.user,d.dis_id,com.total, uv.name,dv.name from discussions d left outer join discussion_comments c1 on c1.dis_id=d.dis_id left outer join discussion_comments c2 on c2.dis_id=d.dis_id and c2.cid<>c1.cid left outer join discussion_comments c3 on c3.dis_id=d.dis_id and c3.cid<>c2.cid and c3.cid<>c1.cid left outer join (select dis_id as id,count(cid) as total from discussion_comments group by dis_id )as com on com.id=d.dis_id left outer join upvotes uv ON uv.name= '$username' AND uv.dis_id=d.dis_id left outer join downvotes dv ON dv.name= '$username' AND dv.dis_id=d.dis_id where ( c1.cid is null or (c1.cid=(select max(cid) from discussion_comments where dis_id=d.dis_id))) and ( c2.cid is null or(c2.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid))) and ( c3.cid is null or (c3.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid and cid<>c2.cid)))order by d.time_posted desc";
 else:
   $query="select distinct d.title, d.time_posted, d.upvotes,d.downvotes,d.content, d.files,c1.user_commented,c1.com_time,c1.comment_text,c2.user_commented,c2.com_time,c2.comment_text,c3.user_commented,c3.com_time,c3.comment_text,d.user,d.dis_id,com.total from discussions d left outer join discussion_comments c1 on c1.dis_id=d.dis_id left outer join discussion_comments c2 on c2.dis_id=d.dis_id and c2.cid<>c1.cid left outer join discussion_comments c3 on c3.dis_id=d.dis_id and c3.cid<>c2.cid and c3.cid<>c1.cid left outer join (select dis_id as id,count(cid) as total from discussion_comments group by dis_id )as com on com.id=d.dis_id  where ( c1.cid is null or (c1.cid=(select max(cid) from discussion_comments where dis_id=d.dis_id))) and ( c2.cid is null or(c2.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid))) and ( c3.cid is null or (c3.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid and cid<>c2.cid)))order by d.time_posted desc";

 endif;
 elseif($_GET["discussions"]==="hot"):
  if($check_for_votes):
 $username = $_SESSION["logged_in_name"];
  $query="select distinct d.title, d.time_posted, d.upvotes,d.downvotes,d.content, d.files,c1.user_commented,c1.com_time,c1.comment_text,c2.user_commented,c2.com_time,c2.comment_text,c3.user_commented,c3.com_time,c3.comment_text,d.user,d.dis_id,com.total, uv.name,dv.name from discussions d left outer join discussion_comments c1 on c1.dis_id=d.dis_id left outer join discussion_comments c2 on c2.dis_id=d.dis_id and c2.cid<>c1.cid left outer join discussion_comments c3 on c3.dis_id=d.dis_id and c3.cid<>c2.cid and c3.cid<>c1.cid left outer join (select dis_id as id,count(cid) as total from discussion_comments group by dis_id )as com on com.id=d.dis_id left outer join upvotes uv ON uv.name= '$username' AND uv.dis_id=d.dis_id left outer join downvotes dv ON dv.name= '$username' AND dv.dis_id=d.dis_id where ( c1.cid is null or (c1.cid=(select max(cid) from discussion_comments where dis_id=d.dis_id))) and ( c2.cid is null or(c2.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid))) and ( c3.cid is null or (c3.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid and cid<>c2.cid)))order by d.upvotes desc";
 else:
   $query="select distinct d.title, d.time_posted, d.upvotes,d.downvotes,d.content, d.files,c1.user_commented,c1.com_time,c1.comment_text,c2.user_commented,c2.com_time,c2.comment_text,c3.user_commented,c3.com_time,c3.comment_text,d.user,d.dis_id,com.total from discussions d left outer join discussion_comments c1 on c1.dis_id=d.dis_id left outer join discussion_comments c2 on c2.dis_id=d.dis_id and c2.cid<>c1.cid left outer join discussion_comments c3 on c3.dis_id=d.dis_id and c3.cid<>c2.cid and c3.cid<>c1.cid left outer join (select dis_id as id,count(cid) as total from discussion_comments group by dis_id )as com on com.id=d.dis_id  where ( c1.cid is null or (c1.cid=(select max(cid) from discussion_comments where dis_id=d.dis_id))) and ( c2.cid is null or(c2.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid))) and ( c3.cid is null or (c3.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid and cid<>c2.cid)))order by d.upvotes desc";

 endif;
 elseif($_GET["discussions"]==="uploaded_by_me"):
  if($check_for_votes):
 $username = $_SESSION["logged_in_name"];
  $query="select distinct d.title, d.time_posted, d.upvotes,d.downvotes,d.content, d.files,c1.user_commented,c1.com_time,c1.comment_text,c2.user_commented,c2.com_time,c2.comment_text,c3.user_commented,c3.com_time,c3.comment_text,d.user,d.dis_id,com.total, uv.name,dv.name from discussions d left outer join discussion_comments c1 on c1.dis_id=d.dis_id left outer join discussion_comments c2 on c2.dis_id=d.dis_id and c2.cid<>c1.cid left outer join discussion_comments c3 on c3.dis_id=d.dis_id and c3.cid<>c2.cid and c3.cid<>c1.cid left outer join (select dis_id as id,count(cid) as total from discussion_comments group by dis_id )as com on com.id=d.dis_id left outer join upvotes uv ON uv.name= '$username' AND uv.dis_id=d.dis_id left outer join downvotes dv ON dv.name= '$username' AND dv.dis_id=d.dis_id where d.user='$username' AND ( c1.cid is null or (c1.cid=(select max(cid) from discussion_comments where dis_id=d.dis_id))) and ( c2.cid is null or(c2.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid))) and ( c3.cid is null or (c3.cid=(select max(cid) from discussion_comments  where dis_id=d.dis_id and cid<>c1.cid and cid<>c2.cid)))order by d.time_posted desc";
 else:
die('You Should log in to view your posts');
 endif;
 endif;
 try{
 $db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$rows = $db->query($query);	
	 header("content-type: application/xml"); ?>
<?xml version="1.0" encoding="utf-8"?>
<discussions>
<?php if ($rows->rowcount() > 0) :
 foreach ($rows as $row) : 
 $is_upvoted = $check_for_votes?($row[18]===$username?"yes":"no"):"not_applicable";
  $is_downvoted = $check_for_votes?($row[19]===$username?"yes":"no"):"not_applicable";
 $txt=xml_entities($row[4]);?>
<discussion id="<?=$row[16];?>" title="<?=$row[0];?>" datetime="<?=$row[1]?>" upvotes="<?=$row[2]?>" downvotes="<?=$row[3]?>" postedBy="<?=$row[15]?>" upvoted_by_me="<?=$is_upvoted?>" downvoted_by_me="<?=$is_downvoted?>" >
		<text> <?=$txt ?></text>
	<?php if($row[5]!==null):?>	<img><?= $row[5]; ?></img><?php endif;?>
	<comments count="<?=$row[17];?>" > <?php
	for($i=6;$row[$i]!==null&&$i<13;$i=$i+3): ?>
		<comment user_commented="<?= $row[$i]; ?>" comment_datetime="<?= $row[$i+1]; ?>">
		<?= $row[$i+2]; ?>
		</comment>
	<?php endfor;?>
	</comments>
</discussion>   <?php endforeach;   endif;	
?></discussions><?php	  
 }catch (pdoexception $e){
	die("connection failed: " . $e->GETmessage());
}		
 endif;
 function xml_entities($string) {#used to escape xml spcial characters
    return preg_replace(array("/</","/>/", "/\"/",  "/'/",  "/&/"),array("&lt;","&gt;", "&quot;", "&apos;","&amp;"  ) , $string
    );
}
function is_photo_uploaded_and_moved($charac) {
	// bail if there were no upload forms
   if(empty($_FILES)):
       return false;
endif;
   $file = $_FILES['cphoto']['tmp_name']; // check for uploaded photo
	if( !empty($file) && is_uploaded_file( $file )):
            move_uploaded_file($file, "characters/$charac.jpg");//rename and move
            return true;
        endif;
  
    // return false if no files were found
   return false;
}
//next is deleting dis_id
if(isSet($_GET['delete_discussion'])):
$id=$_GET['delete_discussion'];
$query = "DELETE FROM discussions WHERE dis_id='$id'";

try{

$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$db->exec($query);

}catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}

endif;
//next:handeling upvotes and downvotes
if(isset($_GET['user_voted'])&&isset($_GET['dis_id'])):
$dis_id=$_GET['dis_id'];
$name=$_GET['user_voted'];
if(isset($_GET['vote'])):
if($_GET['vote']=='up'):
$query = "select * from upvotes where dis_id=".$dis_id." and name='".$name."'";
$query1 = "insert into upvotes values(".$dis_id.",'".$name."')";

elseif($_GET['vote']=='down'):
$query = "select * from downvotes where dis_id=".$dis_id." and name='".$name."'";
$query1 = "insert into downvotes values(".$dis_id.",'".$name."')";
endif;

try{

$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$already_there = $db->query($query);
if($already_there->rowcount() === 0):
$db->exec($query1);
endif;


}catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}
endif;

if(isset($_GET['unvote'])):
if($_GET['unvote']=='up'):
$query1 = "delete from upvotes where dis_id=".$dis_id." and name='".$name."'";
elseif($_GET['unvote']=='down'):
$query1 = "delete from downvotes where dis_id=".$dis_id." and name='".$name."'";
endif;

try{

$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$db->exec($query1);

}catch (pdoexception $e){	
	die("connection failed: " . $e->GETmessage());
}
endif;

endif;

//end of upvotes and downvotes thingies

//next is inserting comment into the database
if(isset($_GET["name"])&&isset($_GET["dis_id"])&&isset($_GET["comment_text"])):
$date =date("y-m-d h:i:s");
$name=$_GET["name"];
$dis_id=$_GET["dis_id"];
$comment=$_GET["comment_text"];
$query="insert into discussion_comments(dis_id,user_commented,comment_text,com_time) values($dis_id,'$name','$comment','$date')";
try{

$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$db->exec($query);

}catch (pdoexception $e){	
	die("connection failed: " . $e->GETmessage());
}

endif;

//end of comment insertion
//start of join team
if(isset($_GET["join_team"])):
			$name=$_SESSION["logged_in_name"];
			$team=$_GET["join_team"];
$query="UPDATE users SET team='$team' WHERE name='$name'";
try{
	//in my laptop mysql at port 3307 you may need to change this
$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$user_result = $db->exec($query);	




$db=null;#closedb


	
}catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}	

endif;
//end of join team

//update votes and comments
if(isset($_GET["update_me_on_discussion"])||(isset($_GET["name"])&&isset($_GET["dis_id"])&&isset($_GET["comment_text"]))):
$dis_id = isset($_GET["update_me_on_discussion"])?$_GET["update_me_on_discussion"]:$_GET["dis_id"];


$query="select d.upvotes,d.downvotes from discussions d  where dis_id=$dis_id";
	$query2="select c.user_commented,c.comment_text,c.com_time from discussion_comments c where dis_id=$dis_id ORDER BY c.com_time";
 try{
 $db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$votes = $db->query($query);	
$vote =$votes->fetch();
$upvotes=$vote[0];
$downvotes=$vote[1];
$rows = $db->query($query2);
$count=$rows->rowcount();
	 header("content-type: application/xml"); ?>
<?xml version="1.0" encoding="utf-8"?>
<discussion id="<?=$dis_id?>" upvotes="<?=$upvotes?>" downvotes="<?=$downvotes?>">
	<comments count="<?=$count?>" > 
	<?php if ( $count> 0) :
 foreach ($rows as $row) : ?>
		<comment user_commented="<?=$row[0]; ?>" comment_datetime="<?=$row[2]; ?>">
				<?= $row[1]; ?>
		</comment>
	<?php endforeach;endif; ?>
	</comments>
</discussion>     	
	  <?php
 }catch (pdoexception $e){
	die("connection failed: " . $e->GETmessage());
}

endif;

//end of update votes and comments
//add character 

if(isset($_POST["cname"])&& isset($_POST["house"])&& isset($_POST["cstory"])&& isset($_POST["state"])){
	$cname=$_POST["cname"];
	$house=$_POST["house"];
	$cstory=xml_entities($_POST["cstory"]);
	$state=$_POST["state"];
	if(isset($_FILES["cphoto"])&& $_FILES["cphoto"]!=null){is_photo_uploaded_and_moved($cname);
	
		$query="insert into characters(name,house,story,state) values('$cname','$house','$cstory','$state')";
try{

$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$db->exec($query);

header("location: ./admin/adminpage.php?st=success");



}catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}
	}
	}

//add new admin 
if( isset( $_POST["aname"]))
{
$anam=$_POST["aname"];
try{
$query1="select name, password, email from users where name='$anam'";

$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$rows= $db->query($query1);
$row= $rows->fetch();
if ($rows->rowcount() > 0) {
	$query="insert into admin(name, password,email) values ('".$row["name"]."','".$row["password"]."','".$row["email"]."')";
 
$db->exec($query);

header("location: ./admin/adminpage.php?st=success");
}


}
catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}
	}
	/***************************/
	//nexttimer
if( isset( $_POST["nexttimer"]))
{
$ntime=$_POST["nexttimer"];
try{
$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);

	$query="update editable set val='$ntime' where Things='timer'";
 
$db->exec($query);

header("location: ./admin/adminpage.php?st=success");
}
catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}
	}	
	/**********************************************/
	//change video

if (isset($_FILES['cvideo'])) {

$vd=$_FILES['cvideo']['name'];
   $file = $_FILES['cvideo']['tmp_name']; // check for uploaded video
	if( !empty($file) && is_uploaded_file( $file )):
            move_uploaded_file($file, "./assets/main_page_video/".$_FILES['cvideo']['name']);//rename and move
      
	  endif;

try{
$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);

	$query="update editable set val='$vd' where Things='video'";
 
$db->exec($query);

header("location: ./admin/adminpage.php?st=success");
}
catch (pdoexception $e){
	
	die("connection failed: " . $e->GETmessage());

}
	}
/**************************************/
		//add photo
	/**************************************/
if (isset($_POST["tm"]))
{ 
$tm= $_POST["tm"];
if(isset ($_FILES['pic'])){
   $file = $_FILES['pic']['tmp_name']; // check for uploaded photo
	if( !empty($file) && is_uploaded_file( $file )):
            move_uploaded_file($file, "./assets/main_page_images/".$tm."/".$_FILES['pic']['name']);//rename and move
       header("location: ./admin/adminpage.php?st=success");
	   endif;
}
else { header("location: ./admin/adminpage.php?st=fail");}
}
	/**********************************************/
	//get images for main page
if(isset($_GET["mainImages"])){
	if($_GET["mainImages"]=="all"){
		if (isset($_SESSION['logged_in_name'])) {
			$query="select team from users where name='". $_SESSION['logged_in_name'] ."'";
		try{

	$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
	$t = $db->query($query);
	$team=$t->fetchColumn();

}
catch (pdoexception $e){
die("connection failed: " . $e->GETmessage());
}
	//json to get images
$images = glob("./assets/main_page_images/".$team."/*.jpg");
	} else {$images = glob("./assets/main_page_images/none/*.jpg");}
header("Content-type: application/json");?>
{ "imag": [
<?php
$i=0;
$ilen= count($images);
foreach ($images as $image) :
if(++$i!==$ilen){

?>
{"source": "<?=$image?>" },
<?php  }
else{?>
{"source":"<?=$images[$ilen-1]?>" }]}
<?php } endforeach; }}
/**********************************************/
//get timer to json 
if(isset($_GET["timer"])){
if($_GET["timer"]=="yes"){
	$query="select val from editable where Things='timer'";
try{

$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
	$t = $db->query($query);
	$time=$t->fetchColumn();
}
catch (pdoexception $e){
die("connection failed: " . $e->GETmessage());
}
header("Content-type: application/json");?>
{ "timer": [
{"time": "<?= $time ?>" }]}

 <?php }}


#next: contact us mail sending
 if(isSet($_POST['msg_name'])&&isSet($_POST['msg_body'])&&isSet($_POST['msg_subject'])&&isSet($_POST['msg_email'])&&$_POST['msg_email']!==""):
 $subject = "GOT_WEB:".$_POST['msg_subject'];
 $message = $_POST['msg_name'].":\n";

 $email=$_POST['msg_email'];

   $headers = "From:$email";

 $message=$message.$_POST['msg_body'];
 #$message = wordwrap($message,70);
 mail("khd.sayed@gmail.com", $subject,$message,$headers);
 header("Location: ./about_us.php?sent=yes");
 endif;
 
 




  /*******************************************/
  /*admin login*/
   if(isset($_POST["AdminName"])&&isset($_POST["AdminPasswd"])){
			$name=$_POST["AdminName"];
			$pass=$_POST["AdminPasswd"];
$query="select name, password from admin where name='$name'";
try{
	//in my laptop mysql at port 3307 you may need to change this
$db = new pdo("mysql:host=localhost:3307;dbname=got", "root", "");
//$db->setattribute(pdo::attr_errmode, pdo::errmode_exception);
$user_result = $db->query($query);	
$user_result = $user_result->fetch(); 
if($user_result[0]==$name && $user_result[1]==$pass){
$db=null;#closed
$_SESSION["AdminName"] = $name;
header("location: ./Admin/adminpage.php");
#exit();
} 
else {header("location: ./Admin/index.php?status=fail");}
}
catch (pdoexception $e){
die("connection failed: " . $e->GETmessage());
}
   }
/*****************************************************/
	?>

