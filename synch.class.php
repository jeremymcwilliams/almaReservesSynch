<?php

/*

This works under the assumption that there is only one reading list per course. Like, why would you need more than one?



*/



class reservesSynch{


    function __construct(){
			
			$this->apiKey="l7xx70b4c665adc344dd864fe7e383d71e4a";
			
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
        $readingListID=mysqli_real_escape_string($this->link, $course["readingListID"]);
        
        $sql="update courses set course_code='$courseCode', course_name='$courseName', course_section='$courseSection', instructor='$instructor', status=$status, readinglist_id='$readingListID' where course_identifier='$courseID'";
    
        if (mysqli_query($this->link, $sql)){
            return true;
        }
        else{
            return false;
        }

    }
    
    function insertCourse($course){

            $courseName=mysqli_real_escape_string($this->link,$course["courseName"]);
            $courseCode=mysqli_real_escape_string($this->link,$course["courseCode"]);
            $courseSection=mysqli_real_escape_string($this->link,$course["courseSection"]);
            $instructor=mysqli_real_escape_string($this->link,$course["courseInstructor"]);
            $courseID=mysqli_real_escape_string($this->link,$course["courseIdentifier"]);    
            $status=mysqli_real_escape_string($this->link,$course["status"]);
            $readingListID=mysqli_real_escape_string($this->link, $course["readingListID"]);
            
            $sql="insert into courses (course_identifier,course_code,course_name,course_section,instructor, status, readinglist_id) values('$courseID','$courseCode','$courseName','$courseSection','$instructor', $status, '$readingListID') ";
    
            if (mysqli_query($this->link, $sql)){
                return true;
            }
            else{
                return false;
            }    
    
    
    
    }

	function getCourses($limit, $offset){



		$ch = curl_init();
		$url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/courses';
		$queryParams = '?' . urlencode('q') . '=' . urlencode('searchableid~res') . '&' . urlencode('limit') . '=' . urlencode($limit) . '&' . urlencode('offset') . '=' . urlencode($offset) . '&' . urlencode('apikey') . '=' . urlencode($this->apiKey);
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


			$check=$this->getCourses("1","0");

			$xml=simplexml_load_string($check);
			$totalCount=intval($xml->attributes()->total_record_count);
			//echo $totalCount;
			
			$loops=ceil($totalCount/100);
			
			echo $loops;
			$offset=0;
			
			for ($i=1;$i<=$loops;$i++){
			
				$offset=($i-1)*100;
				
				echo "$i $offset<br>";
				
				$data=$this->getCourses("100",$offset);
				$xml=simplexml_load_string($data);
				//echo $data;
				//echo "<hr>";
				
				
				foreach ($xml->course as $course){
			
					$courseID=$course->id;
					$courseCode=$course->code;
					$courseName=$course->name;
					$instructor=$course->instructors->instructor->last_name;
					$courseSection=$course->section;
					$st=$course->status;
					if ($st=="ACTIVE"){$status=1;}
					else{$status=0;}
				
					$readingListID=$this->getReadingListID($courseID);
				
					echo $readingListID."<br>";
				
					//update this to query API
					
					$rl=$this->getCourseReadingList($courseID, $readingListID);
					

					
					$rlxml=simplexml_load_string($rl);



					$this->writeFile($courseID, $rlxml); 
				
				
				
		        	$almaCourses["$courseID"]["courseName"]=$courseName;
		        	$almaCourses["$courseID"]["courseCode"]=$courseCode;
		        	$almaCourses["$courseID"]["courseSection"]=$courseSection;
		        	$almaCourses["$courseID"]["courseInstructor"]=$instructor;
		        	$almaCourses["$courseID"]["courseIdentifier"]=$courseID;
            		$almaCourses["$courseID"]["status"]=$status;
            		$almaCourses["$courseID"]["readingListID"]=$readingListID;				
			
			
				}				
				
				
				
				
				
				
			
			}
			
			
			//var_dump($almaCourses);
			
			
			
			//var_dump($xml);
			//exit;
			/*
			
			foreach ($xml->course as $course){
			
				$courseID=$course->id;
				$courseCode=$course->code;
				$courseName=$course->name;
				$instructor=$course->instructors->instructor->last_name;
				$courseSection=$course->section;
				$st=$course->status;
				if ($st=="ACTIVE"){$status=1;}
				else{$status=0;}
				
				$readingListID=$this->getReadingListID($courseID);
				
				echo $readingListID."<br>";
				
				//update this to query API
				//$this->writeFile($courseId, $xml); 
				
				
				
		        $almaCourses["$courseID"]["courseName"]=$courseName;
		        $almaCourses["$courseID"]["courseCode"]=$courseCode;
		        $almaCourses["$courseID"]["courseSection"]=$courseSection;
		        $almaCourses["$courseID"]["courseInstructor"]=$instructor;
		        $almaCourses["$courseID"]["courseIdentifier"]=$courseID;
            	$almaCourses["$courseID"]["status"]=$status;
            	$almaCourses["$courseID"]["readingListID"]=$readingListID;				
			
			
			}
			
			var_dump($almaCourses);
			
			*/
	   

           return $almaCourses;    

    }


	function getCourseReadingList($courseID, $readingListID){
		$ch = curl_init();
		$url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/courses/{course_id}/reading-lists/{reading_list_id}';
		$templateParamNames = array('{course_id}','{reading_list_id}');
		$templateParamValues = array(urlencode($courseID),urlencode($readingListID));
		$url = str_replace($templateParamNames, $templateParamValues, $url);
		$queryParams = '?' . urlencode('view') . '=' . urlencode('full') . '&' . urlencode('apikey') . '=' . urlencode($this->apiKey);
		curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$response = curl_exec($ch);
		curl_close($ch);
	

		return $response;
	
	}

	

	function getReadingListID($courseID){
	
		$sql="select readinglist_id from courses where course_identifier='$courseID'";
        if ($result = mysqli_query($this->link, $sql)) {
        
        	$row_cnt = mysqli_num_rows($result);
        	if ($row_cnt>0){
        		$obj=mysqli_fetch_object($result);
        		$readingListID=$obj->readinglist_id;
        	}
        	else{
        		$readingListID=$this->getReadingListIDapi($courseID);

        	}

            /* free result set */
            mysqli_free_result($result);
        }
        return $readingListID;		

	}
	
	function getReadingListIDapi($courseID){
	
		$ch = curl_init();
		$url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/courses/{course_id}/reading-lists';
		$templateParamNames = array('{course_id}');
		$templateParamValues = array(urlencode($courseID));
		$url = str_replace($templateParamNames, $templateParamValues, $url);
		$queryParams = '?' . urlencode('apikey') . '=' . urlencode($this->apiKey);
		curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$response = curl_exec($ch);
		curl_close($ch);		
		$xml=simplexml_load_string($response);
		$readingListID=$xml->reading_list->id;
		return $readingListID;	

	}
	



    
    function writeFile($id, $xml){
            $myFile = "xml/$id.xml";
            
            $xml->asXML($myFile);
            
            //$fh = fopen($myFile, 'w') or die("can't open file");
            //fwrite($fh, $xml);
            //fclose($fh);
 
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