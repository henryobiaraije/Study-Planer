import {ref, computed, watch} from "@vue/composition-api";
import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {_Deck, _Tag, _CardGroup, _Card, _TableItem, _ImageItem, _ImageBox, IMAGE_DISPLAY_TYPE} from "../interfaces/inter-sp";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import useDeckGroupLists from "./useDeckGroupLists";
import RegexHelper from "../classes/RegexHelper";
import TableHelper from "../classes/TableHelper";
import Common from "../classes/Common";
import useBgImage from "./useBgImage";
import {createPopper} from "@popperjs/core";
import ImageHelper from "../classes/ImageHelper";

declare var bootstrap;

export default function (cardGroupId = 0) {
  const ajax         = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxCreate   = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxUpdate   = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxTrash    = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxDelete   = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  let items          = ref<Array<_Card>>([]);
  let cardGroup      = ref<_CardGroup>({
    tags          : [],
    cards         : [],
    id            : 0,
    deck          : null,
    reverse       : false,
    bg_image_id   : 0,
    name          : '',
    group_type    : '',
    image_type    : '' as IMAGE_DISPLAY_TYPE,
    whole_question: null,
    scheduled_at  : '',
  });
  let setBgAsDefault = ref(false);
  const rotateParams = {}
  let currentBox     = ref(null);

  let imageItem = ref<_ImageItem>({
    w    : 300,
    h    : 300,
    hash : Common.getRandomString(),
    boxes: [],
  });

  const currentTableData = ref({
    row: 0,
    col: 0,
  });

  const _AddImage = () => {
    useBgImage().pickImage('Pick', 'Select Medical Image').then((res) => {
      imageItem.value.boxes.push({
        top     : 0,
        left    : 0,
        w       : 100,
        h       : 100,
        hash    : Common.getRandomString(),
        imageUrl: res.url,
        angle   : 0,
        show    : false,
        hide    : false,
        asked    : false,
      });
      _addBoxEvents();
    });
  }
  const _AddBox   = () => {
    imageItem.value.boxes.push({
      top     : 0,
      left    : 0,
      w       : 100,
      h       : 30,
      hash    : Common.getRandomString(),
      imageUrl: '',
      angle   : 0,
      show    : false,
      hide    : false,
      asked    : false,
    });
    _addBoxEvents();
  }

  const applyCss             = () => {
    applyMainCss();
    applyBoxesCss();
  }
  const applyMainCss         = () => {
    const mainHash = imageItem.value.hash;
    const mainId   = 'main-' + mainHash;
    const styleId  = 'main-style-' + mainHash;
    const css      = `
      <style id="${styleId}">
        #${mainId}{
          height: ${imageItem.value.h}px;
          width: ${imageItem.value.w}px;
        }
      </style>
    `;
    jQuery('head').find('#' + styleId).remove();
    jQuery('head').append(css);
  }
  const applyBoxesCss        = () => {
    imageItem.value.boxes.forEach((box: _ImageBox) => {
      const hash    = box.hash;
      const id      = 'sp-box-' + hash;
      const styleId = 'sp-box-style-' + id;
      const css     = `
      <style id="${styleId}">
        #${id}{
          height: ${box.h}px;
          width: ${box.w}px;
          top: ${box.top};
          left : ${box.left};
          transform : 'rotate(${box.angle}rad)';
        }
      </style>
    `;
      // console.log('box', {hash, css});
      jQuery('head').find('#' + styleId).remove();
      jQuery('head').append(css);
    });
  }
  const applyPreviewCss      = (_imageItem: _ImageItem) => {
    const mainHash = _imageItem.hash;
    const mainId   = 'main-preview-' + mainHash;
    const styleId  = 'main-preview-style-' + mainHash;
    const css      = `
      <style id="${styleId}">
        #${mainId}{
          height: ${_imageItem.h}px;
          width: ${_imageItem.w}px;
        }
      </style>
    `;
    jQuery('head').find('#' + styleId).remove();
    jQuery('head').append(css);
  }
  const applyBoxesPreviewCss = (boxes: Array<_ImageBox>) => {
    boxes.forEach((box: _ImageBox) => {
      const hash    = box.hash;
      const id      = 'sp-box-preview-' + hash;
      const styleId = 'sp-box-preview-style-' + id;
      const css     = `
      <style id="${styleId}">
        #${id}{
          height: ${box.h}px;
          width: ${box.w}px;
          top: ${box.top}px;
          left : ${box.left}px;
          transform : rotate(${box.angle}rad);
        }
      </style>
    `;
      // console.log('box', {hash, css});
      jQuery('head').find('#' + styleId).remove();
      jQuery('head').append(css);
    });
  }

  const _bringToFront = () => {
    _closeActionMenu();
    const oldBoxes = imageItem.value.boxes;
    const index    = oldBoxes.findIndex((box: _ImageBox) => {
      const isSame = box.hash === currentBox.value.hash;
      // console.log(box.hash, currentBox.value.hash, {isSame});
      return isSame;
    });

    // console.log('bring to front', {index, current: currentBox.value, oldBoxes});
    // if (index >= (imageItem.value.boxes.length - 1)) {
    //   return;
    // }
    const newBoxes = [];
    let flagSet    = false;
    oldBoxes.forEach((box: _ImageBox, i) => {
      console.log('count ' + i);
      if (i < index) {
        newBoxes.push(box);
        console.log('Less than', {i, index, newBoxes, len: newBoxes.length, box, oldBoxes});
      } else if (i === index) {
        console.log('Equals ', {i, index, newBoxes, box, oldBoxes});
        // if(index > oldBoxes.length - 1 ){
        //
        // }
      } else {

        if (!flagSet) {
          newBoxes.push(imageItem.value.boxes[index]);
          flagSet = true;
        }
        console.log('Greater than ', {i, index, newBoxes, oldBoxes, flagSet, box});
        newBoxes.push(box);
      }
    });
    console.log('bring to front', {index, current: currentBox.value, oldBoxes});
    imageItem.value.boxes = newBoxes;
    _addBoxEvents();
  };
  const _sendToBack   = () => {};
  const _delete       = () => {
    _closeActionMenu();
    // if (!confirm('Are you sure you want to delete this?')) {
    //   return;
    // }
    const oldBoxes = imageItem.value.boxes;
    const index    = oldBoxes.findIndex((box: _ImageBox) => {
      const isSame = box.hash === currentBox.value.hash;
      // console.log(box.hash, currentBox.value.hash, {isSame});
      return isSame;
    });
    if (index > -1) {
      imageItem.value.boxes.splice(index, 1);
    }
    _addBoxEvents();
  };

  const _addBoxEvents = () => {
    setTimeout(() => {
      imageItem.value.boxes.forEach((box: _ImageBox, i) => {
        const hash = box.hash;
        const id   = 'sp-box-' + hash;
        try {
          // @ts-ignore
          jQuery('#' + id).resizable('destroy');
          // @ts-ignore
          jQuery('#' + id).draggable('destroy');
          // @ts-ignore
          jQuery('#' + id).rotatable('destroy');
        } catch (e) {
          console.error('Cant destroy', {e});
        }
        // @ts-ignore
        jQuery('#' + id).resizable({
          containment: "parent",
          stop       : (event, ui) => {
            const width  = ui.size.width;
            const height = ui.size.height;
            console.log('box stop resize', {event, ui, width, height});
            _boxDropped({
              hash: hash,
              w   : width,
              h   : height,
            });
          }
        }).draggable({
          containment: "parent",
          cursor     : "pointer",
          stop       : (event, ui) => {
            const target = jQuery(event.target);
            const hash   = target.attr('data-hash');
            const top    = ui.position.top;
            const left   = ui.position.left;
            console.log('box stop', {event, ui, target, hash, top, left});
            _boxDropped({
              hash: hash,
              top,
              left,
            });
          }
        }).rotatable({
          angle               : false,
          degrees             : false,
          handle              : false,
          handleOffset        : {
            top : 0,
            left: 0
          },
          radians             : false,
          rotationCenterOffset: {
            top : 0,
            left: 0
          },
          snap                : false,
          step                : 22.5,
          transforms          : null,
          wheelRotate         : false,
          rotate              : function (event, ui) {
          },
          start               : function (event, ui) {
          },
          stop                : function (event, ui) {
            console.log('rotation stop', {event, ui});
            const angle = ui.angle.current;
            // const height = ui.size.height;
            console.log('box stop rotate', {event, ui, angle});
            _boxDropped({
              hash : hash,
              angle: angle,
            });
          },
        });
      });
      applyCss();
    }, 500);
  }
  const _addEvents    = () => {
    console.log('Creating new event');
    try {
      // @ts-ignore
      jQuery('.image-area-inner').resizable("destroy");
    } catch (e) {
      console.error('Cant destroy main', {e});
    }
    // @ts-ignore
    jQuery('.image-area-inner').resizable({
      autoHide: true,
      stop    : function (event, ui) {
        console.log({event, ui});
        const width  = ui.size.width;
        const height = ui.size.height;
        _mainDropped(width, height);
        _addEvents();
      },
      create  : function (event, ui) {
        _addBoxEvents();
      }
    });
    applyCss();
  }

  const _createOrUpdate  = () => {
    if (cardGroupId > 0) {
      xhrUpdate();
    } else {
      xhrCreate();
    }
  }
  const _load            = () => {
    return new Promise((resolve, reject) => {
      if (cardGroupId > 0) {
        xhrLoad().then((res) => {
          resolve(res);
        }).catch(() => {
          reject();
        });
      } else {
        resolve(0);
      }
    });
  }
  const _closeActionMenu = () => {
    jQuery('#image-menu-action').hide();
  }
  const _openActionMenu  = (box: _ImageBox) => {
    console.log('_openActionMenu', {box});
    // currentTableData.value.row = row;
    // currentTableData.value.col = col;
    currentBox.value = box;
    jQuery('#image-menu-action').show();
    const targetId = '#action-box-' + box.hash;
    const target   = document.querySelector(targetId);
    const tooltip  = document.querySelector('#image-menu-action');
    console.log({target, tooltip, targetId});
    const popperInstance = createPopper(target, tooltip as HTMLElement);
  };

  const _mainDropped    = (w: number, h: number) => {
    imageItem.value.w = w;
    imageItem.value.h = h;
    applyCss();
  }
  const _boxDropped     = (args: { hash: string; w?: number; h?: number; top?: number; left?: number, angle?: number }) => {
    const boxIndex = imageItem.value.boxes.findIndex((box: _ImageBox) => {
      return box.hash === args.hash;
    });
    if (!(undefined === boxIndex)) {
      const box = imageItem.value.boxes[boxIndex];
      box.w     = args?.w || box.w;
      box.h     = args?.h || box.h;
      box.top   = args?.top || box.top;
      box.left  = args?.left || box.left;
      box.angle = args?.angle || box.angle;
      console.log('_boxDropped', {imageItem, box});
    }
    applyCss();
  }
  const _refreshPreview = () => {
    console.log('refresh now');
    items.value = ImageHelper.getCardsFromImageItem(imageItem.value, cardGroup.value);
    items.value.forEach((_card: _Card) => {
      const question: _ImageItem = _card.question as _ImageItem;
      const answer: _ImageItem   = _card.answer as _ImageItem;
      console.log('preview', {question, answer});
      applyPreviewCss(question);
      applyPreviewCss(answer);
      applyBoxesPreviewCss(question.boxes);
      applyBoxesPreviewCss(answer.boxes);
    });
  }

  const xhrCreate = () => {
    cardGroup.value.whole_question = imageItem.value;
    const handleAjax: HandleAjax   = new HandleAjax(ajaxCreate.value);
    new Server().send_online({
      data: [
        Store.nonce,
        {
          cards            : items.value,
          cardGroup        : cardGroup.value,
          set_bg_as_default: setBgAsDefault.value,
        }
      ],
      what: "admin_sp_ajax_admin_create_new_image_card",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        window.location = done.data;
      },
      funcFailue(done) {
        handleAjax.error(done);
      },
    });
  };
  const xhrUpdate = () => {
    const handleAjax: HandleAjax   = new HandleAjax(ajaxCreate.value);
    cardGroup.value.whole_question = imageItem.value;
    new Server().send_online({
      data: [
        Store.nonce,
        {
          cards            : items.value,
          cardGroup        : cardGroup.value,
          set_bg_as_default: setBgAsDefault.value,
        }
      ],
      what: "admin_sp_ajax_admin_update_image_card",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        // window.location = done.data;
      },
      funcFailue(done) {
        handleAjax.error(done);
      },
    });
  };
  const xhrLoad   = () => {
    return new Promise((resolve, reject) => {
      const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
      new Server().send_online({
        data: [
          Store.nonce,
          {
            card_group_id: cardGroupId,
          }
        ],
        what: "admin_sp_ajax_admin_load_basic_card",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          const hold: _CardGroup = done.data.card_group;
          if (hold.cards.length > 0) {
            // item.value = hold.cards[0];
          }
          // cardGroup.value = hold;
          cardGroup.value.whole_question      = hold.whole_question;
          imageItem.value                     = hold.whole_question;
          cardGroup.value.cards               = hold.cards;
          cardGroup.value.name                = hold.name;
          cardGroup.value.created_at          = hold.created_at;
          cardGroup.value.updated_at          = hold.updated_at;
          cardGroup.value.tags                = hold.tags;
          cardGroup.value.cards_count         = hold.cards_count;
          cardGroup.value.deck                = hold.deck;
          cardGroup.value.bg_image_id         = hold.bg_image_id;
          cardGroup.value.group_type          = hold.group_type;
          cardGroup.value.deleted_at          = hold.deleted_at;
          cardGroup.value.id                  = hold.id;
          cardGroup.value.card_group_edit_url = hold.card_group_edit_url;
          cardGroup.value.reverse             = hold.reverse;
          cardGroup.value.scheduled_at        = hold.scheduled_at;
          items.value                         = hold.cards;
          // console.log({hold})
          resolve(done.data);
        },
        funcFailue(done) {
          // handleAjax.error(done);
          reject();
        },
      });
    });
  };

  const _cssMain = computed(() => {
    return {
      width : imageItem.value.w,
      height: imageItem.value.h,
    };
  })

  const _cssBox = computed((box: _ImageBox) => {
    return {
      width    : box.w,
      height   : box.h,
      top      : box.top,
      left     : box.left,
      transform: box.angle,
    };
  })

  return {
    ajaxCreate, ajax, ajaxUpdate, ajaxDelete, ajaxTrash,
    _createOrUpdate, cardGroup, _load, _refreshPreview,
    imageItem, _AddBox, _AddImage,
    _bringToFront, _sendToBack, _delete,
    items, setBgAsDefault, _addEvents, _openActionMenu,
  };
}