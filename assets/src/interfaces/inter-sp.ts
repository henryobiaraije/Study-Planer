export interface _DeckGroup {
  id: string;
  name: string;
  trashed: boolean;
  tags: Array<_Tag>;
  created_at: string;
  updated_at: boolean,
}

export interface _Deck {
  id: number;
  name: string;
  trashed: boolean;
  deck_group: _DeckGroup,
  tags: Array<_Tag>;
  created_at: string;
  updated_at: boolean,
}

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
  user: any,
  tags: Array<_Tag>,
  all_tags: boolean,
  no_to_revise: number,
  no_of_new: number,
  no_on_hold: number,
  revise_all: boolean,
  study_all_new: boolean,
  study_all_on_hold: boolean,
}


export interface _Card extends _TimeStamps {
  id?: number;
  hash: string,
  question: string;
  card_group?: _CardGroup,
  answer: string;
  c_number: string;
  x_position?: number;
  y_position?: number;
  answering_type?: string;
}

export interface _CardGroup extends _TimeStamps {
  id: number;
  bg_image_id: number;
  deck: _Deck,
  group_type: string;
  tags: Array<_Tag>;
  cards: Array<_Card>,
  name: string,
  whole_question: string,
  scheduled_at: string,
  reverse: boolean;
  cards_count?: number;
  card_group_edit_url?: string;
}