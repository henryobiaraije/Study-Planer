export interface _DeckGroup {
  id: string;
  name: string;
  trashed: boolean;
  tags: Array<_Tag>;
  created_at: string;
  updated_at: boolean,
}

export interface _Deck {
  id: string;
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
  created_at: string;
  updated_at: string;
  deleted_at: string;
}

export interface _BasicCard extends _TimeStamps {
  id: string;
  deck: _Deck;
  tags: Array<_Tag>;
  question: string;
  answer: string;
  x_position: number;
  y_position: number;
}

export interface _CardGroup extends _TimeStamps {
  id: number;
  bg_image: number;
  deck: _Deck,
  group_type: string;
  tags : Array<_Tag>;
  name: string,
  whole_question: string,
  scheduled_at: string,
  reverse: boolean;
}