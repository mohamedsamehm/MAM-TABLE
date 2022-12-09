<?php
	$body = "dshboard";
	$pageTitle = "Dashboard";
	session_start();
	if(!isset($_SESSION['name'])) {
		header('Location: login.php');
		exit();
	}
	include 'init.php';
  if(isset($_GET['type'])) {
    $type = $_GET['type'];
  } else {
		$type = "free_places";
	}
	$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
	$period = ['9:00-9:30', '9:30-10:00', '10:00-10:30', '10:30-11:00', '11:00-11:30', '11:30-12:00', '12:00-12:30', '12:30-01:00', '01:00-01:30', '01:30-02:00'];
?>
<?php include 'sidebar.php'; ?>
<?php if($type == "free_places") {
	$fullinfo = [];
	$stmt = $con->prepare("SELECT work_place.*,place.* FROM work_place INNER JOIN place ON place.code=work_place.Place_code");
	$stmt->execute();
	$results = $stmt->fetchAll();
	foreach ($results as $key => $value) {
		for ($i=$value['period_from']; $i <= $value['period_to']; $i++) {
			$fullinfo[$value['code']][$value['day']]['full'][] = $i;
		}
	}
	foreach ($fullinfo as $code => $codeval) {
		foreach ($codeval as $day => $dayvalue) {
			foreach ($dayvalue as $full => $fullvalue) {
				for ($i=1; $i <= 10; $i++) { 
					if(!in_array($i, $fullvalue)) {
						$fullinfo[$code][$day]['free'][] = $i;
					}
				}
			}
		}
	}?>
	<h3 class="text-center mt-3">جدول التفرغات</h3>
	<div class="table__wrapper">
		<table>
			<thead>
				<tr>
					<th class="day">Days</th>
					<th class="numeric"></th>
					<th class="times">Times</th>
					<th colspan="5">Locations</th>
				</tr>
			</thead>
		<tbody>
			<?php for ($j=0; $j < count($days); $j++) :?>
				<?php for ($i=1; $i <= count($period); $i++) :$counter=0;$array_codes=[]; ?>
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
						<?php foreach ($fullinfo as $code => $codeval) {
							foreach ($codeval as $day => $dayvalue) {
								foreach ($dayvalue as $free => $freevalue) {
									if($free == 'free') {
										if($days[$j] == $day && (in_array($i, $freevalue))) {
											$counter++;
											echo '<td>' . $code . '</td>';
										}
									} else {
										continue;
									}
								}
							}
						} 
						if($counter !== 5) {
							for ($counter_td=$counter; $counter_td < 5; $counter_td++) { 
								echo '<td></td>';
							}
						}
						?>
					</tr>
				<?php endfor;?>
			<?php endfor;?>
		</tbody>
		</table>
	</div>
<?php } elseif($type=="load_eng") {
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if(isset($_POST['ID'])) {
			$id = $_POST['ID'];
			$stmt = $con->prepare("SELECT lab_and_sections.*,engineers.*,work_place.*,courses.name AS course_name FROM engineers 
			INNER JOIN lab_and_sections ON lab_and_sections.engineers_ID=engineers.ID
			INNER JOIN work_place ON work_place.ID=lab_and_sections.work_place_ID
			INNER JOIN courses ON courses.code=work_place.Courses_code
			WHERE lab_and_sections.engineers_ID = '$id'");
			$stmt->execute();
			$results = $stmt->fetchAll();
			$stmt = $con->prepare("SELECT name FROM engineers WHERE ID = '$id'");
			$stmt->execute();
			$eng = $stmt->fetch();?>
			<h3 class="text-center mt-3">م/ <?php echo $eng['name'];?></h3>
			<div class="table__wrapper">
				<table>
						<thead>
								<tr>
										<th class="day">Days</th>
										<th class="numeric"></th>
										<th class="times">Times</th>
										<th>Location / Courses</th>
								</tr>
						</thead>
						<tbody>
								<?php for ($j=0; $j < count($days); $j++) :?>
										<?php $counter = 0; for ($i=1; $i <= count($period); $i++) : ?>
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
														<?php endif;
														foreach($results as $key => $value) {
															if($value['period_from'] == $i && $days[$j] == $value['day']) {
																if($value['period_to'] !== $value['period_from']) {
																	$td_row_enabled = 1;
																	$c_counter = $value['period_to']-$value['period_from']+1;
																		if($value['period_from'] == 9) {
																			echo '<td class="last_rowspan" rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['Place_code'].'</td>';
																		} else {
																			echo '<td rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['Place_code'].'</td>';
																		}
																} else {
																	echo '<td>'.$value['course_name'] . '<br>' . $value['Place_code'].'</td>';
																}
																for ($counter_period=$value['period_from']; $counter_period <= $value['period_to']; $counter_period++) { 
																	$counter++;
																}
															}
														}
														if($counter < $i) {
															$counter++;
															echo '<td></td>';
														}
														?>
												</tr>
										<?php endfor;?>
								<?php endfor;?>
						</tbody>
				</table>
			</div>
		<?php } 
	} else {
		$stmt = $con->prepare("SELECT * FROM `engineers`");
		$stmt->execute();
		$results = $stmt->fetchAll();?>
		<div class="container">
			<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="POST" class="mt-4">
				<div class="form-group">
						<label>Select Engineer</label>
						<select class="form-control" name="ID">
								<?php foreach ($results as $key => $value) {?>
										<option value="<?php echo $value['ID'];?>"><?php echo $value['name'];?></option>
								<?php } ?>
						</select>
				</div>
				<button type="submit" class="btn btn-primary">Select</button>
		</form>
		</div>
	<?php }?>
<?php } elseif($type=="load_dr") {
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if(isset($_POST['ID'])) {
			$id = $_POST['ID'];
			$stmt = $con->prepare("SELECT lecture.*,professor.*,work_place.*,courses.name AS course_name FROM professor 
			INNER JOIN lecture ON lecture.professor_ID=professor.ID
			INNER JOIN work_place ON work_place.ID=lecture.work_place_ID
			INNER JOIN courses ON courses.code=work_place.Courses_code
			WHERE lecture.Professor_ID = '$id' GROUP BY work_place_ID");
			$stmt->execute();
			$results = $stmt->fetchAll();
			$stmt = $con->prepare("SELECT name FROM professor WHERE ID = '$id'");
			$stmt->execute();
			$professor = $stmt->fetch();?>
			<h3 class="text-center mt-3">د/ <?php echo $professor['name'];?></h3>
			<div class="table__wrapper">
				<table>
						<thead>
								<tr>
										<th class="day">Days</th>
										<th class="numeric"></th>
										<th class="times">Times</th>
										<th>Location / Courses</th>
								</tr>
						</thead>
						<tbody>
								<?php for ($j=0; $j < count($days); $j++) :?>
										<?php $counter = 0; for ($i=1; $i <= count($period); $i++) : ?>
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
														<?php endif;
														foreach($results as $key => $value) {
															if($value['period_from'] == $i && $days[$j] == $value['day']) {
																if($value['period_to'] !== $value['period_from']) {
																	$td_row_enabled = 1;
																	$c_counter = $value['period_to']-$value['period_from']+1;
																		if($value['period_from'] == 9) {
																			echo '<td class="last_rowspan" rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['Place_code'].'</td>';
																		} else {
																			echo '<td rowspan="'.($value['period_to']-$value['period_from']+1).'">'.$value['course_name'] . '<br>' . $value['Place_code'].'</td>';
																		}
																} else {
																	echo '<td>'.$value['course_name'] . '<br>' . $value['Place_code'].'</td>';
																}
																for ($counter_period=$value['period_from']; $counter_period <= $value['period_to']; $counter_period++) { 
																	$counter++;
																}
															}
														}
														if($counter < $i) {
															$counter++;
															echo '<td></td>';
														}
														?>
												</tr>
										<?php endfor;?>
								<?php endfor;?>
						</tbody>
				</table>
			</div>
		<?php } 
	} else {
		$stmt = $con->prepare("SELECT * FROM `professor`");
		$stmt->execute();
		$results = $stmt->fetchAll();?>
		<div class="container">
			<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="POST" class="mt-4">
				<div class="form-group">
						<label>Select Professor</label>
						<select class="form-control" name="ID">
								<?php foreach ($results as $key => $value) {?>
										<option value="<?php echo $value['ID'];?>"><?php echo $value['name'];?></option>
								<?php } ?>
						</select>
				</div>
				<button type="submit" class="btn btn-primary">Select</button>
		</form>
		</div>
	<?php }?>
<?php }?>
<?php include $tpl . 'footer.php'; ?>