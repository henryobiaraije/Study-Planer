<?php
if (!is_user_logged_in()) {
    echo "<div>Sorry, you have to login to access your dashboard</div>";
    exit;
}
?>


<div class="modal fade" id="modal-questions" style="z-index:99999999;display: none" tabindex="-1"
     aria-labelledby="exampleModalEdit" aria-hidden="true" style="padding:0; margin:0">
    <div class="modal-dialog" style="max-width: 100%;margin: 0;height: 100%;min-height: 100%;max-height: 100%;">
        <form v-if="null !== studyToEdit" @submit.prevent="userDash.startStudy" class="modal-content" style="height: 100%;">
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
            <div class="modal-body" style="background-color: #fff">
                <div class="mb-4">
                    <?php \StudyPlanner\load_template('shortcodes/dashboard/template-part-accept-changes'); ?>
                </div>
                <div v-if="null !== currentQuestion && !currentQuestion.has_updated" class="sp-question min-h-[65vh] flex align-items-center overflow-x-auto"
                     style="background-repeat: no-repeat;background-size: cover;"
                     :style="{'background-image' : 'url('+currentQuestion.card_group.bg_image_url+')'}">
                    <?php /*** Basic Card ***/ ?>
                    <div v-if="'basic' === currentQuestion.card_group.card_type" class="sp-basic-question"
                         style="font-family: 'Montserrat', sans-serif;">
                        <div v-html="(currentQuestion.card_group.reverse === 1) ? currentQuestion.answer : currentQuestion.question "
                             class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center mb-2"></div>
                        <div v-show="userDash.showCurrentAnswer.value" style="font-family: 'Montserrat', sans-serif;"
                             v-html="(currentQuestion.card_group.reverse === 1) ? currentQuestion.question : currentQuestion.answer"
                             class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center "></div>
                    </div>
                    <?php /*** Gap Card ***/ ?>
                    <div v-else-if="'gap' === currentQuestion.card_group.card_type" class="sp-gap-question ">
                        <div v-show="!userDash.showCurrentAnswer.value"
                             @click="userDash._showAnswer()" v-html="currentQuestion.question" style="font-family: 'Montserrat', sans-serif;"
                             class=" p-2 rounded-2 text-center mb-4 lg:max-w-4xl m-auto "></div>
                        <div v-show="userDash.showCurrentAnswer.value" v-html="currentQuestion.answer" style="font-family: 'Montserrat', sans-serif;"
                             class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center "></div>
                    </div>
                    <?php /*** Table Card ***/ ?>
                    <div v-else-if="'table' === currentQuestion.card_group.card_type" class="sp-table-question ">
                        <table v-show="!userDash.showCurrentAnswer.value"
                               @click="userDash._showAnswer()" v-if="currentQuestion.question.length > 0"
                               class="table gap-table  p-2 bg-sp-100 rounded  mb-2" style="font-family: 'Montserrat', sans-serif;">
                            <thead>
                            <tr>
                                <th v-for="(item2,itemIndex2) in currentQuestion.question[0]"
                                    class="table-cell border-1 border-sp-200">
                                    <div v-html="item2"></div>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item2,itemIndex2) in currentQuestion.question"
                                :class="{'bg-gray-100' : (itemIndex2 / 2 > 0)}"
                                v-if="itemIndex2 !== 0">
                                <td v-for="(item3,itemIndex3) in item2" class="table-cell border-1 border-sp-200">
                                    <div v-html="item3"></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <table v-if="currentQuestion.answer.length > 0 && userDash.showCurrentAnswer.value"
                               style="font-family: 'Montserrat', sans-serif;"
                               class="table gap-table  p-2 bg-sp-100 rounded ">
                            <thead>
                            <tr>
                                <th v-for="(item2,itemIndex2) in currentQuestion.answer[0]"
                                    class="table-cell border-1 border-sp-200">
                                    <div v-html="item2"></div>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item2,itemIndex2) in currentQuestion.answer"
                                :class="{'bg-gray-100' : (itemIndex2 / 2 > 0)}"
                                v-if="itemIndex2 !== 0">
                                <td v-for="(item3,itemIndex3) in item2" class="table-cell border-1 border-sp-200">
                                    <div v-html="item3"></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php /*** Image Card ***/ ?>
                    <div v-else-if="'image' === currentQuestion.card_group.card_type" class="w-full">
                        <div v-show="!userDash.showCurrentAnswer.value"
                             class="sp-image-question m-auto  mb-2">
                            <div class="image-area" :style="{height: currentQuestion.h+'px' }">
                                <div :id="'main-preview-'+currentQuestion.hash"
                                     class="image-area-inner-preview image-card-view ">
									<span v-for="(item2,itemIndex2) in currentQuestion.question.boxes"
                                          style="font-family: 'Montserrat', sans-serif;"
                                          :id="'sp-box-preview-'+item2.hash"
                                          :class="{'show-box': item2.show, 'asked-box' : item2.asked, 'hide-box' : item2.hide }"
                                          :key="item2.hash" class="sp-box-preview ">
										<span v-if="item2.imageUrl.length < 2"></span>
										<img v-if="item2.imageUrl.length > 0" :src="item2.imageUrl" alt="">
									</span>
                                </div>
                            </div>
                        </div>
                        <div v-show="userDash.showCurrentAnswer.value" class="sp-image-question m-auto ">
                            <div class="image-area" :style="{height: currentQuestion.h+'px' }">
                                <div :id="'main-preview-'+currentQuestion.hash"
                                     class="image-area-inner-preview image-card-view ">
									<span v-for="(item2,itemIndex2) in currentQuestion.answer.boxes"
                                          style="font-family: 'Montserrat', sans-serif;"
                                          :id="'sp-box-preview-'+item2.hash"
                                          :class="{'show-box': item2.show, 'hide-box' : item2.hide, 'hide-box' : item2.hide }"
                                          :key="item2.hash" class="sp-box-preview">
										<span v-if="item2.imageUrl.length < 2"></span>
										<img v-if="item2.imageUrl.length > 0" :src="item2.imageUrl" alt="">
									</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="userDash.ajaxLoadingCard.sending" style="text-align: center;flex: 12;font-size: 50px;"><i
                                class="fa fa-spin fa-spinner"></i></div>
                </div>

                <div class="modal-footer justify-center">
                    <?php /*** Buttons (Show Answer | Hold) ***/ ?>
                    <div v-if="!userDash.showGrade.value && null !== currentQuestion && !currentQuestion.has_updated" class="show-answer m-2">
                        <button @click="userDash._showAnswer()" type="button" class="sp-action-button">Show Answer
                        </button>
                        <button @click="userDash._hold()" type="button" class="sp-action-button">Hold</button>
                    </div>
                    <?php /*** Buttons (Again | Hard | Good | Easy) ***/ ?>
                    <div v-if="userDash.showGrade.value" class="show-grade flex justify-center">
                        <div class="one-grade flex-initial px-2 mx-2">
                            <span class="grade-time block text-center"></span>
                            <button @click="userDash._markAnswer('again')" class="sp-action-button" type="button">
                                Again
                            </button>
                        </div>
                        <div class="one-grade flex-initial mx-2">
                            <span class="grade-time block text-center"></span>
                            <button @click="userDash._markAnswer('hard')" type="button" class="sp-action-button">Hard
                            </button>
                        </div>
                        <div class="one-grade flex-initial mx-2">
                            <span class="grade-time block text-center"></span>
                            <button @click="userDash._markAnswer('good')" type="button" class="sp-action-button">Good
                            </button>
                        </div>
                        <div class="one-grade flex-initial mx-2">
                            <span class="grade-time block text-center"></span>
                            <button @click="userDash._markAnswer('easy')" type="button" class="sp-action-button">Easy
                            </button>
                        </div>
                    </div>
                    <?php /*** Accept changes ***/ ?>
                    <div v-if="null !== currentQuestion && currentQuestion.has_updated" class="show-grade flex justify-center">
                        <div class="flex align-items-center font-bold">Accept Changes?</div>
                        <div class="one-grade flex-initial px-2 mx-2">
                            <span class="grade-time block text-center"></span>
                            <button @click="userDash._acceptChanges('yes')" class="sp-action-button" type="button">Yes</button>
                        </div>
                        <div class="one-grade flex-initial mx-2">
                            <span class="grade-time block text-center"></span>
                            <button @click="userDash._acceptChanges('no')" type="button" class="sp-action-button">No
                            </button>
                        </div>
                        <div class="one-grade flex-initial mx-2">
                            <span class="grade-time block text-center"></span>
                            <button @click="userDash._acceptChanges('remind_me_later')" type="button" class="sp-action-button">Remind Me Later</button>
                        </div>
                    </div>
                    <div v-if="null !== currentQuestion && currentQuestion.has_updated" class="w-full mt-2 flex-1 min-w-300px" style="flex:12">
                        <label class="w-full">
                            <textarea v-model="currentQuestion.accept_changes_comment" class="w-full" placeholder="Comment on this change"></textarea>
                        </label>
                    </div>
                </div>
                <?php /** Debug Section */ ?>
                <section v-if="showExtra && null !== userDash.lastAnsweredDebugData.value" class="debug-section p-2">
                    <table class="table  rounded">
                        <tbody>
                        <tr v-for="(value,key) in userDash.lastAnsweredDebugData.value">
                            <td>{{key}}</td>
                            <td>{{value}}</td>
                        </tr>
                        </tbody>
                    </table>
                </section>
        </form>
    </div>
</div>

