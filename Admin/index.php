<?php 
if (isset($_GET["destroy"])){
if($_GET["destroy"]==1){session_start(); session_destroy();}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>login</title>
<script type="text/javascript">
function validate(){
	if(check_name() && check_password()){ return true;}
	else alert("invalid data"); return false;
}
function check_name(){
var name = document.getElementById("nam");
	name.style.backgroundColor="#f2f2f2";
if(name.value.length==0){
		name.style.backgroundColor="#ff6666";
		document.getElementById("submit").disabled="disabled";
		return false;
	}
	return true;
}

function check_password(){
var pass = document.getElementById("pass1");
	pass.style.backgroundColor="#f2f2f2";
if(pass.value.match(/^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9]+)$/)==null){
		pass.style.backgroundColor="#ff6666";
		document.getElementById("submit").disabled="disabled";
	return false;
	}
	return true;
}


</script>
<style>
body{	background: url("../signup.jpg") no-repeat center center fixed;
  background-size: cover;
   -webkit-background-size: cover;
-moz-background-size: cover;}

#signup{
	width:100%;
	height:100%;

  -o-background-size: cover;
  font-family: "Cursive", sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale; 
  color:white;
}
#register-form {

	 position: relative;
  z-index: 1;

  max-width: 360px;
  margin: 0 auto 300px;
  padding: 100px;
  text-align: center;
  box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
}	
#register-form 	.data {
  font-family: "Cursive", sans-serif;
  outline: 0;
  background:#f2f2f2;
  width: 100%;
  border: 0;
  margin: 0 0 15px;
  padding: 15px;
  box-sizing: border-box;
  font-size: 14px;
 
}
#register-form  #submit{
  font-family: "Cursive", sans-serif;
  text-transform: uppercase;
  outline: 0;
  background: #800000;
  width: 100%;
  border: 0;
  padding: 15px;
  color: #FFFFFF ;
  font-size: 14px;
  -webkit-transition: all 0.3 ease;
  transition: all 0.3 ease;
  cursor: pointer;
}
#register-form #submit:hover,#register-form #submit:active,#register-form #submit:focus {
  background: #FF0000;
}
#signup_fieldset { margin: auto; 

width:100%;
}
</style>
</head>
<body>
<div id="signup">
<form id="register-form" action="../server.php" method="POST" enctype="multipart/form-data" Onsubmit= "return validate();">
	<fieldset id="signup_fieldset">
		<legend>Admin Login:</legend>

		<div>
			<strong>Name:</strong>
			<input class="data" id="nam" type="text" name="AdminName" size="16" onblur="check_name();" />
		</div>

		<div>
			<strong>Password:</strong>
			<input class="data" id="pass1" type="password" name="AdminPasswd" size="16" onblur="check_password()"  />
		</div>


		<div>
			<input id="submit" type="submit" value="Login" />
		</div>
		
	</fieldset>
	<?php
if(isset($_GET["status"])){
	if($_GET["status"]=="fail"){?>
<div> wrong username or password</div>
<?php }}
?>
</form>
</div>

</body>
</html>