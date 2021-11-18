<?php
?>


<div class="sp-sc-ud" >

	<div class="all-loaded" style="display: none" >
		<ul class="sp-deck-groups" >
			<li v-for="(item,itemIndex) in deckGroupList" :key="item.id" class="mb-4" >
				<?php /**** Deck group header ***/ ?>
				<div class="sp-dg-header cursor-pointer  flex gap-2" >
					<div @click="toggle('.decks-'+item.id)" class="sp-header-title flex bg-gray-100 hover:bg-gray-200  px-3 py-3 flex-1" >
						<div class="sp-icon flex-initial items-center flex" >
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="30.727px" height="30.727px" viewBox="0 0 30.727 30.727" style="width: 16px;enable-background:new 0 0 30.727 30.727;" xml:space="preserve" >
<g >
	<path d="M29.994,10.183L15.363,24.812L0.733,10.184c-0.977-0.978-0.977-2.561,0-3.536c0.977-0.977,2.559-0.976,3.536,0   l11.095,11.093L26.461,6.647c0.977-0.976,2.559-0.976,3.535,0C30.971,7.624,30.971,9.206,29.994,10.183z" />
</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
								<g >
								</g >
</svg >
						</div >
						<div class="sp-name text-2xl px-10 py-2  flex-1 font-medium" >{{item.name}}</div >
						<div class="sp-deck-count flex-initial flex items-center" >{{item.decks.length}} decks</div >
					</div >
					<div class="sp-header-stats rounded py-2 px-4 flex-initial bg-gray-100" >
						The status
					</div >
				</div >
				<ul class="sp-decks " :class="['decks-'+item.id]" style="display: none" >
					<?php /**** Deck header ***/ ?>
					<li v-for="(item2,itemIndex2) in item.decks" :key="item2.id" class="pl-4 mt-2" >
						<div @click="userDash.openStudyModal(item2)" class="sp-d-header cursor-pointer  flex gap-2" >
							<div class="sp-header-title flex bg-gray-100 hover:bg-gray-200  px-3 py-3 flex-1" >
								<div class="sp-name text-2xl px-10 py-2  flex-1 font-medium" >{{item2.name}}</div >
								<div class="sp-deck-count flex-initial flex items-center" ></div >
							</div >
							<div class="sp-header-stats rounded py-2 px-4 flex-initial bg-gray-100" >
								The status
							</div >
						</div >
					</li >
				</ul >
			</li >
		</ul >
	</div >


	<?php /** Edit Study */ ?>
	<div class="modal fade" id="modal-new" style="z-index:99999999;display: none" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true" >
		<div class="modal-dialog" >
			<form v-if="null !== studyToEdit" @submit.prevent="userDash.startStudy" class="modal-content" >
				<div class="modal-header" >
					<h5 class="modal-title" id="exampleModalEdit" >Study ({{studyToEdit.deck.name}})</h5 >
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button >
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


	<?php /***** Notifications ***/ ?>
	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
