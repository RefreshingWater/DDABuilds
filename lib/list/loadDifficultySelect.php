<div class="form-group">
	<label for="difficultyselect">Difficulty:</label>
	<select class="form-control" id="difficultyselect" name="difficulty">
		<?php
		if ( $this->templateName !== 'map' ) {
			echo '<option value="0">Any</option>';
		}

		$difficulties = Difficulties::getAllDifficulties();
		foreach ( $difficulties as $difficulty ) {
			$difficultyId = $difficulty->getID();
			$difficultyName = $difficulty->getData('name');
			$selected = '';
			if ( !empty($_GET['load']) && $difficultyId == $build->getData('difficulty') ) {
				$selected = ' selected="selected"';
			}
			echo '<option value="'.$difficultyId.'"'.$selected.'>'.$difficultyName.'</option>';
		}
		?>
	</select>
</div>