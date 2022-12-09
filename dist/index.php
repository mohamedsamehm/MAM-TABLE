<?php
	ob_start();
	$pageTitle = 'Table';
    $full_periods = [];
	include 'init.php';
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
    $period = ['9:00-9:30', '9:30-10:00', '10:00-10:30', '10:30-11:00', '11:00-11:30', '11:30-12:00', '12:00-12:30', '12:30-01:00', '01:00-01:30', '01:30-02:00'];
    if(isset($_GET['depart_name']) && isset($_GET['level']) && isset($_GET['semester'])) {
        $level = $_GET['level'];
        $depart_name = $_GET['depart_name'];
        $semester = $_GET['semester'];
        $fullinfo = [];
        $stmt = $con->prepare("SELECT code FROM place WHERE type = 'lecture'");
        $stmt->execute();
        $placesLectures = $stmt->fetchAll();
        $stmt = $con->prepare("SELECT code FROM place WHERE type = 'section'");
        $stmt->execute();
        $placesSections = $stmt->fetchAll();
        $stmt = $con->prepare("SELECT code FROM place WHERE type = 'lab'");
        $stmt->execute();
        $placesLabs = $stmt->fetchAll();
        $stmt = $con->prepare("SELECT specification FROM place WHERE type = 'lab'");
        $stmt->execute();
        $placesLabsSpec = $stmt->fetchAll();
        $stmt = $con->prepare("SELECT 
            department.*, levels.*
            FROM 
                department
            INNER JOIN
                levels 
            ON 
                levels.Department_code = department.code
            WHERE 
                department.name = '$depart_name' 
            AND 
                levels.level = '$level'
            AND 
                levels.semester =  '$semester'
            LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        $fullinfo['code'] = $result['code'];
        if(!$result['code']) {
            header("Location: http://localhost/MAM_TABLES/dist/"); exit();
        }
        $total_no_of_student = $result['total_no_of_student'];
        $fullinfo['no_of_sections'] = $result['no_of_sections'];
        $fullinfo['periodes_lectures'] = $fullinfo['periodes_sections'] = $fullinfo['periodes_labs'] = $fullinfo['periodes'] = 0;
        $stmt = $con->prepare("SELECT 
            courses.*
            FROM 
                courses
            INNER JOIN 
                levels_has_courses 
            ON 
                levels_has_courses.Courses_code = courses.code
            INNER JOIN 
                levels
            ON 
                levels_has_courses.levels_code = levels.code
            INNER JOIN 
                department
            ON 
                levels.Department_code = department.code
            WHERE
                levels.level = '$level'
            AND
                levels.semester = '$semester'
            AND
                department.name = '$depart_name'");
        $stmt->execute();
        $courses = $stmt->fetchAll();
        foreach ($courses as $key => $value) {
            $fullinfo['subject'][$value['code']] = [];
            $fullinfo['subject'][$value['code']]['name'] = $value['name'];
            $fullinfo['subject'][$value['code']]['period_lecture'] = $value['period_lecture'];
            $fullinfo['subject'][$value['code']]['period_section'] = $value['period_section'];
            $fullinfo['subject'][$value['code']]['period_lab'] = $value['period_lab'];
            $fullinfo['subject'][$value['code']]['credit_hours'] = $value['credit_hours'];
            $fullinfo['subject'][$value['code']]['Regulation'] = $value['Regulation'];
            $fullinfo['periodes_lectures'] = $fullinfo['periodes_lectures'] + $fullinfo['subject'][$value['code']]['period_lecture'];
            $fullinfo['periodes_sections'] = $fullinfo['periodes_sections'] + $fullinfo['subject'][$value['code']]['period_section'];
            $fullinfo['periodes_labs'] = $fullinfo['periodes_labs'] + $fullinfo['subject'][$value['code']]['period_lab'];
        }
        $fullinfo['periodes'] = $fullinfo['periodes_lectures'] + $fullinfo['periodes_sections'] + $fullinfo['periodes_labs'];
        foreach ($courses as $code => $value) {
            $course_code = $value['code'];
            $stmt = $con->prepare("SELECT 
                professor.*, courses.code
            FROM 
                professor
            INNER JOIN 
                professor_has_courses
            ON 
                professor_has_courses.Professor_ID = professor.ID
            INNER JOIN 
                courses 
            ON 
                professor_has_courses.Courses_code = courses.code
            WHERE
                courses.code = '$course_code'");
            $stmt->execute();
            $professors = $stmt->fetchAll();
            foreach ($professors as $key => $value) {
                $fullinfo['subject'][$course_code]['dr'][$value['ID']]['name'] = $value['name'];
                $fullinfo['subject'][$course_code]['dr'][$value['ID']]['free_time'] = $value['free_time'];
                $fullinfo['subject'][$course_code]['dr'][$value['ID']]['hour_per_week'] = $value['hour_per_week'];
            }
            $stmt = $con->prepare("SELECT 
                engineers.*, courses.code
            FROM 
                engineers
            INNER JOIN 
                engineers_has_courses
            ON 
                engineers_has_courses.engineers_ID = engineers.ID
            INNER JOIN 
                courses 
            ON 
                engineers_has_courses.Courses_code = courses.code
            WHERE
                courses.code = '$course_code'");
            $stmt->execute();
            $engineers = $stmt->fetchAll();
            foreach ($engineers as $key => $value) {
                $fullinfo['subject'][$course_code]['eng'][$value['ID']]['name'] = $value['name'];
                $fullinfo['subject'][$course_code]['eng'][$value['ID']]['free_time'] = $value['free_time'];
                $fullinfo['subject'][$course_code]['eng'][$value['ID']]['hour_per_week'] = $value['hour_per_week'];
            }
        }
        $n = 0;
        for ($i = 0; $i < $fullinfo['no_of_sections']; $i++) {
            $n++;
            $stmt = $con->prepare("INSERT IGNORE INTO 
                level_sections(code, Section_name, levels_code)
                VALUES(:zcode, :zsection, :zlevelcode)");
            $stmt->execute(array('zcode' => $fullinfo['code'].'_S'.$n, 'zsection' => 'S'.$n, 'zlevelcode' => $fullinfo['code']));
        }
        $stmt = $con->prepare("SELECT code FROM level_sections WHERE levels_code = ?");
        $stmt->execute(array($fullinfo['code']));
        $sections = $stmt->fetchAll();
        $sections_query_lecture = "";
        $sections_query_sections_and_labs = "";
        foreach ($sections as $key => $value) {
            if($key == count($sections)-1) {
                $sections_query_lecture .= "lecture.level_sections_code = '" . $value['code'] . "'";
            } else {
                $sections_query_lecture .= "lecture.level_sections_code = '" . $value['code'] . "' OR ";
            }
        }
        foreach ($sections as $key => $value) {
            if($key == count($sections)-1) {
                $sections_query_sections_and_labs .= "lab_and_sections.level_sections_code = '" . $value['code'] . "'";
            } else {
                $sections_query_sections_and_labs .= "lab_and_sections.level_sections_code = '" . $value['code'] . "' OR ";
            }
        }
        $dayold = $days[4];
        $counter = 0;
        $stmt = $con->prepare("SELECT work_place.*, courses.name AS course_name, courses.period_lecture, professor.name AS professor_name, level_sections.Section_name, place.address, place.code AS place_code,lecture.level_sections_code
            FROM 
                work_place
            INNER JOIN
                lecture
            ON
                work_place.ID = lecture.work_place_ID
            INNER JOIN
                professor
            ON
                lecture.Professor_ID = professor.ID
            INNER JOIN
                place
            ON
                work_place.Place_code = place.code
            INNER JOIN
                courses
            ON
                courses.code = work_place.Courses_code
            INNER JOIN
                level_sections
            ON
                level_sections.code = lecture.level_sections_code
            WHERE
                level_sections.levels_code = ?");
        $stmt->execute(array($fullinfo['code']));
        $Alllectures = $stmt->fetchAll();
        $stmt = $con->prepare("SELECT work_place.*, courses.name AS course_name, courses.period_section, engineers.name AS engineer_name, level_sections.Section_name, place.address, place.code AS place_code,lab_and_sections.level_sections_code
            FROM 
                work_place
            INNER JOIN
                lab_and_sections
            ON
                work_place.ID = lab_and_sections.work_place_ID
            INNER JOIN
                engineers
            ON
                lab_and_sections.engineers_ID = engineers.ID
            INNER JOIN
                place
            ON
                work_place.Place_code = place.code
            INNER JOIN
                courses
            ON
                courses.code = work_place.Courses_code
            INNER JOIN
                level_sections
            ON
                level_sections.code = lab_and_sections.level_sections_code
            WHERE
                level_sections.levels_code = ?
            and 
            lab_and_sections.type = 'section'");
        $stmt->execute(array($fullinfo['code']));
        $Allsections = $stmt->fetchAll();
        $stmt = $con->prepare("SELECT work_place.*, courses.name AS course_name, courses.period_section, engineers.name AS engineer_name, level_sections.Section_name, place.address, place.code AS place_code,lab_and_sections.level_sections_code
            FROM 
                work_place
            INNER JOIN
                lab_and_sections
            ON
                work_place.ID = lab_and_sections.work_place_ID
            INNER JOIN
                engineers
            ON
                lab_and_sections.engineers_ID = engineers.ID
            INNER JOIN
                place
            ON
                work_place.Place_code = place.code
            INNER JOIN
                courses
            ON
                courses.code = work_place.Courses_code
            INNER JOIN
                level_sections
            ON
                level_sections.code = lab_and_sections.level_sections_code
            WHERE
                level_sections.levels_code = ?
            and 
            lab_and_sections.type = 'lab'");
        $stmt->execute(array($fullinfo['code']));
        $AllLabs = $stmt->fetchAll();
        if(count($Alllectures) == 0 && count($Alllectures) == 0 && count($Alllectures) == 0) {
            foreach ($fullinfo['subject'] as $course_key => $value) {
                if(isset($value['dr'])) {
                    foreach ($days as $day_key => $dayval) {
                        $day;
                        $period_from = 0;
                        $period_to = 0;
                        $counter_hours_check = 0;
                        $free_time_dr;
                        $professor_periods = 0;
                        if($dayval !== $dayold) {
                            $periodes__array = [];
                        }
                        foreach($value['dr'] as $id => $name) {
                            $counter_hours_check++;
                            $free_time_dr = $name['free_time'];
                            $hour_per_week = $name['hour_per_week'];
                            $stmt = $con->prepare("SELECT * FROM `work_place`INNER JOIN lecture ON lecture.work_place_ID = work_place.ID WHERE lecture.Professor_ID = '$id' GROUP BY period_from");
                            $stmt->execute();
                            $professor_data = $stmt->fetchAll();
                            $professor_periods = 0;
                            foreach ($professor_data as $professor_data_key => $professor_data_value) {
                                for ($i=$professor_data_value['period_from']; $i <= $professor_data_value['period_to']; $i++) {
                                    $professor_periods++;
                                }
                            }
                            if($professor_periods >= $name['hour_per_week'] || (isset($name['free_time']) && $free_time_dr == $dayval)) {
                                continue;
                            } else {
                                break;
                            }
                        }
                        if($professor_periods >= $hour_per_week) {
                            break;
                        }
                        if(isset($name['free_time']) && $free_time_dr == $dayval) {
                            continue;
                        } else {
                            for ($place_i=0; $place_i < count($placesLectures); $place_i++) {
                                $day = $dayval;
                                $dayold = $day;
                                $place = $placesLectures[$place_i]['code'];
                                $j = 0;
                                $stmt = $con->prepare("SELECT * FROM `work_place`INNER JOIN lecture ON lecture.work_place_ID = work_place.ID WHERE ($sections_query_lecture) AND work_place.day = '$day' GROUP BY work_place.period_from");
                                $stmt->execute();
                                $dayComplete = $stmt->fetchAll();
                                $dayCompleteArr = [];
                                foreach ($dayComplete as $key => $dayCompleteVal) {
                                    $j++;
                                    for ($i=$dayCompleteVal['period_from']; $i <= $dayCompleteVal['period_to']; $i++) {
                                        if(!in_array($i, $dayCompleteArr)) {
                                            array_push($dayCompleteArr, $i);
                                        }
                                    }
                                    if($j % 2 == 0 && count($dayCompleteArr) !== 0) {
                                        array_push($dayCompleteArr, max($dayCompleteArr)+1);
                                        array_push($dayCompleteArr,  max($dayCompleteArr)+1);
                                    }
                                }
                                $periodes__allowed = [];
                                for ($i=1; $i <= 10; $i++) {
                                    if(!in_array($i, $periodes__allowed) && !in_array($i, $dayCompleteArr)) {
                                        array_push($periodes__allowed, $i);
                                    }
                                }
                                if(count($periodes__allowed) == 0) {
                                    break;
                                } else {
                                    $period_from = min($periodes__allowed);
                                }
                                $period_to = $period_from + $value['period_lecture'] - 1;
                                if(!in_array($period_to, $periodes__allowed) || $period_to > 10) {
                                    break;
                                } else {
                                    $stmt = $con->prepare("SELECT * FROM `work_place` WHERE work_place.day = '$day' AND work_place.Place_code = '$place'");
                                    $stmt->execute();
                                    $results = $stmt->fetchAll();
                                    if($counter == 0) {
                                        $stmt = $con->prepare("SELECT * FROM `work_place` INNER JOIN lecture ON lecture.work_place_ID = work_place.ID WHERE work_place.period_from=1 AND work_place.Place_code='$place' AND work_place.day = '$day' AND NOT ($sections_query_lecture)");
                                        $stmt->execute();
                                        $results_periods = $stmt->fetchAll();
                                        if(count($results_periods) > 0) {
                                            continue;
                                        }
                                    }
                                    $counter++;
                                    $full_periods = [];
                                    foreach ($results as $key => $value_period) {
                                        for ($i=$value_period['period_from']; $i <= $value_period['period_to']; $i++) {
                                            if(!in_array($i, $full_periods)) {
                                                array_push($full_periods, $i);
                                            }
                                        }
                                    }
                                    if($counter % 2 == 0 && count($full_periods) !== 0) {
                                        array_push($full_periods, max($full_periods)+1);
                                        array_push($full_periods,  max($full_periods)+1);
                                    }
                                    $periodes__allowed_2 = [];
                                    for ($i=1; $i <= 10; $i++) {
                                        if(!in_array($i, $periodes__allowed_2) && !in_array($i, $full_periods)) {
                                            array_push($periodes__allowed_2, $i);
                                        }
                                    }
                                    if(!in_array($period_from, $periodes__allowed_2) || !in_array($period_to, $periodes__allowed_2) || count($periodes__allowed_2) == 0) {
                                        continue;
                                    } else {
                                        break;
                                    }
                                }
                            }
                            if(count($periodes__allowed) == 0 || $period_to > 10) {
                                continue;
                            } else {
                                break;
                            }
                        }
                    }
                    if($period_from > 0 && $period_to > 0) {
                        $stmt = $con->prepare("SELECT * FROM `work_place`INNER JOIN lecture ON lecture.work_place_ID = work_place.ID WHERE work_place.Courses_code = '$course_key' AND ($sections_query_lecture)");
                        $stmt->execute();
                        $results = $stmt->fetchAll();
                        if(count($results) < $fullinfo['no_of_sections']) {
                            $stmt = $con->prepare("INSERT IGNORE INTO 
                            work_place(ID, day, period_from, period_to, Place_code, Courses_code)
                            VALUES(NULL, :zday, :zperiod_from, :zperiod_to, :zplace_code, :zcourse_code)");
                            $stmt->execute(array('zday' => $day, 'zperiod_from' => $period_from, 'zperiod_to' => $period_to, 'zplace_code' => $place, 'zcourse_code' => $course_key));
                            $stmt = $con->prepare("SELECT LAST_INSERT_ID() FROM work_place");
                            $stmt->execute();
                            $work_place_ID_results = $stmt->fetch();
                            $work_place_ID = $work_place_ID_results['LAST_INSERT_ID()'];
                            $counter_dr=0;
                            for ($section_i=0; $section_i < count($sections); $section_i++) {
                                $counter_dr=0;
                                foreach ($value['dr'] as $id => $name) {
                                    if($counter_dr == $section_i || count($value['dr']) == 1) {
                                        $section = $sections[$section_i]['code'];
                                        $stmt = $con->prepare("INSERT IGNORE INTO 
                                        lecture(ID, work_place_ID, Professor_ID, level_sections_code)
                                        VALUES(NULL, :zwork_place_ID, :zProfessor_ID, :zlevel_sections_code)");
                                        $stmt->execute(array('zwork_place_ID' => $work_place_ID, 'zProfessor_ID' => $id, 'zlevel_sections_code' => $section));
                                        break;
                                    } else {
                                        $counter_dr++;
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            foreach ($fullinfo['subject'] as $course_key => $value) {
                $counter_eng = 0;
                if(isset($value['eng']) && count($value['eng']) !== 0) {
                    foreach($value['eng'] as $id => $name) {
                        $engineer_id;
                        $stmt = $con->prepare("SELECT * FROM `work_place`INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE lab_and_sections.engineers_ID = '$id' GROUP BY period_from");
                        $stmt->execute();
                        $eng_data = $stmt->fetchAll();
                        $eng_periods = 0;
                        foreach ($eng_data as $eng_data_key => $eng_data_value) {
                            for ($i=$eng_data_value['period_from']; $i <= $eng_data_value['period_to']; $i++) {
                                $eng_periods++;
                            }
                        }
                        if($eng_periods >= $name['hour_per_week']) {
                            continue;
                        } else {
                            $stmt = $con->prepare("SELECT * FROM `work_place` INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE work_place.Courses_code = '$course_key' AND ($sections_query_sections_and_labs) AND lab_and_sections.type='section'");
                            $stmt->execute();
                            $results = $stmt->fetchAll();
                            for($section_i=count($results); $section_i < $fullinfo['no_of_sections']; $section_i++) {
                                $section_code = $sections[$section_i]['code'];
                                foreach ($days as $day_key => $dayval) {
                                    $day;
                                    $period_from = 0;
                                    $period_to = 0;
                                    $periodes__allowed = [];
                                    if($dayval == $name['free_time']) {
                                        continue;
                                    } else {
                                        $engineer_id = $id;
                                        for ($place_i=0; $place_i < count($placesSections); $place_i++) {
                                            $day = $dayval;
                                            $dayold = $day;
                                            $place = $placesSections[$place_i]['code'];
                                            $j = 0;
                                            $stmt = $con->prepare("SELECT * FROM `work_place` 
                                            LEFT JOIN lecture ON lecture.work_place_ID = work_place.ID 
                                            LEFT JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID 
                                            WHERE (
                                                lab_and_sections.level_sections_code = '$section_code' OR 
                                                lecture.level_sections_code = '$section_code'
                                                ) 
                                            AND
                                            work_place.day = '$day' 
                                            GROUP BY work_place.period_from");
                                            $stmt->execute();
                                            $dayComplete = $stmt->fetchAll();
                                            $dayCompleteArr = [];
                                            foreach ($dayComplete as $key => $dayCompleteVal) {
                                                $j++;
                                                for ($i=$dayCompleteVal['period_from']; $i <= $dayCompleteVal['period_to']; $i++) {
                                                    if(!in_array($i, $dayCompleteArr)) {
                                                        array_push($dayCompleteArr, $i);
                                                    }
                                                }
                                                if(max($dayCompleteArr) !== 10) {
                                                    if($j % 2 == 0 && count($dayCompleteArr) !== 0) {
                                                        array_push($dayCompleteArr, max($dayCompleteArr)+1);
                                                        array_push($dayCompleteArr,  max($dayCompleteArr)+1);
                                                    }
                                                }
                                            }
                                            $stmt = $con->prepare("SELECT * FROM `work_place`INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE lab_and_sections.engineers_ID = '$engineer_id' AND day = '$dayval' GROUP BY period_from");
                                            $stmt->execute();
                                            $eng_data = $stmt->fetchAll();
                                            foreach ($eng_data as $eng_data_key => $eng_data_value) {
                                                for ($i=$eng_data_value['period_from']; $i <= $eng_data_value['period_to']; $i++) {
                                                    if(!in_array($i, $dayCompleteArr)) {
                                                        array_push($dayCompleteArr, $i);
                                                    }
                                                }
                                                if(max($dayCompleteArr) !== 10) {
                                                    if($j % 2 == 0 && count($dayCompleteArr) !== 0) {
                                                        array_push($dayCompleteArr, max($dayCompleteArr)+1);
                                                        array_push($dayCompleteArr,  max($dayCompleteArr)+1);
                                                    }
                                                }
                                            }
                                            $periodes__allowed = [];
                                            for ($i=1; $i <= 10; $i++) {
                                                if(!in_array($i, $periodes__allowed) && !in_array($i, $dayCompleteArr)) {
                                                    array_push($periodes__allowed, $i);
                                                }
                                            }
                                            if(count($periodes__allowed) == 0) {
                                                $period_from = 0;
                                                $period_to = 0;
                                                break;
                                            } else {
                                                $period_from = min($periodes__allowed);
                                            }
                                            $period_to = $period_from + $value['period_section'] - 1;
                                            if(!in_array($period_to, $periodes__allowed) || $period_to > 10) {
                                                break;
                                            } else {
                                                $stmt = $con->prepare("SELECT * FROM `work_place` WHERE work_place.day = '$day' AND work_place.Place_code = '$place'");
                                                $stmt->execute();
                                                $results = $stmt->fetchAll();
                                                if($counter == 0) {
                                                    $stmt = $con->prepare("SELECT * FROM `work_place` INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE work_place.period_from=1 AND work_place.Place_code='$place' AND work_place.day = '$day' AND NOT lab_and_sections.level_sections_code = '$section_code'");
                                                    $stmt->execute();
                                                    $results_periods = $stmt->fetchAll();
                                                    if(count($results_periods) > 0) {
                                                        continue;
                                                    }
                                                }
                                                $counter++;
                                                $full_periods = [];
                                                foreach ($results as $key => $value_period) {
                                                    for ($i=$value_period['period_from']; $i <= $value_period['period_to']; $i++) {
                                                        if(!in_array($i, $full_periods)) {
                                                            array_push($full_periods, $i);
                                                        }
                                                    }
                                                }
                                                if($counter % 2 == 0 && count($full_periods) !== 0) {
                                                    array_push($full_periods, max($full_periods)+1);
                                                    array_push($full_periods,  max($full_periods)+1);
                                                }
                                                $periodes__allowed_2 = [];
                                                for ($i=1; $i <= 10; $i++) {
                                                    if(!in_array($i, $periodes__allowed_2) && !in_array($i, $full_periods)) {
                                                        array_push($periodes__allowed_2, $i);
                                                    }
                                                }
                                                if(!in_array($period_from, $periodes__allowed_2) || !in_array($period_to, $periodes__allowed_2) || count($periodes__allowed_2) == 0) {
                                                    continue;
                                                } else {
                                                    break;
                                                }
                                            }
                                        }
                                        if(!in_array($period_to, $periodes__allowed) || count($periodes__allowed) == 0 || $period_to > 10) {
                                            continue;
                                        } else {
                                            break;
                                        }
                                    }
                                }
                                if($period_from > 0 && $period_to > 0) {
                                    $stmt = $con->prepare("SELECT * FROM `work_place` INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE work_place.Courses_code = '$course_key' AND ($sections_query_sections_and_labs) AND lab_and_sections.type = 'section'");
                                    $stmt->execute();
                                    $results = $stmt->fetchAll();
                                    if(count($results) < $fullinfo['no_of_sections']) {
                                        $stmt = $con->prepare("INSERT IGNORE INTO 
                                        work_place(ID, day, period_from, period_to, Place_code, Courses_code)
                                        VALUES(NULL, :zday, :zperiod_from, :zperiod_to, :zplace_code, :zcourse_code)");
                                        $stmt->execute(array('zday' => $day, 'zperiod_from' => $period_from, 'zperiod_to' => $period_to, 'zplace_code' => $place, 'zcourse_code' => $course_key));
                                        $stmt = $con->prepare("SELECT LAST_INSERT_ID() FROM work_place");
                                        $stmt->execute();
                                        $work_place_ID_results = $stmt->fetch();
                                        $work_place_ID = $work_place_ID_results['LAST_INSERT_ID()'];
                                        $stmt = $con->prepare("INSERT IGNORE INTO 
                                        lab_and_sections(ID, type, work_place_ID, engineers_ID, level_sections_code)
                                        VALUES(NULL, 'section', :zwork_place_ID, :zengineers_ID, :zlevel_sections_code)");
                                        $stmt->execute(array('zwork_place_ID' => $work_place_ID, 'zengineers_ID' => $engineer_id, 'zlevel_sections_code' => $sections[count($results)]['code']));
                                        $counter_eng++;
                                        if($counter_eng == 1) {
                                            if(round($fullinfo['no_of_sections']/count($value['eng'])) == $counter_eng) {
                                                break;
                                            } else {
                                                continue;
                                            }
                                        } else {
                                            if(floor($fullinfo['no_of_sections']/count($value['eng'])) == $counter_eng) {
                                                break;
                                            } else {
                                                continue;
                                            }
                                        }
                                    } else {
                                        reset($value['eng']);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
            foreach ($fullinfo['subject'] as $course_key => $value) {
                if(isset($value['period_lab']) && $value['period_lab'] > 0) {
                    foreach($value['eng'] as $id => $name) {
                        $engineer_id;
                        $stmt = $con->prepare("SELECT * FROM `work_place`INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE lab_and_sections.engineers_ID = '$id' GROUP BY period_from");
                        $stmt->execute();
                        $eng_data = $stmt->fetchAll();
                        $eng_periods = 0;
                        foreach ($eng_data as $eng_data_key => $eng_data_value) {
                            for ($i=$eng_data_value['period_from']; $i <= $eng_data_value['period_to']; $i++) {
                                $eng_periods++;
                            }
                        }
                        if($eng_periods >= $name['hour_per_week']) {
                            continue;
                        } else {
                            $stmt = $con->prepare("SELECT * FROM `work_place` INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE work_place.Courses_code = '$course_key' AND ($sections_query_sections_and_labs) AND lab_and_sections.type='lab'");
                            $stmt->execute();
                            $results = $stmt->fetchAll();
                            for($section_i=count($results); $section_i < $fullinfo['no_of_sections']; $section_i++) {
                                $section_code = $sections[$section_i]['code'];
                                foreach ($days as $day_key => $dayval) {
                                    $day;
                                    $period_from = 0;
                                    $period_to = 0;
                                    $periodes__allowed = [];
                                    if($dayval == $name['free_time']) {
                                        continue;
                                    } else {
                                        $engineer_id = $id;
                                        for ($place_i=0; $place_i < count($placesLabs); $place_i++) {
                                            $day = $dayval;
                                            $dayold = $day;
                                            $place = $placesLabs[$place_i]['code'];
                                            $spec = $placesLabsSpec[$place_i]['specification'];
                                            if($spec == $course_key) {
                                                $j = 0;
                                                $stmt = $con->prepare("SELECT * FROM `work_place` 
                                                LEFT JOIN lecture ON lecture.work_place_ID = work_place.ID 
                                                LEFT JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID 
                                                WHERE (
                                                    lab_and_sections.level_sections_code = '$section_code' OR 
                                                    lecture.level_sections_code = '$section_code'
                                                    ) 
                                                AND
                                                work_place.day = '$day' 
                                                GROUP BY work_place.period_from");
                                                $stmt->execute();
                                                $dayComplete = $stmt->fetchAll();
                                                $dayCompleteArr = [];
                                                foreach ($dayComplete as $key => $dayCompleteVal) {
                                                    $j++;
                                                    for ($i=$dayCompleteVal['period_from']; $i <= $dayCompleteVal['period_to']; $i++) {
                                                        if(!in_array($i, $dayCompleteArr)) {
                                                            array_push($dayCompleteArr, $i);
                                                        }
                                                    }
                                                    if(max($dayCompleteArr) !== 10) {
                                                        if($j % 2 == 0 && count($dayCompleteArr) !== 0) {
                                                            array_push($dayCompleteArr, max($dayCompleteArr)+1);
                                                            array_push($dayCompleteArr,  max($dayCompleteArr)+1);
                                                        }
                                                    }
                                                }
                                                $stmt = $con->prepare("SELECT * FROM `work_place`INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE lab_and_sections.engineers_ID = '$engineer_id' AND day = '$dayval' GROUP BY period_from");
                                                $stmt->execute();
                                                $eng_data = $stmt->fetchAll();
                                                foreach ($eng_data as $eng_data_key => $eng_data_value) {
                                                    for ($i=$eng_data_value['period_from']; $i <= $eng_data_value['period_to']; $i++) {
                                                        if(!in_array($i, $dayCompleteArr)) {
                                                            array_push($dayCompleteArr, $i);
                                                        }
                                                    }
                                                    if(max($dayCompleteArr) !== 10) {
                                                        if($j % 2 == 0 && count($dayCompleteArr) !== 0) {
                                                            array_push($dayCompleteArr, max($dayCompleteArr)+1);
                                                            array_push($dayCompleteArr,  max($dayCompleteArr)+1);
                                                        }
                                                    }
                                                }
                                                $periodes__allowed = [];
                                                for ($i=1; $i <= 10; $i++) {
                                                    if(!in_array($i, $periodes__allowed) && !in_array($i, $dayCompleteArr)) {
                                                        array_push($periodes__allowed, $i);
                                                    }
                                                }
                                                if(count($periodes__allowed) == 0) {
                                                    $period_from = 0;
                                                    $period_to = 0;
                                                    break;
                                                } else {
                                                    $period_from = min($periodes__allowed);
                                                }
                                                $period_to = $period_from + $value['period_lab'] - 1;
                                                if(!in_array($period_to, $periodes__allowed) || $period_to > 10) {
                                                    break;
                                                } else {
                                                    $stmt = $con->prepare("SELECT * FROM `work_place` WHERE work_place.day = '$day' AND work_place.Place_code = '$place'");
                                                    $stmt->execute();
                                                    $results = $stmt->fetchAll();
                                                    if($counter == 0) {
                                                        $stmt = $con->prepare("SELECT * FROM `work_place` INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE work_place.period_from=1 AND work_place.Place_code='$place' AND work_place.day = '$day' AND NOT lab_and_sections.level_sections_code = '$section_code'");
                                                        $stmt->execute();
                                                        $results_periods = $stmt->fetchAll();
                                                        if(count($results_periods) > 0) {
                                                            continue;
                                                        }
                                                    }
                                                    $counter++;
                                                    $full_periods = [];
                                                    foreach ($results as $key => $value_period) {
                                                        for ($i=$value_period['period_from']; $i <= $value_period['period_to']; $i++) {
                                                            if(!in_array($i, $full_periods)) {
                                                                array_push($full_periods, $i);
                                                            }
                                                        }
                                                    }
                                                    if($counter % 2 == 0 && count($full_periods) !== 0) {
                                                        array_push($full_periods, max($full_periods)+1);
                                                        array_push($full_periods,  max($full_periods)+1);
                                                    }
                                                    $periodes__allowed_2 = [];
                                                    for ($i=1; $i <= 10; $i++) {
                                                        if(!in_array($i, $periodes__allowed_2) && !in_array($i, $full_periods)) {
                                                            array_push($periodes__allowed_2, $i);
                                                        }
                                                    }
                                                    if(!in_array($period_from, $periodes__allowed_2) || !in_array($period_to, $periodes__allowed_2) || count($periodes__allowed_2) == 0) {
                                                        continue;
                                                    } else {
                                                        break;
                                                    }
                                                }
                                            } else {
                                                continue;
                                            }
                                        }
                                        if(!in_array($period_to, $periodes__allowed) || count($periodes__allowed) == 0 || $period_to > 10) {
                                            continue;
                                        } else {
                                            break;
                                        }
                                    }
                                }
                                if($period_from > 0 && $period_to > 0) {
                                    $stmt = $con->prepare("SELECT * FROM `work_place` INNER JOIN lab_and_sections ON lab_and_sections.work_place_ID = work_place.ID WHERE work_place.Courses_code = '$course_key' AND ($sections_query_sections_and_labs) AND lab_and_sections.type = 'lab'");
                                    $stmt->execute();
                                    $results = $stmt->fetchAll();
                                    if(count($results) < $fullinfo['no_of_sections']) {
                                        $stmt = $con->prepare("INSERT IGNORE INTO 
                                        work_place(ID, day, period_from, period_to, Place_code, Courses_code)
                                        VALUES(NULL, :zday, :zperiod_from, :zperiod_to, :zplace_code, :zcourse_code)");
                                        $stmt->execute(array('zday' => $day, 'zperiod_from' => $period_from, 'zperiod_to' => $period_to, 'zplace_code' => $place, 'zcourse_code' => $course_key));
                                        $stmt = $con->prepare("SELECT LAST_INSERT_ID() FROM work_place");
                                        $stmt->execute();
                                        $work_place_ID_results = $stmt->fetch();
                                        $work_place_ID = $work_place_ID_results['LAST_INSERT_ID()'];
                                        $stmt = $con->prepare("INSERT IGNORE INTO 
                                        lab_and_sections(ID, type, work_place_ID, engineers_ID, level_sections_code)
                                        VALUES(NULL, 'lab', :zwork_place_ID, :zengineers_ID, :zlevel_sections_code)");
                                        $stmt->execute(array('zwork_place_ID' => $work_place_ID, 'zengineers_ID' => $engineer_id, 'zlevel_sections_code' => $sections[count($results)]['code']));
                                        $counter_eng++;
                                        if($counter_eng == 1) {
                                            if(round($fullinfo['no_of_sections']/count($value['eng'])) == $counter_eng) {
                                                break;
                                            } else {
                                                continue;
                                            }
                                        } else {
                                            if(floor($fullinfo['no_of_sections']/count($value['eng'])) == $counter_eng) {
                                                break;
                                            } else {
                                                continue;
                                            }
                                        }
                                    } else {
                                        reset($value['eng']);
                                        break;
                                    }
                                }
                            }
                        }
                        }
                }
            }
            $stmt = $con->prepare("SELECT work_place.*, courses.name AS course_name, courses.period_lecture, professor.name AS professor_name, level_sections.Section_name, place.address, place.code AS place_code,lecture.level_sections_code
                FROM 
                    work_place
                INNER JOIN
                    lecture
                ON
                    work_place.ID = lecture.work_place_ID
                INNER JOIN
                    professor
                ON
                    lecture.Professor_ID = professor.ID
                INNER JOIN
                    place
                ON
                    work_place.Place_code = place.code
                INNER JOIN
                    courses
                ON
                    courses.code = work_place.Courses_code
                INNER JOIN
                    level_sections
                ON
                    level_sections.code = lecture.level_sections_code
                WHERE
                    level_sections.levels_code = ?");
            $stmt->execute(array($fullinfo['code']));
            $Alllectures = $stmt->fetchAll();
            $stmt = $con->prepare("SELECT work_place.*, courses.name AS course_name, courses.period_section, engineers.name AS engineer_name, level_sections.Section_name, place.address, place.code AS place_code,lab_and_sections.level_sections_code
                FROM 
                    work_place
                INNER JOIN
                    lab_and_sections
                ON
                    work_place.ID = lab_and_sections.work_place_ID
                INNER JOIN
                    engineers
                ON
                    lab_and_sections.engineers_ID = engineers.ID
                INNER JOIN
                    place
                ON
                    work_place.Place_code = place.code
                INNER JOIN
                    courses
                ON
                    courses.code = work_place.Courses_code
                INNER JOIN
                    level_sections
                ON
                    level_sections.code = lab_and_sections.level_sections_code
                WHERE
                    level_sections.levels_code = ?
                and 
                lab_and_sections.type = 'section'");
            $stmt->execute(array($fullinfo['code']));
            $Allsections = $stmt->fetchAll();
            $stmt = $con->prepare("SELECT work_place.*, courses.name AS course_name, courses.period_section, engineers.name AS engineer_name, level_sections.Section_name, place.address, place.code AS place_code,lab_and_sections.level_sections_code
                FROM 
                    work_place
                INNER JOIN
                    lab_and_sections
                ON
                    work_place.ID = lab_and_sections.work_place_ID
                INNER JOIN
                    engineers
                ON
                    lab_and_sections.engineers_ID = engineers.ID
                INNER JOIN
                    place
                ON
                    work_place.Place_code = place.code
                INNER JOIN
                    courses
                ON
                    courses.code = work_place.Courses_code
                INNER JOIN
                    level_sections
                ON
                    level_sections.code = lab_and_sections.level_sections_code
                WHERE
                    level_sections.levels_code = ?
                and 
                lab_and_sections.type = 'lab'");
            $stmt->execute(array($fullinfo['code']));
            $AllLabs = $stmt->fetchAll();
        }?>
        <div class="table__wrapper">
            <table>
                <thead>
                    <tr>
                        <th class="day">Days</th>
                        <th class="numeric"></th>
                        <th class="times">Times</th>
                        <?php for ($i=0; $i < $fullinfo['no_of_sections']; $i++) : ?>
                            <th>Section <?php echo ($i+1); ?></th>
                        <?php endfor;?>
                    </tr>
                </thead>
                <tbody>
                    <?php $c=0; $c_counter=0; $td = []; $td_row_enabled=[]; for ($j=0; $j < count($days); $j++) :?>
                        <?php for ($i=1; $i <= count($period); $i++) : ?>
                            <tr>
                                <?php if($i == 1) : ?>
                                    <?php if($j == (count($days)-1)) : ?>
                                        <td rowspan="8" class="day"><div class="rotate"><?php echo $days[$j];?></div></td>
                                    <?php else:?>
                                        <td rowspan="10" class="day"><div class="rotate"><?php echo $days[$j];?></div></td>
                                    <?php endif;?>
                                <?php endif;?>
                                <?php if($j == (count($days)-1) && $i > 8) :
                                    break;
                                else:?>
                                    <td><?php echo $i; ?></td>
                                    <td class="nowrap"><?php echo $period[$i-1]; ?></td>
                                <?php endif;?>
                                <?php for ($k=0; $k < $fullinfo['no_of_sections']; $k++) :
                                    foreach($Alllectures as $key => $value) {
                                        if($sections[$k]['code'] == $value['level_sections_code'] && $days[$j] == $value['day'] && $i == $value['period_from']) {
                                            if($value['period_to'] !== $value['period_from']) {
                                                $td_row_enabled[$k] = 1;
                                                $c_counter = $value['period_lecture'];
                                                if($value['period_from'] == 9) {
                                                    $td[$k] = '<td class="last_rowspan" title="'.$value['address'].'" rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['professor_name'] . '<br>' . $value['place_code'].'</td>';
                                                } else {
                                                    $td[$k] = '<td title="'.$value['address'].'" rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['professor_name'] . '<br>' . $value['place_code'].'</td>';
                                                }
                                            } else {
                                                $td[$k] = '<td title="'.$value['address'].'">'.$value['course_name'] . '<br>' . $value['professor_name'] . '<br>' . $value['place_code'].'</td>';
                                            }
                                        } else {
                                            continue;
                                        }
                                    }
                                    foreach($Allsections as $key => $value) {
                                        if(($sections[$k]['code'] == $value['level_sections_code']) && ($days[$j] == $value['day']) && ($i == $value['period_from'])) {
                                            if($value['period_to'] !== $value['period_from']) {
                                                $td_row_enabled[$k] = 1;
                                                $c_counter = $value['period_section'];
                                                if($value['period_from'] == 9) {
                                                    $td[$k] = '<td class="last_rowspan" title="'.$value['address'].'" rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['engineer_name'] . '<br>' . $value['place_code'].'</td>';
                                                } else {
                                                    $td[$k] = '<td title="'.$value['address'].'" rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['engineer_name'] . '<br>' . $value['place_code'].'</td>';
                                                }
                                            } else {
                                                $td[$k] = '<td title="'.$value['address'].'">'.$value['course_name'] . '<br>' . $value['engineer_name'] . '<br>' . $value['place_code'].'</td>';
                                            }
                                        } else {
                                            continue;
                                        }
                                    }
                                    foreach($AllLabs as $key => $value) {
                                        if(($sections[$k]['code'] == $value['level_sections_code']) && ($days[$j] == $value['day']) && ($i == $value['period_from'])) {
                                            if($value['period_to'] !== $value['period_from']) {
                                                $td_row_enabled[$k] = 1;
                                                $c_counter = $value['period_section'];
                                                if($value['period_from'] == 9) {
                                                    $td[$k] = '<td class="last_rowspan" title="'.$value['address'].'" rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['engineer_name'] . '<br>' . $value['place_code'].'</td>';
                                                } else {
                                                    $td[$k] = '<td title="'.$value['address'].'" rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['engineer_name'] . '<br>' . $value['place_code'].'</td>';
                                                }
                                            } else {
                                                $td[$k] = '<td title="'.$value['address'].'">'.$value['course_name'] . '<br>' . $value['engineer_name'] . '<br>' . $value['place_code'].'</td>';
                                            }
                                        } else {
                                            continue;
                                        }
                                    }
                                endfor;
                                if(count($td_row_enabled) !== 0) {
                                    $c++;
                                }
                                for ($counter=0; $counter < $fullinfo['no_of_sections']; $counter++) {
                                    if (isset($td[$counter]) && $td[$counter] !== '') {
                                        echo $td[$counter];
                                    } else {
                                        if(!isset($td_row_enabled[$counter])) {
                                            echo '<td></td>';
                                        }
                                    }
                                }
                                $td=[];
                                if($c == $c_counter) {
                                    $td_row_enabled=[];
                                    $c=0;
                                }
                                ?>
                            </tr>
                        <?php endfor;?>
                    <?php endfor;?>
                </tbody>
            </table>
            <div class="d-flex justify-content-end align-items-center w-100 mt-4">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addnewtable">Create New Table</button>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="addnewtable" tabindex="-1" role="dialog" aria-labelledby="addnewtableLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addnewtableLabel">Create Table</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="GET">
                        <div class="form-group mb-3">
                            <label>Department</label>
                            <select class="form-control" name="depart_name">
                                <?php 
                                $stmt = $con->prepare("SELECT * FROM department");
                                $stmt->execute();
                                $department = $stmt->fetchAll();
                                foreach ($department as $key => $value) {?>
                                    <option value="<?php echo $value['name'];?>"><?php echo $value['name'];?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Level</label>
                            <input type="text" class="form-control" name="level">
                        </div>
                        <div class="form-group">
                            <label>Semester</label>
                            <select class="form-control" name="semester">
                                <?php 
                                $stmt = $con->prepare("SELECT * FROM levels GROUP BY semester");
                                $stmt->execute();
                                $semester = $stmt->fetchAll();
                                foreach ($semester as $key => $value) {?>
                                    <option value="<?php echo $value['semester'];?>"><?php echo $value['semester'];?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="wrapper">
            <div class="d-flex justify-content-center align-items-center">
                <button type="button" class="btn btn-primary mr-3" data-toggle="modal" data-target="#exampleModal">Start Creating Tables</button>
                <?php 
                    if($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $stmt = $con->prepare("TRUNCATE TABLE lab_and_sections; TRUNCATE TABLE lecture; SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE work_place; SET FOREIGN_KEY_CHECKS = 1; ");
                        $stmt->execute();
                        if($stmt) {?>
                            <script> alert('Tables Deleted Successfully'); </script>
                        <?php }
                    }
                ?>
                <form action="" method="POST">
                    <button type="submit" class="btn btn-danger">Clear All Tables</button>
                </form>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create Table</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="GET">
                        <div class="form-group mb-3">
                            <label>Department</label>
                            <select class="form-control" name="depart_name">
                                <?php 
                                $stmt = $con->prepare("SELECT * FROM department");
                                $stmt->execute();
                                $department = $stmt->fetchAll();
                                foreach ($department as $key => $value) {?>
                                    <option value="<?php echo $value['name'];?>"><?php echo $value['name'];?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Level</label>
                            <input type="text" class="form-control" name="level">
                        </div>
                        <div class="form-group">
                            <label>Semester</label>
                            <select class="form-control" name="semester">
                                <?php 
                                $stmt = $con->prepare("SELECT * FROM levels GROUP BY semester");
                                $stmt->execute();
                                $semester = $stmt->fetchAll();
                                foreach ($semester as $key => $value) {?>
                                    <option value="<?php echo $value['semester'];?>"><?php echo $value['semester'];?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    <?php }?>
<?php include $tpl . 'footer.php'; ?>