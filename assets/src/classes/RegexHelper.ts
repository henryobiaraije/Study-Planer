import {_GapCard} from "../interfaces/inter-sp";

export default class RegexHelper {


  public static getItemsFromGapWholeQuestion(wholeQuestion: string): Array<_GapCard> {
    console.groupCollapsed('getItemsFromGapWholeQuestion');
    const cDetails: { [key: string]: Array<string> } = RegexHelper.getCDetails(wholeQuestion);
    const items: Array<_GapCard>                     = [];

    // Get the answer
    let answer = wholeQuestion;
    for (let [key, value] of Object.entries(cDetails)) {
      value.forEach(val => {
        const _hold = RegexHelper.getAnswerFromCurlyBrackets(val);
        answer      = answer.replace(val, _hold);
      });
    }

    for (let [key, value] of Object.entries(cDetails)) {
      let text = wholeQuestion;
      for (let [key2, value2] of Object.entries(cDetails)) {
        if (key2 !== key) {
          value2.forEach(val => {
            const answer  = RegexHelper.getAnswerFromCurlyBrackets(val);
            const _rRegex = new RegExp(val);
            text          = text.replace(_rRegex, answer);
            console.log({cDetails, answer, text, key, value, key2, value2, val})
          });
        }
      }
      items.push({
        question: text,
        answer  : answer,
      });
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
  public static getCDetails(wholeQuestion: string): { [key: string]: Array<string> } {
    const cDetails: { [key: string]: Array<string> } = {};

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
        if (undefined === cDetails[cId]) cDetails[cId] = [];
        cDetails[cId].push(match);

        return false;
        // console.log(`Found match, group ${groupIndex}: ${match} has html = ${hasHtml}`);
      });
    }

    for (let [key, value] of Object.entries(cDetails)) {
      let text = wholeQuestion;
      for (let [key2, value2] of Object.entries(cDetails)) {
        if (key2 !== key) {
          value2.forEach(val => {
            const answer  = RegexHelper.getAnswerFromCurlyBrackets(val);
            const _rRegex = new RegExp(val);
            text          = text.replace(_rRegex, answer);
            console.log({cDetails, answer, text, key, value, key2, value2, val})
          });
        }
      }
    }
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