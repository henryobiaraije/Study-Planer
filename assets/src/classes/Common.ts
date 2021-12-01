import sha256 from 'crypto-js/sha256';

export default class Common {

  private static generatedRandom = [];

  public static getRandomString() {
    let rand1 = Common.rand();
    let rand2 = Common.rand();
    // while (Common.generatedRandom.indexOf(rand) > -1) {
    //   rand = Common.rand();
    //   console.log('Looping');
    // }
    // Common.generatedRandom.push(rand);
    const rand = sha256(rand2 + rand1).toString();
    return rand;
  }

  private static rand(length = 20) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
      result += characters.charAt(Math.floor(Math.random() *
                                             charactersLength));
    }
    result += '-' + new Date().getTime() + '-' + new Date().getMilliseconds();

    return result;
  }
}
