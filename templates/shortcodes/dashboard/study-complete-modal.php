<?php
	if ( ! is_user_logged_in() ) {
		echo "<div>Sorry, you have to login to access your dashboard</div>";
		exit;
	}
?>


<div class="modal fade" id="modal-study-complete" style="z-index:99999999;display: none" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true" >
	<div class="modal-dialog" style="max-width: 95%;" >
		<form v-if="null !== studyToEdit" @submit.prevent="userDash.startStudy" class="modal-content" >
			<div class="modal-header" >
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button >
			</div >
			<div class="modal-body" >
				<div class="p-4 m-2 rounded text-bold bg-green-100 text-green-400-shadow" >
					Congratulations. You have completed today's study.
				</div >
			</div >
			<div class="modal-footer justify-center" >
				<ajax-action-not-form
						button-text="Okay"
						@click="userDash._getQuestions"
						css-classes="button"
						icon="fa fa-redo"
						:ajax="userDash.ajaxSaveStudy.value" >
				</ajax-action-not-form >
			</div >
		</form >
	</div >
</div >

