<?php

/**
 *	Central controller for the entire webnaplo application 
 *
 *	@author Team WebnNaplo
 *	@date 06/11/2011 - Sunday
 **/

// We're using the Limanade Micro PHP Framework
require_once("lib/limonade/limonade.php");

// Start the session for the application right here
session_start();

/**
 *	Setting the Configuration for the system
 **/
function configure() {
	option('env', ENV_PRODUCTION);
	// option('env', ENV_DEVELOPMENT);
	option('limonade_public_dir', file_path(dirname(__FILE__), 'lib', 'limonade', 'public'));
	option('limonade_views_dir', file_path(dirname(__FILE__), 'lib', 'limonade', 'views'));
	option('error_views_dir',    option('limonade_views_dir'));
	option('controllers_dir', file_path(dirname(__FILE__), 'controllers'));
	option('reports_dir', file_path(dirname(__FILE__), 'export'));
	option('gzip', true);
	
	// System Settings 
	option('SYSTEM_VERSION', '1.1');
	option('SYSTEM_NAME', 'WebNaplo');
	
	// Include all the models to the system
	require_once_dir(file_path(dirname(__FILE__), 'models'));
	require_once_dir(file_path(dirname(__FILE__), 'lib'));
	require_once_dir(file_path(dirname(__FILE__), 'lib' , 'wkhtmltopdf'));

	// Include the configuration file
	include("config.php");
	
	$db = new db("mysql:host=$db_host;port=$db_port;dbname=$db_name", "$db_user", "$db_pass");
	$db->setErrorCallbackFunction("showError", "text");
	
	$GLOBALS['db'] = $db;
}

/**
 * Utility function that returns the curent instance of the user using the system
 **/
function get_user() {
	if(isset($_SESSION['user'])) return User::load(get_object_vars($_SESSION['user']));
	
	return new User;
}

/**
 *	Callback function of PDO Library
 **/
function showError($message) {
	// header("Content-type: application/json");
	// return json(array("status" => false, "message" => $message));
	halt(SERVER_ERROR, $message);
}

/**
 *	Hook that is to be executed before processing any request
 **/
function before($route) {
	// header("X-LIM-route-function: ".$route['callback']);
	// layout('layout.html.php');
	$route_pattern = $route['pattern'];

	$func_calls_no_user_session = array('user_login', 'user_login_authenticate', 'user_logout', 'add_student_proxy', 'dataentry_report_list');
	if(!in_array($route['callback'], $func_calls_no_user_session)) {
		// redirect('/');
		if(!isset($_SESSION['user'])) {
			flash("error", "You need to login to view the requested resource");
			redirect('/user/login');
			// redirect(htmlspecialchars_decode(url_for('/user/login/', array("redirect" => $route['pattern'])), ENT_NOQUOTES));
		} else {
			// Get the current User instance using the application
			$user = get_user();
			
			$access = -1;
			
			// Caculating the Access level of the route based on the callback function
			if(preg_match('/^admin_*/', $route['callback'], $match) > 0) {
				// Admin Access Route to be enabled nly by the admin users
				$access = 0;
			} else if(preg_match('/^dataentry_*/', $route['callback'], $match) > 0) {
				$access = 1;
			} else if(preg_match('/^staff_*/', $route['callback'], $match) > 0){
				$access = 2;
			} else if(preg_match('/^student_*/', $route['callback'], $match) > 0) {
				$access = 3;
			} else {
				$access = -1;
			}
			
			// Now decide if the user has the access to access the requested resource
			if($user->accessLevel > $access || $user->accessLevel == -1) {
				halt(HTTP_FORBIDDEN, "Sorry hacker, your request cannot be handled. ");
			}
		}
	}
}
/**
 *	Dispatch routes follow
 *	DO NOT EDIT BELOW THIS LINE UNTIL YOU KNOW WHAT YOU ARE DOING
 **/

// -------------------------------------------
// Central Dispatch for Dataentry module
// -------------------------------------------
dispatch_get('/dataentry/home/', 'dataentry_home');
dispatch_post('/dataentry/changepass/', 'dataentry_changepass');

// -------------------------------------------
// Delete Dataentry controllers
// -------------------------------------------
dispatch_get('/dataentry/course/delete/', 'delete_course_render');
dispatch_post('/dataentry/course/delete/', 'delete_course_post');

dispatch_get('/dataentry/student/delete/', 'delete_student_render');
dispatch_post('/dataentry/student/delete/', 'delete_student_post');

dispatch_get('/dataentry/staff/delete/', 'delete_staff_render');
dispatch_post('/dataentry/staff/delete/', 'delete_staff_post');

dispatch_get('/dataentry/programme/delete/', 'delete_programme_render');
dispatch_post('/dataentry/programme/delete/', 'delete_programme_post');

dispatch_get('/dataentry/department/delete/', 'delete_department_render');
dispatch_post('/dataentry/department/delete/', 'delete_department_post');

// -------------------------------------------
// Add Dataentry controllers
// -------------------------------------------
dispatch_get('/dataentry/course/add/', 'add_course_render');
dispatch_post('/dataentry/course/add/', 'add_course_post');

dispatch_get('/dataentry/department/add/', 'add_department_render');
dispatch_post('/dataentry/department/add/', 'add_department_post');

dispatch_get('/dataentry/programme/add/', 'add_programme_render');
dispatch_post('/dataentry/programme/add/', 'add_programme_post');

dispatch_get('/dataentry/section/add/', 'add_section_render');
dispatch_post('/dataentry/section/add/', 'add_section_post');

dispatch_get('/dataentry/staff/add/', 'add_staff_render');
dispatch_post('/dataentry/staff/add/', 'add_staff_post');

dispatch_get('/dataentry/student/add/proxy', 'add_student_proxy');
dispatch_get('/dataentry/student/add/', 'add_student_render');
dispatch_post('/dataentry/student/add/', 'add_student_post');

// ------------------------------------------
// Edit Dataentry controllers
// ------------------------------------------
dispatch_get('^/dataentry/course/(\d+)/edit', 'edit_course_render');
dispatch_get('^/dataentry/course/edit', 'edit_course_render');
dispatch_post('^/dataentry/course/(\d+)/edit', 'edit_course_post');

dispatch_get('^/dataentry/department/(\d+)/edit', 'edit_department_render');
dispatch_post('^/dataentry/department/(\d+)/edit', 'edit_department_post');

dispatch_get('^/dataentry/programme/(\d+)/edit/', 'edit_programme_render');
dispatch_post('^/dataentry/programme/(\d+)/edit/', 'edit_programme_post');

dispatch_get('^/dataentry/section/(\d+)/edit/', 'edit_section_render');
dispatch_post('^/dataentry/section/(\d+)/edit/', 'edit_section_post');

dispatch_get('^/dataentry/staff/(\d+)/edit/', 'edit_staff_render');
dispatch_post('^/dataentry/staff/(\d+)/edit/', 'edit_staff_post');

dispatch_get('^/dataentry/student/(\d+)/edit/', 'edit_student_render');
dispatch_post('^/dataentry/student/(\d+)/edit/', 'edit_student_post');

// ------------------------------------------
// List Dataentry controllers
// ------------------------------------------
dispatch_get('/dataentry/programme/list/', 'list_programme_render');
dispatch_post('/dataentry/programme/list/', 'list_programme_post');

dispatch_get('/dataentry/section/list/', 'list_section_render');
dispatch_post('/dataentry/section/list/', 'list_section_post');

dispatch_get('/dataentry/staff/list/', 'list_staff_render');
dispatch_post('/dataentry/staff/list/', 'list_staff_post');

dispatch_get('/dataentry/course/list/', 'list_course_render');
dispatch_post('/dataentry/course/list/', 'list_course_post');

dispatch_get('/dataentry/export/list/:type', 'dataentry_export_list');
dispatch_get('/dataentry/report/list/:type', 'dataentry_report_list');

dispatch_post('/dataentry/**', 'dataentry_home');

// ------------------------------------------
// Student View Controllers
// ------------------------------------------

dispatch_post('/student/profile/update', 'student_profile_post');
dispatch_get('/student/profile/**', 'student_profile_render');
dispatch_get('/student/cia/**', 'student_cia_render');
dispatch_get('/student/attendance/**', 'student_attendance_render');
dispatch_get('/student/calendar/**', 'student_calendar_render');
dispatch_get('/student/timetable/**', 'student_timetable_render');
dispatch_get('/student/feedback/**', 'student_feedback_render');

// Matches all other fields in student controller
dispatch_get('/student/home', 'student_home_render');
dispatch_get('/student/**', 'student_home_render');


// ------------------------------------------
// Staff View Controllers
// ------------------------------------------
dispatch_get('/staff/course_profile/', 'staff_cp_view_render');
dispatch_get('/staff/course_profile/add/', 'staff_cp_add_render');
dispatch_post('/staff/course_profile/create', 'staff_cp_create');
dispatch_get('/staff/timetable/', 'staff_timetable_render');
dispatch_get('/staff/attendance/', 'staff_attendance_render');
dispatch_get('/staff/cia/', 'staff_cia_render');

dispatch_get('^/staff/course_profile/(\d+)/delete', 'staff_cp_delete');
dispatch_get('^/staff/course_profile/(\d+)/edit', 'staff_cp_edit');
dispatch_post('/staff/course_profile/edit', 'staff_cp_edit_post');

dispatch_post('/staff/course_profile/batch/delete', 'staff_cp_batch_delete');
// Matches all other fields in student controller
dispatch_get('/staff/home', 'staff_home_render');
dispatch_get('/staff/**', 'staff_home_render');

// ------------------------------------------
// Admin functions
// ------------------------------------------
dispatch_get('/admin/advanced/', 'admin_advanced_render');

// Reset Passwords
dispatch_post('/admin/user/reset/', 'admin_user_reset_password');
dispatch_post('/admin/user/reset/staff/all', 'admin_staff_all_reset_password');
dispatch_post('/admin/user/reset/student/all', 'admin_student_all_reset_password');

// Lock and Unlock page
dispatch_get('/admin/lock/', 'admin_lock_render');
// Lock and Unlock Staff
dispatch_get('^/admin/lock_unlock/(\d+)/(\d+)/lock', 'admin_lock_entity');
dispatch_get('^/admin/lock_unlock/(\d+)/(\d+)/unlock', 'admin_unlock_entity');

// Block and UnBlock Page
dispatch_get('/admin/block_unblock/', 'admin_block_unblock_render');

// Staff Blocking and Unblocking process
dispatch_post('/admin/staff/block', 'admin_staff_block_post');
dispatch_get('^/admin/staff/(\d+)/block', 'admin_staff_block');
dispatch_get('^/admin/staff/(\d+)/unblock', 'admin_staff_unblock');

// Student Blocking and Unblocking process
dispatch_post('/admin/student/block', 'admin_student_block_post');
dispatch_get('^/admin/student/(\d+)/block', 'admin_student_block');
dispatch_get('^/admin/student/(\d+)/unblock', 'admin_student_unblock');

dispatch_get('/admin/js/', 'admin_js_render');
dispatch_get('/admin/home', 'admin_home_render');

// ------------------------------------------
// Main or Other functions
// ------------------------------------------
dispatch_get('/user/login', 'user_login');
dispatch_post('/user/login', 'user_login_authenticate');
dispatch_get('/user/logout', 'user_logout');
dispatch_post('/user/logout', 'user_logout');

// Must be the last entry in the order of controller actions
dispatch_post('/**', 'webnaplo_home');
dispatch_get('/**', 'webnaplo_home');

// Run the application
run();
