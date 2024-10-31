
//Form Vars
var btn_login;
var info_p;
var div_r4earth;


//On document loads instantiate elements and button event
jQuery(document).ready(function(){
    btn_login = jQuery("#r4earth_button_login");
    info_p = document.getElementById("result_info");
    div_r4earth = document.getElementById("r4earth_div");

    //On user login is pressed
    btn_login.click( function(){
        if(jQuery("#r4earth_button_login").data("status") == false){
            onLogin();
        }else{
            logOut();
        }

    });


});

//Check if email is null
function isUserPasswdNull(email){

    var isValid = false;

    if(email != ""){
        isValid = true;
    }

    return isValid;
}


//Function to login when button is pressed
function onLogin(){
    if(isUserPasswdNull(jQuery("#r4earth_field_email").val())){

        loginUser(jQuery("#r4earth_field_email").val(), jQuery("#r4earth_field_passwd").val());
    }else{

        setLoginStatusInfo("block", "block", "Email can't be null");
    }

}

//Change status of content on login box
function setLoginStatusInfo(div_r4earth_status, p_info_status, p_info_content){

    div_r4earth.style.display = div_r4earth_status;

    info_p.style.display = p_info_status;
    info_p.innerHTML = p_info_content;
}

//Change button content on login or out
function changeButtonLogInOut( status){

    if(status == true){
        jQuery("#r4earth_button_login").data("status", true)

        jQuery("#r4earth_button_login").text("Logout");

    }else{
        jQuery("#r4earth_button_login").data("status", false)
        jQuery("#r4earth_button_login").text("Login");

    }

}

//Function on log out
function logOut(){

    deleteCookie("user_email");
    deleteCookie("user_id");
    deleteCookie("user_token");

    changeButtonLogInOut( false);
    setLoginStatusInfo("block", "none", "");

}

function deleteCookie(name){
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';

}

//Function to login user. Ajax call

function sendUserLogged(email, user_id, token){
    var json =  {action: 'user_session_on',  user_email: email, user_id: user_id, user_token:token};
    jQuery.ajax({
        type: "POST",
        url: my_ajax_object.ajax_url,
        data: json,
        ContentType: "application/json",
       success: function(data) {
           console.log(data);
           if(data.user_email){
               setLoginStatusInfo("none", "block", "You are logged in with email: "+ data.user_email);
               changeButtonLogInOut( true);

           }

       }
   });

}



function loginUser( email, password){

    var url = "https://api.rewards4earth.com/api/v1/login";
    var json =  {user_email: email, user_password: password, type: "user"};
    jQuery.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(json),
        contentType: "application/json",
       success: function(data) {
           console.log(data);
           if(data.token){
               sendUserLogged(email, data.result.user_id, data.token);

           }

       }, error: function(error){
           console.log(error.responseJSON.message);
           setLoginStatusInfo("block", "block", "Incorrect Email or Password");
       }
   });


}
