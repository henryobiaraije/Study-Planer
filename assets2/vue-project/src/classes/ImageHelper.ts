import Common from "./Common";
import type {_Card, _CardGroup, _ImageItem} from "@/interfaces/inter-sp";
import {IMAGE_DISPLAY_TYPE} from "@/interfaces/inter-sp";


export default class ImageHelper {
    public static getCardsFromImageItem(imageItem: _ImageItem, cardGroup: _CardGroup, existingItems: Array<_Card>): Array<_Card> {
        const foundCards: Array<_Card> = [];
        console.groupCollapsed('getCardsFromImageItem');

        if (cardGroup.image_type === IMAGE_DISPLAY_TYPE.HIDE_ALL_ASK_ALL) {
            const question: _ImageItem = JSON.parse(JSON.stringify(imageItem));
            const answer: _ImageItem = JSON.parse(JSON.stringify(imageItem));

            question.hash = Common.getRandomString();
            answer.hash = Common.getRandomString();

            question.boxes.forEach((box) => {
                box.hash = Common.getRandomString();
                box.asked = true;
            });
            answer.boxes.forEach((box) => {
                box.show = true;
                box.hash = Common.getRandomString();
            });
            const cExists = existingItems.findIndex((_card, b) => {
                return 'c1' === _card.c_number;
            });
            if (cExists > -1) {
                const _existingItem = existingItems[cExists];
                foundCards.push({
                    ..._existingItem,
                    question: question,
                    answer: answer,
                });
            } else {
                foundCards.push({
                    id: 0,
                    question: question,
                    answer: answer,
                    c_number: 'c1',
                    hash: Common.getRandomString(),
                });
            }
        } else if (cardGroup.image_type === IMAGE_DISPLAY_TYPE.HIDE_ALL_ASK_ONE) {
            let cId = 0;
            imageItem.boxes.forEach((box, i) => {
                if (box.imageUrl.length > 0) {
                    return;
                }
                cId++;
                const question: _ImageItem = JSON.parse(JSON.stringify(imageItem));
                const answer: _ImageItem = JSON.parse(JSON.stringify(imageItem));
                question.hash = Common.getRandomString();
                answer.hash = Common.getRandomString();
                question.boxes.forEach((box2, i2) => {
                    if (box.imageUrl.length > 0) {
                        return;
                    }
                    box2.hash = Common.getRandomString();
                    box2.asked = i === i2;
                });
                answer.boxes.forEach((box2, i2) => {
                    if (box.imageUrl.length > 0) {
                        return;
                    }
                    box2.show = i === i2;
                    box2.hash = Common.getRandomString();
                });

                const cExists = existingItems.findIndex((_card, b) => {
                    return 'c' + cId === _card.c_number;
                });
                if (cExists > -1) {
                    const _existingItem = existingItems[cExists];
                    foundCards.push({
                        ..._existingItem,
                        question: question,
                        answer: answer,
                    });
                } else {
                    foundCards.push({
                        id: 0,
                        question: question,
                        answer: answer,
                        c_number: 'c' + cId,
                        hash: Common.getRandomString(),
                    });
                }
            });
        } else if (cardGroup.image_type === IMAGE_DISPLAY_TYPE.HIDE_ONE_ASK_ONE) {
            let cId = 0;
            imageItem.boxes.forEach((box, i) => {
                if (box.imageUrl.length > 0) {
                    return;
                }
                cId++;
                const question: _ImageItem = JSON.parse(JSON.stringify(imageItem));
                const answer: _ImageItem = JSON.parse(JSON.stringify(imageItem));
                question.hash = Common.getRandomString();
                answer.hash = Common.getRandomString();
                question.boxes.forEach((box2, i2) => {
                    if (box2.imageUrl.length > 0) {
                        return;
                    }
                    box2.hash = Common.getRandomString();
                    box2.asked = i === i2;
                    box2.hide = i !== i2;
                });
                answer.boxes.forEach((box2, i2) => {
                    if (box2.imageUrl.length > 0) {
                        return;
                    }
                    box2.show = i === i2;
                    box2.hash = Common.getRandomString();
                    box2.hide = i !== i2;
                });

                const cExists = existingItems.findIndex((_card, b) => {
                    return 'c' + cId === _card.c_number;
                });
                if (cExists > -1) {
                    const _existingItem = existingItems[cExists];
                    foundCards.push({
                        ..._existingItem,
                        question: question,
                        answer: answer,
                    });
                } else {
                    foundCards.push({
                        id: 0,
                        question: question,
                        answer: answer,
                        c_number: 'c' + cId,
                        hash: Common.getRandomString(),
                    });
                }
            });
        }
        console.log({foundCards, imageItem, cardGroup});
        console.groupEnd();
        return foundCards;
    }
}