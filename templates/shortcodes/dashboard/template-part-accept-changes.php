<div v-if="null !== currentQuestion && currentQuestion.has_updated" class="sp-question min-h-[65vh] flex align-items-center overflow-x-auto overflow-y-hidden"
     style="background-repeat: no-repeat;background-size: cover;"
     :style="{'background-image' : 'url('+currentQuestion.card_group.bg_image_url+')'}">
    <?php /*** Basic Card ***/ ?>
    <div v-if="'basic' === currentQuestion.card_group.card_type" class="sp-basic-question"
         style="font-family: 'Montserrat', sans-serif;">
        <div class="font-bold ">Old Answer</div>
        <div  style="font-family: 'Montserrat', sans-serif;"
             v-html="(currentQuestion.card_group.reverse === 1) ? currentQuestion.old_question : currentQuestion.old_answer"
             class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center "></div>
        <div class="font-bold mt-2">New Answer</div>
        <div style="font-family: 'Montserrat', sans-serif;"
             v-html="(currentQuestion.card_group.reverse === 1) ? currentQuestion.question : currentQuestion.answer"
             class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center "></div>
    </div>
    <?php /*** Gap Card ***/ ?>
    <div v-else-if="'gap' === currentQuestion.card_group.card_type" class="sp-gap-question ">
        <div class="font-bold ">Old Answer</div>
        <div  v-html="currentQuestion.old_answer" style="font-family: 'Montserrat', sans-serif;"
             class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center "></div>
        <div class="font-bold mt-2">New Answer</div>
        <div v-html="currentQuestion.answer" style="font-family: 'Montserrat', sans-serif;"
             class="sp-answer lg:max-w-4xl m-auto  p-2 rounded-2 text-center "></div>
    </div>
    <?php /*** Table Card ***/ ?>
    <div v-else-if="'table' === currentQuestion.card_group.card_type" class="sp-table-question m-auto">
        <div class="font-bold ">Old Answer</div>
        <table v-if="currentQuestion.answer.length > 0 "
               style="font-family: 'Montserrat', sans-serif;"
               class="table gap-table  p-2 bg-sp-100 rounded ">
            <thead>
            <tr>
                <th v-for="(item2,itemIndex2) in currentQuestion.old_answer[0]"
                    class="table-cell border-1 border-sp-200">
                    <div v-html="item2"></div>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item2,itemIndex2) in currentQuestion.old_answer"
                :class="{'bg-gray-100' : (itemIndex2 / 2 > 0)}"
                v-if="itemIndex2 !== 0">
                <td v-for="(item3,itemIndex3) in item2" class="table-cell border-1 border-sp-200">
                    <div v-html="item3"></div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="font-bold mt-2">Old Answer</div>
        <table v-if="currentQuestion.answer.length > 0 "
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
        <div v-show="currentQuestion.has_updated" class="sp-image-question m-auto ">
            <div class="font-bold ">Old Answer</div>
            <div class="image-area" :style="{height: currentQuestion.h+'px' }">
                <div :id="'main-preview-old-'+currentQuestion.old_answer.hash"
                     class="image-area-inner-preview image-card-view ">
                        <span v-for="(item2,itemIndex2) in currentQuestion.old_answer.boxes"
                              style="font-family: 'Montserrat', sans-serif;"
                              :id="'sp-box-preview-old-'+item2.hash"
                              :class="{'show-box': item2.show, 'hide-box' : item2.hide, 'hide-box' : item2.hide }"
                              :key="item2.hash" class="sp-box-preview">
                            <span v-if="item2.imageUrl.length < 2"></span>
                            <img v-if="item2.imageUrl.length > 0" :src="item2.imageUrl" alt="">
                        </span>
                </div>
            </div>
        </div>
        <div v-show="currentQuestion.has_updated" class="sp-image-question m-auto ">
            <div class="font-bold m-2">New Answer</div>
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
</div>