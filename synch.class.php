<?php

class reservesSynch{


    function __construct(){
			
			ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
			
            $link = mysqli_connect("localhost", "reserves", "theCharlatansUK", "reserves");

            /* check connection */
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }
            $this->link=$link;
            
            $detailsUpdated=array();
            $this->detailsUpdated=$detailsUpdated;
            $detailsAdded=array();
            $this->detailsAdded=$detailsAdded;
            $detailsDeleted=array();
            $this->detailsDeleted=$detailsDeleted;
    }
    
    function controller(){
        
        if (isset($_REQUEST["state"])){$state=$_REQUEST["state"];}
        else{$state="thebutton";}
        
        switch($state){
            
            case "thebutton":
            $this->theButton();
            break;
            
            case "synch":            
            $this->synchIt();
            break;
        
        
        
        }
    

    }
    
    function theButton(){
        ?>
        
        <h2>Watzek Course Reserve Synching</h2>
        
        <p>Click the button below to update public interface for course reserves.</p>
        <p>The button makes courses publicly available that meet the following conditions:<br>
        <ul>
            <li>Course has a searchable ID value of <em>res</em> .</li>
            <li>Course Code must be in the format BIO100 (i.e. NO SPACES) .</li>
            <li>Course must have a section. For repeated courses, consult the registrar list. Otherwise use <em>01</em> .
        
        </ul>
        <p>You only need to click the button when adding, editing, or deleting course information.</p>
        <p>You need not click the button if you edit a reading list on a pre-existing course.</p>
        
        </p>
  
        <form action='index.php' method='post'>
        <input type='hidden' name='state' value='synch'>
        <input id='submit' type='submit' value='the button'>
        </form>
    
        <?php    
    }
    
    
    function synchIt(){
        $almaIDs=array();
        $almaCourses=$this->getLatest();
        $dbIDs=$this->getDBarray();
        $updated=0;
        $notUpdated=0;
        $inserted=0;
        $notInserted=0;
        
        foreach ($almaCourses as $course){
        
            $courseID=$course["courseIdentifier"];
            array_push($almaIDs,$courseID);
            if (in_array($courseID, $dbIDs)){
                
                if ($this->updateCourse($course)){
                    $deet=$course["courseCode"]." - ".$course["courseInstructor"]." updated";
                    array_push($this->detailsUpdated, $deet);                    
                    $updated++;
                    }
                else{$notUpdated++;}
            
            }
            else{
            
                if ($this->insertCourse($course)){
                    $inserted++;
                    $deet=$course["courseCode"]." - ".$course["courseInstructor"]." added";
                    array_push($this->detailsAdded, $deet);                    
                    
                    }
                else{$notInserted++;}

            }

        
        }
    
    
        $results=$this->compareArrays($almaIDs, $dbIDs);
        $deleted=$results[0];
        $notDeleted=$results[1];
    
        ?>
        
        <h2>Summary</h2>
        <p>Updates: <?php echo $updated;?></p>
        <p>Failed Updates: <?php echo $notUpdated;?></p>
        <p>New Courses: <?php echo $inserted;?></p>
        <p>Failed New Courses: <?php echo $notInserted;?></p>
        <p>Deleted: <?php echo $deleted;?></p>
        <p>Failed Deletes: <?php echo $notDeleted;?></p>
        
        <p>Go click <a href='index.php?state=thebutton'>the button</a> again.
        
        <h3>Details</h3>
        <?php
        sort($this->detailsAdded);
        echo "<p>Added:<br>";
        foreach ($this->detailsAdded as $detail){
            echo $detail."<br>";
        }
        echo "</p>";
        echo "Deleted:<br>";
        sort($this->detailsDeleted);
        foreach ($this->detailsDeleted as $detail){
            echo $detail."<br>";
        }    
        echo "</p>";
        echo "<p>Updated:<br>";
        sort($this->detailsUpdated);
        foreach ($this->detailsUpdated as $detail){
            echo $detail."<br>";
        }    
        echo "</p>";
        
    }
    
    function compareArrays($almaCourses, $dbIDs){
        $deleted=0;
        $notDeleted=0;
        foreach ($dbIDs as $id){
            if(!in_array($id, $almaCourses)){
                
                if ($this->deleteCourse($id)){$deleted++;}
                else{$notDeleted++;}
            }

        }
        $results=array($deleted, $notDeleted);
        return $results;

    }
    
    
    function deleteCourse($id){
        
        $deet=$this->dbDeets["$id"];
        array_push($this->detailsDeleted, "$deet deleted");
    
        $sql="delete from courses where course_identifier='$id'";
        if (mysqli_query($this->link, $sql)){return true;}
        else{return false;}
    
    
    
    }
    
    function updateCourse($course){
    
        
        $courseName=mysqli_real_escape_string($this->link,$course["courseName"]);
        $courseCode=mysqli_real_escape_string($this->link,$course["courseCode"]);
        $courseSection=mysqli_real_escape_string($this->link,$course["courseSection"]);
        $instructor=mysqli_real_escape_string($this->link,$course["courseInstructor"]);
        $courseID=mysqli_real_escape_string($this->link,$course["courseIdentifier"]);
        $status=mysqli_real_escape_string($this->link,$course["status"]);
        $sql="update courses set course_code='$courseCode', course_name='$courseName', course_section='$courseSection', instructor='$instructor', status=$status where course_identifier='$courseID'";
    
        if (mysqli_query($this->link, $sql)){
            return true;
        }
        else{
            return false;
        }

    }
    
    function insertCourse($course){

            $coursName=mysqli_real_escape_string($this->link,$course["courseName"]);
            $courseCode=mysqli_real_escape_string($this->link,$course["courseCode"]);
            $courseSection=mysqli_real_escape_string($this->link,$course["courseSection"]);
            $instructor=mysqli_real_escape_string($this->link,$course["courseInstructor"]);
            $courseID=mysqli_real_escape_string($this->link,$course["courseIdentifier"]);    
            $status=mysqli_real_escape_string($this->link,$course["status"]);
            
            $sql="insert into courses (course_identifier,course_code,course_name,course_section,instructor, status) values('$courseID','$courseCode','$courseName','$courseSection','$instructor', $status) ";
    
            if (mysqli_query($this->link, $sql)){
                return true;
            }
            else{
                return false;
            }    
    
    
    
    }

	function getCourses(){



		$ch = curl_init();
		$url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/courses';
		$queryParams = '?' . urlencode('q') . '=' . urlencode('searchableid~res') . '&' . urlencode('limit') . '=' . urlencode('500') . '&' . urlencode('offset') . '=' . urlencode('0') . '&' . urlencode('apikey') . '=' . urlencode('l7xx70b4c665adc344dd864fe7e383d71e4a');
		curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;


	}

    

    function getLatest(){
        	$almaCourses=array();


			$courses=$this->getCourses();
			
			var_dump($courses);
			

	        for ($i = 0; $i <= $limit; $i++) {
	            $u="false";
	            $courses=$alma->queryAPI("searchCourseInformation", array("arg0"=>"searchableid=res","arg1"=>"$i","arg2"=>"1"));
	            $xml=$courses->asXML();
	            //echo $xml;
	            if ($courseId = $courses->results->course->course_information->identifier){
	                //$id=$this->getID($courseId);
	                //if ($this->updateIndex($id, $i)){$u="true";}
	               
	                $this->writeFile($courseId, $xml);                
	
	
	           		foreach ($courses->results->course as $course) {
		            	$courseCode = $course->course_information->code;
		            	$courseName = $course->course_information->name;
		            	$courseSection = $course->course_information->section;
		            	$instructor = $course->course_information->instructors->instructor; //there might be more than one instructor
		            	$courseID = $course->course_information->identifier;
		            	$st=$course->course_information->status;
		            	if ($st=="ACTIVE"){$status=1;}
		            	else{$status=0;}
		            	
		            	
		            	$almaCourses["$courseID"]["courseName"]=$courseName;
		            	$almaCourses["$courseID"]["courseCode"]=$courseCode;
		            	$almaCourses["$courseID"]["courseSection"]=$courseSection;
		            	$almaCourses["$courseID"]["courseInstructor"]=$instructor;
		            	$almaCourses["$courseID"]["courseIdentifier"]=$courseID;
            			$almaCourses["$courseID"]["status"]=$status;
            
            //echo "<p>$courseID | $courseCode | $courseName | $courseSection</p>";

           			} 

	            }
	            else{
	            	$courseId="N/A";$id="N/A";
					break;
				}    
 
	        }		   

           return $almaCourses;    

    }
    
    function writeFile($id, $xml){
            $myFile = "/home/watzek_web/html/reserves/xml/$id.xml";
            $fh = fopen($myFile, 'w') or die("can't open file");
            fwrite($fh, $xml);
            fclose($fh);
 
    }    

    
    function getDBarray(){
        $dbIDs=array();
        $sql="select course_identifier, course_code, instructor from courses";
        $dbDeets=array();
                
        if ($result = mysqli_query($this->link, $sql)) {
            while ($obj = mysqli_fetch_object($result)) {
                $id=$obj->course_identifier;
                $courseCode=$obj->course_code;
                $instructor=$obj->instructor;
                $dbDeets["$id"]="$courseCode - $instructor";
                array_push($dbIDs, $id);
                //echo $id;
            }
            /* free result set */
            mysqli_free_result($result);
        }
        $this->dbDeets=$dbDeets;
        return $dbIDs;
    
    }
    


}






?>