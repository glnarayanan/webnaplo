<?php

	$db = $GLOBALS['db'];
	
	content_for('body');
?>
<!-- 100% Box Grid Container: Start -->
<div class="grid_24">
<?php
	if(isset($flash['success'])) {
?>
	<!-- Success Notice: Start -->
	<div class="notice success">
		<p><?php echo $flash['success']; ?>.</p>
	</div>
	<!-- Success Notice: End -->
<?php
	}

	if(isset($flash['error'])) {
?>
	<!-- Error Notice: Start -->
	<div class="notice error">
		<p><?php echo $flash['error']; ?>.</p>
	</div>
	<!-- Error Notice: End -->

<?php
	}
	
	if(isset($flash['warning'])) {
?>
	<!-- Error Notice: Start -->
	<div class="notice warning">
		<p><?php echo $flash['warning']; ?>.</p>
	</div>
	<!-- Error Notice: End -->

<?php
		}
?>

	<!-- Box Header: Start -->
	<div class="box_top">
		<h1 class="icon frames">Add Section</h1>
	</div>
	<!-- Box Header: End -->
	
	<!-- Box Content: Start -->
	<div class="box_content padding">
	<form method="POST" action="<?php echo url_for('/dataentry/section/add'); ?>">	

		<div class="field noline">
			<label class="left"> Name</label>
			<label class="nobold left nowidth"><input type="text" class="required big validate" name="name" id="name" /></label>
		</div>
		<div class="field">
			<label class="left">Programme Name</label>
			<label class="nobold left nowidth">
			<?php
				$p = $db->select("programme", "1 = 1 order by name asc");
			?>
				<select name="programme_id" id="programme">
				<?php
					foreach($p as $pgm) {
				?>
					<option value="<?php echo $pgm['idprogramme']; ?>"><?php echo $pgm['name']; ?></option>
				<?php
					}
				?>
				</select>
			</label>
		</div>
		<div class="field noline">
			<button type="submit"> Add </button>
			<button type="reset"> Reset </button>
		</div>
	</form>
	</div>
	<!-- Box Content: End -->
	
</div>
<!-- 100% Box Grid Container: End -->

<?php
	end_content_for();