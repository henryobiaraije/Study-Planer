import * as _ from "underscore";

// for text inputs
export interface InterFilterParam {
    text: string,
    type: string, // input type (text,date)
    title?: string, // html element title
    key: string,
    placeHolder: string,
    isDropDown: boolean,
    isDate: boolean,
    classes: string,
    naturallyStrict: boolean // set this to use (===) for matching instead of localeCompare,
    asDateRangeStart?: boolean,
    asDateRangeStop?: boolean,
}

// for select inputs
export interface InterFilterDropDownParam {
    key: string, // used to search during filtering. e.g. 'lname'
    text: string, // the v-model
    isDropDown: boolean,
    isDate: boolean,
    classes: string, // css classes to add
    dropDownCaption: string,  // Used only for drop down
    dropDownValues: Array<{ id: number, name: string, values: Array<any> }> // Used only for drop down
    naturallyStrict: boolean // set this to use (===) for matching instead of localeCompare,
    asDateRangeStart?: boolean,
    asDateRangeStop?: boolean,
}

export interface InterPagination {
    pageNow: number,
    pageStart: number,
    pageStop: number,
    text: number,
}

export interface InterSortParam {
    dis_class: object,
    vue: object; // Vue instance
    all: Array<any>, // The list e.g. users list
    allToUse: Array<any>, // The list e.g. users list to use, (to preserve the the main $all during filter)
    holdDisplay: Array<any>, // hold display chunk from $all
    holdAllDisplay222?: Array<any>, //// hold display chunk from $all
    pageNow222: number, //// hold display chunk from $all
    pagination222: Array<{ text: number, index: number }>, //// hold display chunk from $all
    showinText222?: "", //// hold display chunk from $all
    perPage: number, // per page
    pageNow: number, // e.g. 0, 1, 2
    sortAscended: Array<string>, // Keys to $all, e.g. username, email
    search: String,  // Typed text to search
    searchPage?: "this",  // can be 'this' or 'all'
    pagination: Array<InterPagination>,
    pageStart: number, // page index start
    pageStop: number, // page index stop
    disableRight: boolean, // disable right arrow
    disableLeft: boolean, // disable left arrow
    showing_text: string, // e.g. Showing 1 - 3 of 20 rows
    filterParams: Array<InterFilterParam | InterFilterDropDownParam>,
}

export class MpSortTable {

    /**
     * Stores true when all rows are selected by clicking on select all checkbox
     * @type {boolean}
     */
    public rowSelectAll: boolean               = false;
    /**
     * Stores true when all pages rows are selected by clicking on select all pages checkbox
     * @type {boolean}
     */
    public rowSelectAllPages: boolean          = false;
    /**
     * Set for spinner in (all page) search button
     * @type {boolean}
     */
    public isSearchingAll: boolean             = false;
    /**
     * Holds bulk action values from the bulk <select> element
     * @type {string}
     */
    public bulkAction: string                  = "";
    /**
     * Saves all modified ids, e.g. when a row is selected
     * @type {any[]}
     */
    public modifiedIds: Array<any>             = [];
    /**
     * Holds the type of search performed last (this or all)
     * @type {boolean}
     */
    public searchedPerformedLast: string       = "";
    /**
     * Set to filter strict
     * @type {boolean}
     */
    public filterStrick: boolean               = false;
    /** Set to show filter area */
    public showFilterArea: boolean             = false;
    /** Holds Searched Ids */
    public searchedIds: Array<number | string> = [];
    /** Holds Filtered Ids */
    public filteredIds: Array<number | string> = [];


    constructor(private sort: InterSortParam) {
        //console.log(this.sort.pagination222, "constroct++++++++++++++++++++++++", this.sort);
        sort.dis_class     = this;
        this.sort.pageStop = this.sort.perPage - 1;
        this.initAll();
        this.initAll222();
    }

    public canDisplayRow(item: any): boolean {
        if ((undefined !== item) && (null !== item) && (item.zz_show_as_filtered)) {
            return true;
        }
        return false;
    }

    public getCurrentRow() {
        return this.sort.holdAllDisplay222[this.sort.pageNow222];
    }

    public initAll() {
        this.initPagination();
        this.sort.holdDisplay = this.getPageArray();
    }

    public initAll222() {
        this.getAllPageArray222();
        this.calcShowingText222();
    }

    public addFilter(param: InterFilterParam) {
        this.sort.filterParams[this.sort.filterParams.length] = param;
    }

    public addFilterDropDown(param: InterFilterDropDownParam) {
        this.sort.filterParams[this.sort.filterParams.length] = param;
    }

    public initPagination() {
        // init pagination
        let all            = this.sort.allToUse;
        let per_page       = this.sort.perPage;
        let page_now       = this.sort.pageNow;
        let length         = 0;
        let numb_of_pages  = Math.ceil(all.length / per_page);
        let page_now_start = page_now - 2;
        page_now_start     = (page_now_start < 0) ? 0 : page_now_start;
        let page_now_stop  = page_now + 3;
        page_now_stop      = (page_now_stop < 5) ? 5 : page_now_stop;
        page_now_stop      = (page_now_stop > numb_of_pages) ? numb_of_pages : page_now_stop;

        this.sort.disableLeft  = ((page_now - 5) < 0);
        this.sort.disableRight = ((page_now + 2) > page_now_stop);

        // page_now_stop = () //
        // numb_of_pages     = (numb_of_pages < 3) ? 3 : numb_of_pages;
        //console.log({page_now_start, page_now_stop, numb_of_pages});

        let pagination: Array<InterPagination> = [];

        for (let a: number = page_now_start; a < page_now_stop; a++) {
            if (a === 0) {
                pagination[pagination.length] = {
                    text     : (a + 1),
                    pageNow  : a,
                    pageStart: a,
                    pageStop : (per_page - 1),
                };
            } else {
                pagination[pagination.length] = {
                    text     : (a + 1),
                    pageNow  : a,
                    pageStart: Math.floor((a * per_page)),
                    pageStop : Math.floor((((a * per_page) + per_page) - 1)),
                };
            }
            /*
              0 - 3
              4 - 7
              8 - 11
              12 - 15
              16 - 19
             */
        }
        this.sort.pagination = pagination;
    }

    public initPagination222() {
        let dis = this;

        let hold       = this.sort.holdAllDisplay222;
        let pageNow    = this.sort.pageNow222;
        let totalLengh = this.sort.holdAllDisplay222.length;
        let perPage    = this.sort.perPage;
        let numOfLis   = 5;

        if (pageNow > hold.length) {
            pageNow = (hold.length - 1);
        }
        if (pageNow < 0) {
            pageNow = 0;
        }

        let startFrom = 0;
        let stopAt    = 0;
        if (pageNow === 0) {
            stopAt = numOfLis;
        } else {
            startFrom = (pageNow - 2);
            startFrom = (startFrom < 0) ? 0 : startFrom;
            stopAt    = (pageNow + 3);
            stopAt    = (stopAt < 5) ? 5 : stopAt;

            if (stopAt > hold.length) {
                stopAt = (hold.length);
            }
        }

        if ((hold.length < perPage)) {
            if (stopAt > hold.length) {
                stopAt = hold.length;
            }
        }

        dis.sort.pagination222 = [];
        for (let a = startFrom; a < stopAt; a++) {
            dis.sort.pagination222.push({
                index: a,
                text : (a + 1)
            });
        }
        // conditions for disable left
        if ((this.sort.pageNow222 - 5) < 0) {
            this.sort.disableLeft = true;
        } else {
            this.sort.disableLeft = false;
        }
        // conditions for disable right
        if ((this.sort.pageNow222 + 5) > (hold.length - 1)) {
            this.sort.disableRight = true;
        } else {
            this.sort.disableRight = false;
        }
        this.reset();
    }

    public inputFilterSomthingTyped(resetTable = false) {
        let dis            = this;
        let somethingTyped = false;
        if (resetTable) {
            this.sort.search = "";
            this.inputSearchNow();
        }
        this.sort.filterParams.forEach((value: InterFilterParam, index, array) => {
            let valueFromFilterInput = value.text.toLowerCase();
            //console.log({valueFromFilterInput});
            if (valueFromFilterInput.trim().length > 0) {
                somethingTyped = true;
            }
        });
        if (!somethingTyped) {
            //console.log("Reset Filter now");
            this.inputSearchNow();
        }

    }

    public calcShowingText222() {
        if ((undefined !== this.sort.holdAllDisplay222) && (this.sort.holdAllDisplay222.length > 0)) {
            let textStart = 0;
            if (this.sort.pageNow222 === 0) {
                textStart = (this.sort.pageNow222 + 1);
            } else {
                textStart = (this.sort.pageNow222 * this.sort.perPage) + 1;
            }
            let textEnd = textStart + this.sort.perPage - 1;
            let total   = ((this.sort.holdAllDisplay222.length - 1) * this.sort.perPage);
            total += this.sort.holdAllDisplay222[(this.sort.holdAllDisplay222.length - 1)].length;
            //console.log({textStart, textEnd});
            if (textEnd > total) {
                textEnd = total;
            }
            //@ts-ignore
            this.sort.showinText222 = `Showing ${textStart} to ${textEnd} of ${total}`;
        } else {
            //@ts-ignore
            this.sort.showinText222 = `Showing 0 Results`;
        }
    }

    private getAllPageArray222() {
        let arr   = [];
        let count = 0;
        let hold  = [];
        let dis   = this;

        this.sort.allToUse.forEach((elem, index) => {
            if (elem.zz_show_as_filtered === true) {
                hold.push(elem);
                count++;
                if (count === dis.sort.perPage) {
                    count = 0;
                    arr.push(hold);
                    hold = [];
//          //console.log(dis.sort.pagination222, "++++++++++++++++++++++");
                }
            }
        });
        if (hold.length > 0) {
            arr.push(hold);
        }

        this.sort.holdAllDisplay222 = arr;
        // console.log({arr, sort: dis.sort, allToUse: dis.sort.allToUse});

        this.initPagination222();
        this.calcShowingText222();

//    this.sort.showing_text = `Showing ${(page_start + 1)} - ${(page_stop + 1)} of ${this.sort.allToUse.length}`;
    }

    private getPageArray() {
        let arr        = [];
        let page_now   = this.sort.pageNow;
        let per_page   = this.sort.perPage;
        let page_start = this.sort.pageStart;
        let page_stop  = this.sort.pageStop;
        let max        = (page_stop + 1);
        //console.log({page_start, page_stop});
        //@ts-ignore
        for (let a: number = page_start; a <= page_stop; a++) {
            // //console.log({page_start, page_stop, max, a});
//      //console.log("==================", this.sort.allToUse[a]);
            if (undefined !== this.sort.allToUse[a]) {
                if (this.sort.allToUse[a]["zz_show_as_filtered"] === true) {
                    arr[arr.length] = this.sort.allToUse[a];
                }
            }
        }

        this.sort.showing_text = `Showing ${(page_start + 1)} - ${(page_stop + 1)} of ${this.sort.allToUse.length}`;
        return arr;
    }

    private resetSort() {
        this.sort.holdDisplay = this.getPageArray();
    }

    private reset() {
        // @ts-ignore
        this.sort.vue.reset();
    }

    public clickSelectRow(row_id) { //zz_bulk_selected
//    this.sort.holdDisplay[row_id].zz_bulk_selected = !this.sort.holdDisplay[row_id].zz_bulk_selected;
        this.reset();
    }

    public clickSelectRow222(db_id) { //zz_bulk_selected
        if (_.indexOf(this.modifiedIds, db_id) < 0) {
            this.modifiedIds.push(db_id);
        }
        //console.log("modified ids = ", this.modifiedIds, {db_id});
//    this.sort.holdAllDisplay222[this.sort.pageNow222][row_id].zz_bulk_selected =
//      !this.sort.holdAllDisplay222[this.sort.pageNow222][row_id].zz_bulk_selected;
        this.reset();
    }

    public clickSelectRowAll(row_id) { //zz_bulk_selected
        let hold          = this.sort.holdDisplay;
        this.rowSelectAll = !this.rowSelectAll;
        hold.forEach((value, index, array) => {
            value.zz_bulk_selected = this.rowSelectAll;
        });
        this.reset();
    }

    public clickSelectRowAll222() { //zz_bulk_selected
        let hold        = this.sort.holdAllDisplay222[this.sort.pageNow222];
        let dis         = this;
        dis.modifiedIds = [];
        hold.forEach((value, index, array) => {
            value.zz_bulk_selected = dis.rowSelectAll;
            if (dis.rowSelectAll) {
                dis.modifiedIds.push(value.id);
            }
        });
        this.reset();
    }

    public clickSelectRowAllAllPages222() { //zz_bulk_selected
        let hold        = this.sort.holdAllDisplay222;
        let dis         = this;
        dis.modifiedIds = [];
        hold.forEach((value, index, array) => {
            value.forEach((vvv) => {
                vvv.zz_bulk_selected = dis.rowSelectAllPages;
                if (dis.rowSelectAllPages) {
                    dis.modifiedIds.push(vvv.id);
                }
            });
        });
        this.reset();
    }

    public clickFilterNow() {
        let hold = this.sort.allToUse;

        let keys: Array<InterFilterParam | InterFilterDropDownParam> = this.sort.filterParams;


        //console.log({keys});

        hold.filter((compare, index) => {
            //console.group("keys");
            let allKeyValuesMatch: boolean = true;
            let someValueInInput           = false; // set to true if there is a value in the input fields
            this.sort.filterParams.forEach((value: InterFilterParam, index, array) => {
                let keyToUse             = value.key;
                let valueFromAll: string = compare[keyToUse].toLowerCase();
                let valueFromFilterInput = value.text.toLowerCase();
                /** Here use localeCompare if you want loose comparison
                 * Or Use === for strict comparism
                 */
                if (valueFromFilterInput.trim().length > 0) {
                    someValueInInput = true; // indicate the something was typed in the input
                }
                if (!(valueFromAll === valueFromFilterInput)) {
                    allKeyValuesMatch = false;
                }
                //console.log({keyToUse, valueFromAll, valueFromFilterInput});
            });

            if (!allKeyValuesMatch) {
                if (someValueInInput) {
                    hold[index].zz_show_as_filtered = false;
                }
            } else {
                hold[index].zz_show_as_filtered = true;
            }
            //console.log({allKeyValuesMatch});
            //console.groupEnd();
            let entries = Object.entries(compare);

            //console.log({index, compare});
        });
        this.initAll222();
    }

    public clickFilterNow222() {
        let hold                                                     = this.sort.allToUse;
        let dis                                                      = this;
        let keys: Array<InterFilterParam | InterFilterDropDownParam> = this.sort.filterParams;
        //console.log({keys});
        this.sort.pageNow222                                         = 0;
        // reset searchedIds and also clear search
        this.sort.search                                             = "";
        this.searchedIds                                             = [];
        this.filteredIds                                             = [];

        hold.filter((holdValue, index) => {
            //console.group("keys");
            let debug                      = [];
            let dis                        = this;
            let allKeyValuesMatch: boolean = true;
            let someValueInInputGeneral    = false; // set to true if there is a value in the input fields
            debug.push("Starting...");
            debug.push({
                holdValue, index
            });
            let ddddloop = [];
            this.sort.filterParams.forEach((value: InterFilterParam | InterFilterDropDownParam, index, array) => {
                let keyToUse             = value.key;
                let valueFromAll: string = holdValue[keyToUse].toLowerCase();
                let valueFromFilterInput = value.text.toLowerCase();
                let typed                = false;
//        someValueInInput = (valueFromFilterInput.trim().length > 0);
                if (someValueInInputGeneral === false) {
                    if ((valueFromFilterInput.trim().length > 0) === true) {
                        someValueInInputGeneral = true;
                    }
                }
                if ((valueFromFilterInput.trim().length > 0) === true) {
                    typed = true;
                }

                ddddloop.push("************* New Param ***************", {
                    allKeyValuesMatch, keyToUse, valueFromAll, valueFromFilterInput, someValueInInputGeneral, typed
                });

                /** Here use localeCompare if you want loose comparison
                 * Or Use === for strict comparism
                 */

                if (value.isDate) {
                    ddddloop.push("Is Date");
                    let dateFromAll   = new Date(valueFromAll);
                    let dateFromInput = new Date(valueFromFilterInput);
                    if ((dateFromAll.getTime() === dateFromAll.getTime()) && (dateFromInput.getTime() === dateFromInput.getTime())) {
                        ddddloop.push(["Both are VALID dates."]);
                        ddddloop.push({dateFromAll, dateFromInput});
                        if (value.asDateRangeStart) {
                            ddddloop.push(["As Date Range Start", "dateFromInput <= dateFromAll = ", dateFromInput >= dateFromAll]);
                            if (!(dateFromInput <= dateFromAll)) {
                                allKeyValuesMatch = false;
                                ddddloop.push("!dateFromInput >= dateFromAll");
                            }
                        } else {
                            ddddloop.push("As Date Range Stop");
                            if (!(dateFromInput >= dateFromAll)) {
                                allKeyValuesMatch = false;
                                ddddloop.push("!dateFromInput <= dateFromAll");
                            }
                        }
                    } else {
                        ddddloop.push(["Invaid date."]);
                    }
                } else {
                    ddddloop.push("NOT Date");
                    if (dis.filterStrick || value.naturallyStrict) {
                        ddddloop.push([
                            "Filter Strinct",
                            {
                                filterStrick   : dis.filterStrick,
                                naturallyStrict: value.naturallyStrict
                            }
                        ]);
                        if (valueFromAll === valueFromFilterInput) {
                            ddddloop.push([
                                "valueFromAll === valueFromFilterInput. allKeyValuesMatch = ", {valueFromAll, valueFromFilterInput},
                                "allKeyValuesMatch now === ", {allKeyValuesMatch}
                            ]);
                        } else {
                            ddddloop.push(["valueFromAll !== valueFromFilterInput", {valueFromAll, valueFromFilterInput}]);
                            if (typed) {
                                ddddloop.push([
                                    "typed is TRUE. Set to False.",
                                    "Typed length = ", valueFromFilterInput.trim().length, {typed}
                                ]);
                                allKeyValuesMatch = false;
                            }
                        }
                    } else {
                        if (!(valueFromAll.toString().toLocaleString().indexOf(valueFromFilterInput) > -1)) {
                            allKeyValuesMatch = false;
                            ddddloop.push("LcaleComare failed.");
                        }
                        ddddloop.push([
                            "NOT Strinct. ", {valueFromAll, valueFromFilterInput}, "Compairing = ",
                            valueFromAll.toString().toLocaleString().indexOf(valueFromFilterInput), {allKeyValuesMatch}
                        ]);
                    }
//        //console.log({keyToUse, value, valueFromAll, valueFromFilterInput, allKeyValuesMatch, holdValue});
                }
                ddddloop.push(["Endingggggggggggggggggg", {allKeyValuesMatch}]);
            });
            debug.push({ddddloop});
            if (!allKeyValuesMatch) {
                debug.push(["allKeyValuesMatch is FALSE. None matched for hold holdvalue = ", {holdValue}]);
                if (someValueInInputGeneral) {
                    debug.push("Somthing is typed. SEtting zz_show_as_filtered to false");
                    hold[index].zz_show_as_filtered = false;
                } else {
                    debug.push(["Nothing typed for holdvalue ", {holdValue}]);
                }
            } else {
                debug.push("allKeyValuesMatch is TRUE. set zz_show_as_filtered to true");
                hold[index].zz_show_as_filtered = true;
                dis.filteredIds.push(hold[index].id);
            }
            //console.log(debug);
            //console.groupEnd();

        });
        this.sort.pageNow222 = 0;
        this.initAll222();
        this.reset();
    }

    /**
     * Click Event: Sort Column
     *
     * @param {string} word
     */
    public clickSortCol(word: string) {
        let hold          = this.sort.holdDisplay;
        let sort_assended = this.sort.sortAscended;
        let desc          = false;
        for (let a = 0; a < sort_assended.length; a++) {
            if (sort_assended[a] === word) {
                desc = true;
                sort_assended.splice(a, 1);
            }
        }
        if (!desc) {
            sort_assended[sort_assended.length] = word;
        }
        hold.sort((a, b) => {
            let a_word = a[word];
            let b_word = b[word];
            // let side1  = a_ word - b_ word;
            // d - a_word;

            let side1 = a_word.toLocaleString().localeCompare(b_word.toLocaleString());
            let side2 = b_word.toLocaleString().localeCompare(a_word.toLocaleString());
            //console.log({word, a, b, a_word, b_word, side1, side2, desc});
            if (desc) {
                return side1;
            } else {
                return side2;
            }
        });
    }

    public clickSortCol222(word: string) {
        let hold: Array<any> = this.sort.holdAllDisplay222[this.sort.pageNow222];
        let sort_assended    = this.sort.sortAscended;
        let desc             = false;
        for (let a = 0; a < sort_assended.length; a++) {
            if (sort_assended[a] === word) {
                desc = true;
                sort_assended.splice(a, 1);
            }
        }
        if (!desc) {
            sort_assended[sort_assended.length] = word;
        }
        hold.sort((a, b) => {
            let a_word = a[word];
            let b_word = b[word];
            // let side1  = a_ word - b_ word;
            // d - a_word;

            let side1 = a_word.toLocaleString().localeCompare(b_word.toLocaleString());
            let side2 = b_word.toLocaleString().localeCompare(a_word.toLocaleString());
            //console.log({word, a, b, a_word, b_word, side1, side2, desc});
            if (desc) {
                return side1;
            } else {
                return side2;
            }
        });
    }

    public inputSearchNow3() {
        //@ts-ignore
        if (this.sort.searchPage === "all") {
            return false;
        }
        let text      = this.sort.search.toLowerCase();
        let to_search = text.toLowerCase();
        let hold      = this.sort.holdDisplay;

        hold.filter((compare, index) => {
            let entries = Object.entries(compare);
            let exists  = false;
            for (let [key, value] of entries) {

                if (_.isString(value)) {
                    value   = value.toLowerCase();
                    //console.log({key, value, to_search});
                    let exx = value.toString().toLocaleString().indexOf(to_search) > -1;
                    //
                    if (exx) {
                        exists = true;
                        //console.log({key, value, entries, exists, index});
                        break;
                    }
                }
            }
            if (exists === true) {
                hold[index].zz_show_as_filtered = true;
            } else {
                hold[index].zz_show_as_filtered = false;
            }
        });
        this.reset();
        //console.log({hold});
    }

    public inputSearchNow(force = false) {
        let dis = this;
        if (false === force) { // to allow when header summeries is clicked
            //@ts-ignore
            if (this.sort.searchPage === "all" && (this.sort.search.trim().length > 0)) {
                console.log("Regurning 78");
                return false;
            }
        }

        // set to this only when something is typed in the search box
        this.searchedPerformedLast = (this.sort.search.trim().length > 0) ? "this" : "";
        // reset filtered ids and searched ids and also filter params
        this.searchedIds           = [];
        this.filteredIds           = [];
//    this.clickResetFilterParams();
        // reset everything once search text length is 0
        if (this.sort.search.trim().length < 1) {
            this.sort.allToUse.forEach((elem) => {
                elem.zz_show_as_filtered = true;
            });
            this.initAll222();
            return false;
        }
        let text      = this.sort.search.toLowerCase();
        let to_search = text.toLowerCase();
        let hold      = this.sort.holdAllDisplay222[this.sort.pageNow222];

        hold.filter((compare, index) => {
            let entries = Object.entries(compare);
            let exists  = false;
            for (let [key, value] of entries) {

                if (_.isString(value)) {
                    value   = value.toLowerCase();
                    //console.log({key, value, to_search});
                    let exx = value.toString().toLocaleString().indexOf(to_search) > -1;
                    //
                    if (exx) {
                        exists = true;
                        //console.log({key, value, entries, exists, index});
                        break;
                    }
                }
            }
            if (exists === true) {
                hold[index].zz_show_as_filtered = true;
                dis.searchedIds.push(hold[index].id);
            } else {
                hold[index].zz_show_as_filtered = false;
            }
        });
        this.reset();
        //console.log({hold});
    }

    public inputSearchNowAllPages() {
        if (this.sort.searchPage === "this") {
            this.inputSearchNow();
            return false;
        }
        this.searchedPerformedLast = "all";
        this.isSearchingAll        = true;
        let dis                    = this;
        this.searchedIds           = [];
        setTimeout(() => {
            let text      = dis.sort.search.toLowerCase();
            let to_search = text.toLowerCase();
            let hold      = dis.sort.allToUse;

            hold.filter((compare, index) => {
                let entries = Object.entries(compare);
                let exists  = false;
                for (let [key, value] of entries) {

                    if (_.isString(value)) {
                        value   = value.toLowerCase();
                        //console.log({key, value, to_search});
                        let exx = value.toString().toLocaleString().indexOf(to_search) > -1;
                        //
                        if (exx) {
                            exists = true;
                            //console.log({key, value, entries, exists, index});
                            break;
                        }
                    }
                }
                if (exists === true) {
                    hold[index].zz_show_as_filtered = true;
                    dis.searchedIds.push(hold[index].id);
                } else {
                    hold[index].zz_show_as_filtered = false;
                }
            });
            dis.initAll222();
            dis.isSearchingAll = false;
            dis.reset();
        }, 500);
    }

    public searchFor(key) {
        //@ts-ignore
        this.sort.searchPage = "all";
        this.sort.search     = key;
        this.inputSearchNowAllPages();
    }

    public refreshEverything() {
        let dis  = this;
        let hold = dis.sort.allToUse;
        hold.filter((compare, index) => {
            hold[index].zz_show_as_filtered = true;
        });
        this.sort.pageNow222 = 0;
        this.sort.search     = "";
        this.sort.searchPage = "this";
        this.initAll222();
    }

    public clickGoToPage(page_now, page_start, page_stop) {
        this.sort.pageNow   = page_now;
        this.sort.pageStart = page_start;
        this.sort.pageStop  = page_stop;
        this.initPagination();
        this.resetSort();
        this.reset();
    }

    public clickGoToPage222(index) {
        this.sort.pageNow222 = index;
        this.initPagination222();
        this.calcShowingText222();
        this.reset();
    }

    public clickToLessPage() {
        if (!this.sort.disableLeft) {
            let all        = this.sort.allToUse;
            let per_page   = this.sort.perPage;
            let page_now   = this.sort.pageNow;
            let page_to_go = page_now - 5;
            if (page_to_go < 0) {
                page_to_go = 0;
            }
            this.sort.pageNow = page_to_go;
            this.initPagination();
        }
    }

    public clickResetFilterParams() {
        this.inputFilterSomthingTyped(true);
        this.sort.filterParams.forEach((value) => {
            value.text = "";
        });
        this.reset();
    }

    public clickShowFilterArea() {
        this.showFilterArea = !this.showFilterArea;
    }

    public clickToMorePage() {
        if (!this.sort.disableRight) {
            let all           = this.sort.allToUse;
            let per_page      = this.sort.perPage;
            let page_now      = this.sort.pageNow;
            let numb_of_pages = Math.ceil(all.length / per_page);
            let page_to_go    = page_now + 5;
            if (page_to_go > numb_of_pages) {
                page_to_go = numb_of_pages;
            }
            this.sort.pageNow = page_to_go;
            this.initPagination();
        }
    }

    public clickToMorePage222() {
        if (!this.sort.disableRight) {
            this.sort.pageNow222 = (this.sort.pageNow222 + 5);
            if (this.sort.pageNow222 > (this.sort.holdAllDisplay222.length - 1)) {
                this.sort.pageNow222 = (this.sort.holdAllDisplay222.length - 1);
            }
            this.initPagination222();
            this.calcShowingText222();
            this.reset();
        }
    }

    public clickToLessPage222() {
        //@ts-ignore
        if (!this.sort.disableLeft) {
            this.sort.pageNow222 = (this.sort.pageNow222 - 5);
            if (this.sort.pageNow222 < 0) {
                this.sort.pageNow222 = 0;
            }
            this.initPagination222();
            this.calcShowingText222();
            this.reset();
        }


    }

    /**
     * Change Per page variable
     */
    public inputChangePerPage() {
        //console.log("checking");
        this.sort.perPage = parseInt(String(this.sort.perPage));
        this.initPagination();
        this.resetSort();
        this.clickGoToPage(0, 0, (this.sort.perPage - 1));
    }

    public inputChangePerPage222() {
        //console.log("checking1", this.sort.pageNow);
        this.sort.pageNow222 = 0;
        this.sort.perPage    = parseInt(String(this.sort.perPage));
        //console.log("checking2", this.sort.pageNow);
        this.initAll222();
    }

}
