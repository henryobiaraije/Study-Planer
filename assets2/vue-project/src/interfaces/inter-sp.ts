export type  _TableItem = Array<_Row>;
export type _Row = Array<string>;

export interface _DeckGroup {
    id: string;
    name: string;
    trashed: boolean;
    tags: Array<_Tag>;
    created_at: string;
    updated_at: boolean,
    decks: Array<_Deck>;
}

export interface _Deck {
    id: number;
    name: string;
    trashed: boolean;
    card_groups: _CardGroup[],
    tags: Array<_Tag>;
    created_at: string;
    updated_at: boolean,
    topics: Array<_Topic>;
    studies?: Array<_Study>;
}

export interface _Topic {
    id: number;
    name: string;
    trashed: boolean;
    deck: _Deck,
    created_at: string;
    updated_at: boolean,
    tags: Array<_Tag>;
    card_groups: Array<_CardGroup>;
    studies?: Array<_Study>;
}

export type _TopicNew = Partial<_Topic & Pick<_Topic, 'name' | 'deck' | 'tags'>>;

export interface _Tag {
    id: string;
    name: string;
    trashed: boolean;
    created_at: string;
    updated_at: boolean,
}

interface _TimeStamps {
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface _Study {
    id?: number,
    deck: _Deck,
    topic?:_Topic,
    user?: any,
    tags: Array<_Tag>,
    tags_excluded: Array<_Tag>,
    all_tags: boolean,
    no_to_revise: number,
    no_of_new: number,
    no_on_hold: number,
    revise_all: boolean,
    study_all_new: boolean,
    study_all_on_hold: boolean,
}

export type _QuestionType = string | _TableItem | _ImageItem;

export interface _Card extends _TimeStamps {
    id?: number;
    hash: string,
    question: any;
    card_group: _CardGroup,
    answer: any; //string | _TableItem;
    c_number: string;
    x_position?: number;
    y_position?: number;
    answering_type?: string;
    has_updated?: boolean;
    old_question?: any;
    old_answer?: any;
}


export enum IMAGE_DISPLAY_TYPE {
    HIDE_ALL_ASK_ONE = 'hide_all_ask_one',
    HIDE_ALL_ASK_ALL = 'hide_all_ask_all',
    HIDE_ONE_ASK_ONE = 'hide_one_ask_one'
}

export interface _CardGroup extends _TimeStamps {
    id: number;
    bg_image_id: number;
    deck: _Deck,
    topic: _Topic,
    collection: _Collection,
    group_type: string;
    card_type: string;
    tags: Array<_Tag>;
    cards: Array<_Card>,
    name: string,
    image_type?: IMAGE_DISPLAY_TYPE,
    whole_question: any,
    scheduled_at: string,
    reverse: boolean;
    cards_count?: number;
    card_group_edit_url?: string;
    //
    bg_image_url: string;
}

export interface _ImageItem {
    w: number;
    h: number;
    boxes: Array<_ImageBox>;
    hash: string;
}

export interface _ImageBox {
    top: number;
    left: number;
    h: number;
    w: number;
    show: boolean;
    asked: boolean;
    hide: boolean;
    angle: number;
    imageUrl: string;
    hash: string;
}

export interface _Localize {
    serverUrl: string;
    actionString: string;
    nonce: string;
    localize: {
        default_bg_image: number,
        nonce: string;
        page_all_cards: string;
        page_basic_card: string;
        page_deck_groups: string;
        page_decks: string;
        page_gap_card: string;
        page_settings: string;
        page_table_card: string;
        page_tags: string;
        page_topics: string;
        site_url: string;
    }
    user_study_deck_id: number;
    user_study: _Study;
}

export interface _Collection {
    id: number;
    name: string;
    trashed: boolean;
    created_at: string;
    updated_at: boolean,
}

export interface _AssignTopic {
    /**
     * The topic to assign the cards to.
     */
    topicToAssign: _Topic,
    /**
     * Topics to read from.
     */
    topic: null | _Topic,
    group: null | _DeckGroup,
    deck: null | _Deck,
    cardTypes: Array<CardType>,
    individualCards: Array<_Card>,
    selectedCards: Array<_Card>,
}

export type CardType = 'basic' | 'gap' | 'table' | 'image';
