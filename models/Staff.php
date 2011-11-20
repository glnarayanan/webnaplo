<?php

/**
 *	Model Class for Staff
 **/
class Staff {

	public $idstaff;
	public $address;
	public $designation;
	public $email;
	public $is_blocked;
	public $mobile;
	public $name;
	public $password;
	public $staff_id;
	public $dept_iddept;
	
	/**
	 *	Returns the list of course profiles that the current staff takes
	 *
	 *	@param	$staffid	StaffId for whom you wish to get the information
	 *	@param	$db			PDOObject Reference
	 *
	 *	@return List of Course profiles for the staff id
	 **/
	public static function SgetCourseProfiles($staffid, $db) {
		return $db->run("select cp.name as cpname, cp.idcourse_profile as idcourse_profile, c.course_name as cname from course_profile cp, course c where cp.course_id = c.idcourse and cp.staff_id = :sid", array(":sid" => $staffid));
	}
	
	/**
	 *	Non-static version of get Course Profiles
	 **/
	public function getCourseProfiles($db) {
		return Staff::SgetCourseProfiles($this->idstaff, $db);
	}
	
	/**
	 *	Clear the Timetable of the staff member. Generally used when updating the timetable, as re-creating is much easier than updating
	 *
	 *	@param	$s_id	Staff ID
	 *	@param	$db		PDO Object
	 *
	 *	@return TRUE if the operation completed successfully, else FALSE
	 **/
	public static function SclearTimetable($s_id, $db) {
		$affected_rows = $db->run("delete from timetable where cp_id in (select idcourse_profile from course_profile where staff_id = :sid)", array(":sid" => $s_id));
		
		return (is_object($affected_rows) && get_class($affected_rows) == "PDOException") ? true : false;
	}
	
	/**
	 * Non-static version of Staff::clearTimetable()
	 **/
	public function clearTimetable($db) {
		return Staff::SclearTimetable($this->staff_id, $db);
	}
	
	/**
	 *	Get the timetable for the staff members
	 *
	 *	@param	$staffid	StaffId for whom you wish to recieve the timetable
	 *	@param	$db			PDOObject Reference
	 *
	 *	@return Get the current timetable views for a particular staff
	 **/
	public static function SgetTimetable($staffid, $db) {
		return $db->run("select hour_of_day, days_of_week, cp_id from timetable where cp_id in (select idcourse_profile from course_profile where staff_id = :sid)", array(":sid" => $staffid));
	}
	
	/**
	 *	Non-Static version of getTimetable()
	 **/
	public function getTimetable($db) {
		return Staff::SgetTimetable($this->staff_id, $db);
	}
	
	/**
	 *	Loads the instance of the staff member and saves its instance
	 *
	 *	@return	FALSE if staff member already exist, 
	 *	@see Staff->save() for other return values
	 **/
	public static function LoadAndSave($staff, $db) {
		extract($staff);
		
		$staffCount = $db->select("staff", "staff_id = :sid", array(":sid" => $staff_id));
		if(count($staffCount) > 0) return false;
		
		$staff = new Staff;
		$staff->name = $name;
		$staff->designation = $designation;
		$staff->dept_id = $dept_id;
		$staff->staff_id = $staff_id;
		$staff->email = $email;
		$staff->mobile = $mobile;
		$staff->address = $address;
		
		$staff->is_blocked = false;
		$staff->password = "src";
		
		return $staff->save($db);
	}
	
	/**
	 *	Returns a valid instance of the current Staff Object based on the StaffID specified
	 *
	 *	@param	$staffid	Staff ID
	 *	@param	$db			PDOObject Reference
	 *
	 *	@return	Valid Staff object if Staff ID is correct, else FALSE
	 **/
	public static function load($staffid, $db) {
		$staffObject = $db->select("staff", "idstaff = :sid", array(":sid" => $staffid));
		if(count($staffObject) < 1) return false;
		
		// extract the staff properties as variables
		extract($staffObject[0]);
		
		$staff = new Staff;
		$staff->idstaff = $idstaff;
		$staff->name = $name;
		$staff->designation = $designation;
		$staff->dept_id = $dept_id;
		$staff->staff_id = $staff_id;
		$staff->email = $email;
		$staff->mobile = $mobile;
		$staff->address = $address;
		
		$staff->is_blocked = $is_blocked;
		$staff->password = $password;
		
		return $staff;
	}
	
	/**
	 *	Save the current instance of the Staff Model to the database
	 *
	 *	@return		 		1 			If operation is successful 
	 *	@return		PDOExceptionObject 	If there is an Error
	 **/
	public function save($db) {
		return $db->insert("staff", array (
			"name" => $this->name,
			"designation" => $this->designation,
			"dept_id" => $this->dept_id,
			"staff_id" => $this->staff_id,
			"email" => $this->email,
			"mobile" => $this->mobile,
			"address" => $this->address,
			"is_blocked" => $this->is_blocked,
			"password" => $this->password
		));
	}
	
	/**
	 *	Get the pending attendance for a given staff member to be posted
	 *
	 *	@param	$staffid	Staff ID
	 *	@param	$db			PDOObject Reference
	 *
	 *	@return Pending attendance List
	 **/
	public static function SgetPendingAttendance($staffid, $db) {
		date_default_timezone_set("Asia/Calcutta");
		
		$pending = array();
		
		$cpListQuery = "select  * from course_profile where staff_id = (select idstaff from staff where staff_id = :sid)";
		$cpList = $db->run($cpListQuery, array(":sid" => $staffid));
		
		foreach($cpList as $cp) {
			$getAttendanceListQuery = "select at.`date` from attendance at where at.timetable_id in (select idtimetable from timetable where cp_id = :cpid) group by at.`date` order by `date` desc ;";
			
			$postedAttendance = $db->run($getAttendanceListQuery, array(":cpid" => $cp['idcourse_profile']));

			if(is_object($postedAttendance) && get_class($postedAttendance) == "PDOException") {
				flash('warning', $postedAttendance->getMessage());
				print_r($postedAttendance->getMessage());
				return false;
			}

			$postedDays = array();
			
			// print_r($postedAttendance);
			foreach($postedAttendance as $posted) {
				$postedDays[] = $posted['date'];
			}
			
			$workingDays = System::getWokingDaysTillNow($db);
			
			// Find the tentative number of pending days
			$tempPending = array_diff($workingDays, $postedDays);
			
			$getTimeTableQuery = "select * from timetable where cp_id = :cpid";
			$timeTable = $db->run($getTimeTableQuery, array(":cpid" => $cp['idcourse_profile']));
			// print_r($timeTable);
			$days_of_week = array();
			$days_hour_tt = array();
			foreach($timeTable as $tt) {
				$days_of_week[] = $tt['days_of_week'];
				$days_hour_tt[$tt['days_of_week']][] = $tt['hour_of_day'];
			}
			
			$pendingDay = array();
			$pendingDay['name'] = $cp['name'];
			$pendingDay['cp_id'] = $cp['idcourse_profile'];
			// echo $cp['name'] . "<br />";
			
			foreach($tempPending as $tp) {
				$day_num = date('N', strtotime($tp));
				// echo $day_num . "<br>";
				if(in_array($day_num, $days_of_week)) {
					foreach($days_hour_tt[$day_num] as $hour) {
						// echo "Pending date - " . date('Y-m-d', strtotime($tp)) . " -- $hour - hour.<br>";
						$pendingDay['name'] = $cp['name'];
						$pendingDay['date'] = date('Y-m-d', strtotime($tp));
						$pendingDay['hour'] = $hour;
						
						// Moving this outside the loop decreases the resoulution of data and causes a lot of abstraction
						$pending[] = $pendingDay;
					}
				}
			}
			
		}
		
		return $pending;
	}
	
	/**
	 *	Non-static version of getPendingAttendance()
	 **/
	public function getPendingAttendance($db) {
		return Staff::SgetPendingAttendance($this->staff_id, $db);
	}
	
	public static function getStudentListForCourseProfile($cp_id,$s_id) {
		// select s.idstudent from staff s ,student stu,course_profile c_p,class c where s.staff_id=$s_id and c_p.staff_idstaff=s.idstaff an c_p.class_iclass=c.idclass and stu.class_idclass=c.iclass;
	}
		
	
	public static function getPendingCIA() {
		// select cs.name from cia_marks cia,staff s,course_profile cs where s.staffid=$s_id and marks_1=NULL or marks_2=NULL or marks_3=NULL or assignment=NULL;
	} 
		
	public static function getLackStatusForCourseProfile($idcourse_profile) {
			// set the attendance for the student 
	}
		
	public static function getLackStatus() {
			// set the attendance for the student 
	}
		
	public static function importStaffList() {
			// import stafflist
	}
		
	public static function getBlockStatus() {
		// select s.name,s.is_blocked from staff s;
	}
	
	/**
	 *	Delete the instance of the staff from the system. There cannnot be non-static version of this function, as it brings about a dependency of managing the user access at the application. Hence this method is made only Static and need to be accessed after managing all the application level user access.
	 *
	 *	@param	$staffid	Staff ID to delete
	 *	@param	$db			PDOObject Reference
	 **/
	public static function Delete($staffid, $db) {
		return $db->delete("staff", "staff_id = :staffid", array(":staffid" => $staffid));
	}
}
