/** @type {import('ts-jest/dist/types').InitialOptionsTsJest} */
module.exports = {
    preset: 'ts-jest',
    testEnvironment: 'node',
    verbose: true,
    "globals": {
        "ts-jest": {
            "skipBabel": true,
            "enableTsDiagnostics": false,
            "tsConfig": "tsconfig.test.json"
        }
    },
    "transform": {
        "^.+\\.(ts|tsx)?$": "ts-jest"
    },
    "testRegex": "(/__tests__/.*|(\\.|/)(spec.jest))\\.(jsx?|tsx?)$",
    // "moduleFileExtensions": [
    //     "ts",
    //     "tsx",
    //     "js",
    //     "jsx",
    //     "json",
    //     "node"
    // ]
};