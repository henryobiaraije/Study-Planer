<?php

?>

<ul class="sp-deck-groups list-none" style="list-style: none;padding: 0">
    <li v-for="(item,itemIndex) in deckGroupList"
        :key="item.id"
        class="mb-4">
        <?php /**** Deck group header ***/ ?>
        <div class="sp-deck-group cursor-pointer shadow  gap-2">
            <div class="flex flex-wrap sp-deck-group-header">
                <div @click="toggle('.decks-'+item.id)"
                     style="min-width: 300px"
                     class="sp-header-title flex bg-sp-200 hover:bg-sp-300  px-3 flex-1">
                    <div class="sp-icon flex-initial items-center flex">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             xmlns:xlink="http://www.w3.org/1999/xlink"
                             version="1.1"
                             id="Capa_1"
                             x="0px"
                             y="0px"
                             width="30.727px"
                             height="30.727px"
                             viewBox="0 0 30.727 30.727"
                             style="width: 12px;enable-background:new 0 0 30.727 30.727;"
                             xml:space="preserve">
<g>
    <path d="M29.994,10.183L15.363,24.812L0.733,10.184c-0.977-0.978-0.977-2.561,0-3.536c0.977-0.977,2.559-0.976,3.536,0   l11.095,11.093L26.461,6.647c0.977-0.976,2.559-0.976,3.535,0C30.971,7.624,30.971,9.206,29.994,10.183z"/>
</g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
                            <g>
                            </g>
</svg>
                    </div>
                    <div class="sp-name ml-2 ml-2 flex-1 font-medium items-center flex flex-1 font-medium items-center px-4 py-2 sp-name ">
                        {{item.name}}
                    </div>
                    <div v-if="undefined !== item.decks_arr" class="sp-deck-count flex-initial flex items-center">{{item.decks_arr.length}} deck{{(item.decks_arr.length > 1) ? 's' : '' }}</div>
                </div>
                <div class="sp-header-stats px-4 flex-initial bg-sp-100 w-full md:w-auto"
                     @click="toggle('.decks-'+item.id)"
                     style="min-width: 300px">
                    <!--                    <div class="status-title text-center font-bold">Number of cards due for revision</div>-->
                    <div class="to-study flex">
                        <div class="one-study bg-white flex-1 shadow px-2 m-2 text-center flex gap-2">
                            <div class="study-title whitespace-nowrap ">On
                                hold: <?php //todo might change to "Previously false" ?></div>
                            <div :class="{'bg-sp-100 rounded-full px-2 font-bold' : item.due_summary['previously_false'] > 0}" class="study-number fs-4">{{item.due_summary['previously_false']}}</div>
                        </div>
                        <div class="one-study bg-white flex-1 shadow px-2 m-2 text-center flex gap-2">
                            <div class="study-title whitespace-nowrap">Revision:</div>
                            <div :class="{'bg-sp-100 rounded-full px-2 font-bold' : item.due_summary['revision'] > 0}" class="study-number fs-4">{{item.due_summary['revision']}}</div>
                        </div>
                        <div class="one-study bg-white flex-1 shadow px-2 m-2 text-center flex gap-2">
                            <div class="study-title whitespace-nowrap">New cards:</div>
                            <div :class="{'bg-sp-100 rounded-full px-2 font-bold' : item.due_summary['new'] > 0}" class="study-number fs-4">{{item.due_summary['new']}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="sp-decks list-none  divide-y divide-sp-400 " :class="['decks-'+item.id]" style="display: none; list-style: none; padding: 0">
                <?php /**** Deck header ***/ ?>
                <!--  <li v-for="(item2,itemIndex2) in item.decks_arr"
                    :key="item2.id"
                    class="pl-4 mt-2">
                    <div @click="userDash.openStudyModal(item2)" class="sp-d-header cursor-pointer  flex flex-wrap gap-0 md:gap-2">
                        <div class="sp-header-title flex bg-sp-100 hover:bg-sp-200  px-3 py-3 flex-1">
                            <div style="min-width: 300px;"
                                 class="sp-name text-2xl px-10 py-2  flex-1 font-medium
									text-2xl px-10 py-2  flex-1 font-medium
									items-center flex flex-1 font-medium items-center justify-center px-10 py-2 sp-name text-2xl text-center">
                                {{item2.name}}
                            </div>
                            <div class="sp-deck-count flex-initial flex items-center">{{item2.card_count}} card{{(item2.card_count > 1) ? 's' : ''
                                }}
                            </div>
                        </div>
                        <div class="sp-header-stats  py-2 flex-initial bg-sp-100 w-full md:w-auto"
                             style="min-width: 300px;">
                            <div class="status-title text-center font-bold">Number of cards due for revision
                            </div>
                            <div class="to-study flex">
                                <div class="one-study bg-white flex-1 shadow p-2 m-2 text-center ">
                                    <div class="study-title whitespace-nowrap whitespace-no-wrap">On
                                        hold <?php /*//todo might change to "Previously false" */ ?></div>
                                    <div class="study-number font-bold fs-4">{{item2.due_summary['previously_false']}}
                                    </div>
                                </div>
                                <div class="one-study bg-white flex-1 shadow p-2 m-2 text-center ">
                                    <div class="study-title whitespace-nowrap whitespace-no-wrap">Revision</div>
                                    <div class="study-number font-bold fs-4">{{item2.due_summary['revision']}}</div>
                                </div>
                                <div class="one-study bg-white flex-1 shadow p-2 m-2 text-center ">
                                    <div class="study-title whitespace-nowrap whitespace-no-wrap">New cards</div>
                                    <div class="study-number font-bold fs-4">{{item2.due_summary['new']}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>-->
                <li v-for="(item2,itemIndex2) in item.decks_arr" :key="item2.id" class="border-b-2 hover:bg-sp-100 cursor-pointer" style="border-bottom: 1px solid #ffecdb">
                    <div @click="userDash.openStudyModal(item2)" class="sp-d-header cursor-pointer flex flex-wrap gap-4">
                        <div class="sp-header-title flex flex-wrap mx:gap-4 justify-content-between flex-1">
                            <div class="flex min-w-[350px]">
                                <div class="sp-name flex-1  px-2 py-1 ">
                                    <span class="font-medium">{{item2.name}}</span>
                                    <span class="bg-sp-100 hover:bg-sp-200 text-gray-600 rounded-full px-4 font-medium"> {{item2.card_count}} card{{(item2.card_count > 1) ? 's' : '' }}</span>
                                </div>
                                <!--                                <div class="sp-deck-count flex-initial flex items-center py-1 px-2 lg:border-r-[#ffecdb]">-->
                                <!--                                    {{item2.card_count}} card{{(item2.card_count > 1) ? 's' : '' }}-->
                                <!--                                </div>-->
                            </div>
                            <ul class="flex flex-wrap gap-4 flex-initial py-1 px-2 justify-content-center md:justify-content-end w-full md:w-auto mr-1" style="list-style: none; padding: 0;">
                                <li class="flex align-items-center"><span class="text-gray-500 ">On Hold: </span> <span class="ml-2" :class="{'bg-sp-100 rounded-full px-2 font-bold' : item2.due_summary['previously_false'] > 0}">{{item2.due_summary['previously_false']}}</span></li>
                                <li class="flex align-items-center"><span class="text-gray-500 ">Revision: </span> <span class="ml-2" :class="{'bg-sp-100 rounded-full px-2 font-bold' : item2.due_summary['revision'] > 0}">{{item2.due_summary['revision']}}</span></li>
                                <li class="flex align-items-center"><span class="text-gray-500 ">New cards: </span> <span class="ml-2" :class="{'bg-sp-100 rounded-full px-2 font-bold' : item2.due_summary['new'] > 0}">{{item2.due_summary['new']}}</span></li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </li>
</ul>
