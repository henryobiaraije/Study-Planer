import Common from "./Common";
import type {_Card, _TableItem} from "@/interfaces/inter-sp";

export interface _CTableDetail {
    [key: string]: {
        cId: string,
        gaps: Array<_TableGap>,
    }
}

export interface _TableGap {
    match: string;
    col: number;
    row: number;
    cId: string;
    answer: string;
}

export default class TableHelper {

    public static addColumn(table: _TableItem, index: number = null): _TableItem {
        const noRowsYet = table.length < 1;
        if (null === index) {
            if (noRowsYet) {
                table.push(['']);
            } else {
                table.forEach((row, i) => {
                    row.push('');
                });
            }
        } else if (0 === index) {
            table.forEach((row, i) => {
                row.unshift('');
            });
            console.log('column added');
        } else {
            table.forEach((row, i) => {
                const newRow = [
                    ...row.slice(0, index),
                    '',
                    ...row.slice(index),
                ];
                table[i] = newRow;
                console.log('col >', {newRow, index, table})
            });
        }
        console.log({table});
        return table;
    }

    public static addRow(table: _TableItem, index: number = null): _TableItem {
        console.log('addRow', index);
        const noRowsYet = table.length < 1;
        if (null === index) {
            if (noRowsYet) {
                table.push(['']);
            } else {
                const columnCount = table[0].length;
                const row = [];
                for (let a = 0; a < columnCount; a++) {
                    row.push('');
                }
                table.push(row);
            }
        } else if (0 === index) {
            const columnCount = table[0].length;
            const row = [];
            for (let a = 0; a < columnCount; a++) {
                row.unshift('');
            }
            table.unshift(row);
            console.log('row added');
        } else if (null !== index) {
            const columnCount = table[0].length;
            const newRow = [];
            for (let a = 0; a < columnCount; a++) {
                newRow.push('');
            }
            table = [
                ...table.slice(0, index),
                newRow,
                ...table.slice(index),
            ]
            console.log('adding > 0', {table, index});
        }
        console.log({table, index});
        return table;
    }

    public static deleteRow(table: _TableItem, index: number): _TableItem {
        console.log('delete row', {index});
        table.splice(index, 1);
        return table;
    }

    public static deleteColumns(table: _TableItem, index: number): _TableItem {
        console.log('delete columns', {index});
        table.forEach((row, i) => {
            row.splice(index, 1);
        });
        return table;
    }

    public static getItemsFromTable(table: _TableItem, existingItems: Array<_Card>): Array<_Card> {
        const cardsFormed: Array<_Card> = [];
        console.groupCollapsed('getItemsFromTable');
        const cDetails = this.getCDetails(table);
        const _regex = /{{c[0-9]+::[^}}]*}}/mg;

        for (const [key, _cDetail] of Object.entries(cDetails)) {
            const tableQuestion: _TableItem = [];
            const tableAnswer: _TableItem = [];

            // Loop through the rows
            table.forEach((row, rowIndex) => {
                const _questionRow = [];
                const _answerRow = [];
                // Loop through the columns
                row.forEach((col, colIndex) => {
                    const _originalText = col;
                    let _newTextQuestion = _originalText;
                    let _newTextAnswer = _originalText;
                    const _gaps = _cDetail.gaps;

                    _gaps.forEach((gap) => {
                        if (gap.row === rowIndex && gap.col === colIndex) {
                            // Replace gaps wit their questions [...]
                            _newTextQuestion = _newTextQuestion.replace(
                                gap.match, // todo add class to strong later
                                `<strong>[...]</strong>`
                            );
                            // Replace gaps wit their answeres [...]
                            _newTextAnswer = _newTextAnswer.replace(
                                gap.match, //todo add class to strong, later
                                `<strong>${gap.answer}</strong>`
                            );
                        }
                    });
                    // _newTextQuestion.replace(_regex, _newTextQuestion)
                    // _newTextAnswer.replace(_regex, _newTextAnswer)
                    // Now replace remaining brackets with their answeres
                    _newTextQuestion = _newTextQuestion.replace(_regex, function (match, i) {
                        let text = match.replace(/{{c[0-9]+::/, '');
                        text = text.replace(/}}/, '');
                        text = text.trim();
                        // console.log('replacing', {i, match, text, _newTextQuestion});
                        return text;
                    });
                    // Now replace remaining brackets with their answeres
                    _newTextAnswer = _newTextAnswer.replace(_regex, function (match, i) {
                        let text = match.replace(/{{c[0-9]+::/, '');
                        text = text.replace(/}}/, '');
                        text = text.trim();
                        // console.log('replacing', {i, match, text, _newTextQuestion});
                        return text;
                    });
                    // console.log('Now replaced', {_newTextQuestion});
                    _questionRow.push(_newTextQuestion);
                    _answerRow.push(_newTextAnswer);
                    console.log('For one', {
                        _gaps,
                        row, col, rowIndex, colIndex, _originalText,
                        _newTextQuestion, _newTextAnswer, _cDetail,
                        _questionRow, _answerRow, tableQuestion, tableAnswer
                    });
                });
                tableQuestion.push(_questionRow);
                tableAnswer.push(_answerRow);
            });
            const cExists = existingItems.findIndex((_card, b) => {
                return key === _card.c_number;
            });
            if (cExists > -1) {
                const _existingItem = existingItems[cExists];
                cardsFormed.push({
                    ..._existingItem,
                    question: tableQuestion,
                    answer: tableAnswer,
                });
            } else {
                cardsFormed.push({
                    id: 0,
                    question: tableQuestion,
                    answer: tableAnswer,
                    c_number: key,
                    hash: Common.getRandomString(),
                    card_group:null,
                    answering_type: '',
                    deleted_at: '',
                    created_at: '',
                    updated_at: '',
                    old_question: '',
                    has_updated: false,
                    old_answer: '',
                    x_position: 0,
                    y_position: 0,
                });
            }

            console.log({_cDetail, key, tableQuestion, cardsFormed, tableAnswer});
        }


        console.log({cDetails, cardsFormed});
        console.groupEnd();
        return cardsFormed;
    }

    public static getCDetails(table: _TableItem) {
        console.groupCollapsed('getCDetails');
        const cDetails: _CTableDetail = {};

        const regex = /{{c[0-9]+::[^}}]*}}/mg;
        let m;

        table.forEach((row, rowIndex) => {
            row.forEach((question, colIndex) => {
                while ((m = regex.exec(question)) !== null) {
                    if (m.index === regex.lastIndex) {
                        regex.lastIndex++;
                    }
                    m.some((match, groupIndex) => {
                        // const hasHtml = TableHelper._hasHtml(match);
                        // if (hasHtml) {
                        //   alert('Error: HTML tag found in ' + match);
                        //   return true;
                        // }
                        const cId = TableHelper._getCId(match);
                        if (undefined === cDetails[cId]) {
                            cDetails[cId] = {
                                cId: cId,
                                gaps: [],
                            };
                        }
                        cDetails[cId].gaps.push({
                            match: match,
                            col: colIndex,
                            row: rowIndex,
                            cId: cId,
                            answer: TableHelper.getAnswerFromCurlyBrackets(match).trim(),
                        });

                        // console.log('Found match, group', {cDetails, groupIndex, match, hasHtml, question})
                        return false;

                    });
                }
            });
        });

        console.groupEnd();
        return cDetails;
    }

    private static replaceAllCurlyBracketsWithAnswers(str: string) {
        const regex = /{{c[0-9]+::[^}}]*}}/mg;
        let m;

        while ((m = regex.exec(str)) !== null) {
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }
            m.some((match, groupIndex) => {
                // const hasHtml = TableHelper._hasHtml(match);
                // if (hasHtml) {
                //   alert('Error: HTML tag found in ' + match);
                //   return true;
                // }
                const cId = TableHelper._getCId(match);
                // if (undefined === cDetails[cId]) {
                //   cDetails[cId] = {
                //     cId : cId,
                //     gaps: [],
                //   };
                // }
                // cDetails[cId].gaps.push({
                //   match : match,
                //   col   : colIndex,
                //   row   : rowIndex,
                //   cId   : cId,
                //   answer: TableHelper.getAnswerFromCurlyBrackets(match).trim(),
                // });

                // console.log('Found match, group', {cDetails, groupIndex, match, hasHtml, question})
                return false;

            });
        }
    }

    /**
     * Gets the answer from curly brackets
     * @param str e.gl {{c1:smoke}}
     * @return string Will return "smoke"
     */
    public static getAnswerFromCurlyBrackets(str: string): string {
        let text = str.replace(/{{c[0-9]+::/, '');
        text = text.replace(/}}/, '');

        return text;
    }

    public static _hasHtml(str: string): boolean {
        const regex = /<[a-z]*>/gm;
        let m;
        let has = false;
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

    public static _getCId = (str: string): string => {
        console.groupCollapsed("_getCId");
        const regex = /{{c[0-9]+/gm;
        let m;
        let cId = '';
        while ((m = regex.exec(str)) !== null) {
            // This is necessary to avoid infinite loops with zero-width matches
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }

            // The result can be accessed through the `m`-variable.
            m.forEach((match, groupIndex) => {
                cId = match.split('').splice(2).join('');
                // console.log('_getCId', {str, cId, groupIndex, match});
            });

        }
        console.groupEnd();
        return cId;
    }

}