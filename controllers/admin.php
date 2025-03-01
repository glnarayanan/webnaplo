<?php

/**
 * Render the Home Page - Dashboard of the Admin User 
 *
 *	@method GET
 *	@route /admin/home
 **/
function admin_home_render() {
	layout('admin/layout.html.php');
	set('title', get_text('ADMIN') . " " . get_text('HOME'));
	set('home_active', 'true');
	
	return render("admin/admin.home.html.php");
}

/**
 * Render the Lock and Unlock Page for the Admin
 *
 *	@method GET
 *	@route /admin/lock/
 **/
function admin_lock_render() {
	layout('admin/layout.html.php');
	set('title', 'Admin - Lock and Unlock Status');
	set('lock_active', 'true');
	
	return render("admin/admin.lock.html.php");
}

/**
 * Lock a particular type of object in the system for the given class
 *
 *	@metod GET
 *	@route ^/admin/lock_unlock/(\d+)/(\d+)/lock
 **/
function admin_lock_entity() {
	$type = params(0);
	$id = params(1);
	
	$r = LockUnLock::lock($type, $id, $GLOBALS['db']);
	flash('success', 'Selected Item has been locked');	
	
	return redirect("admin/lock");
}

/**
 * 	Unlock a particular type of object in the system for the given class
 *
 *	@method GET
 * 	@route ^/admin/lock_unlock/(\d+)/(\d+)/unlock
 **/
function admin_unlock_entity() {
	$type = params(0);
	$id = params(1);
	
	$r = LockUnLock::unlock($type, $id, $GLOBALS['db']);
	
	flash('success', 'Selected Item has been unlocked');
	
	return redirect("admin/lock");
}

/**
 * Render custom javascript for Admin Interface
 *
 *	@method GET
 * 	@route /admin/js/
 **/
function admin_js_render() {
	return js('admin/admin_js.php');
}

/**
 * Block or Unblock selected group of staff users
 *
 *	@method POST
 *	@route /admin/staff/block
 **/
function admin_staff_block_post() {
	$students = $_POST['staff_profile'];
	$db = $GLOBALS['db'];
	
	switch($_POST['operation']) {
	case "block":
		while($student = current($students)) {
			$db->update("staff", array("is_blocked" => '1'), "idstaff = :sid", array(":sid" => key($students)));

			next($students);
		}
		flash('success', "Selected students have been blocked");
		break;
	
	case "unblock":
		while($student = current($students)) {
			$db->update("staff", array("is_blocked" => '0'), "idstaff = :sid", array(":sid" => key($students)));
			
			next($students);
		}
		flash('success', "Selected students have been unblocked");
		break;
	}
	
	return redirect('admin/block_unblock');
}

/**
 *	Block or Unblock selected group of Student users
 *
 *	@method POST
 *	@route /admin/student/block
 **/
function admin_student_block_post() {
	$students = $_POST['student_profile'];
	$db = $GLOBALS['db'];
	
	if(count($students) < 1) {
		flash('error', 'Seems like you forgot to select the Students');
		return redirect('admin/block_unblock');
	}
	
	switch($_POST['operation']) {
	case "block":
		while($student = current($students)) {
			$db->update("student", array("is_blocked" => '1'), "idstudent = :sid", array(":sid" => key($students)));

			next($students);
		}
		flash('success', "Selected students have been blocked");
		break;
	
	case "unblock":
		while($student = current($students)) {
			$db->update("student", array("is_blocked" => '0'), "idstudent = :sid", array(":sid" => key($students)));
			
			next($students);
		}
		flash('success', "Selected students have been unblocked");
		break;
	}
	
	return redirect('admin/block_unblock');
}

/**
 * Render Block/Unblock Page for the Admin
 *
 * 	@method GET
 *	@route /admin/block_unblock/
 **/
function admin_block_unblock_render() {
	layout('admin/layout.html.php');
	set('title', get_text('ADMIN') . " - " . get_text('BLOCK_UNBLOCK_USERS'));
	set('block_active', 'true');
	
	return render("admin/admin.blockunblock.html.php");
}

/**
 *	Block a particular Staff User
 *
 *	@method GET
 *	@route ^/admin/staff/(\d+)/block
 **/
function admin_staff_block() {
	$staffid = params(0);
	$db = $GLOBALS['db'];
	
	$db->update("staff", array("is_blocked" => '1'), "idstaff = :sid", array(":sid" => $staffid));
	
	flash('success', "Staff has been blocked");
	return redirect('admin/block_unblock');
}

/**
 *	Unblock a particular Staff User
 *
 *	@method GET
 *	@route	^/admin/staff/(\d+)/unblock
 **/
function admin_staff_unblock() {
	$staffid = params(0);
	$db = $GLOBALS['db'];
	
	$db->update("staff", array("is_blocked" => '0'), "idstaff = :sid", array(":sid" => $staffid));
	
	flash('success', "Staff has been unblocked");
	return redirect('admin/block_unblock');
}

/**
 *	Block a particular Student User
 *
 *	@method GET
 *	@route ^/admin/student/(\d+)/block
 **/
function admin_student_block() {
	$studid = params(0);
	$db = $GLOBALS['db'];

	$db->update("student", array("is_blocked" => '1'), "idstudent = :sid", array(":sid" => $studid));
	
	flash('success', "Student has been blocked");
	return redirect('admin/block_unblock');
}

/**
 *	Unblock a particular Student user
 *
 *	@method GET
 *  @route ^/admin/student/(\d+)/unblock
 **/
function admin_student_unblock() {
	$studid = params(0);
	$db = $GLOBALS['db'];

	$db->update("student", array("is_blocked" => '0'), "idstudent = :sid", array(":sid" => $studid));
	
	flash('success', "Student has been unblocked");
	return redirect('admin/block_unblock');
}

/**
 *	Render the Admin Advanced Page
 *
 *	@method GET
 *	@route 	/admin/advanced/
 **/
function admin_advanced_render() {
	layout('/admin/layout.html.php');
	set('title', 'Admin Advanced');
	set('advanced_visible', 'true');
	
	return render('/admin/admin.advanced.html.php');
}

/**
 *	Reset a particular type of User's password
 *
 *	@method POST
 *	@route 	/admin/user/reset/
 **/
function admin_user_reset_password() {
	// return h("TODO Method");
	$username = $_POST['username'];
	
	$db = $GLOBALS['db'];
	
	// Check to make sure that username is not that of dataentry's or Admins
	$dataentry_username = 	Configuration::get(Configuration::$CONFIG_DATAENTRY_USER, $db, true);
	$admin_username 	=	Configuration::get(Configuration::$CONFIG_ADMIN_USER, $db,  true);
	
	if($username == $dataentry_username || $username == $admin_username) {
		flash('error', "You cannot Reset password for Dataentry or Admin. Use the form on the right. <a href='" . url_for('/docs/admin/advanced') . "'>View Help</a>");
		return redirect('/admin/advanced');
	} else {
		// Seems like not the admin or the dataenty user
		preg_match("/[a-zA-Z]+[0-9]+/", $username, $matchs);
		if(count($matchs) > 0) {
			$staff = $db->select("staff", "staff_id = :staffid", array(":staffid" => $username));
			
			if(count($staff) < 1) {
				flash('error', "Staff User ID - $username not found in the system.");
			} else {
				$default_staff_password = Configuration::get(Configuration::$CONFIG_DEFAULT_STAFF_PASSWORD,$db, true);
				$update = $db->update("staff", array("password" => $default_staff_password), "staff_id = :staffid", array(":staffid" => $username));

				flash('success', "Password successfully reset.");
				if(is_object($update) && get_class($update) == "PDOException") halt(SERVER_ERROR, $update->getMessage());
			}
		} else {
			$students = $db->select("student", "idstudent = :studid", array(":studid" => $username));
			
			if(count($students) < 1) {
				flash('error', "User ID - $username not found in the system.");
			} else {
				$default_student_password = Configuration::get(Configuration::$CONFIG_DEFAULT_STUDENT_PASSWORD,$db, true);
				$update = $db->update("student", array("password" => $default_student_password), "idstudent = :stuid", array(":stuid" => $username));
				flash('success', "Password successfully reset");
				if(is_object($update) && get_class($update) == "PDOException") halt(SERVER_ERROR, $update->getMessage());
			}
		}
		return redirect('admin/advanced');
	}
	// you should never come here
	// return h("");
	flash('warning', "Something unexpected happened here, Please try again");
	return redirect('admin/advanced');
}

/**
 *	Reset all Staff Password
 *	@method POST
 *	@route	/admin/user/reset/staff/all
 **/
function admin_staff_all_reset_password() {
	$db = $GLOBALS['db'];
	
	$default_staff_password = Configuration::get(Configuration::$CONFIG_DEFAULT_STAFF_PASSWORD,$db, true);
	$update = $db->update("staff", array("password" => $default_staff_password), "1=1"); // 1=1 is required for update query 

	flash('success', "Password successfully reset for all Staffs");
	if(is_object($update) && get_class($update) == "PDOException") halt(SERVER_ERROR, $update->getMessage());
	return redirect('admin/advanced');
}

/**
 *	Reset all Students Password
 *
 *	@method POST
 *	@route	/admin/user/reset/student/all
 **/
function admin_student_all_reset_password() {
	$db = $GLOBALS['db'];
	
	$default_student_password = Configuration::get(Configuration::$CONFIG_DEFAULT_STUDENT_PASSWORD,$db, true);
	$update = $db->update("student", array("password" => $default_student_password), "1=1"); // 1=1 is required for update query 

	flash('success', "Password successfully reset for all Students");
	if(is_object($update) && get_class($update) == "PDOException") halt(SERVER_ERROR, $update->getMessage());
	return redirect('admin/advanced');
}

/**
 *	Change the Admin Password
 *
 *	@method POST
 *	@route /admin/user/admin/update/password
 **/
function admin_update_admin_password() {
	$db = $GLOBALS['db'];
	
	if(!isset($_POST['adminpassword']) || strlen(trim($_POST['adminpassword'])) < 1) {
		flash('warning', "Blank passwords are not supported in the system");
		return redirect("admin/advanced");
	}
	
	$password = $_POST['adminpassword'];
	
	Configuration::put(Configuration::$CONFIG_ADMIN_PASSWORD, $password, $db);
	flash('success', "Your Admin password is successfully changed");
	return redirect('admin/advanced');
}

/**
 *	Update the Dataentry password
 *
 *	@method POST
 *	@route /admin/user/dataentry/update/password
 **/
function admin_update_dataentry_password() {
	$db = $GLOBALS['db'];
	
	if(!isset($_POST['dataentryPassword']) || strlen(trim($_POST['dataentryPassword'])) < 1) {
		flash('warning', "Blank passwords are not supported in the system");
		return redirect("admin/advanced");
	}
	
	$password = $_POST['dataentryPassword'];
	
	Configuration::put(Configuration::$CONFIG_DATAENTRY_PASSWORD, $password, $db);
	flash('success', "Your Dataentry password is successfully changed");
	return redirect('admin/advanced');
}

/**
 * Delete Student View Page
 *
 *	@method	GET
 *	@route	/admin/student/delete
 **/
function admin_delete_student_render() {
	layout('admin/layout.html.php');
	set("title" ,"Delete Student");
	set("delete_active" ,"true");

    return render("admin/delstud.html.php");
}
/**
 * Delete the Student from the dataentry
 *
 *	@method	POST
 *	@route	/admin/student/delete
 **/
function admin_delete_student_post() {
	$reg = $_POST['regno'];

	// Delete the student static function to delete the object
	$r = Student::Delete($reg, $GLOBALS['db']);
	if(is_object($r) && get_class($r) == "PDOException") {
		
		switch($r->getCode()) {
			case 23000:
				$msg = "There are other dependencies for the given Student, delete them before deleting this student";
			break;
		}
		
		flash('error', $msg);
	} else {
		if($r == 0) {
			flash('warning', "Student with $reg was not found in the system");
		} else {
			flash('success', "Student with $reg has been successfully deleted");
		}
	}
	
	// Redirect the user back 
	return redirect('/admin/student/delete');
}

/**
 * Delete Staff view page
 *
 *	@method	GET
 *	@route	/admin/staff/delete
 **/
function admin_delete_staff_render() {
	layout('admin/layout.html.php');
	set("title" ,"Delete Staff");
	set("delete_active" ,"true");

    return render("admin/delstaff.html.php");
}

/**
 * Delete Staff from the system
 *
 *	@method	POST
 *	@route	/admin/staff/delete
 **/
function admin_delete_staff_post() {
	$staffid = $_POST['staffid'];

	$db = $GLOBALS['db'];
	// Delete the student static function to delete the object
	$r = Staff::Delete($staffid, $db);
	
	if(is_object($r) && get_class($r) == "PDOException") {
		
		switch($r->getCode()) {
			case 23000:
				$msg = "There are other dependencies for the given Staff, delete them before deleting this Staff";
			break;
		}
		
		flash('error', $msg);
	} else {
		if($r == 0) {
			flash('warning', "Staff with $staffid is not found in the system");
		} else {
			flash('success', "Staff with $staffid has been successfully deleted");
		}
	}
	
	// Redirect the user back 
	return redirect('/admin/staff/delete');
}

/**
 * Delete Programme View Page
 *
 *	@method	GET
 *	@route	/admin/programme/delete
 **/
function admin_delete_programme_render() {
	layout('admin/layout.html.php');
	set("title" ,"Delete Programme");
	set("delete_active" ,"true");

    return render("admin/delprog.html.php");
}

/**
 * Delete the programme from the system
 *
 *	@method	POST
 *	@route	/admin/programme/delete
 **/
function admin_delete_programme_post() {
	$pgmid = $_POST['Programme_FK'];

	$db = $GLOBALS['db'];
	
	// Delete the student static function to delete the object
	$r = Programme::Delete($pgmid, $db);
	if(is_object($r) && get_class($r) == "PDOException") {
		
		switch($r->getCode()) {
			case 23000:
				$msg = "There are other dependencies for the given Programme, delete them before deleting this programme";
			break;
		}
		
		flash('error', $msg);
	} else {
		if($r == 0) {
			flash('warning', "Programme is not found in the system");
		} else {
			flash('success', "Programme has been successfully deleted");
		}
	}
	
	// Redirect the user back 
	return redirect('/admin/programme/delete');
}

/**
 * Delete Course View page
 *
 *	@method	GET
 *	@route	/admin/course/delete
 **/
function admin_delete_course_render() {
	layout('admin/layout.html.php');
	set("title" ,"Delete Course");
	set("delete_active" ,"true");

    return render("admin/delcourse.html.php");
}

/**
 * Delete post from the system
 *
 *	@method	POST
 *	@route	/admin/course/delete
 **/
function admin_delete_course_post() {
	$cid = $_POST['coursecode'];

	// Delete the student static function to delete the object
	$r = Course::Delete($cid, $GLOBALS['db']);
	if(is_object($r) && get_class($r) == "PDOException") {
		
		switch($r->getCode()) {
			case 23000:
				$msg = "There are other dependencies for the given Course, delete them before deleting this Course";
			break;
		}
		
		flash('error', $msg);
	} else {
		if($r == 0) {
			flash('warning', "Course with $cid not found in the system");
		} else {
			flash('success', "Course with $cid has been successfully deleted");
		}
	}
	
	// Redirect the user back 
	return redirect('/admin/course/delete');
}

/**
 * Delete Department page
 *
 *	@method	GET
 *	@route	/admin/department/delete
 **/
function admin_delete_department_render() {
	layout('admin/layout.html.php');
	set("title" ,"Delete Department");
	set("delete_active" ,"true");

    return render("admin/deldept.html.php");
}

/**
 * Delete programme from the system
 *
 *	@method	POST
 *	@route	/admin/department/delete
 **/
function admin_delete_department_post() {
	$did = $_POST['dept_FK'];
	
	// Move this to Department Model class
	$db = $GLOBALS['db'];

	// Delete the student static function to delete the object
	$r = Department::Delete($did, $db);
	
	if(is_object($r) && get_class($r) == "PDOException") {
		
		switch($r->getCode()) {
			case 23000:
				$msg = "There are other dependencies for the given Department, delete them before deleting this department";
			break;
		}
		
		flash('error', $msg);
	} else {
		if($r == 0) {
			flash('warning', "Department was not found in the system");
		} else {
			flash('success', "Department has been successfully deleted");
		}
	}
	
	// Redirect the user back 
	return redirect('/admin/department/delete');
}

/**
 * Edit Course View Page
 **/
function admin_edit_course_render() {
	layout('admin/layout.html.php');
	set("title" ,"Edit Course");
	set("edit_active" ,"true");

    return render("admin/edit.course.html.php");
}

/**
 * Edit Course in the system
 **/
function admin_edit_course_post() {
	// $did = $_POST['dept_FK'];
	extract($_POST);

	// Delete the student static function to delete the object
	$r = Course::LoadAndUpdate($_POST, $GLOBALS['db']);
	if(is_object($r) && get_class($r) == "PDOException") {
		
		switch($r->getCode()) {
			case 23000:
				$msg = "There are other dependencies for the given Department, delete them before deleting this department";
			break;
		}
		
		flash('error', $msg);
	} else {
		if($r == 0) {
			flash('warning', "Course was not found in the system");
		} else {
			flash('success', "Course $courseName has been successfully edited");
		}
	}
	
	// Redirect the user back 
	return redirect("/admin/course/$idcourse/edit");
}

/**
 * Edit Department View Page
 **/
function admin_edit_department_render() {
	layout('admin/layout.html.php');
	set("title" ,"Edit Department");
	set("edit_active" ,"true");

    return render("admin/edit.department.html.php");
}

/**
 * Edit Department, existing from the system
 **/
function admin_edit_department_post() {
	// $did = $_POST['dept_FK'];
	extract($_POST);

	// Delete the student static function to delete the object
	Department::LoadAndUpdate($_POST);
	flash('success', "department $departmentName has been successfully edited");
	
	// Redirect the user back 
	return redirect('/admin/department/edit');
}

/**
 * Edit Programme View Page
 **/
function admin_edit_programme_render() {
	layout('admin/layout.html.php');
	set("title" ,"Edit Programme");
	set("edit_active" ,"true");

    return render("admin/edit.programme.html.php");
}

/**
 * Edit Programme in the system
 **/
function admin_edit_programme_post() {
	// $did = $_POST['dept_FK'];
	extract($_POST);

	// Delete the student static function to delete the object
	Programme::LoadAndUpdate($_POST);
	flash('success', "Programme has been successfully edited");
	
	// Redirect the user back 
	return redirect('/admin/programme/edit');
}

/**
 * Edit Staff View page
 **/
function admin_edit_staff_render() {
	layout('admin/layout.html.php');
	set("title" ,"Edit Staff");
	set("edit_active" ,"true");

    return render("admin/edit.staff.html.php");
}

/**
 * Edit Staff in the system
 **/
function admin_edit_staff_post() {
	// $did = $_POST['dept_FK'];
	extract($_POST);

	// Delete the student static function to delete the object
	Staff::LoadAndUpdate($_POST);
	flash('success', "Staff $staffName has been successfully edited");
	
	// Redirect the user back 
	return redirect('/admin/staff/edit');
}

/**
 * Edit Student View page
 **/
function admin_edit_student_render() {
	layout('admin/layout.html.php');
	set("title" ,"Edit student");
	set("edit_active" ,"true");

    return render("admin/edit.student.html.php");
}

/**
 * Edit Student in the system
 **/
function admin_edit_student_post() {
	// $did = $_POST['dept_FK'];
	extract($_POST);

	// Delete the student static function to delete the object
	Student::LoadAndUpdate($_POST);
	flash('success', "Student $name has been successfully deleted");
	
	// Redirect the user back 
	return redirect('/admin/student/edit');
}

function admin_list_staff_render() {
	return list_staff_render();
}

function admin_list_programme_render() {
	return list_programme_render();
}

function admin_list_course_render() {
	return list_course_render();
}

function admin_report_list() {
	return dataentry_report_list();
}

function admin_export_list() {
	return dataentry_export_list();
}

/**
 *	Render the Import From Excel Page in the Admin
 *
 *	@method GET
 *	@route 	/admin/advanced/import
 **/
function admin_import_render() {
	layout('/admin/layout.html.php');
	set('title', "Advanced - Import ");
	
	return render('/admin/admin.import.html.php');
}

/**
 *	Import Student List from a Excel/CSV/OpenCalc File
 *
 *	@method POST
 *	@route /admin/advanced/import/upload/students
 **/
function admin_import_students() {
	$accept_mime = array('text/csv','application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// See if the file was uploaded at all
	if(isset($_FILES['studentlist']['name']) && strlen($_FILES['studentlist']['name']) > 0) {
		$filename = $_FILES['studentlist']['tmp_name'];
		
		$mime = file_mime_content_type($_FILES['studentlist']['name']);
		
		if(in_array($mime, $accept_mime)) {
			$class_id = $_POST['classid'];
			$batch_errors = Student::Import($filename, $class_id, $GLOBALS['db']);
			
			// Complete the process with a proper error message
			if(count($batch_errors) > 0) flash('error', $batch_errors);
			else flash('success', 'All Students from the file has been imported successfully.');
		} else {
			flash('warning', 'Import failure. Please use only XLS/XLSX/CSV file. ');
		}
		// Remove the uploaded file now
		unlink($filename);
	} else {
		flash('warning', 'Please upload a file to import contents from');
	}
	
	return redirect('/admin/advanced/import');
}

/**
 *	Import Staff List from a Excel/CSV/OpenCalc File
 *
 *	@method POST
 *	@route /admin/advanced/import/upload/staffs
 **/
function admin_import_staffs() {
	$accept_mime = array('text/csv','application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// See if the file was uploaded at all
	if(isset($_FILES['stafflist']['name']) && strlen($_FILES['stafflist']['name']) > 0) {
		$filename = $_FILES['stafflist']['tmp_name'];
		
		$mime = file_mime_content_type($_FILES['stafflist']['name']);
		
		if(in_array($mime, $accept_mime)) {
			$deptid = $_POST['deptid'];
			$batch_errors = Staff::Import($filename, $deptid, $GLOBALS['db']);
			
			// Complete the process with a proper error message
			if(count($batch_errors) > 0) flash('error', $batch_errors);
			else flash('success', 'All Staffs from the file has been imported successfully.');
		} else {
			flash('warning', 'Import failure. Please use only XLS/XLSX/CSV file. ');
		}
		// Remove the uploaded file now
		unlink($filename);
	} else {
		flash('warning', 'Please upload a file to import contents from');
	}
	
	return redirect('/admin/advanced/import');
}

/**
 *	Import Programme List from a Excel/CSV/OpenCalc File
 *
 *	@method POST
 *	@route /admin/advanced/import/upload/programme
 **/
function admin_import_programmes() {
	$accept_mime = array('text/csv','application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// See if the file was uploaded at all
	if(isset($_FILES['programmelist']['name']) && strlen($_FILES['programmelist']['name']) > 0) {
		$filename = $_FILES['programmelist']['tmp_name'];
		
		$mime = file_mime_content_type($_FILES['programmelist']['name']);
		
		if(in_array($mime, $accept_mime)) {
			$deptid = $_POST['deptid'];
			$batch_errors = Programme::Import($filename, $deptid, $GLOBALS['db']);
			
			// Complete the process with a proper error message
			if(count($batch_errors) > 0) flash('error', $batch_errors);
			else flash('success', 'All Programmes from the file has been imported successfully.');
		} else {
			flash('warning', 'Import failure. Please use only XLS/XLSX/CSV file. ');
		}
		// Remove the uploaded file now
		unlink($filename);
	} else {
		flash('warning', 'Please upload a file to import contents from.');
	}
	
	return redirect('/admin/advanced/import');
}

/**
 *	Import Course List from a Excel/CSV/OpenCalc File
 *
 *	@method POST
 *	@route /admin/advanced/import/upload/course
 **/
function admin_import_courses() {
	$accept_mime = array('text/csv','application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// See if the file was uploaded at all
	if(isset($_FILES['courselist']['name']) && strlen($_FILES['courselist']['name']) > 0) {
		$filename = $_FILES['courselist']['tmp_name'];
		
		$mime = file_mime_content_type($_FILES['courselist']['name']);
		
		if(in_array($mime, $accept_mime)) {
			$pgmid = $_POST['pgmid'];
			$batch_errors = Course::Import($filename, $pgmid, $GLOBALS['db']);
			
			// Complete the process with a proper error message
			if(count($batch_errors) > 0) flash('error', $batch_errors);
			else flash('success', 'All Courses from the file has been imported successfully.');
		} else {
			flash('warning', 'Import failure. Please use only XLS/XLSX/CSV file. ');
		}
		// Remove the uploaded file now
		unlink($filename);
	} else {
		flash('warning', 'Please upload a file to import contents from.');
	}
	
	return redirect('/admin/advanced/import');
}

/**
 *	Import Department List from a Excel/CSV/OpenCalc File
 *
 *	@method POST
 *	@route /admin/advanced/import/upload/dept
 **/
function admin_import_dept() {
	$accept_mime = array('text/csv','application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// See if the file was uploaded at all
	if(isset($_FILES['deptlist']['name']) && strlen($_FILES['deptlist']['name']) > 0) {
		$filename = $_FILES['deptlist']['tmp_name'];
		
		$mime = file_mime_content_type($_FILES['deptlist']['name']);
		
		if(in_array($mime, $accept_mime)) {
			$batch_errors = Department::Import($filename, $GLOBALS['db']);
			
			// Complete the process with a proper error message
			if(count($batch_errors) > 0) flash('error', $batch_errors);
			else flash('success', 'All Departments from the file has been imported successfully.');
		} else {
			flash('warning', 'Import failure. Please use only XLS/XLSX/CSV file. ');
		}
		// Remove the uploaded file now
		unlink($filename);
	} else {
		flash('warning', 'Please upload a file to import contents from.');
	}
	
	return redirect('/admin/advanced/import');
}

/**
 *	Admin settings to add the ChangeDay Order dates
 *
 *	@method GET
 *	@route	/admin/advanced/changedayorder
 **/
function admin_advanced_changedayorder() {
	layout('/admin/layout.html.php');
	set('title', "Advanced - Change Day Order ");

	return render('/admin/admin.changedayorder.html.php');
}

/**
 *	Delete the single ChangeDayOrder Information
 *
 *	@method	GET
 *	@route	^/admin/changedayorder/(\d+)/delete
 **/
function admin_advanced_changedayorder_delete() {
	$change_day_order_id = params(0);
	
	$db = $GLOBALS['db'];
	
	$delete_status = $db->delete('changedayorder', 'idchangedayorder = :id', array(':id' => $change_day_order_id));
	
	if(is_object($delete_status) && get_class($change_day_order_id) == "PDOException") {
		flash('error', 'Some technical problem has occured. Please try again later.');
		return redirect('/admin/advanced/changedayorder');
	}
	
	// There seems to be no error, so go on happily
	flash('success', 'Requested Day Order has been successfully changed');
	return redirect('/admin/advanced/changedayorder');
}

/**
 * Handles the Creation of new Change Day Order 
 *
 *	@method	POST
 *	@route	/admin/changedayorder/add
 **/
function admin_advanced_changedayorder_add() {
	$holiday_date = strtotime($_POST['holiday_date']);
	$compensation_date = strtotime($_POST['compensation_date']);
	$day_order = $_POST['day_order'];
	
	$db = $GLOBALS['db'];
	
	$check_status = $db->select("changedayorder", "holiday_date = :hol", array(":hol" => date('Y-m-d', $holiday_date)));
	
	if(count($check_status) < 1) {
		$insert_stauts = $db->insert("changedayorder", array("holiday_date" => date('Y-m-d', $holiday_date), "compensation_date" => date('Y-m-d', $compensation_date), "day_order" => $day_order));
		
		if(is_object($insert_status) && get_class($insert_status) == "PDOException") {
			flash('error', "Some technical error has occured. Please try again");
		} else {
		}
			flash('success', 'Holiday - Compensation rule added successfully');
	} else {
		flash('error', "Another Holiday - Compensation Rule already exist with the same Holiday. ");
	}
	
	return redirect('/admin/advanced/changedayorder');
}

/**
 *	Batch Delete of Change Day Order values
 *
 *	@method	POST
 *	@route	/admin/changedayorder/delete
 **/
function admin_advanced_changedayorder_batch_delete() {
	$change_day_orders = $_POST['change_day_order'];
	
	$db = $GLOBALS['db'];
	
	while($cday = current($change_day_orders)) {
		$del_status = $db->delete('changedayorder', 'idchangedayorder = :id', array(':id' => key($change_day_orders)));
		
		next($change_day_orders);
	}
	flash('success', 'Requested Change Day Order Rules were deleted!');
	return redirect('/admin/advanced/changedayorder');
}

/**
 *	Render the News page
 *
 *	@method	GET
 *	@route /admin/news/
 **/
function admin_news_render() {
	layout('admin/layout.html.php');
	set('title', get_text('ADMIN') . " " . get_text('NEWS'));
	set('news_active', 'true');
	
	return render("admin/admin.list.news.html.php");
}

/**
 *	Batch Delete the news articles in the system
 *
 *	@method	POST
 *	@route	/staff/news/batch/delete
 **/
function admin_news_batch_delete_post() {
	$db = $GLOBALS['db'];
	
	$newsElement = $_POST['news'];
	
	while($nid = current($newsElement)) {
		News::Delete(key($newsElement), $db);
		
		next($newsElement);
	}
	
	return redirect("/admin/news");
}

/**
 *	Delete the news element
 *
 *	@method	GET
 *	@route	^/admin/news/(\d+)/delete
 **/
function admin_news_delete() {
	$nid = params(0);
	$db = $GLOBALS['db'];
	
	$r = News::Delete($nid, $db);
	
	if(is_object($r) && get_class($r) == "PDOException") {
		flash('error', "Error deleting the news item. ");
	} else {
		flash('success', "News Item successfully deleted.");
	}
	
	return redirect("/admin/news");
}

/**
 *	Render the Add News page
 *
 *	@method	GET
 *	@route	/admin/news/add
 **/
function admin_news_add_render() {
	layout('admin/layout.html.php');
	set('title', get_text('ADMIN') . " " . get_text('NEWS'));
	set('news_active', 'true');
	
	return render("admin/admin.add.news.html.php");
}

/**
 *	Create a News Item
 *
 *	@method	POST
 *	@route	/admin/news/add
 **/
function admin_news_add_post() {
	$db = $GLOBALS['db'];

	$news = new News;
	$news->news = $_POST['news'];
	$news->date = date('Y-m-d', strtotime($_POST['date']));
	$news->title = $_POST['title'];
	$news->type = $_POST['type'];
	$r = $news->save($db);
	
	if(is_object($r) && get_class($r) == "PDOException") flash('error', "Due to some technical error, we cannot process your request now. Please try again. ");
	else flash("success", "News item titled " . $news->title . " has been successfully added");
	
	return redirect("/admin/news");
}
