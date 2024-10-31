<?php

//Add admin page to the menu


add_action('wp_ajax_session_action_on', 'rwds4earth_action_log_on_business');

function rwds4earth_action_log_on_business(){

    $error = false;

    if(!isset($_POST, $_POST['biz_email'], $_POST['biz_token'], $_POST['business_id'])) $error = true;
    if(empty($_POST['biz_email']) || empty($_POST['biz_token']) || empty($_POST['business_id'])) $error = true;


    if($error){
        die(json_encode(array('error' => "Incorrect path")));
        exit();
    }

    update_option('r4earth_biz_email', sanitize_email($_POST['biz_email']));
    update_option('r4earth_biz_token', sanitize_token($_POST['biz_token']));
    update_option('r4earth_biz_id', sanitize_key($_POST['business_id']));

    $reponse = array("response" => true);

    header( "Content-Type: application/json" );
    echo json_encode($response);

    //Don't forget to always exit in the ajax function.
    exit();

}

add_action('wp_ajax_session_action_off', 'rwds4earth_action_log_off_business');

function rwds4earth_action_log_off_business(){

    update_option('r4earth_biz_email', '');
    update_option('r4earth_biz_token', '');
    update_option('r4earth_biz_id', '');

    $reponse = array("logout" => true);

    header( "Content-Type: application/json" );
    echo json_encode($response);

    //Don't forget to always exit in the ajax function.
    exit();

}


global $page;

add_action( 'admin_menu', 'rwds4earth_add_admin_page');

function rwds4earth_add_admin_page() {
  // add top level menu page

  global $page;
  $page = add_menu_page(
    'Rewards4Earth Business Page', //Page Title
    'Rewards4Earth', //Menu Title
    'manage_options', //Capability
    'r4earth_admin', //Page slug
    'rwds4earth_admin_page_html' //Callback to print html
  );

  add_action('admin_enqueue_scripts', 'rwds4earth_add_admin_scripts');

}

function rwds4earth_add_admin_scripts($hook_suffix){

    global $page;

    if($hook_suffix != $page) return;


    wp_enqueue_script( 'js-file', plugins_url( '/admin_script.js', __FILE__ ), array('jquery'));
    wp_register_script('prefix_bootstrap', plugins_url('/bootstrap.min.js', __FILE__), array('jquery'));

    wp_register_script('popperjs',   plugins_url('/popper.min.js', __FILE__), array('jquery'));


    wp_enqueue_script('prefix_bootstrap');
    wp_enqueue_script('popperjs');

    //Styles
    wp_register_style('style_bootstrap',   plugins_url('/bootstrap.min.css', __FILE__));

    wp_enqueue_style('style_bootstrap');

}


//View of business login
function rwds4earth_content_html_login(){

    $default_view = null;
    $view = isset($_GET['view']) ? sanitize_key($_GET['view']) : $default_view;

    if($view === "forgot_password"){
        echo '<div class="text-center">
                <div id="recover_password_section">
                    <h5 class="mb-3">Please insert your phone number continue</h5>
                    <div class="row justify-content-sm-center mb-3">
                        <div class="form-group col-md-2">
                            <select id="user_phone_dial" name="phone_dial" class="form-select countries_dials" required></select>
                        </div>
                        <div class="form-group col-md-2">
                            <input type="number" maxLength="10" oninput="this.value=this.value.slice(0,this.maxLength)" class="form-control" id="r4earth_biz_recovery_phone_number" placeholder="Phone Number">
                        </div>
                    </div>
                    <div class="row justify-content-sm-center">
                        <div class="form-group col-md-4 mb-3">
                            <p id="error_phone_number" class="text-danger" hidden>Phone number is incorrect</p>
                        </div>
                    </div>
                    <div class="row justify-content-sm-center mb-3">
                        <button type="button" id="btn_biz_send_phone_number" class="col-sm-2 btn btn-primary">Send</button>
                    </div>
                    <div class="row justify-content-sm-center">
                        <div class="col-sm-2 mb-3"><span>Go back to </span><a href="?page=r4earth_admin">Login</a></div>
                    </div>

                </div>
            </div>';


    }else{
        echo  '<div class="text-center row">
            <div class="col-md-4 offset-md-3">
                <h5>Please Login with your Business account</h5>
                <form >
                    <div class="form-group mb-3">
                        <label for="r4earth_biz_email">Business Email address</label>
                        <input type="email" class="form-control" id="r4earth_biz_email" aria-describedby="emailHelp" placeholder="Enter email">
                    </div>
                    <div class="form-group mb-3">
                        <label for="r4earth_biz_password">Password</label>
                        <input type="password" class="form-control" id="r4earth_biz_password" placeholder="Password">
                    </div>
                    <p id="error_password" class="text-danger" hidden>Your Email or Password is incorrect</p>

                    <button type="button" id="btn_biz_login" class="btn btn-primary">Login</button>

                </form>
                <div class="mb-3"><span>Don\'t have an account? </span><a href="https://www.rewards4earth.com">Create one!</a></div>
                <div  class="mb-3"><span>Forgot your password? </span><a href="?page=r4earth_admin&view=forgot_password">Recover it</a></div>

            </div>
        <div>';

    }

}

function sanitize_token($string) {

   return preg_replace('/[^A-Za-z0-9-_.]/', '', $string);

}

function rwds4earth_content_html_admin(){

    $default_tab = null;
    $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : $default_tab;


     //  Here are our tabs
     echo '<nav class="nav-tab-wrapper">';
        echo '<a href="?page=r4earth_admin" class="nav-tab ';  if($tab===null): echo esc_html('nav-tab-active'); endif; echo '">Company details</a>';
        // echo '<a href="?page=r4earth_admin&tab=settings" class="nav-tab '; if($tab==='settings'): echo esc_html('nav-tab-active'); endif; echo  '">Settings</a>';
      echo '</nav>';

      echo '<div class="tab-content">';
      switch($tab) :
        case 'settings':
            echo esc_html('');
            break;

        default:

            $args['headers'] = [
                'Authorization' => 'Bearer ' . sanitize_token(get_option('r4earth_biz_token')),
                'Content-Type' => 'application/json',
            ];
            $json_biz_info = wp_remote_get("https://live.rewards4earth.com/api/v1/businesses/" . sanitize_key(get_option('r4earth_biz_id')), $args);

            if(is_wp_error($json_biz_info) || !isset($json_biz_info['body'])){
                echo esc_html("Error con connection");
            }else{
                rwds4earth_display_business_info(json_decode($json_biz_info['body'], true));

            }


          break;
      endswitch;
      echo '</div>';


}


function rwds4earth_display_business_info($json_biz_info){

    echo '<br>';
    echo '<div class="row"><div class="col-md-4 offset-md-4">';
    echo '<table class="table table-striped">';
    echo '<tbody>';

    echo '<tr>
        <th>Business Name</th>
        <td id="txt_r4e_biz_name">'. esc_html($json_biz_info['business_name']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th>Business ABN</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_abn']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th>Business Address</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_address']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business City</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_city']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Postal Code</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_postal_code']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business State</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_state']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business State</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_state']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Country</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_country']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Type</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_type']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Registration Date</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_creation_date']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Description</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_description']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Email</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_email']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Phone Number</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_phone_number']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Website</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['business_website']).'</td>';
    echo '</tr>';

    echo '<tr>
        <th >Business Contact Person</th>
        <td id="txt_r4e_biz_name" >'. esc_html($json_biz_info['first_name_contact_person']).' '. esc_html($json_biz_info['last_name_contact_person']).'</td>';
    echo '</tr>';


    echo '</tbody>';
    echo '</table>';
    echo '</div></div>';
    echo '<div class="text-center"><button type="button" id="btn_biz_logout" class="btn btn-primary">Logout</button></div>';


}

function rwds4earth_admin_page_html() {
  // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap">';

    echo '<h1>'; echo esc_html( get_admin_page_title() ); echo '</h1>';
    echo '<div id="wrap_r4earth_admin_content"></div>';

    if(get_option('r4earth_biz_email') != '' && get_option('r4earth_biz_id') != ''){

        rwds4earth_content_html_admin();

    } else {
        rwds4earth_content_html_login();
    }

    echo '</div>';

}
 ?>
