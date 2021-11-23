<?php

?>

<ul class="sp-deck-groups" >
	<li v-for="(item,itemIndex) in deckGroupList"
	    :key="item.id"
	    class="mb-4" >
		<?php /**** Deck group header ***/ ?>
		<div class="sp-deck-group cursor-pointer shadow  gap-2" >
			<div class="flex sp-deck-group-header" >
				<div @click="toggle('.decks-'+item.id)" class="sp-header-title flex bg-gray-100 hover:bg-gray-200  px-3 py-3 flex-1" >
					<div class="sp-icon flex-initial items-center flex" >
						<svg xmlns="http://www.w3.org/2000/svg"
						     xmlns:xlink="http://www.w3.org/1999/xlink"
						     version="1.1"
						     id="Capa_1"
						     x="0px"
						     y="0px"
						     width="30.727px"
						     height="30.727px"
						     viewBox="0 0 30.727 30.727"
						     style="width: 16px;enable-background:new 0 0 30.727 30.727;"
						     xml:space="preserve" >
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
					<div class="sp-name text-2xl px-10 py-2  flex-1 font-medium items-center flex flex-1 font-medium items-center justify-center px-10 py-2 sp-name text-2xl text-center" >{{item.name}}
					</div >
					<div class="sp-deck-count flex-initial flex items-center" >{{item.decks.length}} decks</div >
				</div >
				<div class="sp-header-stats rounded py-2 px-4 flex-initial bg-gray-100" >
					<div class="status-title text-center font-bold" >Number of cards due for revision</div >
					<div class="to-study flex" >
						<div class="one-study flex-1 shadow p-2 m-2 text-center rounded" >
							<div class="study-title whitespace-nowrap" >Previously false</div >
							<div class="study-number font-bold fs-4" >0</div >
						</div >
						<div class="one-study flex-1 shadow p-2 m-2 text-center rounded" >
							<div class="study-title whitespace-nowrap" >Revision</div >
							<div class="study-number font-bold fs-4" >0</div >
						</div >
						<div class="one-study flex-1 shadow p-2 m-2 text-center rounded" >
							<div class="study-title whitespace-nowrap" >New cards</div >
							<div class="study-number font-bold fs-4" >{{item.id === 5 ? 20 : 0}}</div >
						</div >
					</div >
				</div >
			</div >
			<ul class="sp-decks "
			    :class="['decks-'+item.id]"
			    style="display: none" >
				<?php /**** Deck header ***/ ?>
				<li v-for="(item2,itemIndex2) in item.decks"
				    :key="item2.id"
				    class="pl-4 mt-2" >
					<div class="sp-d-header cursor-pointer  flex gap-2" >
						<div @click="userDash.openStudyModal(item2)"
						     class="sp-header-title flex bg-gray-100 hover:bg-gray-200  px-3 py-3 flex-1" >
							<div class="sp-name text-2xl px-10 py-2  flex-1 font-medium
									text-2xl px-10 py-2  flex-1 font-medium
									items-center flex flex-1 font-medium items-center justify-center px-10 py-2 sp-name text-2xl text-center" >{{item2.name}}
							</div >
							<div class="sp-deck-count flex-initial flex items-center" ></div >
						</div >
						<div class="sp-header-stats rounded py-2 flex-initial bg-gray-100" >
							<div class="status-title text-center font-bold" >Number of cards due for revision</div >
							<div class="to-study flex" >
								<div class="one-study flex-1 shadow p-2 m-2 text-center rounded" >
									<div class="study-title whitespace-nowrap" >Previously false</div >
									<div class="study-number font-bold fs-4" >0</div >
								</div >
								<div class="one-study flex-1 shadow p-2 m-2 text-center rounded" >
									<div class="study-title whitespace-nowrap" >Revision</div >
									<div class="study-number font-bold fs-4" >0</div >
								</div >
								<div class="one-study flex-1 shadow p-2 m-2 text-center rounded" >
									<div class="study-title whitespace-nowrap" >New cards</div >
									<div class="study-number font-bold fs-4" >4</div >
								</div >
							</div >
						</div >
					</div >
				</li >
			</ul >
	</li >
</ul >
