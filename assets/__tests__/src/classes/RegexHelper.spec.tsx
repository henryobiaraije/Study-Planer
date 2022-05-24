// const _gapText = "When you {{c1::decide}} to use a {{c2::gap}} card that has a latin {{c3::numeral like [iii]}} in them, " +
//                  "remember to {{c2::reload}} the preview before {{c4::saving}} the post.";
import RegexHelper from "../../../src/classes/RegexHelper";


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
});

describe('Prepare gap texts',() => {
 test('fejfei',() => {
   // const fullQuestion = "When you {{c1::decide}} to use a {{c2::gap}} card that has a latin {{c3::numeral like [iii]}} in them, remember to {{c2::reload}} the preview before {{c4::saving}} the post.";
   const fullQuestion = "When you {{c1::decide}} to use a {{c2::gap}} card. ";
   const all = RegexHelper.getItemsFromGapWholeQuestion(fullQuestion,[]);
 });
});


