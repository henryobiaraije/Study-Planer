import RegexHelper, {_CDetail} from "../../../../../../../laragon/www/Test-Site-Wordpress/wp-content/plugins/study-planner/assets/src/classes/RegexHelper";
import {_Card} from "../../../../../../../laragon/www/Test-Site-Wordpress/wp-content/plugins/study-planner/assets/src/interfaces/inter-sp";

// const fullQuestion = "When you {{c1::decide}} to use a {{c2::gap}} card that has a latin {{c3::numeral like [iii]}} in them, remember to {{c2::reload}} the preview before {{c4::saving}} the post.";
const fullQuestion      = "When you {{c1::decide}} to use a {{c2::gap}} card  ";
const cDetail: _CDetail = {
  "c1": {
    "cId" : "c1",
    "gaps": [
      "{{c1::decide}}"
    ]
  },
  "c2": {
    "cId" : "c2",
    "gaps": [
      "{{c2::gap}}",
      "{{c2::reload}}"
    ]
  },
  // "c3": {
  //   "cId" : "c3",
  //   "gaps": [
  //     "{{c3::numeral like [iii]}}"
  //   ]
  // },
  // "c4": {
  //   "cId" : "c4",
  //   "gaps": [
  //     "{{c4::saving}}"
  //   ]
  // }
};

const _getCId                    = (str: string): string => {

  const regex = /{{c[0-9]+/gm;
  const exec  = regex.exec(str);
  if (null !== exec) {
    return exec[0].split('').slice(2).join('')
  }

  return '';
}
const getAnswerFromCurlyBrackets = (str: string): string => {
  let text = str.replace(/{{c[0-9]+::/, '');
  text     = text.replace(/}}/, '');

  return text;
}

console.log(cDetail);
let card: _Card           = null;
const items: Array<_Card> = [];

Object.keys(cDetail).forEach((key) => {
  const value  = cDetail[key];
  let question = fullQuestion;
  let answer   = fullQuestion;

  value.gaps.forEach(cText => {

    console.log(cText);
    // Replace current c text with [...]
    question = question.replace(cText, '[...]');
    console.log(question);
    // Replace other c text with (c answer)
    value.gaps.forEach((theCText) => {
      console.log(theCText);
      const cAnswer = getAnswerFromCurlyBrackets(theCText);
      const replace = question.replace(cText, cAnswer);

      console.log(question);
      console.log(cAnswer);
      console.log(cText)
      console.log(replace);
    });

    const regexAnswer = /{{c[0-9]+::/;
    let cAnswer       = cText.replace(regexAnswer, '');
    cAnswer           = cAnswer.replace(/}}/, '');
    answer            = answer.replace(cText, `<b>${cAnswer}</b>`);

    // card = {
    //   hash    : Common.getRandomString(),
    //   answer  : answer,
    //   question: question,
    //   c_number: cId,
    //   id      : 0,
    // };

    console.log(cText);
    console.log(cAnswer);
    console.log(question);
    console.log(answer);
    // answer   = answer.replace(cText, `<b>${cText}</b>`);

    items.push({
      hash    : '',
      question: question,
      answer  : answer,
      c_number: _getCId(cText)
    });
  });


  console.log(question);
  console.log(answer);

  console.log(value, key);
});
console.log(items);

