<?php
?>



<div class="modal fade" id="modal-new" style="z-index:99999999;display: none" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true" >
	<div class="modal-dialog" >
		<form v-if="null !== studyToEdit" @submit.prevent="userDash.startStudy" class="modal-content" >
			<div class="modal-header" >
				<h5 class="modal-title" id="exampleModalEdit" >Study ({{studyToEdit.deck.name}})</h5 >
				<button id="hide-modal-new" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button >
			</div >
			<div class="modal-body" >
				<div class="sp-study-input shadow p-2 rounded fs-5 mb-4 shadow p-2 rounded" >
					<div class="my-1" >Include Tags |
						<label class="cursor-pointer pl-4 bg-gray-200  hover:bg-gray-300 py-1 px-2 rounded" >
							<span class="pr-2" >Include All :</span >
							<input v-model="studyToEdit.all_tags"
							       class="transform scale-150 mx-1" type="checkbox" >
						</label >
					</div >
					<vue-mulitiselect
							v-if="!studyToEdit.all_tags"
							v-model="studyToEdit.tags"
							:options="searchTags.results.value"
							:multiple="true"
							:loading="searchTags.ajax.value.sending"
							:searchable="true"
							:close-on-select="true"
							:taggable="true"
							:createTag="false"
							@tag="searchTags.addTag"
							@search-change="searchTags.search"
							placeholder="Search Tags"
							label="name"
							track-by="id"
					></vue-mulitiselect >
				</div >
				<div class="sp-study-input shadow p-2 rounded fs-5 mb-4 sp-study-input" >
					<div class="my-1" >Number of cards to revise |
						<label class="cursor-pointer pl-4 bg-gray-200   hover:bg-gray-300 py-1 px-2 rounded" >
							<span class="pr-2" > All :</span >
							<input v-model="studyToEdit.revise_all"
							       class="transform scale-150 mx-1" type="checkbox" >
						</label >
						<label v-if="!studyToEdit.revise_all" class="block my-2" >
							<input v-model.number="studyToEdit.no_to_revise"
							       class="w-full bg-white rounded"
							       placeholder="Enter number here" type="text" pattern="[0-9]+" >
						</label >
					</div >
				</div >
				<div class="sp-study-input shadow p-2 rounded fs-5 mb-4 sp-study-input" >
					<div class="my-1" >Number of new cards |
						<label class="cursor-pointer pl-4 bg-gray-200   hover:bg-gray-300 py-1 px-2 rounded" >
							<span class="pr-2" > All :</span >
							<input v-model="studyToEdit.study_all_new"
							       class="transform scale-150 mx-1" type="checkbox" >
						</label >
						<label v-if="!studyToEdit.study_all_new" class="block my-2" >
							<input v-model.number="studyToEdit.no_of_new"
							       class="w-full bg-white rounded"
							       placeholder="Enter number here" type="text" pattern="[0-9]+" >
						</label >
					</div >
				</div >
				<div class="sp-study-input shadow p-2 rounded fs-5 mb-4 sp-study-input" >
					<div class="my-1" >Number of new cards |
						<label class="cursor-pointer pl-4 bg-gray-200   hover:bg-gray-300 py-1 px-2 rounded" >
							<span class="pr-2" > All :</span >
							<input v-model="studyToEdit.study_all_on_hold"
							       class="transform scale-150 mx-1" type="checkbox" >
						</label >
						<label v-if="!studyToEdit.study_all_on_hold" class="block my-2" >
							<input v-model="studyToEdit.no_on_hold"
							       class="w-full bg-white rounded"
							       placeholder="Enter number here" type="text" pattern="^[0-9]+$" >
						</label >
					</div >
				</div >
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

