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
        box.hash = Common.getRandomString();
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
    }else if (cardGroup.image_type === IMAGE_DISPLAY_TYPE.HIDE_ALL_ASK_ONE) {
      imageItem.boxes.forEach((box) => {
        const question: _ImageItem = JSON.parse(JSON.stringify(imageItem));
        const answer: _ImageItem   = JSON.parse(JSON.stringify(imageItem));

        question.hash = Common.getRandomString();
        answer.hash   = Common.getRandomString();

        question.boxes.forEach((box) => {
          box.hash = Common.getRandomString();
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
      });
    }

    return foundCards;
  }


}