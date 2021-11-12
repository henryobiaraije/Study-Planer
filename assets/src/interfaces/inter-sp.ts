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