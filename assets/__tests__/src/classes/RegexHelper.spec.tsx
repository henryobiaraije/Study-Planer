// const _gapText = "When you {{c1::decide}} to use a {{c2::gap}} card that has a latin {{c3::numeral like [iii]}} in them, " +
//                  "remember to {{c2::reload}} the preview before {{c4::saving}} the post.";
import RegexHelper from "../../../src/classes/RegexHelper";
import {_Card} from "../../../src/interfaces/inter-sp";


// test('To add two numbers', () => {
//   expect(RegexHelper.sum(4, 5)).toBe(9);
// });

describe('Can extract c number from any c variable', function () {
  test('Can return c number from c variable', () => {
    const cIdText = '{{c1:the text}}';
    expect(RegexHelper._getCId(cIdText)).toBe('c1');
  });
  test('Can get c number when special characters are there.', () => {
    const cIdText = '{{c1:the text [some]}}';
    expect(RegexHelper._getCId(cIdText)).toBe('c1');
  });
  test('Can get c number when html are there.', () => {
    const cIdText = '{{c1:the text <h2 style="color:blue">a heading</h2>}}';
    expect(RegexHelper._getCId(cIdText)).toBe('c1');
  });
  test('Can get c number when html are there.', () => {
    const cIdText = '{{c1:the text [<h2 style="color:blue">a heading</h2>]}}';
    expect(RegexHelper._getCId(cIdText)).toBe('c1');
  });
});

describe('Get cdetails from the whole questions.', () => {
  const fullQuestion = "When you {{c1::decide}} to use a {{c2::gap}} card that has a latin {{c3::numeral like [iii]}} in them, remember to {{c2::reload}} the preview before {{c4::saving}} the post.";
  const shouldBe     = {
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
    "c3": {
      "cId" : "c3",
      "gaps": [
        "{{c3::numeral like [iii]}}"
      ]
    },
    "c4": {
      "cId" : "c4",
      "gaps": [
        "{{c4::saving}}"
      ]
    }
  };
  test('Should do cdetails 2', () => {
    const cDetails = RegexHelper.getCDetailsSimple(fullQuestion);
    expect(cDetails).toEqual(shouldBe);
  });
  test('Should do cdetails 2 1', () => {
    const questionHtml = "When you {{c1::decide <b>to be</b> good}} to use a {{c2::gap}} card that has a latin {{c3::numeral like [iii]}} in them, remember to {{c2::reload}} the preview before {{c4::saving}} the post.";
    const shouldBe     = {
      "c1": {
        "cId" : "c1",
        "gaps": [
          "{{c1::decide <b>to be</b> good}}"
        ]
      },
      "c2": {
        "cId" : "c2",
        "gaps": [
          "{{c2::gap}}",
          "{{c2::reload}}"
        ]
      },
      "c3": {
        "cId" : "c3",
        "gaps": [
          "{{c3::numeral like [iii]}}"
        ]
      },
      "c4": {
        "cId" : "c4",
        "gaps": [
          "{{c4::saving}}"
        ]
      }
    };


    const cDetails = RegexHelper.getCDetailsSimple(questionHtml);
    expect(cDetails).toEqual(shouldBe);
  });
});

describe('Prepare gap texts', () => {
  test('Normal should return normal', () => {
    // const fullQuestion = "When you {{c1::decide}} to use a {{c2::gap}} card that has a latin {{c3::numeral like [iii]}} in them, remember to {{c2::reload}} the preview before {{c4::saving}} the post.";
    const fullQuestion       = "When you {{c1::decide}} to use a {{c2::gap}} card.";
    const want: Array<_Card> = [
      {
        hash    : '',
        question: "When you <strong>[...]</strong> to use a gap card.",
        answer  : "When you <strong>decide</strong> to use a gap card.",
        c_number: "c1",
        id      : 0,
      },
      {
        id      : 0,
        hash    : '',
        question: "When you decide to use a <strong>[...]</strong> card.",
        answer  : "When you decide to use a <strong>gap</strong> card.",
        c_number: "c2",
      },
    ];
    const all                = RegexHelper.getItemsFromGapWholeQuestion(fullQuestion, []);
    expect(want).toEqual(all);
  });
  test('With Html should return well', () => {
    // const fullQuestion = "When you {{c1::decide}} to use a {{c2::gap}} card that has a latin {{c3::numeral like [iii]}} in them, remember to {{c2::reload}} the preview before {{c4::saving}} the post.";
    const fullQuestion       = "When you {{c1::decide to [<b>fly</b>]}} with a {{c2::gap}} card.";
    const want: Array<_Card> = [
      {
        hash    : '',
        question: "When you <strong>[...]</strong> with a gap card.",
        answer  : "When you <strong>decide to [<b>fly</b>]</strong> with a gap card.",
        c_number: "c1",
        id      : 0,
      },
      {
        id      : 0,
        hash    : '',
        question: "When you decide to [<b>fly</b>] with a <strong>[...]</strong> card.",
        answer  : "When you decide to [<b>fly</b>] with a <strong>gap</strong> card.",
        c_number: "c2",
      },
    ];
    const all                = RegexHelper.getItemsFromGapWholeQuestion(fullQuestion, []);
    expect(want).toEqual(all);
  });
});


