<?php
if (!is_user_logged_in()) {
    echo "<div>Sorry, you have to login to access your dashboard</div>";
    exit;
}
?>


<div class="modal fade" id="modal-deck-card-type" style="z-index:99999999;display: none" tabindex="-1"
     aria-labelledby="exampleModalEdit" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 95%;">
        <form v-if="null !== studyToEdit" @submit.prevent="userStatus.startStudy" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span @click.prevent="incrShowExtra">Study</span>
                    ({{studyToEdit.deck.name}}) | {{userDash.answeredCount.value}}
                    <span v-if="null !== currentQuestion"
                          class="text-sm ring-sp-300 bg-sp-500 text-white font-bold px-2 rounded-2 pb-1 hover:bg-sp-600">
						{{currentQuestion.answering_type}}
					</span>
                </h5>
                <button type="button" id="hide-question-moadl" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div v-if="useStats.ajaxDeckCardTypeChart.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                            class="fa fa-spin fa-spinner"></i></div>
            </div>
        </form>
    </div>
</div>

