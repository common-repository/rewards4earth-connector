jQuery(document).ready(function(){
    //On business login is pressed

    let url_params = new URLSearchParams(window.location.search);
    if(url_params.has("view") && url_params.get("view") === "forgot_password"){
        insertDials();
    }

    jQuery("#btn_biz_login").click(function(){

        onBusinessLogin();
    });

    jQuery("#btn_biz_send_phone_number").click(function(){
        checkPhoneNumber();
    });

    jQuery("#btn_biz_logout").click(function(){
        onBusinessLogout();
    });


});

//Insert dials in select
function insertDials(){
    jQuery.ajax({
        url: "https://restcountries.com/v2/all",
        type: "GET",
        success: function(response) {

            var html = "";
            for (var i = 0; i < response.length; i++) {

                html += "<option data-value='"+response[i].alpha3Code+"' value='+"+response[i].callingCodes[0]+"'>" + response[i].name + " (+"+ response[i].callingCodes[0] +") " + "</option>";

            }

            jQuery("#user_phone_dial").html(html);

        },
        error: function(response, error) {
           console.log(error)
        }
    });
}


//CHECK FOR TOKEN
function checkSessionAvailable(){
    var token = window.localStorage.getItem("x-token");

}

//Check phone number on recovery password

function checkPhoneNumber(){
    // var phone_number = jQuery("#user_phone_dial").val() + jQuery("#r4earth_biz_recovery_phone_number").val();
    var phone_number = jQuery("#r4earth_biz_recovery_phone_number").val();

    if(phone_number != "" || phone_number !== null){
        var url = "https://live.rewards4earth.com/api/v1/sms"
        var data = {to: phone_number, type: "business"}
        jQuery.ajax({
            type: "POST",
            url: url,
            contentType: "application/json",
            data: JSON.stringify(data),
            success: function(data) {
                console.log(data)
               if(data.error){
                   jQuery("#error_phone_number").attr("hidden", false);
               }else{
                   console.log(data)
                   jQuery("#error_phone_number").attr("hidden", true);jQuery("#error_phone_number").attr("hidden", true);
                   displayPinScreen(phone_number, data.token);
               }

           },
           error: function(error){
                jQuery("#error_phone_number").attr("hidden", false);
           }
       });

    }else{
        jQuery("#error_phone_number").attr("hidden", false);

    }

}

//Insert received pin for recovey Password

function displayPinScreen(phone_number, token){
    jQuery("#recover_password_section").html(
        '<h5>You should receive a SMS with a PIN</h5>'+
        '<div class="row justify-content-sm-center mb-3">'+
            '<div class="form-group col-md-3 mb-3">'+
                '<input type="number" class="form-control" id="r4earth_biz_recovery_pin_number" oninput="this.value=this.value.slice(0,this.maxLength)" maxlength="6" placeholder="Pin Number">'+
            '</div>'+
        '</div>'+
        '<div class="row justify-content-sm-center">'+
            '<div class="form-group col-md-3 mb-3">'+
                '<p id="error_pin_number" class="text-danger" hidden>Pin is incorrect</p>'+
            '</div>'+
        '</div>'+
        '<div class="row justify-content-sm-center mb-3">'+
            '<button type="button" id="btn_biz_send_pin_number" class="col-sm-2 btn btn-primary">Send</button>'+
        '</div>'+
        '<div class="row justify-content-sm-center mb-3">'+
            '<div  class="mb-3"><span>Go back to </span><a href="?page=r4earth_admin">Login</a></div>'+
        '</div>'
    );

    jQuery("#btn_biz_send_pin_number").click(function(){
        sendPin(phone_number, token);
    });
}

// On pin sent for change password

function sendPin(phone_number, token){
    var pin = jQuery("#r4earth_biz_recovery_pin_number").val();

    //Change for ajax in future

    if(pin === "" || pin === null){
        jQuery("#error_pin_number").attr("hidden", false);

    }else{
        // In server part on send ajax
        var url = "https://live.rewards4earth.com/api/v1/sms/"+phone_number+"/verify/"+pin+"/business"
        jQuery.ajax({
            type: "GET",
            url: url,
            //contentType: "application/json",
            //data: JSON.stringify(data),
            headers:{
                "Authorization": "Bearer "+token,
            },

            success: function(data) {
               if(data.error){
                   jQuery("#error_pin_number").attr("hidden", false);
                   console.log(data)
               }else{
                   console.log(data)
                   jQuery("#error_pin_number").attr("hidden", true);
                   displayPasswordChangeScreen(token, data.business_id);
               }

           },
           error: function(error){
               console.log(error)
               jQuery("#error_pin_number").attr("hidden", false);
           }
       });


    }

}

//Set password change screen

function displayPasswordChangeScreen(token, business_id){
    jQuery("#recover_password_section").html(
        '<h5>Please Insert your new password</h5>'+
        '<div class="row justify-content-sm-center mb-3">'+
            '<div class="col-md-3 form-group mb-3">'+
                '<label for="r4earth_biz_new_password">New Password</label>'+
                '<input type="password" class="form-control" id="r4earth_biz_new_password" placeholder="New Password">'+
            '</div>'+

        '</div>'+
        '<div class="row justify-content-sm-center mb-3">'+
            '<div class="col-md-3 form-group mb-3">'+
                '<label for="r4earth_biz_repeat_new_password">Repeat New Password</label>'+
                '<input type="password" class="form-control" id="r4earth_biz_repeat_new_password" placeholder="Repeat New Password">'+
            '</div>'+
        '</div>'+

        '<div class="row justify-content-sm-center">'+
            '<div class="form-group">'+
                '<p id="error_password_change" class="text-danger" hidden>Passwords are not the same</p>'+
            '</div>'+
        '</div>'+
        '<div class="row justify-content-sm-center mb-3">'+
            '<button type="button" id="btn_biz_change_passwords" class="col-md-2 btn btn-primary">Set New Password</button>'+

        '</div>'+
        '<div class="row justify-content-sm-center">'+
            '<div  class="mb-3"><span>Go back to </span><a href="?page=r4earth_admin">Login</a></div>'+

        '</div>'


    );

    jQuery("#btn_biz_change_passwords").click(function(){
        sendPasswords(token, business_id);
    })
}

function sendPasswords(token, business_id){
    var password = jQuery("#r4earth_biz_new_password").val();
    var r_pasword = jQuery("#r4earth_biz_repeat_new_password").val();



    if(password === r_pasword){
        jQuery("#error_password_change").attr("hidden", true);
        //send passwords with token
        var data = {"business_password": password}
        var url = "https://live.rewards4earth.com/api/v1/businesses/"+business_id
        jQuery.ajax({
            type: "PUT",
            url: url,
            data: JSON.stringify(data),
            contentType: "application/json",
            //contentType: "application/json",
            //data: JSON.stringify(data),
            headers:{
                "Authorization": "Bearer "+token,
            },

            success: function(data) {
               if(data.error){

                   alert(data.error)
               }else{
                   console.log(data)
                   alert("Password changed successfully")
                   location.reload();

               }

           },
           error: function(error){
               console.log(error)
               jQuery("#error_pin_number").attr("hidden", false);
           }
       });

    }else{
        jQuery("#error_password_change").attr("hidden", false);

    }
}

//SEND REQUEST TO PHP IF SESSION IS OPEN OR NOT
function sendSessionOn( email, token, business_id){
    var json =  {action: 'session_action_on', biz_email: email, business_id: business_id, biz_token: token};
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: json,
       success: function(data) {
           console.log(data);
           location.reload();

       }
   });

}

function sendSessionOff(user_logged){
    var json =  {action: 'session_action_off'};
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: json,
       success: function(data) {
           console.log(data);
           location.reload();

       }
   });

}

//Function when the business log logOut
function onBusinessLogout(){
    sendSessionOff(0);
}

//Function to login when button is pressed
function onBusinessLogin(){
    if(isUserPasswdNull(jQuery("#r4earth_biz_email").val())){

        loginUser(jQuery("#r4earth_biz_email").val(), jQuery("#r4earth_biz_password").val());
    }else{

        //SHOW ERROR
    }

}

//check if email is null
function isUserPasswdNull(email){

    var isValid = false;

    if(email != ""){
        isValid = true;
    }

    return isValid;
}

function loginUser(email, password){
    var url = "https://live.rewards4earth.com/api/v1/login";
    var json =  {business_email: email, business_password: password, type: "business"};
    jQuery.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(json),
        contentType: "application/json",
       success: function(data) {
           console.log(data);
           if(data.token){
               console.log("Success login");
               jQuery("#error_password").attr("hidden", true);
               //window.localStorage.setItem("x-token", "asjdasldj");

               sendSessionOn(email, data.token, data.business_id);

           }
           jQuery("#error_password").attr("hidden", true);

       }, error: function(error){
           console.log(error);
           jQuery("#error_password").attr("hidden", false);
       }
   });


}

//********************PIN JAVASCRIPT CODE**********************************



//**************************FINISH******************************
