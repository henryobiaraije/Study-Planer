import {_Card, _CardGroup, _ImageItem, IMAGE_DISPLAY_TYPE} from "../interfaces/inter-sp";
import Common from "./Common";


export default class ImageHelper {

  public static getCardsFromImageItem(imageItem: _ImageItem, cardGroup: _CardGroup): Array<_Card> {
    const foundCards: Array<_Card> = [];

    if (cardGroup.image_type === IMAGE_DISPLAY_TYPE.HIDE_ALL_ASK_ALL) {
      const question: _ImageItem = JSON.parse(JSON.stringify(imageItem));
      const answer: _ImageItem   = JSON.parse(JSON.stringify(imageItem));

      question.hash = Common.getRandomString();
      answer.hash   = Common.getRandomString();

      question.boxes.forEach((box) => {
        box.hash  = Common.getRandomString();
        box.asked = true;
      });
      answer.boxes.forEach((box) => {
        box.show = true;
        box.hash = Common.getRandomString();
      });
      foundCards.push({
        id      : 0,
        question: question,
        hash    : Common.getRandomString(),
        c_number: '',
        answer  : answer,
      });
    } else if (cardGroup.image_type === IMAGE_DISPLAY_TYPE.HIDE_ALL_ASK_ONE) {
      imageItem.boxes.forEach((box, i) => {
        if (box.imageUrl.length > 0) {
          return;
        }
        const question: _ImageItem = JSON.parse(JSON.stringify(imageItem));
        const answer: _ImageItem   = JSON.parse(JSON.stringify(imageItem));
        question.hash              = Common.getRandomString();
        answer.hash                = Common.getRandomString();
        question.boxes.forEach((box2, i2) => {
          if (box.imageUrl.length > 0) {
            return;
          }
          box2.hash  = Common.getRandomString();
          box2.asked = i === i2;
        });
        answer.boxes.forEach((box2, i2) => {
          if (box.imageUrl.length > 0) {
            return;
          }
          box2.show  = i === i2;
          box2.hash  = Common.getRandomString();
        });

        foundCards.push({
          id      : 0,
          question: question,
          hash    : Common.getRandomString(),
          c_number: '',
          answer  : answer,
        });
      });
    }else if (cardGroup.image_type === IMAGE_DISPLAY_TYPE.HIDE_ONE_ASK_ONE) {
      imageItem.boxes.forEach((box, i) => {
        if (box.imageUrl.length > 0) {
          return;
        }
        const question: _ImageItem = JSON.parse(JSON.stringify(imageItem));
        const answer: _ImageItem   = JSON.parse(JSON.stringify(imageItem));
        question.hash              = Common.getRandomString();
        answer.hash                = Common.getRandomString();
        question.boxes.forEach((box2, i2) => {
          if (box2.imageUrl.length > 0) {
            return;
          }
          box2.hash  = Common.getRandomString();
          box2.asked = i === i2;
          box2.hide = i !== i2;
        });
        answer.boxes.forEach((box2, i2) => {
          if (box2.imageUrl.length > 0) {
            return;
          }
          box2.show  = i === i2;
          box2.hash  = Common.getRandomString();
          box2.hide = i !== i2;
        });

        foundCards.push({
          id      : 0,
          question: question,
          hash    : Common.getRandomString(),
          c_number: '',
          answer  : answer,
        });
      });
    }

    return foundCards;
  }


}