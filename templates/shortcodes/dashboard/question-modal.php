<?php

?>


<div class="modal fade" id="modal-questions" style="z-index:99999999;display: none" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true" >
	<div class="modal-dialog" >
		<form v-if="null !== studyToEdit" @submit.prevent="userDash.startStudy" class="modal-content" >
			<div class="modal-header" >
				<h5 class="modal-title" id="exampleModalEdit" >Study ({{studyToEdit.deck.name}})</h5 >
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button >
			</div >
			<div class="modal-body" >

				<div v-if="userDash.ajaxLoadingCard" style="text-align: center;flex: 12;font-size: 50px;" ><i class="fa fa-spin fa-spinner" ></i ></div >
			</div >
			<div class="modal-footer" >
				<ajax-action
						button-text="Study"
						css-classes="button"
						icon="fa fa-save"
						:ajax="userDash.ajaxSaveStudy.value" >
				</ajax-action >
			</div >
		</form >
	</div >
</div >

