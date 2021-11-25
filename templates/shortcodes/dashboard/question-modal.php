<?php
	if ( ! is_user_logged_in() ) {
		echo "<div>Sorry, you have to login to access your dashboard</div>";
		exit;
	}
?>


<div class="modal fade" id="modal-questions" style="z-index:99999999;display: none" tabindex="-1" aria-labelledby="exampleModalEdit" aria-hidden="true" >
	<div class="modal-dialog" style="max-width: 95%;" >
		<form v-if="null !== studyToEdit" @submit.prevent="userDash.startStudy" class="modal-content" >
			<div class="modal-header" >
				<h5 class="modal-title" id="exampleModalEdit" >
					<span @click.prevent="incrShowExtra" >Study</span >
					({{studyToEdit.deck.name}}) | {{userDash.answeredCount.value}}</h5 >
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button >
			</div >
			<div class="modal-body" >

				<div v-if="null !== currentQuestion" class="sp-question" >
					<div v-if="'basic' === currentQuestion.card_group.card_type" class="sp-basic-question" >
						Basic card
					</div >
					<div v-else-if="'gap' === currentQuestion.card_group.card_type" class="sp-gap-question " >
						<div v-html="currentQuestion.question" class="shadow p-2 rounded-2 text-center mb-4 lg:max-w-4xl m-auto" ></div >
						<div v-show="userDash.showCurrentAnswer.value" v-html="currentQuestion.answer" class="sp-answer lg:max-w-4xl m-auto shadow p-2 rounded-2 text-center" ></div >
					</div >
				</div >

				<div v-if="userDash.ajaxLoadingCard.sending" style="text-align: center;flex: 12;font-size: 50px;" ><i class="fa fa-spin fa-spinner" ></i ></div >
			</div >
			<div class="modal-footer justify-center" >
				<div v-if="!userDash.showGrade.value" class="show-answer m-2" >
					<button @click="userDash._showAnswer()" type="button" class="sp-action-button" >Show Answer</button >
					<button @click="userDash._hold()" type="button" class="sp-action-button" >Hold</button >
				</div >
				<div v-if="userDash.showGrade.value" class="show-grade flex justify-center" >
					<div class="one-grade flex-initial px-2 mx-2" >
						<span class="grade-time block text-center" ></span >
						<button @click="userDash._markAnswer('again')" class="sp-action-button" type="button" >Again</button >
					</div >
					<div class="one-grade flex-initial mx-2" >
						<span class="grade-time block text-center" ></span >
						<button @click="userDash._markAnswer('hard')" type="button" class="sp-action-button" >Hard</button >
					</div >
					<div class="one-grade flex-initial mx-2" >
						<span class="grade-time block text-center" ></span >
						<button @click="userDash._markAnswer('good')" type="button" class="sp-action-button" >Good</button >
					</div >
					<div class="one-grade flex-initial mx-2" >
						<span class="grade-time block text-center" ></span >
						<button @click="userDash._markAnswer('easy')" type="button" class="sp-action-button" >Easy</button >
					</div >
				</div >
<!--				<div v-if="showExtra > 5" >-->
<!--					<ajax-action-not-form-->
<!--							button-text="Load question"-->
<!--							@click="userDash._getQuestions"-->
<!--							css-classes="button"-->
<!--							icon="fa fa-redo"-->
<!--							:ajax="userDash.ajaxSaveStudy.value" >-->
<!--					</ajax-action-not-form >-->
<!--				</div >-->
			</div >
			<?php /** Debug Section */ ?>
			<section v-if="showExtra && null !== userDash.lastAnsweredDebugData.value" class="debug-section p-2" >
				<table class="table shadow rounded" >
					<tbody >
					<tr v-for="(value,key) in userDash.lastAnsweredDebugData.value" >
						<td >{{key}}</td >
						<td >{{value}}</td >
					</tr >
					</tbody >
				</table >
			</section >
		</form >
	</div >
</div >

