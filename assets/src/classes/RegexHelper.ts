import Common from "./Common";
import {_Card} from "../interfaces/inter-sp";

export interface _CDetail {
  [key: string]: {
    cId: string,
    gaps: Array<string>,
  }
}

export default class RegexHelper {


  /**
   * Forms _Cards from a whole question
   * @param wholeQuestion
   * @param existingItems
   */
  public static getItemsFromGapWholeQuestion(wholeQuestion: string, existingItems: Array<_Card>): Array<_Card> {
    console.groupCollapsed('getItemsFromGapWholeQuestion');
    const cDetails: _CDetail  = RegexHelper.getCDetails(wholeQuestion);
    const items: Array<_Card> = [];

    // Get the questions and answers
    for (let [key, value] of Object.entries(cDetails)) {
      // Looping through all cDetails
      let question = wholeQuestion;
      let answer   = wholeQuestion;
      for (let [key2, value2] of Object.entries(cDetails)) {
        // Looping through all cDetails again
        if (key2 !== key) {
          value2.gaps.forEach(val => {
            // Looping through all add fill answers in other gaps normally except in the current card
            const qAnswer = RegexHelper.getAnswerFromCurlyBrackets(val);
            const _rRegex = new RegExp(val);
            question      = question.replace(_rRegex, `${qAnswer}`);
            const _hold   = RegexHelper.getAnswerFromCurlyBrackets(val);
            answer        = answer.replace(val, `${_hold}`);
            console.log(' Not equall', {
              key, value, key2, value2,
              cDetails, answer, qAnswer, question, val
            })
          });
        } else {
          value2.gaps.forEach(val => {
            // Looping through and add [...] to gaps and then bold answers in answer
            const _rRegex = new RegExp(val);
            question      = question.replace(_rRegex, "<strong>[...]</strong>");
            const _hold   = RegexHelper.getAnswerFromCurlyBrackets(val);
            answer        = answer.replace(val, `<strong>${_hold}</strong>`);
            console.log({cDetails, answer, question, key, value, key2, value2, val})
            console.log(' Yes Equal', {
              key, value, key2, value2,
              cDetails,  answer, question, val
            })
          });
        }
      }

      const cExists = existingItems.findIndex((_card, b) => {
        return key === _card.c_number;
      });
      if (cExists > -1) {
        const _existingItem = existingItems[cExists];
        items.push({
          ..._existingItem,
          question: question,
          answer  : answer,
        });
      } else {
        items.push({
          id : 0,
          question: question,
          answer  : answer,
          c_number: key,
          hash    : Common.getRandomString(),
        });
      }

    }
    console.groupEnd();
    return items;
  }

  /**
   * Searches the whole question and returns cDetails
   *
   * @param wholeQuestion
   * @return eg.  {
   *   c1 : ['{{c1:smoke}}','{{c1:science}}'],
   *   c2 : ...
   * }
   */
  public static getCDetails(wholeQuestion: string): _CDetail {
    console.groupCollapsed('getCDetails');
    const cDetails: _CDetail = {};

    const regex = /{{c[0-9]+::[^}}]*}}/mg;
    let m;

    while ((m = regex.exec(wholeQuestion)) !== null) {
      if (m.index === regex.lastIndex) {
        regex.lastIndex++;
      }
      m.some((match, groupIndex) => {
        const hasHtml = RegexHelper._hasHtml(match);
        if (hasHtml) {
          alert('Error: HTML tag found in ' + match);
          return true;
        }
        const cId = RegexHelper._getCId(match);
        if (undefined === cDetails[cId]) {
          cDetails[cId] = {
            cId : cId,
            gaps: [],
          };
        }
        cDetails[cId].gaps.push(match);

        console.log('Found match, group', {cDetails, groupIndex, match, hasHtml, wholeQuestion})
        return false;

      });
    }
    console.log('Found ', {cDetails, wholeQuestion})

    for (let [key, value] of Object.entries(cDetails)) {
      let text = wholeQuestion;
      for (let [key2, value2] of Object.entries(cDetails)) {
        if (key2 !== key) {
          value2.gaps.forEach(val => {
            const answer  = RegexHelper.getAnswerFromCurlyBrackets(val);
            const _rRegex = new RegExp(val);
            text          = text.replace(_rRegex, answer);
            console.log({cDetails, answer, text, key, value, key2, value2, val})
          });
        }
      }
    }
    console.groupEnd();
    return cDetails;
  }

  /**
   * Returns true if the text has html tags in it
   * @param  str
   */
  public static _hasHtml(str: string): boolean {
    const regex = /<[a-z]*>/gm;
    let m;
    let has     = false;
    while ((m = regex.exec(str)) !== null) {
      // This is necessary to avoid infinite loops with zero-width matches
      if (m.index === regex.lastIndex) {
        regex.lastIndex++;
      }

      // The result can be accessed through the `m`-variable.
      m.forEach((match, groupIndex) => {
        has = true;
        // console.log(`_hasHtml ${groupIndex}: ${match}`);
      });
    }
    return has;
  }

  /**
   * Cets the cid from a string
   * @param str
   */
  public static _getCId = (str: string): string => {
    console.groupCollapsed("_getCId");
    const regex = /{{c[0-9]+/gm;
    let m;
    let cId     = '';
    while ((m = regex.exec(str)) !== null) {
      // This is necessary to avoid infinite loops with zero-width matches
      if (m.index === regex.lastIndex) {
        regex.lastIndex++;
      }

      // The result can be accessed through the `m`-variable.
      m.forEach((match, groupIndex) => {
        cId = match.split('').splice(2).join('');
        console.log('_getCId', {str, cId, groupIndex, match});
      });

    }
    console.groupEnd();
    return cId;
  }

  /**
   * Gets the answer from curly brackets
   * @param str e.gl {{c1:smoke}}
   * @return string Will return "smoke"
   */
  public static getAnswerFromCurlyBrackets(str: string): string {
    let text = str.replace(/{{c[0-9]+::/, '');
    text     = text.replace(/}}/, '');

    return text;
  }
}