export enum ENUM_NOTIFICATION_TYPE {
  SUCCESS = 'success',
  ERROR   = 'error',
  WARNING = 'warning'
}

export type _HoverNotification = {
  text: string,
  type: ENUM_NOTIFICATION_TYPE,
  show: boolean;
  key: string;
  additionalMessage?: string,
}

